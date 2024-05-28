var menuOpened = false;
var overlayPanelOpened = null;
var slowConnectionMessage = null;
var navigationHistory = [];
var customData = {};

/* ===== Application messages ===== */

//Send message to application
function sendApplicationMessage(event_key, event_value=null){
	console.log("sentApplicationMessage", [event_key, event_value]);
	parent.postMessage({
		key: event_key,
		value: event_value
	}, "*");
}

//These functions are used regularly and called from PHP files
function exitApplication(){ sendApplicationMessage("Exit-Application"); }
function triggerLink(value){ sendApplicationMessage("Trigger-Link", value); }

/* ===== Header and footer update ===== */

//Set header title
function setHeaderTitle(title, attributes=true){
	$("#webviewTitle").html(title);
	if (attributes){ $(".mainWebview.active").attr("data-title",title); }
}

//Set header buttons
function setHeaderButtons(buttons, attributes=true){
	$("#mobile_header > [data-label]").hide();
	if (!buttons){ return; }
	$("#mobile_header > [data-label]").each(function(){
		if (buttons.includes($(this).attr("data-label"))){
			$("#mobile_header > [data-label=" + $(this).attr("data-label") + "]").show();
		}
	});
	if (attributes){ $(".mainWebview.active").attr("data-header-buttons",buttons); }
}

//Set footer buttons
function setFooterButtons(buttons, attributes=true){
	$("#mobile_footer > [data-label]").addClass("disabled");
	if (!buttons){ return; }
	$("#mobile_footer > [data-label]").each(function(){
		if (buttons.includes($(this).attr("data-label"))){
			$("#mobile_footer > [data-label=" + $(this).attr("data-label") + "]").removeClass("disabled");
		}
	});
	if (attributes){ $(".mainWebview.active").attr("data-footer-buttons",buttons); }
}

/* ===== Webview URL update ===== */

//Set IFrame URL
function setWebviewURL(src, forceRefresh=false, byBackButtonPress=false){
	var currentWebview = $(".mainWebview.active");
	var cleanPath = cleanWebviewPath(src)
	var targetWebview = $(".mainWebview[src='" + cleanPath + "']");
	
	//Return if offline
	if (!isOnline()){
		connectionError();
		return;
	}
	
	//Avoid double page load
	if (currentWebview.attr("src")==cleanPath){
		if (forceRefresh){
			startLoading();
			$(".mainWebview.active").attr("src", cleanPath);
			return;
		} else {
			return;
		}
	}

	//If body doesn't have keep history attribute, force loading in same frame
	var keepHistory = currentWebview.contents().find("body").attr("keep-history");
	if (keepHistory!=1){
		currentWebview.addClass("remove");
	}
	
	//Unset current active webview
	$(".mainWebview").removeClass("active");
	$(".mainWebview").removeAttr("name"); //--For form target _parent

	//Webview already exists
	if (targetWebview.length){
		if (!byBackButtonPress){
			targetWebview.css("z-index", "10").show("slide", { direction: "left" }, 300, function(){
				targetWebview.css("z-index", "initial");
				currentWebview.css("z-index", "initial").hide();
				pushHistory(targetWebview.attr("src"));
				
				//Remove current webview if it's not kept in history
				if (currentWebview.hasClass("remove")){
					currentWebview.remove();
				}
				
				/* iOS Fix */
				currentWebview.contents().find("#applicationContainer").removeClass("applicationContainer");
				targetWebview.contents().find("#applicationContainer").addClass("applicationContainer");
			});
			
			
		} else {
			targetWebview.css("z-index", "10").show();
			currentWebview.css("z-index", "15").hide("slide", { direction: "left" }, 300, function(){
				targetWebview.css("z-index", "initial");
				currentWebview.css("z-index", "initial");

				//Remove current webview if it's not kept in history
				if (currentWebview.hasClass("remove")){
					currentWebview.remove();
				}
				
				/* iOS Fix */
				currentWebview.contents().find("#applicationContainer").removeClass("applicationContainer");
				targetWebview.contents().find("#applicationContainer").addClass("applicationContainer");
			});
		}
		
		targetWebview.addClass("active");
		targetWebview.attr("name", "activewebview"); //--For form target _parent
		setHeaderTitle(targetWebview.attr("data-title"));
		setHeaderButtons(targetWebview.attr("data-header-buttons"));		

	//Webview doesn't exist
	} else {
		startLoading();
		$(".mainWebview.initial")
			.clone()
			.removeClass("initial")
			.addClass("active")
			.attr("src", cleanPath)
			.attr("name", "activewebview")
			.insertBefore("#webviewsWrapper")
			.css("z-index","10").show("slide", { direction: "left" }, 300, function(){
				$(this).css("z-index","initial");
				currentWebview.hide();
			});
		
		//Remove current webview if it's not kept in history
		if (currentWebview.hasClass("remove")){
			currentWebview.remove();
		}
	}
}

/* ===== Functions ===== */

//Start mobile application
function startApplication(src=null, title="", header_buttons=[], footer_buttons=[]){
	setHeaderTitle(title); //Set header title
	setHeaderButtons(header_buttons); //Show header buttons
	setFooterButtons(footer_buttons); //Set footer buttons
	setWebviewURL(src) //Set webview URL
}

//Reload current webview page
function reloadPage(){
	setWebviewURL($(".mainWebview.active").attr("src"), true);
}

//Check if online
function isOnline(){ return navigator.onLine; }

/* ===== History management ===== */

//Clean webview path
function cleanWebviewPath(path){
	return (path==$("base").attr("href") || !path ? "." : path.replace($("base").attr("href"),"").replace(/\/\s*$/,""));
}

//Push to navigation history
function pushHistory(src){
	var cleanPath = cleanWebviewPath(src);
	var filterPaths = ["broken-link"];
	//Push history if not duplicate from current page
	if (navigationHistory[navigationHistory.length - 1] != cleanPath && $.inArray(cleanPath,filterPaths) === -1){
		navigationHistory.push(cleanPath);
	}
}

//Remove from navigation history
function removeHistory(src){
	for (var i = navigationHistory.length-1; i--;){
		if (navigationHistory[i] === src){
			navigationHistory.splice(i, 1);
		}
	}
}

//Reset navigation
function resetNavigation(){
	navigationHistory = ["."];
	$(".mainWebview").not(".initial").not(".active").remove();
}

//Remove webviews
function removeWebviews(paths){
	paths.forEach(function(src, index){
		var cleanPath = cleanWebviewPath(src);
		var targetWebview = $(".mainWebview[src='" + cleanPath + "']");
		removeHistory(cleanPath);
		targetWebview.remove();
	});
}

/* ===== Loading functions ===== */

//Show loading cover
function startLoading(targetFancybox=false){
	if (!targetFancybox){
		$(".jconfirm").remove();
		$(".fancybox-container").remove();
	} else {
		$("#webviewLoader").css("z-index", 100000);
	}
	$("#slow_connection").hide();
	$("#webviewLoader").fadeIn(100);
	monitorConnection(true);
}

//Hide loading cover
function endLoading(){
	$("#slow_connection").hide();
	$("#webviewLoader").fadeOut(100, function(){
		$(this).css("z-index", 100)
	});
	monitorConnection(false);
}

/* ===== Click back button ===== */

function backButton(){
	//If fancybox modal is open close it
	if ($(document).find(".fancybox-container")[0]){
		$(document).find(".fancybox-container:last-child").find(".fancybox-button--close").trigger("click");
	
	//If js confirm message is open close it
	} else if ($(".mainWebview.active")[0].contentWindow.jconfirmInstance){
		$(".mainWebview.active")[0].contentWindow.jconfirmInstance.close();

	//If bootstrap modal is open close it
	} else if ($(".mainWebview.active").contents().find(".modal").hasClass("in")){
		$(".mainWebview.active").contents().find(".modal").find("[data-dismiss=modal]").trigger("click");
	
	//If menu is open close it
	} else if (menuOpened){
		hideNavMenu();

	//If overlay page is open close it
	} else if (overlayPanelOpened){
		if ($("#" + overlayPanelOpened).attr("data-mandatory")){
			promptExitApplication();
		} else {
			hideOverlayPanel(overlayPanelOpened);
		}

	//If on home page show back message
	} else if (typeof $(".mainWebview.active")[0].contentWindow.indexPage !== "undefined" && $(".mainWebview.active")[0].contentWindow.indexPage == true){
		promptExitApplication();
		
	//Go back if there is an internet connection
	} else {
		navigationHistory.pop();
		var previousPage = navigationHistory[navigationHistory.length - 1];
		setWebviewURL((previousPage ? previousPage : "."), false, true);
	}
}

//Prompt exit application message
var exitMessage = null;

function promptExitApplication(){
	if (exitMessage){
		exitApplication();
	} else {
		exitMessage = $.confirm({
			title: readLanguage.mobile.exit_application,
			content: readLanguage.mobile.exit_message,
			animateFromElement:false,
			containerFluid: true,
			buttons: {
				confirm: {
					text: readLanguage.mobile.footer_exit,
					btnClass: "btn-primary",
					action: function(){
						exitApplication();
					}
				},
				cancel: {
					text: readLanguage.plugins.message_cancel,
					action: function(){
						exitMessage = null;
					}
				}
			}
		});
	}
}

/* ===== Connectivity functions ===== */

//Prompt connection error
function connectionError(){
	endLoading();
	showOverlayPanel("no_connection");
	$(".jconfirm").remove();
	$(".fancybox-container").remove();
}

//Retry connection
function retryConnection(){
	if (isOnline()){
		reloadPage();
		hideOverlayPanel("no_connection");
	} else {
		return;
	}
}

//Monitor slow connection
function monitorConnection(isActive){
	if (isActive){
		window.addEventListener("offline",connectionError);
		if (slowConnectionMessage){
			clearTimeout(slowConnectionMessage);
			slowConnectionMessage = null;
		}
		slowConnectionMessage = setTimeout(function(){
			$("#slow_connection").show(); /* Or reloadPage(); */
		}, 10000); /* 10 Seconds */
	} else {
		clearTimeout(slowConnectionMessage);
		$("#slow_connection").hide();
	}
}

/* ===== Navigation Menu ===== */

//Initialize main menu
$(document).ready(function(){
	$(".nav-dropdown-item").click(dropdownClick).on("click", "div", function(e){
		e.stopPropagation();
	});
	$(".nav-cover").click(hideNavMenu);
});

//Override navigation menu dropdown click
function dropdownClick(){
	event.preventDefault();
	var navDropdown = $(this).find(".nav-dropdown");
	$(".nav-dropdown-item.active").each(function(){
		hideDropdown($(this));
	});
	if (navDropdown.css("display") == "none"){
		showDropdown($(this));
	} else {
		hideDropdown($(this));
	}
}

//Hide navigation dropdown
function hideDropdown(object){
	if (!object.hasClass("animating")){
		object.addClass("animating");
		var objHeight = object.find(".nav-dropdown").height();
		object.find(".nav-dropdown").animate({
			height: "0px"
		}, 300, function(){
			$(this).css("height", objHeight + "px").css("display", "none");
			object.removeClass("animating");
		});
		object.removeClass("active");
	}
}

//Show navigation dropdown
function showDropdown(object){
	if (!object.hasClass("animating")){
		object.addClass("animating");
		var objHeight = object.find(".nav-dropdown").height();
		object.find(".nav-dropdown").css("height", "0px").css("display", "block").animate({
			height: objHeight + "px"
		}, 300, function(){
			scrollToView(object,-parseFloat(object.css("border-bottom-width")),"start");
			object.removeClass("animating");
		});
		object.addClass("active");
	}
}

//Hide navigation menu
function hideNavMenu(){
	$(".nav-cover").css("visibility", "hidden").css("opacity", "0").hide();
	$(".nav-menu").removeClass("nav-menu-opened").delay(500).queue(function(){
		$(".nav-dropdown-item.active").each(function(){
			hideDropdown($(this));
		});
		$(".nav-menu").css("transition", "transform 0s");
		$(this).dequeue();
	});
	menuOpened = false;
}

//Show navigation menu
function showNavMenu(){
	$(".nav-cover").show().css("visibility", "visible").css("opacity", "1");
	$(".nav-menu").css("transition", "transform 0.5s").addClass("nav-menu-opened");
	menuOpened = true;
}

/* ===== Show/hide overlay panels ===== */

//Show Overlay Page
function showOverlayPanel(id){
	if (id != overlayPanelOpened){
		hideNavMenu();
		var targetPage = $("#" + id + ".overlay_panel");
		setHeaderTitle(targetPage.attr("data-title"),false);
		setHeaderButtons(targetPage.attr("data-header-buttons"),false);
		setFooterButtons(targetPage.attr("data-footer-buttons"),false);
		targetPage.show("slide", { direction: "left" }, 300);
		overlayPanelOpened = id;
	}
}

//Hide overlay page
function hideOverlayPanel(id){
	if (id == overlayPanelOpened){
		var targetPage = $("#" + id + ".overlay_panel");
		setHeaderTitle($(".mainWebview.active").attr("data-title"));
		setHeaderButtons($(".mainWebview.active").attr("data-header-buttons"));
		setFooterButtons($(".mainWebview.active").attr("data-footer-buttons"));
		targetPage.hide("slide", { direction: "left" }, 300);
		overlayPanelOpened = null;
	}
}

/* ===== Listen for Messages from webpage ===== */

$(document).ready(function(){
	var eventMethod = window.addEventListener ? "addEventListener" : "attachEvent";
	var eventListen = window[eventMethod];
	var messageEvent = eventMethod == "attachEvent" ? "onmessage" : "message";
	eventListen(messageEvent,function(e){
		console.log("webviewMessageReceived", [e.data.key, e.data.value]);
		switch (e.data.key){
			//Send message directly to cordova
			case "Send-Application-Message":
				sendApplicationMessage(e.data.value.key, e.data.value.value);
			break;
			
			//Pass received data
			case "Pass-Data":
				//If function exists in webview call it, otherwise pass to webpage
				var targetFunction = window["onDataReceived_" + e.data.value.key];
				if (typeof targetFunction === "function"){
					targetFunction(e.data.value.value);
				} else {
					$(".mainWebview.active")[0].contentWindow.onDataReceived(e.data.value.key, e.data.value.value);
				}
			break;

			//Call a function in webview
			case "Call-Function":
				window[e.data.value]();
			break;
			
			//Open external URL
			case "Open-External-URL":
				triggerLink(e.data.value);
			break;

			//Set iframe URL
			case "Set-Webview-URL":
				if (typeof e.data.value === "string"){
					setWebviewURL(e.data.value);
				} else {
					setWebviewURL(e.data.value[0], e.data.value[1]);
				}
			break;

			//Reload iframe URL
			case "Reload-Page":
				reloadPage();
			break;

			//Iframe loaded and ready
			case "Webview-Loaded":
				endLoading();
				window.removeEventListener("offline", connectionError);
				pushHistory($(".mainWebview.active").attr("src"));
			break;
				
			//Webview started loading
			case "Webview-Start-Loading":
				startLoading();
			break;
			
			//Webview ended loading
			case "Webview-End-Loading":
				endLoading();
			break;
				
			//Fancybox started loading
			case "Fancybox-Start-Loading":
				startLoading(true);
			break;

			//Set webview attributes
			case "Set-Webview-Attributes":
				setHeaderTitle(e.data.value[0]);
				setHeaderButtons(e.data.value[1]);
				setFooterButtons(e.data.value[2]);
			break;

			//Update Floating Button Visibility
			case "Update-Floating-Button-Visibility":
				if (!e.data.value){ //Hide floating button
					$(".floating_button").addClass("inactive");
				} else { //Show floating button
					$(".floating_button").removeClass("inactive");
				}
			break;

			//Default
			default: break;
		}
	}, false);
});

/* ===== Initializations ===== */

//Initialize JSConfirm
jconfirm.defaults = {
	rtl: ($("html").attr("dir")=="rtl" ? true : false),
	animateFromElement: false,
	containerFluid: true,
};

//Initialize fancybox
$.fancybox.defaults.buttons = ["close"];
$.fancybox.defaults.autoSize = false;
$.fancybox.defaults.protect = true;
$.fancybox.defaults.iframe.preload = true;
$.fancybox.defaults.hideScrollbar = false;
$.fancybox.defaults.backFocus = false;

//On document ready
$(document).ready(function(){
	//Initialize Waves
	if (typeof Waves !== "undefined"){
		Waves.init();
	}
});

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
	
	//Always hide navigation menu
	hideNavMenu();
});

//Forms
$(document).on("submit", "form", function(e){
	if (!navigator.onLine){
		connectionError();
		
	} else {
		$(this).prop("target", "activeWebview");
		startLoading();
	}
});