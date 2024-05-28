//Parse language file
var readLanguage = null;
$.getJSON("core/languages/" + $("html").attr("lang") + ".json", function(data){
	readLanguage = data;
	if (typeof enable_localization !== "undefined" && enable_localization){
		$.getJSON("website/languages/" + $("html").attr("lang") + ".json", function(data){
			readLanguage = Object.assign(readLanguage, data);
			$(document).ready(function(){
				if (typeof initializeLocalization == "function"){
					initializeLocalization();
				}
			});
		});
	} else {
		$(document).ready(function(){
			if (typeof initializeLocalization == "function"){
				initializeLocalization();
			}
		});
	}
});