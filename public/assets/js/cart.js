function number_format(number, decimals, dec_point, separator ) {
	number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
	var n = !isFinite(+number) ? 0 : +number,
		prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
		sep = (typeof separator === 'undefined') ? ',' : separator ,
		dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
		s = '',
		toFixedFix = function(n, prec) {
			var k = Math.pow(10, prec);
			return '' + (Math.round(n * k) / k)
				.toFixed(prec);
		};
	// Фиксим баг в IE parseFloat(0.55).toFixed(0) = 0;
	s = (prec ? toFixedFix(n, prec) : '' + Math.round(n))
		.split('.');
	if (s[0].length > 3) {
		s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
	}
	if ((s[1] || '')
		.length < prec) {
		s[1] = s[1] || '';
		s[1] += new Array(prec - s[1].length + 1)
			.join('0');
	}
	return s.join(dec);
}
(function($){
	var cart = $("[role=\"cart\"]"),
			cartCounts = cart.find("[role=\"count\"]");
	
	cart.on("update", function(){
		var amount = 0;
		
		cart.find("tbody tr").each(function(){
			var price = $(this).data("price"),
				count = $(this).find("[role=\"count\"] [role=\"val\"]").val();
			
			amount += price * count;
		});
		
		cart.find("[role=\"amount\"]").html(number_format(amount, 2, ",", " "));
	})
	
	cartCounts.each(function(){
		var self = $(this),
			up = self.find("[role=\"up\"]"),
			down = self.find("[role=\"down\"]"),
			val = self.find("[role=\"val\"]"),
			max = parseInt(val.data("max"));
		
		up.on("click", function(){
			var value = parseInt(val.val());
			
			value++;
			
			if (value > max) {
				val.val(max)
			} else {
				val.val(value)
			}
			
			cart.trigger("update");
		})
		
		down.on("click", function(){
			var value = parseInt(val.val());
			
			value--;
			
			if (value < 1) {
				val.val(1)
			} else {
				val.val(value)
			}
			
			cart.trigger("update");
		});
		
		val.on("change keyup", function(){
			var value = parseInt(val.val());
			
			if (value < 1) {
				val.val(1)
			} else if (value > max) {
				val.val(max)
			}
			
			cart.trigger("update");
		});
	});
})(jQuery);