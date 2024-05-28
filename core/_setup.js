//Always send user token and whether on mobile or not with every ajax request
$.ajaxSetup({
    data: {
        token: user_token,
        on_mobile: on_mobile
    }
});

//Prepare validation language variable
var validationLanguage = null;

//Initialize plugins requiring localization
function initializeLocalization(){
	//Select2
	if (typeof $.fn.select2 !== "undefined"){
		$.fn.select2.defaults.set("language", {
			errorLoading: function(){
				return readLanguage.plugins.select2_error;
			},
			loadingMore: function(){
				return readLanguage.plugins.select2_loading;
			},
			searching: function(){
				return readLanguage.plugins.select2_searching;
			},
			noResults: function(){
				return readLanguage.plugins.select2_empty;
			},
			inputTooLong: function(a){
				return readLanguage.plugins.select2_long.replace("%s", a.input.length - a.maximum);
			},
			inputTooShort: function(a){
				return readLanguage.plugins.select2_short.replace("%s", a.minimum - a.input.length);
			},
			maximumSelected: function(a) {
				return readLanguage.plugins.select2_max.replace("%s", a.maximum);
			}
		});
	}

	validationLanguage = {
		errorTitle: readLanguage.plugins.validation_title,
		requiredFields: readLanguage.plugins.validation_missing,
		badEmail: readLanguage.plugins.validation_email,
		badInt: readLanguage.plugins.validation_int,
		groupCheckedRangeStart: readLanguage.plugins.validation_check_start,
		groupCheckedTooFewStart: readLanguage.plugins.validation_check_few,
		groupCheckedTooManyStart: readLanguage.plugins.validation_check_many,
		groupCheckedEnd: readLanguage.plugins.validation_check_end,
		lengthBadStart: readLanguage.plugins.validation_length_start,
		lengthBadEnd: readLanguage.plugins.validation_length_end,
		lengthTooLongStart: readLanguage.plugins.validation_length_long,
		lengthTooShortStart: readLanguage.plugins.validation_length_short,
		wrongFileSize: readLanguage.plugins.validation_file_size,
		wrongFileType: readLanguage.plugins.validation_file_type
	};

	//Validate editor
	$.formUtils.addValidator({
		name: "validateEditor",
		validatorFunction: function(value, $el, config, language, $form){
			var html_content = tinymce.get($el.attr("name")).getBody();
			var content_text = html_content.textContent;
			var content_images = $(html_content).find("img").length;
			var content_videos = $(html_content).find("iframe").length;
			console.log(content_text, content_images, content_videos);
			return (content_text!="" || content_images >= 1 || content_videos >= 1);
		},
		errorMessage: readLanguage.plugins.validation_missing
	});

	//Validate attachments
	$.formUtils.addValidator({
		name: "validateAttachments",
		validatorFunction: function(value, $el, config, language, $form){
			var target = $("[data-attachments=" + $el.attr("name") + "]").find("ul li");
			var requiredMinimum = ($el.attr("validation-min") ? $el.attr("validation-min") : 1);
			var requiredMaximum = ($el.attr("validation-max") ? $el.attr("validation-max") : 100);
			return (target.length >= requiredMinimum && target.length <= requiredMaximum ? true : false);
		},
		errorMessage: readLanguage.plugins.validation_missing
	});

	//Required if visible
	$.formUtils.addValidator({
		name: "requiredVisible",
		validatorFunction: function(value, $el, config, language, $form){
			if ($el.is(":visible") && !value){
				return false;
			} else {
				return true;
			}
		},
		errorMessage: readLanguage.plugins.validation_missing
	});

	//Required if parent table row is visible [Used for Select2 included search]
	$.formUtils.addValidator({
		name: "requiredParentVisible",
		validatorFunction: function(value, $el, config, language, $form){
			if ($el.parents("tr").is(":visible") && (!value || value==0)){
				return false;
			} else {
				return true;
			}
		},
		errorMessage: readLanguage.plugins.validation_missing
	});

	//Validate YouTube URL
	$.formUtils.addValidator({
		name: "validateYouTube",
		validatorFunction: function(value, $el, config, language, $form){
			if (value && getYoutubeID(value)){
				return true;
			} else {
				return false;
			}
		},
		errorMessage: readLanguage.plugins.validation_youtube
	});

	//Initialize JSConfirm
	var jconfirmInstance = null;
	jconfirm.defaults = {
		rtl: ($("html").attr("dir")=="rtl" ? true : false),
		animateFromElement: false,
		containerFluid: true,
		container: parent.document.getElementsByTagName("body"),
		buttons: {},
		onOpenBefore: function(){
			jconfirmInstance = this;
		},
		onClose: function(){
			jconfirmInstance = null;
		},
		defaultButtons: {
			ok: {
				text: readLanguage.plugins.message_ok,
				action: function (){}
			},
			cancel: {
				text: readLanguage.plugins.message_cancel,
				action: function (){}
			},
		},
	};

	//Initialize DataTable (If exists)
	if (typeof $.fn.dataTable !== "undefined"){
		$.fn.dataTable.ext.classes.sPageButton = "btn btn-primary btn-sm";
		$.extend(true,$.fn.dataTable.defaults, {
			language: {
				"decimal":        "",
				"emptyTable":     readLanguage.plugins.table_no_data,
				"info":           readLanguage.plugins.table_showing,
				"infoEmpty":      readLanguage.plugins.table_showing_empty,
				"infoFiltered":   "",
				"infoPostFix":    "",
				"thousands":      ",",
				"lengthMenu":     "_MENU_",
				"loadingRecords": readLanguage.plugins.table_loading,
				"processing":     readLanguage.plugins.table_loading,
				"search":         "",
				"zeroRecords":    readLanguage.plugins.table_no_records,
				"paginate": {
					"first":      readLanguage.plugins.table_first,
					"last":       readLanguage.plugins.table_last,
					"next":       readLanguage.plugins.table_next,
					"previous":   readLanguage.plugins.table_previous
				},
				"aria": {
					"sortAscending":  "",
					"sortDescending": ""
				}
			}
		});
	}

	//Initialize Bootbox (If exists)
	if (typeof bootbox !== "undefined"){
		bootbox.addLocale($("html").attr("lang"), {
			OK : readLanguage.plugins.message_ok,
			CANCEL : readLanguage.plugins.message_cancel,
			CONFIRM : readLanguage.plugins.message_confirm
		});
		bootbox.setLocale($("html").attr("lang"));
	}
}

//Sortable Select2
$(document).ready(function(){
	$("select[data-sort]").each(((t, e) => {
		const a = $(e).select2(),
			r = a.parent().find("ul.select2-selection__rendered");
		if (a.attr("data-sort")) {
			let t = a.attr("data-sort").split(",");
			a.val(t).trigger("change");
			let e = Array.from(a.select2("data"));
			e.sort(((e, a) => t.indexOf(e.id) > t.indexOf(a.id) ? -1 : 1)), e.forEach((t => a.prepend(a.find(`option[value='${t.id}']`))))
		}
		r.sortable({
			containment: "parent",
			forcePlaceholderSize: true,
			items: "li:not(.select2-search)",
			start: function(e, ui){
				ui.placeholder.width(ui.item.width());
			},
			update: function(){
				let t = Array.from(a.select2("data")),
					e = Array.from($(this).find("li.select2-selection__choice")).map((t => $(t).attr("title")));
				t.sort(((t, a) => e.indexOf(t.text) > e.indexOf(a.text) ? -1 : 1)), t.forEach((t => a.prepend(a.find(`option[value='${t.id}']`))))
			}
		})
	}));
});

//Initialize Fancybox
$.fancybox.defaults.buttons = ["close"];
$.fancybox.defaults.autoSize = false;
$.fancybox.defaults.protect = false;
$.fancybox.defaults.keyboard = false;
$.fancybox.defaults.parentEl = parent.document.getElementsByTagName("body");
$.fancybox.defaults.iframe.preload = true;
$.fancybox.defaults.animationEffect = "zoom-in-out";
$.fancybox.defaults.animationDuration = 200;
$.fancybox.defaults.transitionEffect = "fade";
$.fancybox.defaults.transitionDuration = 200;
$(parent).on("beforeLoad.fb", function(e, instance, slide){
	//For mobile only (Trigger connection error if not online)
	if (on_mobile && !navigator.onLine){
		sendParentMessage("Call-Function", "connectionError");
		instance.close();
		return false;
	}
	//Set frame width if set as an attribute
	var element = slide.opts.$orig;
	if (element){
		slide.opts.iframe.css = (element.data("frame-width") ? {width: element.data("frame-width")} : {});
	}
	//Set type class (For styling each type individually)
	var type = instance.current.contentType;
	slide.$slide.closest(".fancybox-container").addClass("fancybox-type-" + type);
});
//Refresh on fancybox close if set as an attribute
$(parent).on("afterClose.fb", function(e, instance, slide){
	if (instance.current.opts.$orig[0].hasAttribute('data-refresh')){
		parent.location.reload(true);
	}
});
$(parent).on("afterShow.fb", function(e, instance, slide){
	slide.opts.iframe.preload = false;
	if (typeof slide.$iframe !== "undefined"){
		slide.$iframe.unbind();
	}
});

//Initialize Waves
if (typeof Waves !== "undefined"){
    Waves.init();
}

//Initialize Bootstrap scripts
$("[data-toggle=tooltip]").tooltip();
$("[data-toggle=popover]").popover();

//Initialize fancy & manage Tables data-label
$("table.fancy, table.manage").each(function(){
	var table = $(this);
	var table_header = [];
	table.find("thead tr th").each(function(){
		table_header.push($(this).text());
	});
	table.find("tbody tr").each(function(){
		$(this).find("td").each(function(index){
			if (!$(this).attr("data-label")){
				$(this).attr("data-label",table_header[index]);
			}
		});
	});
});

//Auto add "reverse" class when dropdown width exceeds screen width
$("[data-toggle=dropdown]").parent().on("show.bs.dropdown", function(){
	//Reset dropdown style
	$(this).find(".dropdown-menu").removeAttr("style");
	
	var width = $(this).find(".dropdown-menu").outerWidth();
	var left = $(this).find(".dropdown-menu").offset().left;
	var right = left + width;
	
	var shouldReverse = right > $(window).width() || left < 0; //(English || Arabic)
	var canReverse = left - width > 0 || right + width < $(window).width();	//(English || Arabic)
	
	if (shouldReverse && !canReverse){
		var style = null;
		if ($("html").attr("lang")=="ar"){
			let difference = left - 10;
			style = "left:initial !important; right:" + difference + "px !important";
		} else {
			let difference = -left + 10;
			style = "right:initial !important; left:" + difference + "px !important";		
		}
		$(this).find(".dropdown-menu").attr("style", style);

	} else if (shouldReverse){
		$(this).find(".dropdown-menu").toggleClass("reverse");
	}
});

//Animate modals (On show)
$(document).on("show.bs.modal", ".modal", function(e){
	var modal = $(this);
	var modal_dialog = modal.find(".modal-dialog");
	var animation_in = modal.attr("animation-in");
	var animation_out = modal.attr("animation-out");
	if (animation_in){
		var animation_speed = (modal.attr("animation-in-speed") ? modal.attr("animation-in-speed") : 0.5);
		animateCSS(modal_dialog, animation_in, animation_speed);
	}
});

//Animate modals (On closing)
$(document).on("hide.bs.modal", ".modal", function(e){
	var modal = $(this);
	var modal_dialog = modal.find(".modal-dialog");
	var animation_in = modal.attr("animation-in");
	var animation_out = modal.attr("animation-out");
	if (animation_out && !modal.hasClass("animated")){
		if (!modal.hasClass("hiding")){
			e.preventDefault();
			var animation_speed = (modal.attr("animation-out-speed") ? modal.attr("animation-out-speed") : 0.5);
			$(".modal-backdrop").fadeOut(500);
			animateCSS(modal_dialog, animation_out, animation_speed, function(){
				modal.removeClass("fade").addClass("hiding").modal("hide");
			});
		}
	}
	modal.on("hidden.bs.modal", function(e){
		modal.removeClass("hiding").addClass("fade");
	});
});

//Prevent POST on refresh
if (typeof replaceState === "undefined" || replaceState == false){
	if (window.history.replaceState){
		window.history.replaceState(null, null, window.location.href);
	}
}

//Disable form submit on enter
$("form input").on("keyup keypress", function(e){
	var keyCode = e.keyCode || e.which;
	if (keyCode === 13){
		e.preventDefault();
		if ($(this).attr("disable-enter")){
			return false;
		} else {
			$(this).parent("form").find(".submit").click();
		}
	}
});

//Form submit if (formSubmit) is not defined
if (typeof window.formSubmit === "undefined"){
    window.formSubmit = function(form){
		if (typeof attachmentsBuild === "function"){ attachmentsBuild() }
		if (typeof multipleDataBuild === "function"){ multipleDataBuild() }
		if (typeof fixedDataBuild === "function"){ fixedDataBuild() }
		if (typeof doMenuBuild === "function"){ doMenuBuild() }
		if (typeof onBeforeSubmit === "function"){ onBeforeSubmit() }
		$(form).submit();
    }
}

//Submit button click
$("form .submit:not(.staged)").on("click", function(){
	var targetForm = $(this).parents("form:first");
	var isValid = validateForm(targetForm);
	if (isValid){
		formSubmit($(this).parents("form:first"));
	}
});

//Show loading on form submit
$("form:not([loading=false])").on("submit", function(){
	if (!on_mobile){
		showLoadingCover();
	}
});

//Initialize rellax
if ($("[data-rellax]").length){
	var rellax = new Rellax("[data-rellax]");
}

//Initialize parallax
$("[data-parallax]").each(function(){
	var parallaxInstance = new Parallax($(this)[0]);
});

//Initialize tilt
$("[data-tilt]").each(function(){
	TiltAnimation.init({
		component: $(this)[0],
		speed: $(this).attr("data-tilt-speed"),
		perspective: $(this).attr("data-tilt-perspective"),
		xMultiplier: $(this).attr("data-tilt-x-multiplier"),
		yMultiplier: $(this).attr("data-tilt-y-multiplier"),
	});
});

//Initialize custom animations
$("[data-custom-animation]").each(function(){
	$(this).addClass($(this).data("custom-animation"));
});

//Initialize text animation
$("[data-text-animate]").each(function(){
	var settings = {};
	settings["animation"] = $(this).attr("data-text-animate") || "fade-in";
	settings["duration"] = $(this).attr("data-text-animate-duration") || 500;
	settings["easing"] = $(this).attr("data-text-animate-easing") || "linear";
	settings["start-delay"] = $(this).attr("data-text-animate-start-delay") || 0;
	settings["letter-delay"] = $(this).attr("data-text-animate-letter-delay") || 100;
	$(this).contents().each(function(){
		if (this.nodeType==3){
			$(this).wrap("<span></span>");
		}
		var node = (this.nodeType==3 ? $(this).parent() : $(this));
		initializeTextAnimation(node, settings);
		settings["start-delay"] += (node.text().length * settings["letter-delay"]);
	});
	$(this).css("visibility", "visible");
});

function initializeTextAnimation(object, settings){
	var string = object.text();
	var count = 0;
	var words = string.split(" ");
	var scentence = [];
	for (var word = 0; word < words.length; word++){
		count++;
		var letters = [];
		for (var i = 0; i < words[word].length; i++){
			count++;
			var delay = settings["start-delay"] + (count * settings["letter-delay"]);
			letters.push("<span class=letter data-aos='" + settings["animation"] + "' data-aos-duration='" + settings["duration"] + "' data-aos-easing='" + settings["easing"] + "' data-aos-delay='" + delay + "'>" + words[word][i] + "</span>");
		}
		scentence.push(letters.join(""));
	}
	object.html(" <span class=word>" + scentence.join(" <span class=word></span>") + "</span>");
}