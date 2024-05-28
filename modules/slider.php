<link href="modules/slider.css?v=<?=$system_settings["system_version"]?>" rel="stylesheet">

<? $result = mysqlQuery("SELECT * FROM " . $suffix . "module_slider ORDER BY priority DESC LIMIT 0,8");
if (mysqlNum($result)){ ?>
<div class=slider>
	<!-- Search -->
	<div class="container container-basic search_container">
		<? include "modules/search.php"; ?>
	</div>
	
	<!-- Slider -->
	<div id=swiper-container-slider class=swiper-container>
		<div class=swiper-wrapper>
			<? while ($entry=mysqlFetch($result)){ ?>
				<div class=swiper-slide preload=true wrapper-style="background:transparent" style="background-image:url('uploads/slider/<?=$entry["image"]?>')">
					<div class=overlay></div>
					<? if ($entry["title"] || $entry["subtitle"]){ ?>
					<div class=container><div class=description>
						<div>
						<?=($entry["title"] ? "<h2 data-animation=fadeInDown class=single-line>" . $entry["title"] . "</h2>" : "")?>
						<?=($entry["subtitle"] ? "<p data-animation=fadeInUp class=single-line>" . $entry["subtitle"] . "</p>" : "")?>
						</div>
					</div></div>
					<? } ?>
					<div class=height_compensation></div>
				</div>
			<? } ?>
		</div>
	</div>
	<div id=swiper-next-slider class=swiper-button-next></div>
	<div id=swiper-prev-slider class=swiper-button-prev></div>
</div>

<script>
//Height Compensation
function heightCompensation(){
	$(".height_compensation").css("height", (parseInt($(".search_container").height()) + 50) + "px");
}

heightCompensation();

$(window).resize(function(){
	heightCompensation();
});

//Swiper
var slider_swiper = new Swiper("#swiper-container-slider", {
	slidesPerView: 1,
	loop: true,
	navigation: {
		nextEl: "#swiper-next-slider",
		prevEl: "#swiper-prev-slider",
	},
	autoplay: {
		delay: 5000,
	},
});
</script>
<? } ?>