/* ===== Redefine Functions ===== */

//Show & hide page loading cover
showLoadingCover = function(){ sendParentMessage("Webview-Start-Loading"); }
hideLoadingCover = function(){ sendParentMessage("Webview-End-Loading"); }

//Set window location (Re-written for mobile application)
setWindowLocation = function(href, refresh=false){
	var inFancybox = $("body").hasClass("inline");
	if (inFancybox){
		sendParentMessage("Fancybox-Start-Loading");
		window.location.href = href;
	} else {
		sendParentMessage("Set-Webview-URL", [href, refresh]);
	}
}

/* ===== Data Saving Functions ===== */

//Save custom data
function saveApplicationData(key, data){
	sendApplicationMessage("Save-Data", [key, data]);
}

//Get custom data
function getApplicationData(key){
	sendApplicationMessage("Get-Data", key);
}

//Call data received function
function onDataReceived(key, data){
	var targetFunction = window["onDataReceived_" + key];
	if (typeof targetFunction === "function"){
		targetFunction(data);
	}
}

/* ===== Target Exceptions Functions ===== */

function startLoading(fancybox=false){
	if (!fancybox){
		showLoadingCover();
	} else {
		sendParentMessage("Fancybox-Start-Loading");
	}
}

function triggerLink(url){
	sendApplicationMessage("Trigger-Link", url);
}

function setWebviewURL(src){
	sendParentMessage("Set-Webview-URL", src);
}