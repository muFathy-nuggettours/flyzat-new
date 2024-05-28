<div class=block_image>
	<? if ($block["url"]){ ?><a href="<?=$block["url"]?>" <?=$block["url_parameters"]?>><? } ?>
		<div preload=true class=image_container style="background-image:url('<?=$block["cover"]?>')"></div>
		<div class=title><?=($block["title"] ? $block["title"] : "<i class='fas fa-search'></i>")?></div>
	<? if ($block["url"]){ ?></a><? } ?>
</div>