moment.locale('ru');
window.declOfNum = function(number, titles) {
	cases = [2, 0, 1, 1, 1, 2];
	return titles[ (number%100>4 && number%100<20)? 2 : cases[(number%10<5)?number%10:5] ];
};
window.number_format = function( number, decimals, dec_point, thousands_sep ) {	// Format a number with grouped thousands
	//
	// +   original by: Jonas Raoni Soares Silva (http://www.jsfromhell.com)
	// +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
	// +	 bugfix by: Michael White (http://crestidg.com)

	var i, j, kw, kd, km;

	// input sanitation & defaults
	if( isNaN(decimals = Math.abs(decimals)) ){
		decimals = 2;
	}
	if( dec_point == undefined ){
		dec_point = ",";
	}
	if( thousands_sep == undefined ){
		thousands_sep = ".";
	}

	i = parseInt(number = (+number || 0).toFixed(decimals)) + "";

	if( (j = i.length) > 3 ){
		j = j % 3;
	} else{
		j = 0;
	}

	km = (j ? i.substr(0, j) + thousands_sep : "");
	kw = i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + thousands_sep);
	//kd = (decimals ? dec_point + Math.abs(number - i).toFixed(decimals).slice(2) : "");
	kd = (decimals ? dec_point + Math.abs(number - i).toFixed(decimals).replace(/-/, 0).slice(2) : "");


	return km + kw + kd;
};
(function($){
	// back to top button
	$.fn.scrollToTop=function(){
		$(this).hide().removeAttr("href");
		if($(window).scrollTop()!="0"){
			$(this).fadeIn("slow")
		}
		var scrollDiv=$(this),
				fixedheader = $('.fixedheader');
		$(window).scroll(function(){
			if($(window).scrollTop()=="0"){
				$(scrollDiv).fadeOut("slow")
			}
			else
			{
				$(scrollDiv).fadeIn("slow")
			}
			if($(window).scrollTop() > "500"){
				fixedheader.addClass("show");
			}
			else
			{
				fixedheader.removeClass("show");
			}
		});
		$(this).click(function(){
			$("html, body").animate({scrollTop:0},"slow")
		})
	}
	$("#toTop").scrollToTop();
	
	// Phone masks
	var phoneInputs = $("[name=\"phone\"]");

	phoneInputs.mask("+7(999)999-99-99");
        
        var SPMaskBehavior = function (val) {
            var arr = val.split("-"),
                delim = "RRR";
            if (arr.length > 1)
            switch (arr[0].length) {
                case 3:
                    delim = "-";
                    break;
                case 4:
                    delim = "A-"
                    break;
                case 5:
                    delim = "AA-";
                    break;
                default:
                    delim = "";
                    break;
            } else {
                if ( arr[0].length === 5)
                    delim = "AA-";
            }
        
            return 'AAA'+delim+'AAAAAAAA';
          },
          spOptions = {
            onKeyPress: function(val, e, field, options) {
                console.log("val",val);
                field.mask(SPMaskBehavior.apply({}, arguments), options);
              },
            translation: {
              'R': {
                pattern: /[0-9,a-z,A-Z,-]/,
              },
            }
          };

        //$('input[name="frame"]').mask(SPMaskBehavior, spOptions);

        $('input[data-toggle="dropdown"]').keypress(function() {
            var menu = $(this).siblings('ul:visible');
            if (menu) {
                menu.dropdown("toggle");
            }
        });
        $('input[name="frame"]').mask("AAAAAAA");
        $('input[name="frame-number"]').mask("AAAAAAAAAA");
        
	$('.dropdown.hover').hover(function() {
		$(this).find('.dropdown-menu').stop(true, true).delay(200).fadeIn(500);
	}, function() {
		$(this).find('.dropdown-menu').stop(true, true).delay(200).fadeOut(500);
	});

	$("[role=\"feedback\"]").on("submit", function(event){
		event.preventDefault();

		var form = $(this),
				submit = form.find("[type=\"submit\"]");

		if( submit.attr("disabled") == "disabled" )
			return false

		submit.attr("disabled", "disabled")

		$.ajax({
			url: "/ajax?action=feedback",
			data: $(this).serialize(),
			method: "POST",
			dataType: "json"
		}).done(function(response){
			submit.removeAttr("disabled")

			if (response.error === true) {
				form.find(".errorsinp").removeClass("errorsinp");
				form.find("[name]").off("change keyup focus");
				form.find(".errors").remove();

				$.each(response.messages, function(){
					var field = form.find("[name=\""+this.field+"\"]");

					field.addClass("errorsinp");

					var error = $("<label class=\"errors\" for=\""+this.field+"\">"+this.message+"</label>")

					field.after(error);

					field.on("change keyup focus", function(){
						field.removeClass("errorsinp");
						error.remove()
						field.off("change keyup focus");
					});
				});
				return
			}
			
			form.after("<p class=\"feedbackSuccess\">Мы получили ваш запрос и скоро с вами свяжемся.</p>")
			form.remove()
		});

		return false
	});
        
        $("[role=\"picking\"]").on("submit", function(event){
		event.preventDefault();

		var form = $(this),
				submit = form.find("[type=\"submit\"]");

		if( submit.attr("disabled") == "disabled" )
			return false

		submit.attr("disabled", "disabled")

		$.ajax({
			url: "/ajax?action=picking",
			data: $(this).serialize(),
			method: "POST",
			dataType: "json"
		}).done(function(response){
			submit.removeAttr("disabled")

			if (response.error === true) {
				form.find(".errorsinp").removeClass("errorsinp");
				form.find("[name]").off("change keyup focus");
				form.find(".errors").remove();

				$.each(response.messages, function(){
					var field = form.find("[name=\""+this.field+"\"]");

					field.addClass("errorsinp");

					var error = $("<label class=\"errors\" for=\""+this.field+"\">"+this.message+"</label>")

					field.after(error);

					field.on("change keyup focus", function(){
						field.removeClass("errorsinp");
						error.remove()
						field.off("change keyup focus");
					});
				});
				return
			}

			$(".modal-body").html("<p class=\"feedbackSuccess\">Мы получили ваш запрос и скоро с вами свяжемся.</p>");
		});

		return false
	});

	$.fn.selectable = function(){
		this.each(function(){
			self = $(this);
			selfPlaceholder = self.data("placeholder")
			selflist = self.children("ul")
			selfvalue = self.children(".value")
			selfBtn = self.children("button")

			selflist.on("click", "li a", function(event){
				event.preventDefault()

				selfvalue.val($(this).data("value")).trigger("change");
			});

			selfvalue.on("change", function(){
				if (selfBtn.find("[role=\"title\"]").length) {
					var btnTitle = selfBtn.find("[role=\"title\"]");
				} else {
					var btnTitle = selfBtn;
				}

				console.log(btnTitle);

				if (selfvalue.val() == "") {
					btnTitle.html(selfPlaceholder)
				} else {
					var valueItem = selflist.find("[data-value=\""+selfvalue.val()+"\"]");

					if (valueItem.length) {
						btnTitle.html(valueItem.html())
					} else {
						selfvalue.val("")
						btnTitle.html(selfPlaceholder)
					}
				}
			});
		});
	}

	$("[rel=\"select\"]").selectable()

	var cart = $("[role=\"cart\"]"),
			cartModal = $("#addcart"),
			cartCounts = cartModal.find("[role=\"count\"]");

	cartCounts.each(function(){
		var self = $(this),
				up = self.find("[role=\"up\"]"),
				down = self.find("[role=\"down\"]"),
				val = self.find("[role=\"val\"]");

		up.on("click", function(){
			var value = parseInt(val.val());
			var max = parseInt(val.data("max"))

			value++;

			if (value > max) {
				val.val(max)
			} else {
				val.val(value)
			}

			val.trigger("change");
		})

		down.on("click", function(){
			var value = parseInt(val.val());

			value--;

			if (value < 1) {
				val.val(1)
			} else {
				val.val(value)
			}

			val.trigger("change");
		});

		val.on("change keyup", function(){
			var value = parseInt(val.val());

			var max = parseInt(val.data("max"))

			if (value < 1) {
				val.val(1)
			} else if (value > max) {
				val.val(max)
			}
		});
	});

	cart.on("set", function(event, count) {
		cart.find("[data-count]").data("count", count)
		cart.find("[data-count]").attr("data-count", count);
		cart.find("[role=\"text\"]").text("Корзина: "+count)
	});

	cartModal.find("[role=\"cancel\"]").on("click", function(event){
		event.preventDefault();

		cartModal.modal("hide");
	});

	cartModal.on("init", function(event, data){
		cartModal.find("[role=\"article\"]").text(data.article);
		cartModal.find("[role=\"article\"]").attr("href", "/catalog/parts?query="+data.article);
		cartModal.find("[role=\"brand\"]").text(data.brand);
		cartModal.find("[role=\"title\"]").text(data.title);

		cartModal.find("[role=\"price\"]").text(number_format(data.price, 0, ".", " ") + " руб.");
		cartModal.find("[role=\"finalPrice\"]").text(number_format(data.price, 0, ".", " ") + " руб.");

		cartModal.find("[name=\"count\"]").val(1)
		cartModal.find("[name=\"count\"]").attr("data-max", data.aviable)
		cartModal.find("[name=\"count\"]").data("max", data.aviable)

		cartModal.find("[name=\"count\"]").off("change.mnbs");
		cartModal.find("[name=\"count\"]").on("change.mnbs", function(){
			var count = parseInt($(this).val());

			cartModal.find("[role=\"cartCount\"]").text(count);
			cartModal.find("[role=\"finalPrice\"]").text(number_format(data.price*count, 0, ".", " ") + " руб.");
		});

		var delivery = moment();

		delivery.add(data.delivery, "days");

		cartModal.find("[role=\"delivery\"]").text(delivery.format("D MMMM"));

		cartModal.find("[name=\"code\"]").val(data.code);

		cartModal.modal("show");
	});

	cartModal.find("[role=\"form\"]").on("submit", function(){
		$.ajax({
			url: "/ajax?action=addToCartFull",
			data: $(this).serialize(),
			dataType: "json"
		}).done(function(response){
			if( response.error )
				return;

			var btn = $("[data-code=\""+cartModal.find("[name=\"code\"]").val()+"\"]");
			
			btn.addClass("gray")
			btn.text("В корзине")

			cart.trigger("set", response.data.count);

			cartModal.modal("hide");
		});

		return false;
	});

	cartModal.on("hidden.bs.modal", function(){
		$(this).find("[name=\"count\"]").val(0).trigger("change");
		
		var btn = $("[data-code=\""+cartModal.find("[name=\"code\"]").val()+"\"]");
		
		btn.removeAttr("disabled")
	});

	$("body").on("click", "[role=\"addToBasket\"]", function(event) {
		event.preventDefault()

		var self = $(this)

		if (self.attr("disabled") == "disabled")
			return false;

		self.attr("disabled", "disabled")

		var cartData = self.data("cart")

		cartModal.trigger("init", cartData);
		return false;
		//
		// var code = self.data("code")
		//
		// $.ajax({
		// 	url: "/ajax?action=addToCart&code="+code,
		// 	dataType: "json"
		// }).done(function(response){
		// 	if( response.error )
		// 		return;
		//
		// 	self.addClass("gray")
		// 	self.text("В корзине")
		//
		// 	cart.trigger("set", response.data.count);
		// });
		//
		// return false;
	});

	$("body").on("click", "[role=\"removeFromCart\"]", function(event) {
		event.preventDefault()

		var self = $(this)

		if (self.attr("disabled") == "disabled")
			return false;

		self.attr("disabled", "disabled")

		var code = self.data("code")

		$.ajax({
			url: "/ajax?action=removeFromCart&code="+code,
			dataType: "json"
		}).done(function(response){
			if( response.error )
				return;

			self.addClass("gray")
			self.text("Удалено")
			window.location.reload()

			cart.trigger("set", response.data.count);
		});

		return false;
	});

	$('a[data-toggle="tooltip"]').tooltip({
		animated: 'fade',
		placement: 'right',
		html: true
	});

	var historyList = $("[role=\"historyList\"]");

	historyList.on("click", "[data-article]", function(){
		historyList.find("input").val($(this).data("article"));
	});
    fixedScroll($('.fixedheader'));
})(jQuery);


function fixedScroll(_item) {
    var item = $(_item[0]),
        coords = _item[0].getBoundingClientRect(),
        width = item.width(),
        marklist = $('.mark-container-header');
    
    $(document).scroll(function () {
        var docTop = $(document).scrollTop();
        if (docTop > 100 && !item.hasClass('visible')) {
            item.addClass('visible');
            marklist.addClass('show');
            marklist.removeClass('hide');
        } else if (docTop < 100 && item.hasClass('visible')) {
            item.removeClass('visible');
            marklist.addClass('hide');
            marklist.removeClass('show');

        }
/*
        if (docTop > 100 && !item.hasClass('fixed')) {
            item.addClass('fixed').css({ left: coords.left, top: 10, width: width });
        } else if (docTop < 100 && item.hasClass('fixed')) {
            item.removeClass('fixed').css({ left: 0, top: 0, width: 'auto' });
        }
        */
    });
}