(function($){
    $.fn.fancyTable = function(options){
        var settings = $.extend({
            inputPlaceholder: "Search...",
            noRecordsPlaceholder: "No records found",
            pagination: false,
            paginationClass: "btn btn-light",
            paginationClassActive: "active",
            pagClosest: 3,
            perPage: 10,
            sortable: true,
            searchable: true,
			toolbarContainer: null,
            onInit: function(){},
            onUpdate: function(){},
            testing: false
        }, options);
        var instance = this;
		var originalTable = null;
        this.tableUpdate = function(elm){
            elm.fancyTable.matches = 0;
            $(elm).find("tbody tr").each(function(){
                var n = 0;
                var match = true;
                var globalMatch = false;
                $(this).find("td").each(function(){
                    if (!elm.fancyTable.search || (new RegExp(elm.fancyTable.search, "i").test($(this).html()))){
                        if (!Array.isArray(settings.globalSearchExcludeColumns) || !settings.globalSearchExcludeColumns.includes(n + 1)){
                            globalMatch = true;
                        }
                    }
                    n++;
                });
                if (globalMatch){
                    elm.fancyTable.matches++
                    if (!settings.pagination || (elm.fancyTable.matches > (elm.fancyTable.perPage * (elm.fancyTable.page - 1)) && elm.fancyTable.matches <= (elm.fancyTable.perPage * elm.fancyTable.page))){
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                } else {
                    $(this).hide();
                }
            });
            if (elm.fancyTable.matches == 0){
                $(elm).find("tfoot").hide();
                $("#" + $(elm).attr("id") + "_no_records").show();
            } else {
                $("#" + $(elm).attr("id") + "_no_records").hide();
                $(elm).find("tfoot").show();
            }
            elm.fancyTable.pages = Math.ceil(elm.fancyTable.matches / elm.fancyTable.perPage);
            if (settings.pagination){
                var paginationElement = (elm.fancyTable.paginationElement) ? $(elm.fancyTable.paginationElement) : $(elm).find(".pag");
                paginationElement.empty();
                for (var n = 1; n <= elm.fancyTable.pages; n++){
                    if (n == 1 || (n > (elm.fancyTable.page - (settings.pagClosest + 1)) && n < (elm.fancyTable.page + (settings.pagClosest + 1))) || n == elm.fancyTable.pages){
                        var a = $("<a>", {
                            html: n,
                            "data-n": n,
                            style: "margin:0.2em",
                            class: settings.paginationClass + " " + ((n == elm.fancyTable.page) ? settings.paginationClassActive : "")
                        }).css("cursor", "pointer").bind("click", function(){
                            elm.fancyTable.page = $(this).data("n");
                            instance.tableUpdate(elm);
                        });
                        if (n == elm.fancyTable.pages && elm.fancyTable.page < (elm.fancyTable.pages - settings.pagClosest - 1)){
                            paginationElement.append($("<span>...</span>"));
                        }
                        paginationElement.append(a);
                        if (n == 1 && elm.fancyTable.page > settings.pagClosest + 2){
                            paginationElement.append($("<span>...</span>"));
                        }
                    }
                }
            }
            settings.onUpdate.call(this, elm);
        };
		this.unset = function(){
			var elm = this;
			$(elm).find("th a").contents().unwrap();
			$(elm).find("th .sortArrow").remove();
			$(".fancytable_toolbar[id='" + $(elm).attr("id") + "_toolbar']").remove();
			$("#" + $(elm).attr("id") + "_no_records").remove();
			$(elm).replaceWith(originalTable.clone());
		};
        this.tableSort = function(elm){
            if (typeof elm.fancyTable.sortColumn !== "undefined" && elm.fancyTable.sortColumn < elm.fancyTable.nColumns){
                $(elm).find("thead th div.sortArrow").each(function(){
                    $(this).remove();
                });
                var sortArrow = $("<div>", {
                    "class": "sortArrow desc"
                }).css({
                    "margin": "0.1em",
                    "display": "inline-block",
                    "width": 0,
                    "height": 0,
                    "border-left": "0.4em solid transparent",
                    "border-right": "0.4em solid transparent"
                });
				if (elm.fancyTable.sortOrder > 0){
					sortArrow.css({
						"border-top": "0.4em solid #000"
					}).removeClass("desc").addClass("asc");
				} else {
					sortArrow.css({
						"border-bottom": "0.4em solid #000"
					}).removeClass("asc").addClass("desc");
				}
                $(elm).find("thead th a").eq(elm.fancyTable.sortColumn).append(sortArrow);
                var excluded = $(elm).find("tbody tr[exclude]").toArray();
                var rows = $(elm).find("tbody tr").not("[exclude]").toArray().sort(
                    function(a, b){
                        var stra = $(a).find("td").eq(elm.fancyTable.sortColumn).html();
                        var strb = $(b).find("td").eq(elm.fancyTable.sortColumn).html();
                        if (elm.fancyTable.sortAs[elm.fancyTable.sortColumn] == 'numeric'){
                            return ((elm.fancyTable.sortOrder > 0) ? parseFloat(stra) - parseFloat(strb) : parseFloat(strb) - parseFloat(stra));
                        } else {
                            return ((stra < strb) ? -elm.fancyTable.sortOrder : (stra > strb) ? elm.fancyTable.sortOrder : 0);
                        }
                    }
                );
                $(elm).find("tbody").empty().append(rows).append(excluded);
            }
        };
        this.each(function(){
            if ($(this).prop("tagName") !== "TABLE"){
                console.warn("fancyTable: Element is not a table.");
                return true;
            }
            var elm = this;
            elm.fancyTable = {
                nColumns: $(elm).find("td").first().parent().find("td").length,
                nRows: $(this).find("tbody tr").length,
                perPage: settings.perPage,
                page: 1,
                pages: 0,
                matches: 0,
                searchArr: [],
                search: "",
                sortColumn: settings.sortColumn,
                sortOrder: (typeof settings.sortOrder === "undefined") ? 1 : (new RegExp("desc", "i").test(settings.sortOrder) || settings.sortOrder == -1) ? -1 : 1,
                sortAs: [],
                paginationElement: settings.paginationElement
            };
            if ($(this).prop("pagination") == true){
                $(elm).addClass("paginated");
            }
            $("<div style='display:none' class='" + $(elm).attr("class").split(" ")[0] + " table_no_records' id=" + $(elm).attr("id") + "_no_records>" + settings.noRecordsPlaceholder + "</div>").insertAfter($(elm));
            if ($(elm).find("tbody").length == 0){
                var content = $(elm).html();
                $(elm).empty();
                $(elm).append("<tbody>").append($(content));
            }
            if ($(elm).find("thead").length == 0){
                $(elm).prepend($("<thead>"));
            }
            if (settings.sortable){
                var n = 0;
                $(elm).find("thead th").each(function(){
                    elm.fancyTable.sortAs.push(($(this).data('sortas') == 'numeric') ? 'numeric' : '');
                    var content = $(this).html();
                    var a = $("<a>", {
                        html: content,
                        "data-n": n,
                        class: ""
                    }).css("cursor", "pointer").bind("click", function(){
                        if (elm.fancyTable.sortColumn == $(this).data("n")){
                            elm.fancyTable.sortOrder = -elm.fancyTable.sortOrder;
                        } else {
                            elm.fancyTable.sortOrder = 1;
                        }
                        elm.fancyTable.sortColumn = $(this).data("n");
                        instance.tableSort(elm);
                        instance.tableUpdate(elm);
                    });
                    $(this).empty();
                    $(this).append(a);
                    n++;
                });
            }
            var showToolbar = false;
            var tableToolbar = $("<div class=fancytable_toolbar id='" + $(elm).attr("id") + "_toolbar'>");
            if (settings.searchable){
                var searchField = $("<input>", {
                    "type": "search",
                    "placeholder": settings.inputPlaceholder
                }).bind("change paste keyup", function(){
                    elm.fancyTable.search = $(this).val();
                    instance.tableUpdate(elm);
                });
                $(searchField).appendTo($(tableToolbar));
                showToolbar = true;
            }
            if (settings.buttons){
                settings.buttons.forEach(function(arrayItem){
                    var tableButton = $("<input>", {
                        "type": "button",
                        "class": (arrayItem.class ? arrayItem.class : "btn btn-primary btn-sm"),
                        "value": arrayItem.text
                    }).bind("click", function(){
                        arrayItem.function();
                    });
                    $(tableButton).appendTo($(tableToolbar));
                });
                showToolbar = true;
            }
            if (showToolbar){
				if (settings.toolbarContainer){
					$(tableToolbar).appendTo(settings.toolbarContainer);
				} else {
					$(tableToolbar).insertBefore($(elm));
				}
            }
            instance.tableSort(elm);
            if (settings.pagination && !settings.paginationElement){
                $(elm).find("tfoot").remove();
                $(elm).append($("<tfoot><tr></tr></tfoot>"));
                $(elm).find("tfoot tr").append($("<td class='pag'></td>", {}).attr("colspan", elm.fancyTable.nColumns));
            }
			originalTable = $(elm);
            instance.tableUpdate(elm);
            settings.onInit.call(this, elm);
        });
        return this;
    };
}(jQuery));