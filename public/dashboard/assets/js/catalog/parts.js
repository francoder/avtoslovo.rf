(function($){
	var showOPC = $("[name=\"originalPriceVisible\"]");
	
	showOPC.on("change", function(){
		if ($(this).prop("checked")) {
			$("[role=\"buyprice\"]").removeAttr("style");
		} else {
			$("[role=\"buyprice\"]").hide();
		}
	});
	
	var photos = $("[role=\"photo\"]");
	
	if (photos.length) {
		photos.vanillabox({
			grouping: false
		});
	}
})(jQuery);