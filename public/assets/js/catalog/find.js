(function($){
	var nav = $("aside > .nav"),
		list = $("[role=\"list\"]"),
		items = $("[role=\"items\"]"),
		search = $("[role=\"search\"]");
	
	search.on("keyup change", function(){
		var val = $(this).val().toUpperCase();
		
		if (val == "") {
			nav.find("li").show();
			nav.find("li.active").each(function(){
				$(this).removeClass("active");
				$(this).find("> a i").removeClass("fa-minus-square-o");
				$(this).find("> a i").addClass("fa-plus-square-o");
			});
		} else {
			searchProcess(nav.children(), val);
		}
	});
	
	function searchProcess(items, searchStr) {
		var result = false;
		
		items.each(function(){
			if ($(this).children("ul").length) {
				var itemResult = searchProcess($(this).children("ul").children(), searchStr);
				
				if (itemResult) {
					result = true;
					$(this).addClass("active");
					$(this).find("> a i").removeClass("fa-plus-square-o");
					$(this).find("> a i").addClass("fa-minus-square-o");
					$(this).show();
				} else {
					$(this).removeClass("active");
					$(this).find("> a i").removeClass("fa-minus-square-o");
					$(this).find("> a i").addClass("fa-plus-square-o");
					$(this).hide();
				}
			} else {
				if ($(this).children("a").text().toUpperCase().search(searchStr) !== -1) {
					$(this).show();
					result = true;
				} else {
					$(this).hide();
				}
			}
		});
		
		return result;
	}
	
	items.on("setItems", function(event, data){
		items.empty();
		
		$(data).children().each(function(){
			var title = $(this).find(" > a").text();
			var dataItem = $(this).find(" > a").data("queryparams");
			
			items.append("<div class=\"col-sm-3\" data-id=\""+dataItem.id+"\">\
				<div style=\"background:url(https://www.emex.ru/Images/Catalog/Units/"+dataItem.id+".gif);\" class=\"unit-container\">\
					<span>"+title+"</span>\
				</div>\
			</div>")
		});
		
		items.show();
		list.css("opacity", 0);
	}).trigger("setItems", nav);
	
	items.on("click", "[data-id]", function(){
		nav.find("[data-id=\""+$(this).data("id")+"\"]").trigger("click");
	});
	
	nav.on("click", "li a", function(event){
		event.preventDefault();
		
		var selfLi = $(this).parent(),
				selfUl = selfLi.parent();
		
		if (selfLi.hasClass("active")) {
			selfLi.find("> a i").removeClass("fa-minus-square-o");
			selfLi.find("> a i").addClass("fa-plus-square-o");
			selfLi.removeClass("active");
			return false;
		} else {
			if (selfUl.children(".active").length) {
				selfUl.children(".active").removeClass("active");
			}
			
			selfLi.find("> a i").removeClass("fa-plus-square-o");
			selfLi.find("> a i").addClass("fa-minus-square-o");
			selfLi.addClass("active");
		}
		
		var data = $(this).data("queryparams");
		
		if (data.link === true) {
			list.trigger("loadlist", data);
		} else {
			items.trigger("setItems", selfLi.find("> .nav"));
		}
		
		return false;
	});
	
	function buildUnit( unitData, response ) {		
                var renderItem = function(table, item, url) {
                    table.append("<tr>\
				<td><a href=\""+url+"\" target=\"_blank\" title=\"Найти у поставщиков\">"+item["oem"]+"</a></td>\
				<td>"+item["codeonimage"]+"</td>\
				<td>"+item["name"]+"</td>\
				<td>"+item["note"]+"</td>\
			</tr>");
                }
                
		var unitHtml = $("<div class=\"clearfix\">\
                            <div class=\"col-sm-3\" role=\"image\"></div>\
                            <div class=\"col-sm-9 container\">\
                            </div>\
                        </div>"),
                    imageUrl = unitData["@attributes"]["imageurl"],
                    table = $("<table class=\"table table-striped m-b-none\">\
					<thead>\
						<tr>\
							<th style=\"width: 150px;\">OEM</th>\
							<th>Номер</th>\
							<th>Название</th>\
							<th>Примечание</th>\
						</tr>\
					</thead>\
					<tbody></tbody>\
				</table>"),
                    unitUrl = "/catalog?action=unit&code="+response.params.code+
			"&vid="+response.params.vid+
			"&ssd="+unitData["@attributes"]["ssd"]+
			"&uid="+unitData["@attributes"]["unitid"];
		
		imageUrl = imageUrl.replace(/%size%/g, "source");
		
                unitHtml.find(".container").append(table);
		unitHtml.find("[role=\"image\"]").append("<a href=\""+imageUrl+"\" data-lightbox=\""+Math.random()+"\" target=\"_blank\" title=\"Открыть изображение\"><img src=\""+imageUrl+"\" style=\"width: 100%;\"></a>");
		unitHtml.find("[role=\"image\"]").append("<a href=\""+unitUrl+"\" style='color: red;'>Перейти к узлу\
			<strong>"+unitData["@attributes"]["code"]+"</strong>\
			"+unitData["@attributes"]["name"]+"\
		</a>");
		
                var tbody = table.find("tbody"),
                    more = $('<a class="show-all" href="#">Показать все</a>'),
                    shower = function(table) {
                    var list = [],                    
                        add = function(item) {
                            list.push(item);
                        },
                        render = function() {
                            $.each(list, function(i, item) {
                                var url = "/catalog/parts?query="+item["oem"];
                                renderItem(table, item, url);
                            });
                        };

                        return {
                            render,
                            add
                        };      
                }(tbody);
                                            
		$.each(unitData.Detail, function(i, item){
			try {
                if (item["@attributes"]["oem"] === "") return;
                var url = "/catalog/parts?query="+item['@attributes']["oem"];
            } catch (e) {

            }
                        if (i < 3)
                            renderItem(table, item["@attributes"], url);
                        else
                            shower.add(item["@attributes"]);
		});
                more.click(function(e) {
                    e.preventDefault();
                    shower.render();
                    e.target.remove();
                });
                table.after(more);
		return unitHtml;
	}
	
	function buildCategory( categoryData, response ) {
		var categoryHtml = $("<div class=\"row\" role=\"category\"></div>");
		
		var categoryUtl = "/catalog?action=units&code="+response.params.code+
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
		items.hide();
		list.css("opacity", 0);
		
		if (data.link === false) {
			return false;
		}
		
		list.data("current", data.id);
		
		$.ajax({
			url: "/catalog/ajax?action=loadfind",
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