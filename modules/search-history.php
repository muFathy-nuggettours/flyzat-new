<? if (count($search_history)){ ?>
<div class=module_search_history>
	<h3><?=readLanguage(search,search_again)?></h3>
	<div id=swiper-container-history class=swiper><div class=swiper-wrapper>
		<? foreach ($search_history AS $key=>$value){ ?>
		<div class=swiper-slide>
			<a class="block <?=($key==0 ? "last" : "previous")?>" href="flights/?<?=http_build_query($value)?>">
				<div class=tag><?=readLanguage(search,last_search)?></div>
				<small><?=$data_flight_types[$value["type"]]?></small>
				<div class=trip>
					<div><b><?=$value["from"]?></b><small><?=getData("system_database_airports", "iata", $value["from"], $suffix . "short_name")?></small></div>
					<i class="<?=($value["type"]==2 ? "fal fa-exchange" : "fal fa-long-arrow-right")?>"></i>
					<div><b><?=$value["to"]?></b><small><?=getData("system_database_airports", "iata", $value["to"], $suffix . "short_name")?></small></div>
				</div>
				<div class=dates>
					<div><i class="fas fa-plane fa-fw"></i>&nbsp;&nbsp;<b><?=dateLanguage("l, d F Y", getTimestamp($value["departure"], "j-n-Y"))?></b></div>
					<? if ($value["arrival"]){ ?><div><i class="fas fa-plane fa-fw"></i>&nbsp;&nbsp;<b><?=dateLanguage("l, d F Y", getTimestamp($value["arrival"], "j-n-Y"))?></b></div><? } ?>
				</div>
				<small><?=$data_flight_classes[$value["class"]]?> - <?=($value["adults"] + $value["children"] + $value["toddlers"])?> <?=readLanguage(common,passenger2)?></small>
			</a>
		</div>
		<? } ?>
	</div></div>
</div>

<script>
var history_swiper = new Swiper("#swiper-container-history", {
	slidesPerView: 4,
	spaceBetween: 20,
	breakpoints: {
		0: { slidesPerView: 1 },
		768: { slidesPerView: 2 },
		992: { slidesPerView: 4 }
	}
});
</script>
<? } ?>