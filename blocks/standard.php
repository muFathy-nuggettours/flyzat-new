<div class=block_standard >
	<? if ($block["url"]){ ?><a href="<?=$block["url"]?>" <?=$block["url_attributes"]?>><? } ?>
		<div class=block_header style="background-image:url('<?=$block["header"]?>')"></div>
		<div class=block_image style="background-image:url('<?=$block["cover"]?>')"></div>
		<div class=block_content>
			<b><?=$block["title"]?></b>
			<? if ($block["subtitle"]){ ?><span><?=$block["subtitle"]?></span><? } ?>
			<? if ($block["description"]){ ?><p><?=$block["description"]?></p><? } ?>
			<small><?=dateLanguage("l, d F Y", $block["date"])?></small>
		</div>
	<? if ($block["url"]){ ?></a><? } ?>
</div>