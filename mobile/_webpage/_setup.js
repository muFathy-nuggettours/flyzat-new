//Scroll to middle on input focus
$("input[type=text], input[type=number], input[type=email], input[type=password], [textarea]").not(".date_field, .time_field").focus(function(){
	if (current_platform != "iOS_Application" && current_platform != "iOS_Browser"){
		scrollToView($(this), 30, "start");
	} else {
		$(this)[0].scrollIntoView();
	}
});

//Pull to refresh
if (current_platform != "iOS_Application" && current_platform != "iOS_Browser"){
	if (typeof indexPage === "undefined" || !indexPage){
		var ptr = PullToRefresh.init({
			iconArrow: "<i class='fas fa-arrow-up'></i>",
			iconRefreshing: "<i class='fas fa-sync-alt fa-spin'></i>",	
			distThreshold: 60,
			distMax: 80,
			distReload: 60,
			onRefresh: function(){ sendParentMessage("Reload-Page"); },
			instructionsPullToRefresh: readLanguage.mobile.refresh_pull,
			instructionsReleaseToRefresh: readLanguage.mobile.refresh_release,
			instructionsRefreshing: readLanguage.mobile.refreshing,
			shouldPullToRefresh: function(){
				var top_position = parent.$(document).scrollTop();
				var modal_opened = $(document).find(".modal.in").length;
				var confirm_opened = (typeof jconfirmInstance !== "undefined" ? jconfirmInstance : false);
				var fancybox_opened = parent.$(document).find(".fancybox-container").length;
				var body_iframe = $("body").hasClass("iframe");
				return !top_position && !modal_opened && !confirm_opened && !fancybox_opened && !body_iframe;
			}
		});
	}
}

//Fade in body & end loading
$(document).ready(function(){
	$("body.body", window.parent.document).scrollTop(0); //Fix for iOS
	$("body").css("opacity", 1);
	sendParentMessage("Webview-Loaded");
});

//===== iOS Sepcific =====

if (current_platform == "iOS_Application" || current_platform == "iOS_Browser"){
	//Fix for bootstrap modal on iOS
	(function($){
		$.fn.modal.Constructor.prototype.backdrop = function(callback){
			var that = this
			var animate = this.$element.hasClass("fade") ? "fade" : ""
			if (this.isShown && this.options.backdrop){
				var doAnimate = $.support.transition && animate
				this.$backdrop = $(document.createElement("div")).addClass("modal-backdrop " + animate).appendTo(".applicationContainer") //<== The change is here
				this.$element.on("click.dismiss.bs.modal", $.proxy(function(e){
					if (this.ignoreBackdropClick){
						this.ignoreBackdropClick = false
						return
					}
					if (e.target !== e.currentTarget) return
					this.options.backdrop == 'static' ? this.$element[0].focus() : this.hide()
				}, this))
				if (doAnimate) this.$backdrop[0].offsetWidth
				this.$backdrop.addClass("in")
				if (!callback) return
				doAnimate ? this.$backdrop.one("bsTransitionEnd", callback).emulateTransitionEnd($.fn.modal.BACKDROP_TRANSITION_DURATION) : callback()
			} else if (!this.isShown && this.$backdrop){
				this.$backdrop.removeClass("in")
				var callbackRemove = function(){
					that.removeBackdrop()
					callback && callback()
				}
				$.support.transition && this.$element.hasClass("fade") ? this.$backdrop.one("bsTransitionEnd", callbackRemove).emulateTransitionEnd($.fn.modal.BACKDROP_TRANSITION_DURATION) : callbackRemove()
			} else if (callback){
				callback()
			}
		}
	})(jQuery);
	
	//Close select2 on scroll to avoid overflow
	$(".applicationContainer").scroll(function(e){
		$("select[data-select2-id]").select2("close");
	});
}

/* ===== Create target exceptions ===== */

//URLs
$(document).on("click", "a", function(e){
	var self = $(this)[0];
	var href = $(this).attr("href");
	var inFancybox = $(this).parents("body").hasClass("inline");
	
	//Connection error
	if (!navigator.onLine){
		e.preventDefault();
		connectionError();
		return;
	}
	
	//Keep urls with the following attributes or missing href
	if (href=="#" || self.hasAttribute("data-toggle") || self.hasAttribute("toggle") || self.hasAttribute("data-fancybox")){
		return;
	
	//Ignore urls with empty hrefs
	} else if (!href){
		e.preventDefault();
		return;
		
	//Trigger links on external urls
	} else if (self.hasAttribute("data-file") || self.hasAttribute("mobile-open-external") || linkExternal(self)){
		e.preventDefault();
		triggerLink(href);
	
	//Otherwise load in webview
	} else {
		//If not in fancybox or in fancybox and target is parent
		if ((inFancybox && $(this).attr("target")=="_parent") || !inFancybox){
			e.preventDefault();
			if ($(this).attr("target")=="_parent"){
				$(this).removeAttr("target");
			}
			setWebviewURL(href);
		
		//If in fancybox and target is the same, just show loading
		} else {
			startLoading(true);
		}
	}
});

//Forms
$(document).on("submit", "form", function(e){
	if (!navigator.onLine){
		connectionError();
		
	} else {
		//If target is parent assign target to active webview and show regular page loading
		if ($(this).attr("target")=="_parent" || $(this).attr("target")=="_blank"){
			$(this).attr("target", "activewebview");
			startLoading();
		
		//Otherwise show loading whether it's inside and iframe or regular page
		} else {
			var inFancybox = $(this).parents("body").hasClass("inline") ? true : false;
			startLoading(inFancybox);
		}
	}
});

//Initialize animations
AOS.init();