(function ($) {
    var itemsTables = $("[role=\"itemsTable\"]"),
            sort = $("[role=\"sort\"]"),
            photos = $("[role=\"photo\"]"),
            currentSort = "price",
            tableItemsShow = 5;

    if (sort.length) {
        $("html, body").scrollTop($(".content .search-container").offset().top);
    }
    if ($("#variantsList").length) {
        $("html, body").scrollTop($(".content .search-container").offset().top);
    }

    if (photos.length) {
        photos.vanillabox({
            grouping: false
        });
    }

    if ($(".showprice").length) {
        $("#showprice").on("change", function () {
            if ($(this).prop("checked")) {
                $(".buyprice").show()
            } else {
                $(".buyprice").hide()
            }
        }).trigger("change");
    }

    itemsTables.each(function () {
        if ($(this).find("tbody tr").length > tableItemsShow)
        {
            $(this).prepend("<thead>\
				<tr>\
					<td colspan=\"6\">\
						<a role=\"toggle\" data-opened=\"0\">Показать все варианты</a>\
					</td>\
				</tr>\
			</thead>")
        }
    });

    sort.on("click", function (event) {
        event.preventDefault()

        if ($(this).hasClass("active")) {
            if ($(this).find(".fa").hasClass("fa-chevron-up")) {
                $(this).find(".fa-chevron-up").removeClass("fa-chevron-up").addClass("fa-chevron-down")
            } else {
                $(this).find(".fa-chevron-down").removeClass("fa-chevron-down").addClass("fa-chevron-up")
            }

            itemsTables.trigger("updateSort");
        } else {
            currentSort = $(this).attr("type");

            sort.find(".fa-chevron-up").removeClass("fa-chevron-up").addClass("fa-chevron-down")
            sort.removeClass("active");
            $(this).addClass("active");

            itemsTables.trigger("updateSort");
        }
    });

    itemsTables.on("click", "[role=\"toggle\"]", function (event) {
        event.preventDefault();

        var current = parseInt($(this).data("opened"));

        if (current) {
            $(this).data("opened", 0);
            $(this).text("Показать все варианты");
            $(this).closest("table").trigger("updateTableShow");
        } else {
            $(this).data("opened", 1);
            $(this).text("Скрыть все варианты");
            $(this).closest("table").trigger("updateTableShow");
        }
    });

    itemsTables.on("updateTableShow", function () {
        var selfTable = $(this),
                selfToggle = selfTable.find("thead [role=\"toggle\"]");

        if (!selfToggle.length) {
            return;
        }

        var opened = parseInt(selfToggle.data("opened"));

        if (opened) {
            selfTable.find("tbody tr").show()
        } else {
            var rows = selfTable.find("tbody tr");

            rows.hide()

            rows.filter(":nth-child(-n+5)").show()
        }
    });

    itemsTables.on("updateSort", function () {
        var sortType = sort.filter(".active").find(".fa").hasClass("fa-chevron-down");

        var selfTable = $(this),
                $tbody = selfTable.find("tbody"),
                tbody = selfTable.find("tbody").get(0),
                rowsArray = [].slice.call(tbody.rows);

        var compare;

        if (!sortType) {
            compare = function (a, b) {
                return parseFloat(a.dataset[currentSort]) < parseFloat(b.dataset[currentSort]) ? 1 : -1;
            };
        } else {
            compare = function (a, b) {
                return parseFloat(a.dataset[currentSort]) > parseFloat(b.dataset[currentSort]) ? 1 : -1;
            };
        }

        rowsArray.sort(compare);

        $tbody.empty()

        $.each(rowsArray, function () {
            $tbody.append(this);
        });

        selfTable.trigger("updateTableShow");
        // itemsTables.each(function(){
        // 	var selfTable = $(this);
        //
        // 	selfTable.find("tbody tr").sortElements(function(a, b){
        // 		// var aData = $(a).data(currentSort),
        // 		// 		bData = $(b).data(currentSort);
        //
        // 		var aData = a.dataset[currentSort],
        // 				bData = b.dataset[currentSort];
        //
        // 		if (aData == bData) return 0;
        //
        // 		if (!sortType) {
        // 			if (aData < bData) return -1;
        // 			else return 1;
        // 		} else {
        // 			if (aData < bData) return 1;
        // 			else return -1;
        // 		}
        // 	});
        // });

        // itemsTables.trigger("updateTableShow");
    }).trigger("updateSort");
})(jQuery);