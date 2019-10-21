(function($){
	var bulkSelectors = $("[role=\"bulk\"]"),
			bulkForm = $("[role=\"bulkForm\"]");
	
	bulkSelectors.each(function(){
		var bulkCnt = $(this);
		
		bulkCnt.find("select").on("change", function(){
			bulkSelectors.find("select").val($(this).val());
		});
		
		bulkCnt.find("button").on("click", function(){
			bulkForm.find("[name=\"bulkAction\"]").val(bulkCnt.find("select").val());
			bulkForm.trigger("submit");
		});
	});
})(jQuery);