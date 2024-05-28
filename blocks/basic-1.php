<div class=block_basic_1>
	<? if ($block["url"]){ ?><a href="<?=$block["url"]?>"><? } ?>
		<div class=hover>
			<div preload=true class=image style="background-image:url(<?=$block["cover"]?>)"></div>
		</div>
		<span>
			<?=$block["title"]?>
			<? if ($block["subtitle"]){ ?><small><?=$block["subtitle"]?></small><? } ?>
		</span>
	<? if ($block["url"]){ ?></a><? } ?>
</div>