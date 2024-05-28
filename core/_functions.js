//Show & hide page loading cover
function showLoadingCover(){ $("#page-loading", window.parent.document).css("display", "flex"); }
function hideLoadingCover(){ $("#page-loading", window.parent.document).fadeOut(200); }

//Toggle visibility
function toggleVisibility(control, name=null, value=null){
	var control = (control ? $(control) : $("[name^='" + name + "']"));
	var name = (name ? name : control[0].name);
	var show = false;
	
	//Visibility
	$("[visibility-control='" + name + "'][visibility-value]").hide().each(function(){
		var values = $(this).attr("visibility-value").split(',');
		if (control.attr("type")=="radio"){
			show = (control.prop("checked")==true && values.indexOf((value ? value : control.val()))!==-1);
		} else if (control.attr("type")=="checkbox"){
			show = false;
			$("[name^='" + name + "']").each(function(){
				if (values.indexOf($(this).val())!==-1 && $(this).prop("checked")==true){
					show = true;
				}
			});
		} else {
			show = (values.indexOf((value ? value : control.val()))!==-1);
		}
		if (show){
			$(this).find("[validate-mandatory]").attr("data-validation-optional", "false");
			$(this).find("[validate-optional]").attr("data-validation", $(this).find("[validate-optional]").attr("x-data-validation"));
			$(this).show();
		} else {
			$(this).find("[validate-mandatory]").attr("data-validation-optional", "true");
			$(this).find("[validate-optional]").removeAttr("data-validation");
		}
	});
	
	//Invisibility
	$("[visibility-control='" + name + "'][invisibility-value]").show();
	$("[visibility-control='" + name + "'][invisibility-value='" + (value!=null ? value : $(control).val()) + "']").hide();
}

//Scroll to Element
function scrollToView(selector, topOffset=0, block="center", speed=300){
	var scrollParent = selector.scrollParent();
	var scrollableParent = null;
	var parentIsBody = false;
	if (scrollParent.prop("tagName")=="BODY"){
		scrollableParent = $("html,body");
		parentIsBody = true;
	} else {
		scrollableParent = scrollParent;
	}

	switch (block){
		case "start":
			offsetPosition = (!parentIsBody ? scrollableParent.scrollTop() : 0) + (!parentIsBody ? $(selector).position().top : $(selector).offset().top) - topOffset;
		break;

		case "center":
			offsetPosition = $(selector).offset().top - ($(window).height()/2) + ($(selector).height() / 2) - topOffset;
		break;
	}

	scrollableParent.stop().animate({
		scrollTop: (offsetPosition ? offsetPosition : 0)
	}, speed);
}

//Set cookie
function setCookie(name, value, hours){
	if (!on_mobile){
		var expires = new Date();
		expires.setTime(expires.getTime() + (hours * 60 * 60 * 1000));
		var expires = "expires=" + expires.toUTCString();
		document.cookie = name + "=" + value + ";" + expires + "; path=/";
	} else {
		$.ajax({
			method: "POST",
			url: "requests/",
			data: {
				token: user_token,
				action: "set-cookie",
				name: name,
				value: value,
				expires: (hours * 60 * 60)
			},
		}).done(function(response){
			sendApplicationMessage("Update-Cookies");
		});
	}
}

//Get cookie
function getCookie(name){
    var name = name + "=";
    var decodedCookie = decodeURIComponent(document.cookie);
    var ca = decodedCookie.split(';');
    for(var i = 0; i <ca.length; i++){
        var c = ca[i];
        while (c.charAt(0) == ' '){
            c = c.substring(1);
        }
        if (c.indexOf(name) == 0){
            return c.substring(name.length, c.length);
        }
    }
    return "";
}

//Quick message box
function messageBox(title, message, icon, color, theme="modern"){
	$.alert({
		title: title,
		content: message,
		icon: icon,
		type: color,
		theme: theme
	});
}

//Quick notification message
function quickNotify(message, title=null, theme="success", icon=null, show_progress=false, url=null, target=null){
	$.notify({
			icon: icon,
			title: title,
			message: message,
			url: url,
			target: target
		},{
			element: "body",
			position: null,
			type: theme,
			allow_dismiss: true,
			newest_on_top: false,
			showProgressbar: show_progress,
			placement: {
				from: "bottom",
				align: "right"
			},
			offset: 10,
			spacing: 10,
			z_index: 999999999,
			delay: 4000,
			timer: 100,
			url_target: "_blank",
			mouse_over: null,
			animate: {
				enter: "animated faster fadeInRight",
				exit: "animated faster fadeOutLeft"
			},
			onShow: null,
			onShown: null,
			onClose: null,
			onClosed: null,
			icon_type: "class",
			template: "<div data-notify=container class='bootstrap_notification alert alert-{0}' role=alert>" +
				"<button type=button aria-hidden=true class=close data-notify=dismiss>×</button>" +
				"<div data-notify=body>" +
				"<span data-notify=icon></span>" +
				"<div>" + (title ? "<span data-notify=title>{1}</span>" : "") + "<span data-notify=message>{2}</span></div>" + "</div>" +
				"<div class=progress data-notify=progressbar>" +
					"<div class='progress-bar progress-bar-{0}' role=progressbar aria-valuenow=0 aria-valuemin=0 aria-valuemax=100 style='width:0%'></div>" +
				"</div>" +
				"<a href='{3}' target='{4}' data-notify=url></a>" +
			"</div>"
	});
}

//PHP date equivalent function (format, js date object or PHP unix timestamp)
function date(n, t){
	var localize = $("html").attr("lang");
	t = (typeof t.now === "function" ? t.now() : t);
	let e, r;
	if (localize=="ar"){
		var array = ["الأحد", "الإثنين", "الثلاثاء", "الأربعاء", "الخميس", "الجمعة", "السبت", "يناير", "فبراير", "مارس", "إبريل", "مايو", "يونيو", "يوليو", "أغسطس", "سبتمبر", "أكتوبر", "نوفمبر", "ديسمبر"];		
	} else {
		var array = ["Sunday", "Monday", "Tuesday", "Wedday", "Thursday", "Friday", "Saturday", "January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
	}
	const u = array,
	o = /\\?(.?)/gi,
		c = function(n, t){
			return r[n] ? r[n]() : t
		},
		i = function(n, t){
			for (n = String(n); n.length < t;) n = "0" + n;
			return n
		};
	r = {
		d: function(){
			return i(r.j(), 2)
		},
		D: function(){
			return r.l().slice(0, 3)
		},
		j: function(){
			return e.getDate()
		},
		l: function(){
			return u[r.w()]
		},
		N: function(){
			return r.w() || 7
		},
		S: function(){
			const n = r.j();
			let t = n % 10;
			return t <= 3 && 1 === parseInt(n % 100 / 10, 10) && (t = 0), ["st", "nd", "rd"][t - 1] || "th"
		},
		w: function(){
			return e.getDay()
		},
		z: function(){
			const n = new Date(r.Y(), r.n() - 1, r.j()),
				t = new Date(r.Y(), 0, 1);
			return Math.round((n - t) / 864e5)
		},
		W: function(){
			const n = new Date(r.Y(), r.n() - 1, r.j() - r.N() + 3),
				t = new Date(n.getFullYear(), 0, 4);
			return i(1 + Math.round((n - t) / 864e5 / 7), 2)
		},
		F: function(){
			return u[6 + r.n()]
		},
		m: function() {
			return i(r.n(), 2)
		},
		M: function(){
			return r.F().slice(0, 3)
		},
		n: function(){
			return e.getMonth() + 1
		},
		t: function(){
			return new Date(r.Y(), r.n(), 0).getDate()
		},
		L: function(){
			const n = r.Y();
			return n % 4 == 0 & n % 100 != 0 | n % 400 == 0
		},
		o: function(){
			const n = r.n(),
				t = r.W();
			return r.Y() + (12 === n && t < 9 ? 1 : 1 === n && t > 9 ? -1 : 0)
		},
		Y: function(){
			return e.getFullYear()
		},
		y: function(){
			return r.Y().toString().slice(-2)
		},
		a: function(){
			return e.getHours() > 11 ? (localize=="ar" ? "م" : "pm") : (localize=="ar" ? "ص" : "am")
		},
		A: function(){
			return r.a().toUpperCase()
		},
		B: function(){
			const n = 3600 * e.getUTCHours(),
				t = 60 * e.getUTCMinutes(),
				r = e.getUTCSeconds();
			return i(Math.floor((n + t + r + 3600) / 86.4) % 1e3, 3)
		},
		g: function(){
			return r.G() % 12 || 12
		},
		G: function(){
			return e.getHours()
		},
		h: function(){
			return i(r.g(), 2)
		},
		H: function(){
			return i(r.G(), 2)
		},
		i: function(){
			return i(e.getMinutes(), 2)
		},
		s: function(){
			return i(e.getSeconds(), 2)
		},
		u: function(){
			return i(1e3 * e.getMilliseconds(), 6)
		},
		e: function(){
			throw new Error("Not supported")
		},
		I: function(){
			return new Date(r.Y(), 0) - Date.UTC(r.Y(), 0) != new Date(r.Y(), 6) - Date.UTC(r.Y(), 6) ? 1 : 0
		},
		O: function(){
			const n = e.getTimezoneOffset(),
				t = Math.abs(n);
			return (n > 0 ? "-" : "+") + i(100 * Math.floor(t / 60) + t % 60, 4)
		},
		P: function(){
			const n = r.O();
			return n.substr(0, 3) + ":" + n.substr(3, 2)
		},
		T: function(){
			return "UTC"
		},
		Z: function(){
			return 60 * -e.getTimezoneOffset()
		},
		c: function(){
			return "Y-m-d\\TH:i:sP".replace(o, c)
		},
		r: function(){
			return "D, d M Y H:i:s O".replace(o, c)
		},
		U: function(){
			return e / 1e3 | 0
		}
	};
	return function(n, t) {
		return e = void 0 === t ? new Date : t instanceof Date ? new Date(t) : new Date(1e3 * t), n.replace(o, c)
	}(n, t)
}

//Bind calendar picker to input
function createCalendar(targetElement, defaultDate, minDate=null, maxDate=null, yearRange=null, container=null, showTime=false, removable=false){
	var target = (targetElement instanceof jQuery ? targetElement : ($(targetElement).length ? $(targetElement) : $("#" + targetElement)));

	var calendarLanguageEN = {
		notSpecified  : "Not Specified",
		time          : "Time",
		previousMonth : "Previous Month",
		nextMonth     : "Next Month",
		months        : ["January","February","March","April","May","June","July","August","September","October","November","December"],
		weekdays      : ["Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday"],
		weekdaysShort : ["Sun","Mon","Tue","Wed","Thu","Fri","Sat"],
		midnight      : "Midnight",
		noon          : "Noon"
	}
	
	var calendarLanguageAR = {
		notSpecified  : "غير محدد",
		time          : "الوقت",
		previousMonth : "الشهر السابق",
		nextMonth     : "الشهر التالي",
		months        : ["يناير","فبراير","مارس","إبريل","مايو","يونيو","يوليو","أغسطس","سبتمبر","اكتوبر","نوفمبر","ديسمبر"],
		weekdays      : ["الأحد","الإثنين","الثلاثاء","الأربعاء","الخميس","الجمعة","السبت"],
		weekdaysShort : ["الأحد","الإثنين","الثلاثاء","الأربعاء","الخميس","الجمعة","السبت"],
		midnight      : "منتصف الليل",
		noon          : "ظهرا"
	}
	
	var calendarLanguageCurrent = ($("html").attr("lang")=="ar" ? calendarLanguageAR : calendarLanguageEN);
	
	var datePicker = new Pikaday({
		field: target[0],
		toString(date){
			var day = date.getDate();
			var month = date.getMonth() + 1;
			var year = date.getFullYear();
			var hour = date.getHours();
			var minute = date.getMinutes();
			hour = (hour < 10 ? "0" + hour : hour);
			minute = (minute < 10 ? "0" + minute : minute);
			return `${day}/${month}/${year}` + (showTime ? ` ${hour}:${minute}` : "");
		},
		parse(dateString){
			var split = dateString.split(" ");
			var date = split[0].split("/");
			var day = parseInt(date[0], 10);
			var month = parseInt(date[1] - 1, 10);
			var year = parseInt(date[1], 10);
			var time = (showTime ? split[1].split(":") : null);
			var hours = (time ? parseInt(time[0], 10) : null);
			var minutes = (time ? parseInt(time[1], 10) : null);
			return new Date(year, month, day, hours, minutes);
		},
		showTime: showTime,
		showMinutes: true,
		use24hour: false,
		incrementHourBy: 1,
		incrementMinuteBy: 1,
		timeLabel: calendarLanguageCurrent.time,
		firstDay: 6,
		minDate: (minDate ? minDate : new Date(1900, 0, 1)),
		maxDate: (maxDate ? maxDate : new Date((new Date()).getFullYear() + 10, 11, 31)),
		yearRange: (yearRange ? yearRange : [1900,2100]),
		defaultDate: (defaultDate.getTime() ? defaultDate : new Date()),
		setDefaultDate: defaultDate,
		position: (target.css("text-align")=="right" ? "bottom right" : "bottom left"),
		reposition: false,
		container: container,
        i18n: calendarLanguageCurrent
	});
	
	if (!defaultDate.getTime()){
		target.val("").attr("placeholder", calendarLanguageCurrent.notSpecified);
	}
	
	if (removable){
		$("<span class=calendar-reset>×</span>").on("click",function(){
			target.val("").attr("placeholder", calendarLanguageCurrent.notSpecified);
		}).insertAfter(target);
	}
	
	$(window).on("resize",function(){
		datePicker.hide();
	});
	
	return datePicker;
}

//Bind time picker to input
function createTime(targetElement, defaultTime, minTime=null, maxTime=null, step=30, forceRound=true){
	var target = (targetElement instanceof jQuery ? targetElement : ($(targetElement).length ? $(targetElement) : $("#" + targetElement)));
	
	target.timepicker({
		scrollDefault:defaultTime,
		timeFormat: "h:i A",
		forceRoundTime: forceRound,
		step: step,
		minTime: minTime,
		maxTime: maxTime,
		orientation: (target.css("text-align")=="right" ? "rb" : "lb")
	});
	
	target.timepicker("setTime", defaultTime);
	
	$(window).on("resize",function(){
		target.timepicker("hide");
	});
}

//Before unload prompt
function beforeUnload(value){
	if (value){
		window.onbeforeunload = function(e){ return true; };
	} else {
		window.onbeforeunload = null;
	}
}

//Set select value
function setSelectValue(selector, value){
	var optionExists = ($(selector + " option[value='" + value + "']").length > 0);
	if (optionExists){
		$(selector).val(value);
	}
}

//Set select text
function setSelectText(selector, value){
	$(selector + " option").filter(function(){
		return $(this).text() == value; 
	}).attr("selected", true);
}

//Disable empty inputs on form submit
function disableEmptyInputs(form){ //To be used on form (onsubmit)
	var controls = form.elements;
	for (var i=0, iLen=controls.length; i<iLen; i++){
		controls[i].disabled = controls[i].value == "";
	}
}

//Create canonical URL
function createCanonical(value, target, extra_hash=false){
	var target = (target instanceof jQuery ? target : (typeof target=="string" && target.charAt(0).match(/[a-z]/i) ? $("#" + target) : $(target)));
	target.removeClass("error");
	if (!target.hasClass("disabled")){
		var text = value;
		text = text.replace(/ /g,"-");
		text = text.replace(/[&\/\\#,،+=@()$~%.'":*?<>{}]/g,'');
		text = text.replace(/-+/g,"-");
		if (extra_hash){
			text = text + "-" + uniqid();
		}
		target.val(text.toLowerCase());
	}
}

//Toggle canonical lock
function toggleCanonicalLock(target, button){
	var target = (target instanceof jQuery ? target : (typeof target=="string" && target.charAt(0).match(/[a-z]/i) ? $("#" + target) : $(target)));
	var button = (button instanceof jQuery ? button : (typeof button=="string" && button.charAt(0).match(/[a-z]/i) ? $("#" + button) : $(button)));
	target.removeClass("error");
	if (target.hasClass("disabled")){
		target.removeClass("disabled");
		button.find("i").removeClass("fa-lock-open").addClass("fa-lock");
	} else {
		if (target.val()){
			target.addClass("disabled");
			button.find("i").removeClass("fa-lock").addClass("fa-lock-open");
		} else {
			target.addClass("error");
		}
	}
}

//JSON validation
function validateJSON(str){
	try {
		var json = JSON.parse(str);
	} catch (e){
		return false;
	}
	return json;
}

//Generate UniqID
function uniqid(prefix="", random=false){
    const sec = Date.now() * 1000 + Math.random() * 1000;
    const id = sec.toString(16).replace(/\./g, "").padEnd(14, "0");
    return `${prefix}${id}${random ? `.${Math.trunc(Math.random() * 100000000)}`:""}`;
}

//Get YouTube video ID
function getYoutubeID(url){
	var regExp = /^.*(youtu\.be\/|v\/|u\/\w\/|embed\/|watch\?v=|\&v=)([^#\&\?]*).*/;
	var match = url.match(regExp);
	if (match && match[2].length == 11){
		return match[2];
	}
}

//Add comma to numbers
function numberFormat(val){
	while (/(\d+)(\d{3})/.test(val.toString())){
		val = val.toString().replace(/(\d)(?=(\d\d\d)+(?!\d))/g, "$1,");
	}
	return val;
}

//Truncate text
function maxLength(string, letters, prefix=".."){
   if (string.length > letters){
		return string.substring(0,letters) + prefix;
   } else {
		return string;
   }
}

//Left trim specific characters
function ltrim(string, characters){
	if (typeof characters=="string"){
		var regExp = new RegExp("^" + characters, "i");
		return (string + "").replace(regExp, "");
	} else {
		return string.slice(0, -characters);
	}
}

//Right trim specific characters
function rtrim(string, characters){
	if (typeof characters=="string"){
		var regExp = new RegExp(characters + "$", "i");
		return string.replace(regExp, "");
	} else {
		return string.substring(characters);
	}	
}

//Decode HTML (Renders escaped HTML entities)
function decodeHTML(html){
	var txt = document.createElement("textarea");
	txt.innerHTML = html;
	var value = txt.value;
	txt.remove();
	return value;
}

//Escape HTML
function escapeHTML(text){
	return text
		.replace(/&/g, "&amp;")
		.replace(/</g, "&lt;")
		.replace(/>/g, "&gt;")
		.replace(/"/g, "&quot;")
		.replace(/'/g, "&#039;");
}

//Escape HTML
function unescapeHTML(text){
  return text
		.replace(/&amp;/g, "&")
		.replace(/&lt;/g, "<")
		.replace(/&gt;/g, ">")
		.replace(/&quot;/g, '"')
		.replace(/&#039;/g, "'");
}

//Parse URL
function parseURL(url){
	var urlComponent = document.createElement("a");
	urlComponent.href = url;
	return urlComponent;
}

//Force Download
function forceDownload(href, filename){
	var link = document.createElement("a");
	link.href = href;
	link.download = filename;
	link.click();
	window.URL.revokeObjectURL(href);	
}

//Check if a string can split
var canSplit = function(str, token){
    return (str || '').split(token).length > 1;         
}

//Validate URL
const isValidURL = (string) => {
	try {
		new URL(string);
		return true;
	} catch (_){
		return false;  
	}
}

//Set window location (Re-written for mobile application)
function setWindowLocation(href, refresh=false){
	window.location.href = href;
}

//Create & submit form
function postForm(params, path=null, method="post", target=null, show_loading=true){
    var form = document.createElement("form");
    form.setAttribute("method", method);
   
	if (path){ form.setAttribute("action", path) }
	if (target){ form.setAttribute("target", target) }
	
	var tokenField = document.createElement("input");
	tokenField.setAttribute("type", "hidden");
	tokenField.setAttribute("name", "token");
	tokenField.setAttribute("value", user_token);
	form.appendChild(tokenField);	
   
   for(var key in params){
        if(params.hasOwnProperty(key)){
            var hiddenField = document.createElement("input");
            hiddenField.setAttribute("type", "hidden");
            hiddenField.setAttribute("name", key);
            hiddenField.setAttribute("value", params[key]);
            form.appendChild(hiddenField);
         }
    }
    document.body.appendChild(form);

	//If on mobile application
	if (on_mobile){
		if (!navigator.onLine){
			sendParentMessage("Call-Function", "connectionError");
			
		} else {
			//If target is parent assign target to active webview and show regular page loading
			if (target == "_parent" || target == "_blank"){
				form.setAttribute("target", "activewebview");
				sendParentMessage("Webview-Start-Loading");
			
			//Otherwise show loading whether it's inside and iframe or regular page
			} else if (!target){
				if ($("body").hasClass("inline")){
					sendParentMessage("Fancybox-Start-Loading");
				} else {
					sendParentMessage("Webview-Start-Loading");
				}
			
			//If any other target is set, just show webview loading
			} else {
				sendParentMessage("Webview-Start-Loading");
			}
			
			form.submit();
		}
		
	//If on browser
	} else {
		if ((!target || (target && target != "_blank")) && show_loading){
			showLoadingCover();
		}
		form.submit();
	}
}

//Serialize form to JSON object
$.fn.serializeObject = function(){
	var o = {};
	var a = this.serializeArray();
	$.each(a, function(){
		if (o[this.name]){
			if (!o[this.name].push){
				o[this.name] = [o[this.name]];
			}
			o[this.name].push(this.value || "");
		} else {
			o[this.name] = this.value || "";
		}
	});
	return o;
};

//Reconstruct header parameters
function reconstructHeaderParameters(remove=[], append=[]){
	var current_parameters = window.location.search.substring(1).split("&");
	var new_parameters = [];
	if (current_parameters){
		for (index = 0; index < current_parameters.length; ++index){
			var parameter = current_parameters[index];
			var param_name = parameter.split("=")[0];
			var param_value = parameter.split("=")[1];
			if (param_name && !remove.includes(param_name)){
				new_parameters.push(param_name + "=" + param_value);
			}
		}
	}
	for (index = 0; index < append.length; ++index){
		new_parameters.push(append[index]);
	}
	return (new_parameters.length ? "?" + new_parameters.join("&") : "");
}

//Copy text
function copyText(value, message=null){
    var dummy = document.createElement("textarea");
    document.body.appendChild(dummy);
    dummy.value = value;
    dummy.select();
    document.execCommand("copy");
    document.body.removeChild(dummy);
	if (message !== false){
		quickNotify((message ? message : readLanguage.plugins.copied), "", "success", "fas fa-copy fa-2x");
	}
}

//========== Plugins Functions ==========

//Build doMenu
function doMenuBuild(){
	$("[domenu-input]").each(function(){
		var domenu = $($(this).attr("domenu-input"));
		if (domenu.find(".dd-item").length){
			domenu.find(".dd-item").each(function(){
				contentValues = {};
				$(this).find(".dd3-content").first().find("[category-item]").each(function(){
					contentValues[$(this).attr("category-item")] = $(this).val();
				});
				$(this).data("content", contentValues);
			});
			$(this).val(domenu.domenu().toJson());
		} else {
			$(this).val("");
		}
	});
}

//Validate single input
function validateInput(selector){
	$form = $("<form></form>");
	$(selector).clone().removeAttr("data-validation-optional").appendTo($form);
	var inputValid = validateForm($form, false);
	$form.remove();
	return inputValid;
}

//Validate form without submission
function validateForm(form, verbose=true){
	var targetForm = $(form);
	var errors = [];
	if (typeof onBeforeValidation === "function"){
		onBeforeValidation();
	}
	var validationConfiguration = {
		validateOnBlur: false,
		errorMessagePosition: (targetForm.attr("error-message-position") ?? "top"),
		scrollToTopOnError: false,
		validateHiddenInputs: true,
		language: validationLanguage,
		onElementValidate: function(valid, $el, $form, errorMess){
			if (!valid){
				errors.push({el: $el, error: errorMess});
			}
		}
	};
	validationConfiguration.inlineErrorMessageCallback = function($input, errorMessage, config){
		if (!verbose){
			return false;
		}
		if (targetForm.attr("error-message-position")=="inline"){
			var targetMessagePosition = (targetForm.attr("error-message-dom") && $input.parent().find(targetForm.attr("error-message-dom")).length ? $input.parent().find(targetForm.attr("error-message-dom")) : $input.parents("td"));
			targetMessagePosition = (targetMessagePosition.length ? targetMessagePosition : $input.parent());
			if (errorMessage){
				targetMessagePosition.append("<div class='help-block form-error'>" + errorMessage + "</div>");
			} else {
				targetMessagePosition.find(".help-block.form-error").remove();
			}
			return false;
		}
	}
	if (!targetForm.isValid(validationLanguage, validationConfiguration, verbose)){
		if (verbose){
			var targetScroll = (targetForm.find(".form-error.alert").length ? targetForm.find(".form-error.alert") : targetForm);
			scrollToView(targetScroll, ($("#nav-sticky").length ? $("#nav-sticky").outerHeight() + 10 : 10), "start");	
		}
		return false;
	} else {
		return true;
	}
}

//Animate object
const animateCSS = (node, animation, speed=1, callback=null) =>
//We create a Promise and return it
new Promise((resolve, reject) => {
	const targetAnimation = `${animation}`;
	node.css("animation-duration", `${speed}s`).addClass(`animated ${targetAnimation}`);

	//When the animation ends, we clean the classes and resolve the Promise
	function handleAnimationEnd(event){
		event.stopPropagation();
		node.removeClass(`animated ${targetAnimation}`);
		resolve();
		if (typeof callback === "function"){
			callback();
		}
	}

	$(node)[0].addEventListener("animationend", handleAnimationEnd, {
		once: true
	});
});

//========== Image Bind Functions (Croppie & Normal Upload) ==========

//Bind image cropping to placeholder
function bindCroppie(selector, width, height, vwidth, vheight, boundry){
	width = parseInt(width ? width : 400);
	height = parseInt(height ? height : 400);
	vwidth = 220;
	vheight = parseInt(vheight ? vheight : height * (220 / width));
	boundry = parseInt(boundry ? boundry : vheight + 30);
	$("#" + selector).on("change", function(){
		var imageObject = this.files[0];
		var croppieObject = null;
		if (this.files && imageObject){
			if (this.files[0]["type"] && this.files[0]["type"].split('/')[0]=="image" && (this.files[0]["size"] / 1024) <= file_size_limit){
				var crop_image = null;
				var file_name = $(this).val().split(/(\\|\/)/g).pop();
				var reader = new FileReader();
				reader.onload = function(e){
					crop_image = e.target.result;
				}
				reader.readAsDataURL(imageObject);
				$.confirm({
					title: file_name,
					onOpenBefore: function(){
						this.showLoading(true);
					},
					content: "<div id=upload-demo></div>",
					animateFromElement: false,
					boxWidth: "300px",
					useBootstrap: false,
					buttons: {
						save: {
							text: readLanguage.plugins.message_save,
							btnClass: "btn-primary",
							action: function(){
								croppieObject.result({
									type: "base64",
									size: { width: width, height: height }
								}).then(function (resp){
									$("input[name=" + selector + "_base64]").val(resp).trigger("change");
									$("[image-placeholder=" + selector + "]").attr("src", resp);
									$("[image-placeholder=" + selector + "]").parent().attr("href", resp);
								});
							}
						},
						cancel: {
							text: readLanguage.plugins.message_cancel
						},
					},
					onContentReady: function(){
						croppieObject = new window.parent.Croppie(this.$content.find("#upload-demo")[0], {
							viewport: {
								width: vwidth,
								height: vheight
							},
							boundary: {
								height: boundry
							},
							enforceBoundary: true,
						});
						croppieObject.bind({
							url: crop_image
						});
						this.hideLoading(true);
					}
				});
			} else {
				messageBox(readLanguage.general.error, readLanguage.plugins.upload_error, "fas fa-times", "red");
			}
		}
	});
}

//Bind image upload to placeholder
function bindImage(selector){
	$("#" + selector).change(function(){
		var imageObject = this.files[0];
		if (this.files && imageObject){
			if (this.files[0]["type"] && imageObject["type"].split('/')[0]=="image" && (imageObject["size"] / 1024) <= file_size_limit){
				var reader = new FileReader();
				reader.onload = function(e){
					$("[image-placeholder=" + selector + "]").attr("src", e.target.result);
					$("[image-placeholder=" + selector + "]").parent().attr("href", URL.createObjectURL(imageObject));
				}
				reader.readAsDataURL(imageObject);
			} else {
				messageBox(readLanguage.general.error, readLanguage.plugins.upload_error, "fas fa-times", "red");
				$("#" + selector).val(null);
				$("[image-placeholder=" + selector + "]").attr("src", "images/placeholder.png");
				$("[image-placeholder=" + selector + "]").parent().attr("href", "images/placeholder.png");	
			}
		} else {
			$("[image-placeholder=" + selector + "]").attr("src", "images/placeholder.png");
			$("[image-placeholder=" + selector + "]").parent().attr("href", "images/placeholder.png");			
		}
	});
}

//========== Forms ==========

function customFormSubmit(button){
	var targetForm = $(button).parents("form");
	var inputValid = validateForm(targetForm);
	var content = {};
	
	targetForm.find("[input-name]").each(function(){
		var name = $(this).attr("input-name").replace(/\[\]/g, "");
		var type = $(this).attr("input-type");
		
		switch (type){
			case "radio":
				var checked = $(this).find("input[type=radio]:checked").val();
				if (checked){
					content[name] = checked;
				}
			break;
			
			case "checkbox":
				var checked = $(this).find("input[type=checkbox]:checked").map(function(){return $(this).val()}).get();
				if (checked.length){
					content[name] = checked;
				}
			break;
			
			case "multiple_select":
				var selected = $(this).find("option:selected").map(function(){return $(this).val()}).get();
				if (selected.length){
					content[name] = selected;
				}
			break;
			
			case "date":
				var split = $(this).val().split('/');
				if (split[2]){
					var timestamp = Math.round(new Date(split[2], split[1] - 1, split[0], 12, 0, 0).getTime() / 1000);
					content[name] = timestamp;
				}
			break;
			
			case "file": break;
			
			default:
				if ($(this).val()){
					content[name] = $(this).val();
				}
		}
	});
	
	if (inputValid){
		//Build all file names in array to easily handle file uploads
		var filenames = [];
		targetForm.find("input[type=file]").each(function(){
			filenames.push($(this).attr("name"));
		});
		
		//Create form data object
		var myFormData = new FormData(targetForm[0]);
		myFormData.append("token", user_token);
		myFormData.append("action", "form");
		myFormData.append("form", targetForm.attr("id"));
		myFormData.append("content", JSON.stringify(content));
		myFormData.append("filenames", JSON.stringify(filenames));
		
		//Show loading cover
		showLoadingCover();
		
		//Set submission request
		$.ajax({
			method: "POST",
			url: "requests/",
			data: myFormData,
			dataType: "text",
			cache: false,
			contentType: false,
			processData: false,
		}).done(function(response){
			targetForm.html(response);
		}).fail(function(response){
			messageBox("Failed to submit form", response.responseText, "fas fa-times", "red");
		}).always(function(){
			hideLoadingCover();
		});
	}
}

//========== Mobile Application Functions ==========

//Check If URL External
function linkExternal(linkElement){
	var isFile = linkElement.href.match(/\.(pdf|doc|docx|xls|xlsx|zip|7zip|rar|exe)/i);
	if (linkElement.host !== window.location.host || isFile || $(linkElement).attr("download")){
		return true;
	} else {
		return false;
	}
}

//Send Message to Parent [Webview]
function sendParentMessage(event_key, event_value){
	console.log("sendParentMessage", [event_key, event_value]);
	parent.postMessage({
		key: event_key,
		value: event_value
	}, "*");
}

//Send Message to Application [Cordova]
function sendApplicationMessage(event_key, event_value){
	console.log("sendApplicationMessage", [event_key, event_value]);
	sendParentMessage("Send-Application-Message", {
		key: event_key,
		value: event_value
	});
}