<div class=block_generic_10>
	<? if ($block["url"]){ ?><a href="<?=$block["url"]?>"><? } ?>
		<div preload=true class=image style="background-image:url('<?=$block["cover"]?>')"></div>
		<span>
			<?=$block["title"]?>
			<? if ($block["subtitle"]){ ?><small><?=$block["subtitle"]?></small><? } ?>
		</span>
	<? if ($block["url"]){ ?></a><? } ?>
</div>