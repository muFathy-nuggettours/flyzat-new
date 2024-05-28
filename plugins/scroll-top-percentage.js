//Scroll to Top
$(document).ready(function(){
	$(window).scroll(function(){
		if ($(this).scrollTop() > 100){
			$(".scroll-top").fadeIn();
		} else {
			$(".scroll-top").fadeOut();
		}
		var s = $(window).scrollTop(), d = $(document).height(), c = $(window).height();
		var scrollPercent = (s / (d - c)) * 100;
		$(".scroll-top circle").css("stroke-dashoffset", (scrollPercent * 1.57) + "%");
	});
	$(".scroll-top").click(function(){
		$("html,body").animate({
			scrollTop: 0
		}, 600);
		return false;
	});
});