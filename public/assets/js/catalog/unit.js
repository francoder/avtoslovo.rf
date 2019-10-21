(function($){
	var viewport = $("[role=\"viewport\"]"),
			table = $("[role=\"table\"]"),
			image = viewport.find("img.dragger"),
			loaded = false;
	
	viewport.on("resizeElement", function(){
		var parent = $(this).parent();
		
		if (image.length) {
			var originalWidth = image.prop("naturalWidth");
			var originalHeight = image.prop("naturalHeight");
			
			var c = originalWidth * 100 / parent.width();
			
			c = c/100;
			
			viewport.css("width", (originalWidth/c)+"px");
			viewport.css("height", (originalHeight/c)+"px");
		} else {
			viewport.css("width", "100%");
			viewport.css("height", "100%");
		}
	});
        
        var changeLineColor = function(checker, isCheck) {
            var tr = checker.parent('td').parent('tr');
            
            if (isCheck) 
                tr.addClass('selected');
            else
                tr.removeClass('selected');
            
        }
        
        var findNumbers = function(val, check) {
            if (check)
                $('.highlight[data-name="'+val+'"]').addClass('active');
            else 
                $('.highlight[data-name="'+val+'"]').removeClass('active');
        };
        
        $('.checker').click(function(e) {
            var val = $(this).val(),
                isCheck = $(this).prop('checked');
           
           changeLineColor($(this), isCheck);
           findNumbers(val, isCheck);
        });
	
	// viewport.on("mousewheel", function(event, delta){
	// 	if (!loaded) return;
	//
	// 	rescaleImage(delta);
	//
	// 	return false;
	// });
	
	viewport.find(".highlight").on("click", function(){
		var itemTop = table.find("tbody tr[data-image=\""+$(this).data("name")+"\"]").offset().top;
		
                var val = $(this).data("name");
                /*
		$("html, body").stop().animate({
			scrollTop: itemTop
		}, 333);
                */
               $('.checker[value="'+val+'"]').click();
	});
	
	viewport.find(".highlight").hover(function(){
		$(this).addClass("hover");
		table.find("tbody tr[data-image=\""+$(this).data("name")+"\"]").addClass("hover");
	}, function(){
		$(this).removeClass("hover");
		table.find("tbody tr[data-image=\""+$(this).data("name")+"\"]").removeClass("hover");
	});
	
	table.find("tbody tr").hover(function(){
		viewport.find(".highlight[data-name=\""+$(this).data("image")+"\"]").addClass("hover");
	}, function(){
		viewport.find(".highlight[data-name=\""+$(this).data("image")+"\"]").removeClass("hover");
	});
	
	$(window).on("resize", function(){
		viewport.trigger("resizeElement");
	})
	
	image.on("load", function(){
		loaded = true;
		viewport.trigger("resizeElement");
		viewport.dragscrollable({dragSelector: ".dragger", acceptPropagatedEvent: false});
		rescaleImage(-100);
	});
	
	if (image.prop("complete")) image.trigger("load");
})(jQuery);

function prepareImage()
{
	var img = jQuery('img.dragger');
	
	var width = img.innerWidth();
	var height = img.innerHeight();

	img.attr('owidth', width);
	img.attr('oheight', height);

	jQuery('div.dragger').each(function(idx){
		var el = jQuery(this);
		el.attr('owidth', parseInt(el.css('width')));
		el.attr('oheight', parseInt(el.css('height')));
		el.attr('oleft', parseInt(el.css('margin-left')));
		el.attr('otop', parseInt(el.css('margin-top')));
	});
}

function rescaleImage(delta) {
	var img = jQuery('img.dragger');
		
	var original_width = img.attr('owidth');
	var original_height = img.attr('oheight');

	if (!original_width)
	{
		prepareImage();

		original_width = img.attr('owidth');
		original_height = img.attr('oheight');
	}

	var current_width = img.innerWidth();
	var current_height = img.innerHeight();

	var scale = current_width / original_width;

	var cont = jQuery('[role=\"viewport\"]');
		
	var view_width = parseInt(cont.css('width'));
	var view_height = parseInt(cont.css('height'));
		
	var minScale = Math.min(view_width / original_width, view_height / original_height);

	var newscale = scale + (delta / 10);
	if (newscale < minScale)
		newscale = minScale;

	if (newscale > 1)
		newscale = 1;

	var correctX = Math.max(0, (view_width - original_width*newscale) / 2);
	var correctY = Math.max(0, (view_height - original_height*newscale) / 2);

	img.attr('width', original_width*newscale);
	img.attr('height', original_height*newscale);
	img.css('margin-left', correctX + 'px');
	img.css('margin-top', correctY + 'px');

	jQuery('div.dragger').each(function(idx){
		var el = jQuery(this);
		el.css('margin-left', (el.attr('oleft')*newscale + correctX) + 'px');
		el.css('margin-top', (el.attr('otop')*newscale + correctY) + 'px');
		el.css('width', el.attr('owidth')*newscale + 'px');
		el.css('height', el.attr('oheight')*newscale + 'px');
	});
}