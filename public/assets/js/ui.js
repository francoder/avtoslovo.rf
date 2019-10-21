(function($){
	var ajaxUrl = "/ajax";
	$.fn.MollyCitySelect = function() {
		this.each(function(){
			var self = $(this),
					selfInputCnt = self.find("[role=\"input\"]"),
					selfInput = selfInputCnt.find("input"),
					selfInputResults = selfInputCnt.find("[role=\"results\"]"),
					selfSelectedCnt = self.find("[role=\"selected\"]"),
					selfValueInput = self.find(".value");
			
			self.on("setCity", function(event, data) {
				
			});
			
			var inputTimeput = null;
			
			selfInput.on("keyup change", function(){
				var value = $(this).val()
				
				if (value.length > 0) {
					if (inputTimeput) clearTimeout(inputTimeput);
					inputTimeput = setTimeout(function(){
						$.ajax({
							url: ajaxUrl + "?action=citySearch",
							data: {
								city: value
							},
							dataType: "json"
						}).done(function(response){
							if (response.error === false) {
								if (response.data.words == value) {
									selfInputResults.empty();
									
									$.each(response.data.result.predictions, function(){
										selfInputResults.append("<li><a href=\"#\" data-place=\""+this.place_id+"\">"+this.description+"</a></li>");
									});
									
									selfInputCnt.addClass("open");
								}
							}
							console.log(response);
						});
					}, 333);
				} else {
					selfInputResults.empty();
					selfInputCnt.removeClass("open");
				}
			});
			
			selfInput.on("focus", function(){
				if (selfInputResults.children().length) selfInputCnt.addClass("open");
			});
			
			selfInput.on("blur", function(){
				setTimeout(function(){
					selfInputCnt.removeClass("open");
				},111);
			});
			
			selfInputResults.on("click", "a", function(event) {
				event.preventDefault();
				
				selfInputResults.empty();
				selfInputCnt.removeClass("open");
				
				selfValueInput.val($(this).data("place"));
				selfInputCnt.addClass("hide");
				selfSelectedCnt.removeClass("hide");
				
				selfSelectedCnt.find("[role=\"city\"]").html($(this).html());
			});
			
			selfSelectedCnt.find("[role=\"cancel\"]").on("click", function(event){
				event.preventDefault();
				
				selfInputCnt.removeClass("hide");
				selfSelectedCnt.addClass("hide");
				
				selfInput.val("");
				selfValueInput.val("");
				selfInputCnt.removeClass("open");
				selfInputResults.children().remove();
				
				selfInput.focus();
			});
			
			selfValueInput.on("change", function(){
				if (selfValueInput.val() == "") {
					selfSelectedCnt.find("[role=\"cancel\"]").trigger("click");
				} else {
					$.ajax({
						url: ajaxUrl + "?action=place",
						data: {
							placeId: selfValueInput.val()
						},
						dataType: "json"
					}).done(function(response){
						if (response.error === false) {
							if (response.data.result.formatted_address) {
								selfInputResults.empty();
								selfInputCnt.removeClass("open");
								
								selfInputCnt.addClass("hide");
								selfSelectedCnt.removeClass("hide");
								
								selfSelectedCnt.find("[role=\"city\"]").html(response.data.result.formatted_address);
							} else {
								selfSelectedCnt.find("[role=\"cancel\"]").trigger("click");
							}
						} else {
							selfSelectedCnt.find("[role=\"cancel\"]").trigger("click");
						}
					});
				}
			});
			
			if (selfValueInput.val() != "") selfValueInput.trigger("change");
		});
	};
})(jQuery);
jQuery(document).ready(function($){
	$("[role=\"ui.city\"]").MollyCitySelect();
});