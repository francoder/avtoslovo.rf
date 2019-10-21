(function($){
	var brandsTables = $("[role=\"brandsTable\"]");
	
	var brandsMarkupsInit = function(items, nameBase) {
		items.each(function(){
			var self = $(this);
			
			if (self.hasClass("initialized")) {
				return;
			}
			
			var code = self.data("supplier"),
					count = self.data("count"),
					tbody = self.find("tbody"),
					rowsCount = tbody.children().length;
			
			tbody.on("click", "[role=\"removeMarkup\"]", function(event){
				event.preventDefault();
				
				if (!confirm("Подтвердите действие")) {
					return;
				}
				
				var row = $(this).closest(".markupRow");
				
				row.remove();
			});
			
			self.on("click", "[role=\"addMarkup\"]", function(event) {
				event.preventDefault();
				
				var newRow = $("<tr class=\"markupRow\">\
				<td><input type=\"text\" class=\"form-control\" name=\"brands["+code+"]["+count+"][markups]["+rowsCount+"][value]\" value=\"\"></td>\
					<td><input type=\"text\" class=\"form-control\" name=\"brands["+code+"]["+count+"][markups]["+rowsCount+"][percent]\" value=\"\"></td>\
					<td><input type=\"text\" class=\"form-control\" name=\"brands["+code+"]["+count+"][markups]["+rowsCount+"][fixed]\" value=\"\"></td>\
					<td><button type=\"button\" class=\"btn btn-danger\" role=\"removeMarkup\">&times;</button></td>\
				</tr>");
				
				rowsCount++;
				tbody.append(newRow);
			});
			
			self.addClass("initialized");
		});
	}
	
	brandsTables.each(function(){
		var self = $(this),
			code = self.data("supplier"),
			add = self.find("[role=\"newBrand\"]"),
			body = self.children("tbody"),
			count = body.children(".item").length;
		
		brandsMarkupsInit(body.find("[role=\"brandMarkups\"]"));
		
		add.on("click", function(event){
			event.preventDefault();
			
			var newItem = $("<tr class=\"item\" data-count=\""+count+"\">\
				<td><input class=\"form-control\" name=\"brands["+code+"]["+count+"][code]\" placeholder=\"Код бренда\" value=\"\"></td>\
				<td><button type=\"button\" role=\"brandPrices\" class=\"btn btn-succcess\">Управление наценками</button></td>\
				<td><button type=\"button\" role=\"brandRemove\" class=\"btn btn-danger\">Удалить</button></td>\
			</tr>\
			<tr data-count=\""+count+"\" style=\"display: none;\">\
				<td colspan=\"3\" style=\"padding: 0px;\">\
					<table style=\"width: 100%;\" role=\"brandMarkups\" data-supplier=\""+code+"\" data-count=\""+count+"\">\
						<thead>\
							<tr>\
								<th>При стоимости более</th>\
								<th>%</th>\
								<th>Фиксировано</th>\
								<th></th>\
							</tr>\
						</thead>\
						<tbody></tbody>\
						<tfoot>\
							<tr>\
								<td colspan=\"4\"><button type=\"button\" role=\"addMarkup\" class=\"btn btn-succcess\">Добавить вариант</button></td>\
							</tr>\
						</tfoot>\
					</table>\
				</td>\
			</tr>");
			
			brandsMarkupsInit(newItem.find("[role=\"brandMarkups\"]"));
			
			count++;
			body.append(newItem);
		});
		
		body.on("click", "[role=\"brandPrices\"]", function(event){
			event.preventDefault();
			
			var row = $(this).closest(".item"),
					pricesRow = body.find("[data-count=\""+row.data("count")+"\"]").not(".item");
			
			if (row.hasClass("prices")) {
				pricesRow.hide();
				row.removeClass("prices");
			} else {
				pricesRow.show();
				row.addClass("prices");
				
				body.children(".prices").not(row).find("[role=\"brandPrices\"]").trigger("click");
			}
		});
		
		body.on("click", "[role=\"brandRemove\"]", function(event) {
			event.preventDefault();
			
			if (!confirm("Подтвердите действие")) {
				return;
			}
			
			var row = $(this).closest(".item"),
					rows = body.find("[data-count=\""+row.data("count")+"\"]");
			
			rows.remove();
		});
	});
	
	var apiTable = $("[role=\"api\"]");
	
	apiTable.on("click", "[role=\"row\"]", function(){
		var row = $(this);
		
		row.find(".hiddenonstart").show()
		
		apiTable.find("[role=\"row\"]").each(function(){
			if (!$(this).is(row)) {
				$(this).find(".hiddenonstart").hide()
			}
		});
	});
	
	var markups = $("[role=\"markups\"]");
	
	markups.each(function(){
		var self = $(this),
				selfList = self.find("[role=\"list\"]");
				selfCode = self.data("code");
		
		self.on("init", function(){
			var add = self.find("[role=\"add\"]");
			
			selfList.on("click", "[role=\"removeItem\"]", function(event){
				event.preventDefault();
				
				if (confirm("Подтвердите")) {
					$(this).closest("tr").remove();
				}
			})
			
			add.on("click", function(event){
				event.preventDefault();
				
				var count = selfList.data("count"),
						row = $("<tr>\
							<td><input type=\"text\" class=\"form-control\" name=\"markup["+self.data("code")+"]["+count+"][value]\" value=\"0\"></td>\
							<td><input type=\"text\" class=\"form-control\" name=\"markup["+self.data("code")+"]["+count+"][percent]\" value=\"0\"></td>\
							<td><input type=\"text\" class=\"form-control\" name=\"markup["+self.data("code")+"]["+count+"][fixed]\" value=\"0\"></td>\
							<td><a role=\"removeItem\">&times;</a></td>\
						</tr>");
				
				selfList.append(row);
				count++;
				selfList.data("count", count)
			});
		}).trigger("init");
	});
	
	var local = $("[role=\"local\"]"),
			localAdd = local.find("[role=\"new\"]"),
			localTable = local.find("[role=\"ltable\"]"),
			localTableBody = localTable.children("tbody"),
			localTableCount = localTableBody.children().length;
	
	localTable.on("click", "[role=\"removeSupplier\"]", function(event){
		event.preventDefault();
		
		if (confirm("Вы действительно хотите удалить этого поставщика?")) {
			$(this).closest("[role=\"row\"]").remove();
		}
	});
	
	localTable.on("focus", "input", function(event){
		var row = $(this).closest("[role=\"row\"]");
		
		row.find(".hiddenonstart").show()
		
		localTable.children().children().each(function(){
			if (!$(this).is(row)) {
				$(this).find(".hiddenonstart").hide()
			}
		});
	});
	
	localTable.on("initmarkups", function(){
		var lmarkups = localTable.find("[role=\"localmarkups\"]");
		
		lmarkups.each(function(){
			var self = $(this),
					selfList = self.find("[role=\"list\"]");
			
			self.on("init", function(){
				if (self.hasClass("inited")) {
					return;
				}
				
				var add = self.find("[role=\"add\"]");
				
				selfList.on("click", "[role=\"removeItem\"]", function(event){
					event.preventDefault();
					
					if (confirm("Подтвердите")) {
						$(this).closest("tr").remove();
					}
				})
				
				add.on("click", function(event){
					event.preventDefault();
					
					var selfCode = self.data("code"),
							row = $("<tr>\
								<td><input type=\"text\" class=\"form-control\" name=\"local["+selfCode+"][markups]["+selfList.children().length+"][value]\" value=\"0\"></td>\
								<td><input type=\"text\" class=\"form-control\" name=\"local["+selfCode+"][markups]["+selfList.children().length+"][percent]\" value=\"0\"></td>\
								<td><input type=\"text\" class=\"form-control\" name=\"local["+selfCode+"][markups]["+selfList.children().length+"][fixed]\" value=\"0\"></td>\
								<td><a role=\"removeItem\">&times;</a></td>\
							</tr>");
					
					selfList.append(row);
				});
				
				self.addClass("inited");
			}).trigger("init");
		});
	}).trigger("initmarkups");
	
	localTable.find(".datetime").datetimepicker({
		format: "dd.mm.yyyy hh:ii"
	})
	
	localAdd.on("click", function(event){
		event.preventDefault();
		
		var newRow = $('<tr role="row">\
			<td>Не сохранен</td>\
			<td>\
				<div class="form-group">\
					<label class="checkbox m-l m-t-none m-b-none i-checks">\
						<input type="checkbox" name="local['+localTableCount+'][active]" value="Y" checked>\
						<i></i>\
						Активен\
					</label>\
				</div>\
				<div class="hiddenonstart">\
					<div class="form-group">\
						<label>Конец активности</label>\
						<input type="text" class="form-control datetime" name="local['+localTableCount+'][activeTo]" value="" placeholder="Конец активности">\
					</div>\
					<div class="form-group">\
						<label>Код</label>\
						<input type="text" class="form-control" name="local['+localTableCount+'][code]" value="" placeholder="Код поставщика (На английском, без пробелов)">\
					</div>\
				</div>\
				<div class="form-group">\
					<div class="hiddenonstart">\
						<label>Название</label>\
					</div>\
					<input type="text" class="form-control" name="local['+localTableCount+'][title]" value="" placeholder="Название поставщика">\
				</div>\
				<div class="hiddenonstart">\
					<div class="form-group">\
						<button type="button" class="btn" role="removeSupplier">Удалить поставщика</button>\
						<button type="submit" class="btn btn-primary">Сохранить</button>\
					</div>\
				</div>\
			</td>\
			<td style="padding: 0px;">\
				<div class="hiddenonstart">\
					<section class="panel panel-default">\
						<header class="panel-heading bg-light">\
							<ul class="nav nav-tabs nav-justified">\
								<li class="active">\
									<a href="#'+localTableCount+'Markups" data-toggle="tab">Цены</a>\
								</li>\
								<li class="">\
									<a href="#'+localTableCount+'Storages" data-toggle="tab">Склады</a>\
								</li>\
							</ul>\
						</header>\
						<div class="panel-body">\
							<div class="tab-content">\
								<div id="'+localTableCount+'Markups" class="tab-pane active">\
									<div role="localmarkups" data-code="'+localTableCount+'">\
										<table style="width: 100%;">\
											<thead>\
												<tr>\
													<th>При стоимости более</th>\
													<th>%</th>\
													<th>Фиксировано</th>\
													<th></th>\
												</tr>\
											</thead>\
											<tbody role="list" data-count="0"></tbody>\
											<tfoot>\
												<tr>\
													<th colspan="4">\
														<button class="btn" role="add">Добавить</button>\
													</th>\
												</tr>\
											</tfoot>\
										</table>\
									</div>\
								</div>\
								<div id="'+localTableCount+'Storages" class="tab-pane">\
									<div class="form-group">\
										<label>Наименование склада</label>\
										<input type="text" class="form-control" name="local['+localTableCount+'][stockName]" value="" placeholder="Наименование склада">\
									</div>\
									<div class="form-group">\
										<label>Время доставки</label>\
										<input type="text" class="form-control" name="local['+localTableCount+'][delivery]" value="" placeholder="Время доставки">\
									</div>\
									<div class="row">\
										<div class="col-md-3">\
											<div class="form-group">\
												<label>Поставлено %</label>\
												<input class="form-control" type="text" name="local['+localTableCount+'][deliveryStat][all]" value="0">\
											</div>\
										</div>\
										<div class="col-md-3">\
											<div class="form-group">\
												<label>Быстрее %</label>\
												<input class="form-control" type="text" name="local['+localTableCount+'][deliveryStat][before]" value="0">\
											</div>\
										</div>\
										<div class="col-md-3">\
											<div class="form-group">\
												<label>Вовремя %</label>\
												<input class="form-control" type="text" name="local['+localTableCount+'][deliveryStat][intime]" value="0">\
											</div>\
										</div>\
										<div class="col-md-3">\
											<div class="form-group">\
												<label>С задержкой %</label>\
												<input class="form-control" type="text" name="local['+localTableCount+'][deliveryStat][fail]" value="0">\
											</div>\
										</div>\
									</div>\
								</div>\
							</div>\
						</div>\
					</section>\
				</div>\
			</td>\
		</tr>');
		
		newRow.find(".datetime").datetimepicker({
			format: "dd.mm.yyyy hh:ii"
		})
		
		localTableBody.prepend(newRow);
		localTableCount++;
		localTable.trigger("initmarkups");
	});
})(jQuery);