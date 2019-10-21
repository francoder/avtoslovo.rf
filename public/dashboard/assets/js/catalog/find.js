(function($){
	var nav = $("aside > .nav"),
			list = $("[role=\"list\"]");
	
	nav.on("click", "li a", function(event){
		event.preventDefault();
		
		var selfLi = $(this).parent(),
				selfUl = selfLi.parent();
		
		if (selfLi.hasClass("active")) {
			selfLi.find("> a i").removeClass("fa-chevron-down");
			selfLi.find("> a i").addClass("fa-chevron-right");
			selfLi.removeClass("active");
			return false;
		} else {
			if (selfUl.children(".active").length) {
				selfUl.children(".active").removeClass("active");
			}
			
			selfLi.find("> a i").removeClass("fa-chevron-right");
			selfLi.find("> a i").addClass("fa-chevron-down");
			selfLi.addClass("active");
		}
		
		var data = $(this).data("queryparams");
		
		if (data.link === true) {
			list.trigger("loadlist", data);
		}
		
		return false;
	});
	
	function buildUnit( unitData, response ) {
		var unitHtml = $("<div>\
			<div class=\"col-sm-3\" role=\"image\"></div>\
			<div class=\"col-sm-9\">\
				<table class=\"table table-striped m-b-none\">\
					<thead>\
						<tr>\
							<th style=\"width: 150px;\">OEM</th>\
							<th>Номер</th>\
							<th>Название</th>\
							<th>Примечание</th>\
						</tr>\
					</thead>\
					<tbody></tbody>\
				</table>\
			</div>\
		</div>");
		
		var imageUrl = unitData["@attributes"]["imageurl"];
		
		imageUrl = imageUrl.replace(/%size%/g, "source");
		
		var unitUrl = "/dashboard/catalog?action=unit&code="+response.params.code+
			"&vid="+response.params.vid+
			"&ssd="+unitData["@attributes"]["ssd"]+
			"&uid="+unitData["@attributes"]["unitid"];
		
		unitHtml.find("[role=\"image\"]").append("<a href=\""+imageUrl+"\" target=\"_blank\" title=\"Открыть изображение\"><img src=\""+imageUrl+"\" style=\"width: 100%;\"></a>");
		unitHtml.find("[role=\"image\"]").append("<a href=\""+unitUrl+"\" target=\"_blank\">Перейти к узлу\
			<b>"+unitData["@attributes"]["code"]+"</b>\
			"+unitData["@attributes"]["name"]+"\
		</a>");
		
		$.each(unitData.Detail, function(){
			if (this["@attributes"]["oem"] == "") return;
			
			var partsLink = "/dashboard/catalog/parts?query="+this["@attributes"]["oem"]
			
			unitHtml.find("table tbody").append("<tr>\
				<td><a href=\""+partsLink+"\" target=\"_blank\" title=\"Найти у поставщиков\">"+this["@attributes"]["oem"]+"</a></td>\
				<td>"+this["@attributes"]["codeonimage"]+"</td>\
				<td>"+this["@attributes"]["name"]+"</td>\
				<td>"+this["@attributes"]["note"]+"</td>\
			</tr>");
		});
		
		return unitHtml;
	}
	
	function buildCategory( categoryData, response ) {
		var categoryHtml = $("<div class=\"row\" role=\"category\"></div>");
		
		var categoryUtl = "/dashboard/catalog?action=units&code="+response.params.code+
			"&vid="+response.params.vid+
			"&cid="+categoryData["@attributes"]["categoryid"]+
			"&ssd="+categoryData["@attributes"]["ssd"]
		
		categoryHtml.append("<a href=\""+categoryUtl+"\" class=\"h4 m-l-md\">"+categoryData["@attributes"]["name"]+"</a>");
		
		if ($.isArray(categoryData.Unit)) {
			$.each(categoryData.Unit, function(){
				categoryHtml.append(buildUnit(this, response));
			});
		} else {
			categoryHtml.append(buildUnit(categoryData.Unit, response));
		}
		
		return categoryHtml;
	}
	
	list.on("loadlist", function(event, data){
		list.css("opacity", 0);
		
		if (data.link === false) {
			return false;
		}
		
		list.data("current", data.id);
		
		$.ajax({
			url: "/dashboard/catalog/ajax?action=loadfind",
			data: data,
			dataType: "json"
		}).done(function(response){
			if (response.error === false) {
				if (list.data("current") == response.params.quickgroupid) {
					list.empty();
					
					if ($.isArray(response.data.Category)) {
						$.each(response.data.Category, function(){
							list.append(buildCategory(this, response))
						});
					} else {
						list.append(buildCategory(response.data.Category, response))
					}
					
					list.css("opacity", 1);
				}
			}
		});
	});
})(jQuery);