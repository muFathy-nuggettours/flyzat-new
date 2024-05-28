//Initialize sticky header
$("#nav-sticky").sticky({
	className: "nav-stuck",
	topSpacing: 0,
	bottomSpacing: 0,
	zIndex: 1000
});

//Update sticky header
$(window).on("load", function(){
	$("#nav-sticky").sticky("update");
});
$(window).resize(function() {
	$("#nav-sticky").sticky("update");
});

//Attach Fancybox to WYSIWYG content
$(".html-content img").each(function(){			
	$(this).wrap("<a data-fancybox=images href='" + $(this).attr("src") + "'></a>");
});

//Initialize smooth scroll
SmoothScroll({
	//Scrolling Core
	animationTime    : 800, //ms
	stepSize         : 60, //px

	//Acceleration
	accelerationDelta : 20,
	accelerationMax   : 2,

	//Keyboard Settings
	keyboardSupport   : true,
	arrowScroll       : 50, //px

	//Pulse (less tweakable) - Ratio of "tail" to "acceleration"
	pulseAlgorithm   : true,
	pulseScale       : 4,
	pulseNormalize   : 1,

	//Other
	touchpadSupport   : false, //Ignore touchpad by default
	fixedBackground   : true, 
	excluded          : ""
});

//Hide loading cover
hideLoadingCover();

//Initialize animations
AOS.init();

//Update moment arabic language
moment.updateLocale("ar", {
	months: ["يناير","فبراير","مارس","إبريل","مايو","يونيو","يوليو","أغسطس","سبتمبر","أكتوبر","نوفمبر","ديسمبر"],
	weekdays: ["الأحد","الأثنين","الثلاثاء","الأربعاء","الخميس","الجمعة","السبت"],
});