(function($){
	var cancelOrderBtn = $("[role=\"cancelOrder\"]"),
			reOrderBtn = $("[role=\"reOrder\"]");

	cancelOrderBtn.on("click", function(event){
		event.preventDefault();
		
		if (!confirm("Вы действительно хотите отменить этот заказ?"))
			return;
		
		var orderId = cancelOrderBtn.data("id");
		
		$.ajax({
			url: "/ajax?action=cancelOrder&id="+orderId,
			dataType: "json"
		}).done(function(response){
			window.location.reload();
		}).error(function(){
			window.location.reload();
		});
	});
	
	reOrderBtn.on("click", function(event){
		event.preventDefault();
		
		if (!confirm("Корзина будет очищена и в неё добавятся товары из заказа"))
			return;
		
		var orderId = cancelOrderBtn.data("id");
		
		$.ajax({
			url: "/ajax?action=reOrder&id="+orderId,
			dataType: "json"
		}).done(function(response){
			window.location.href = "/cart";
		}).error(function(){
			window.location.reload();
		});
	});
})(jQuery);