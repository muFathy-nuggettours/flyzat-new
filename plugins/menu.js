/* Same Page URL [For Modules] */
$(window).on("load",function(){
	String.prototype.trimRight = function(charlist){
		if (charlist === undefined){ charlist = "\s"; }
		return this.replace(new RegExp("[" + charlist + "]+$"), "");
	};
	$(".nav-menu-container a").click(function(e){
		if ($(this).attr("href")){
			var invalid = $(this).parent().hasClass("nav-dropdown-item") && $(window).width() < 992;
			var sectionURL = $(this).attr("href").trimRight("/").replace($("base").attr("href"),"");
			if (sectionURL && sectionURL!="."){
				var moduleSelector = $("[section-url='" + sectionURL + "']");
				if (moduleSelector.length && !invalid){
					e.preventDefault();
					e.stopPropagation();
					scrollToView(moduleSelector, $("#nav-sticky").outerHeight()-10, "start", 1000);
					if ($(window).width() < 992){
						hideNavMenu();
					}
				}
			}
		}
	});
});

//Initialize main menu
$(document).ready(function(){
	$(".nav-dropdown-item").click(dropdownClick).on("click", "ul", function(e){
		e.stopPropagation();
	});
	
	var hoverTimeout;
	$(".nav-dropdown-item").hover(function(){
		clearTimeout(hoverTimeout);
		$(".nav-dropdown-item").each(function(){
			$(this).removeClass("hover");
		});
		$(this).addClass("hover");
	}, function(){
		var $self = $(this);
		hoverTimeout = setTimeout(function(){
			$self.removeClass("hover");
		}, 250);
	});

	$(".nav-cover").click(hideNavMenu);
});

//Override navigation menu dropdown click
function dropdownClick(){
	if ($(window).width() < 992){
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
}

//Hide navigation dropdown
function hideDropdown(object){
	if ($(window).width() < 992 && !object.hasClass("animating")){
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
	if ($(window).width() < 992 && !object.hasClass("animating")){
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
	if ($(window).width() < 992){
		$(".nav-cover").css("visibility", "hidden").css("opacity", "0").hide();
		$(".nav-menu").removeClass("nav-menu-opened").delay(500).queue(function(){
			$(".nav-dropdown-item.active").each(function(){
				hideDropdown($(this));
			});
			$(".nav-menu").css("transition", "all 0s");
			$(this).dequeue();
		});
		$("body").removeClass("noscroll");
	}
}

//Show navigation menu
function showNavMenu(){
	if ($(window).width() < 992){
		$(".nav-cover").show().css("visibility", "visible").css("opacity", "1");
		$(".nav-menu").css("transition", "all 0.5s").addClass("nav-menu-opened");
		$("body").addClass("noscroll");
	}
}