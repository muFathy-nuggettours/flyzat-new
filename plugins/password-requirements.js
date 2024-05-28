(function ($) {
$.fn.PassRequirements = function (options){

var defaults = {};

if (!options || options.defaults == true || options.defaults == undefined ){
	if (!options){ options = {}; }
	defaults.rules = $.extend({
		minlength: {
			text: "Minimum minLength characters long",
			minLength: 8,
		},
		containSpecialChars: {
			text: "Contains at least minLength special character",
			minLength: 1,
			regex: new RegExp("([^!,%,&,@,#,$,^,*,?,_,~])","g")
		},
		containLowercase: {
			text: "Contains at least minLength lower case letter",
			minLength: 1,
			regex: new RegExp("[^a-z]","g")
		},
		containUppercase: {
			text: "Contains at least minLength upper case letter",
			minLength: 1,
			regex: new RegExp("[^A-Z]","g")
		},
		containNumbers: {
			text: "Contains at least minLength number",
			minLength: 1,
			regex: new RegExp("[^0-9]","g")
		}
	}, options.rules);
} else {
	defaults = options;
}

var i = 0;

return this.each(function(){
	var requirementList = "";
	$(this).data("password-valid", false);
	
	//Update Validation
	$(this).keyup(function(){
		var requirements_list = 0;
		var valid_requirements = 0;
		var this_ = $(this);
		Object.getOwnPropertyNames(defaults.rules).forEach(function (val, idx, array){
			requirements_list++;
			if (this_.val().replace(defaults.rules[val].regex, "").length > defaults.rules[val].minLength - 1){
				this_.next(".popover").find("#" + val).attr("validated",true);
				valid_requirements++;
			} else {
				this_.next(".popover").find("#" + val).attr("validated",false);
				valid_requirements--;
			}
		})
		var valid_passowrd = (valid_requirements == requirements_list ? true : false);
		$(this).data("valid-password", valid_passowrd);
	});
	
	Object.getOwnPropertyNames(defaults.rules).forEach(function (val, idx, array) {
		requirementList += (("<li id='" + val + "'><span></span>&nbsp;&nbsp;" + defaults.rules[val].text).replace("minLength", defaults.rules[val].minLength));
	})
	
	$(this).popover({
		title: "Password Requirements",
		animation: true,
		trigger: options.trigger ? options.trigger : "focus",
		html: true,
		placement: options.popoverPlacement ? options.popoverPlacement : "bottom",
		content: "<ul class=password_validation>" + requirementList + "</ul>"
	}).on("inserted.bs.popover", function(e){
		var id = $(this).attr("aria-describedby");
		var popover = $("#" + id);
		popover.addClass("password_popover").css("margin-left", parseInt(($(this).width() - popover.width()) / 2, 10) + "px");
	});

	$(this).focus(function () {
		$(this).keyup();
	});
});

};

}(jQuery));
