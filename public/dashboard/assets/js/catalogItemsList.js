(function($){
	var bulkSelectors = $("[role=\"bulk\"]"),
			bulkForm = $("[role=\"bulkForm\"]");
	
	bulkSelectors.each(function(){
		var bulkCnt = $(this);
		
		bulkCnt.find("select").on("change", function(){
			bulkSelectors.find("select").val($(this).val());
		});
		
		bulkCnt.find("button[type=\"submit\"]").on("click", function(){
			bulkForm.find("[name=\"bulkAction\"]").val(bulkCnt.find("select").val());
			bulkForm.trigger("submit");
		});
	});
	
	var importModal = $("#importModal"),
			importModalForm = importModal.find("[role=\"form\"]"),
			importModalMarkups = importModal.find("[role=\"markups\"]"),
			importLoader = importModal.find("[role=\"loader\"]");
	
	importModal.data("rows", {});
	
	importModal.on("updateRowsData", function(){
		var optionsRows = "<option value=\"\">Не выбрано</option>",
				data = importModal.data("rows");
		
		$.each(data, function(key){
			var value = parseInt(key)+1;
			optionsRows = optionsRows + "<option value=\""+key+"\">"+value+"</option>";
		});
		
		importModal.find(".rowselect").html(optionsRows).val("").trigger("change");
	});
	
	importModal.find(".rowselect").on("change", function(){
		var optionsColls = "<option value=\"\">Не выбрано</option>",
				data = importModal.data("rows"),
				value = $(this).val();
		
		if (data[value] != undefined) {
			$.each(data[value], function(key){
				optionsColls = optionsColls + "<option value=\""+this.key+"\">"+this.key+": "+this.value+"</option>"
			});
			importModal.find(".colselect").html(optionsColls).val("");
		} else {
			importModal.find(".colselect").html(optionsColls).val("");
		}
	});
	
	importModalMarkups.on("reset", function(){
		var list = importModalMarkups.find("[role=\"list\"]");
		
		list.empty();
		list.data("count", 0);
	});
	
	importModalMarkups.on("click", "[role=\"removeItem\"]", function(event){
		event.preventDefault();
		
		$(this).closest("tr").remove();
	});
	
	importModalMarkups.find("[role=\"add\"]").on("click", function(event){
		event.preventDefault();
		
		var list = importModalMarkups.find("[role=\"list\"]"),
				count = list.data("count"),
				row = $("<tr>\
					<td><input type=\"text\" class=\"form-control\" name=\"markup["+count+"][value]\" value=\"0\"></td>\
					<td><input type=\"text\" class=\"form-control\" name=\"markup["+count+"][percent]\" value=\"0\"></td>\
					<td><input type=\"text\" class=\"form-control\" name=\"markup["+count+"][fixed]\" value=\"0\"></td>\
					<td><a role=\"removeItem\">&times;</a></td>\
				</tr>");
		
		list.append(row);
		count++;
		list.data("count", count);
	});
	
	importModal.on("hidden.bs.modal", function(){
		importModalForm.get(0).reset();
		
		importLoader.hide()
		
		importModal.on("selectstep", "first");
	});
	
	importModal.on("selectstep", function(event, step){
		importModal.find(".modal-step").hide();
		importModal.find(".modal-step[role=\""+step+"\"]").show();
		importModal.find("[name=\"step\"]").val(step);
	});
	
	importModalForm.on("submit", function(){
		var submit = importModalForm.find("[type=\"submit\"]"),
				step = importModalForm.find("[name=\"step\"]");
		
		if (submit.attr("disabled") == "disabled") {
			return false;
		}
		
		if (step.val() == "three") {
			importModal.modal("hide");
			return false;
		}
		
		submit.attr("disabled", "disabled");
		
		var data = new FormData(importModalForm.get(0));
		
		importLoader.show()
		
		$.ajax({
			url: "/dashboard/catalog/ajax?action=import",
			data: data,
			cache: false,
			contentType: false,
			processData: false,
			method: "POST",
			type: "POST",
			dataType: "json"
		}).done(function(response){
			importLoader.hide()
			submit.removeAttr("disabled");
			
			if (response.error) {
				alert(response.errorMsg);
				return;
			}
			
			if (step.val() == "first") {
				importModal.data("rows", response.data.rows);
				importModal.trigger("updateRowsData");
				
				importModal.trigger("selectstep", "second");
			} else if (step.val() == "second") {
				importModal.find("[role=\"countAll\"]").html(response.data.countAll);
				importModal.find("[role=\"countSuccess\"]").html(response.data.countSuccess);
				importModal.find("[role=\"countFailed\"]").html(response.data.countFailed);
				importModal.find("[role=\"countRemoved\"]").html(response.data.countRemoved);
				
				importModal.trigger("selectstep", "three");
			}
			
			console.log(response);
		}).error(function(error){
			importLoader.hide()
			submit.removeAttr("disabled");
			
			alert("Произошла ошибка, попробуйте позже");
		});
		
		return false;
	});
})(jQuery);