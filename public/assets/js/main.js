(function($){
	var catalogForm = $("[role=\"catalogForm\"]");
	
	catalogForm.on("submit", function(){
		if (catalogForm.find(".errors").length) {
			return false;
		}
		
		if (catalogForm.find("[name=\"code\"]").val() == "") {
			var error = $("<label class=\"errors\" for=\"code\">Выберите марку</label>");
			catalogForm.find("[name=\"code\"]").after(error)
			catalogForm.on("change", function(){
				error.remove()
			});
			
			return false;
		}
	});
})(jQuery)