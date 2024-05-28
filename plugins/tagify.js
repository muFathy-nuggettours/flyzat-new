$(document).ready(function(){
	var remove = "<a class=remove></a>";
	$("[data-tags]").each(function(){
		var tagifyObject = $(this);
		var tagSelector = tagifyObject.attr("data-tags");
		var preTags = (tagifyObject.val() ? tagifyObject.val().split((tagifyObject.attr("data-separator") ? (tagifyObject.attr("data-separator")=="{NewLine}" ? "\n" : tagifyObject.attr("data-separator")) : ",")) : "");
		tagifyObject.after("<ul class=" + (tagifyObject.attr("data-class") ? tagifyObject.attr("data-class") : "tag-box") + " name='" + tagSelector + "_tags'></ul>");
		if (preTags){
			for (i = 0; i < preTags.length; i++ ){
				$("[name='" + tagSelector + "_tags']").append("<li class=tags>" + remove + "<span>" + escapeHTML(preTags[i]) + "</span></li>");
			}
		}
		$("[name='" + tagSelector + "_tags']").append("<li class=new-tag><input autocomplete=off class=input-tag name='" + tagSelector + "_input' maxlength=255 type=text placeholder='" + tagifyObject.attr("placeholder") + "'>&nbsp;<button name='" + tagSelector + "_button' type=button class='btn btn-default btn-sm btn-square'><i class='fas fa-external-link-alt'></i></button></li>");

		//Taging
		$("[name='" + tagSelector + "_input']").bind("keydown", function(keypress){
			if (keypress.keyCode == 13){ event.preventDefault(); insertTag(); }
		});
		$("[name='" + tagSelector + "_button']").bind("click", function(){
			insertTag();
		});
		
		//Delete tag
		$("[name='" + tagSelector + "_tags']").on("click", ".remove", function(){
			$(this).parent().remove();
			buildTags();
			$("[name='" + tagSelector + "_input']").focus();
		});

		//Edit Tag
		$("[name='" + tagSelector + "_tags']").on("click", "li.tags span", function(){
			var self = $(this);
			$.confirm({
				title: self.text(),
				content: "<input type=text id=update style='border-radius:3px' value='" + self.text() + "'>",
				theme: "light-noborder",
				buttons: {
					formSubmit: {
						text: readLanguage.plugins.message_save,
						btnClass: "btn-green",
						action: function (){
							var update = this.$content.find("#update").val();
							if (!update){ 
								this.$content.find("#update").css("border","1px solid rgb(185, 74, 72)");
								return false;
							} else {
								self.text(update);
								buildTags();
							}
						}
					},
					Cancel: { text: readLanguage.plugins.message_cancel },
				}
			});
		});
		
		//Sorting
		$(function(){
			$("[name='" + tagSelector + "_tags']").sortable({
				items: "li:not(.new-tag)",
				containment: "parent",
				scrollSpeed: 100,
				update: function(event, ui){
					buildTags();
				}
			});
		});
		
		//Insert Tag
		function insertTag(){
			var tag = $("[name='" + tagSelector + "_input']").val().trim();
			if (tag){
				$("[name='" + tagSelector + "_tags']").find(".new-tag").before("<li class=tags>" + remove + "<span>" + escapeHTML(tag) + "</span></li>");
				$("[name='" + tagSelector + "_input']").val("");
				buildTags();
			}			
		}
		
		//Build Tags
		function buildTags(){
			var tags = [];
			$("[name='" + tagSelector + "_tags']").find(".tags").each(function(){
				if ($(this).text()){ tags.push($(this).text()); }
			});
			tagifyObject.val(tags.join((tagifyObject.attr("data-separator") ? (tagifyObject.attr("data-separator")=="{NewLine}" ? "\n" : tagifyObject.attr("data-separator")) : ",")));
		}
	});
});