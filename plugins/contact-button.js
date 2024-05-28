var main_button = null;
var sub_button = null;
var containerVisible = false;
var currentIcon = 0;

$(document).ready(function(){
	main_button = $(".live_contact .main_button");
	sub_button = $(".live_contact .sub_buttons_container");
	$(".contact_overlay").click(function(){
		toggleLiveContainer();
	});
	main_button.html("<span class='fas fa-times'></span><i class='" + contact_buttons[currentIcon].icon + "'></li>").click(function(){
		toggleLiveContainer()
	});
	function intervalFunction(){
		if (document.hasFocus()){
			main_button.find("i").fadeOut(250, function(){
				$(this).attr("class", contact_buttons[currentIcon].icon);
			}).fadeIn(250);
			if (currentIcon == contact_buttons.length - 1){
				currentIcon = 0;
			} else {
				currentIcon += 1;
			}
		}
	}
	var fadeInterval = setInterval(intervalFunction, 2000);
	contact_buttons.forEach(function(val, index){
		sub_button.append("<div class=sub_button_container><div class=sub_button id=sub_button_" + index + "><i class='" + val.icon + "'></i><span>" + val.title + "</span></div></div>");
		$("#sub_button_" + index).on("click",val.action);
		$("#sub_button_" + index).on("click",function(){
			$(".contact_overlay").trigger("click");
		});
	});
	function toggleLiveContainer(){
		if (containerVisible){
			containerVisible = false;
			fadeInterval = setInterval(intervalFunction, 2000);
			$(".contact_overlay").fadeOut(200);
			main_button.find("span").fadeOut(200, function(){
				main_button.find("i").fadeIn(200);
			});
			main_button.removeClass("active");
			$(".live_contact").removeClass("active");
			$(".contact_overlay").removeClass("active");
			sub_button.animate({
				opacity: "0",
				top: "20px",
			}, 250, function() {
				sub_button.css("display", "none");
			});
		} else {
			containerVisible = true;
			clearInterval(fadeInterval);
			$(".contact_overlay").fadeIn(200);
			main_button.find("i").fadeOut(200, function(){
				main_button.find("span").fadeIn(200);
			});
			main_button.addClass("active");
			$(".live_contact").addClass("active");
			$(".contact_overlay").addClass("active");
			sub_button.css("display", "flex");
			sub_button.animate({
				opacity: "1",
				top: "0px",
			}, 250);
		}
	}
});