<div class=block_video>
	<? if ($block["url"]){ ?><a href="<?=$block["url"]?>" <?=$block["url_parameters"]?>><? } ?>
		<div class=image_container>
			<div preload=true class=image style="background-image:url(<?=$block["cover"]?>)">
				<div class=overlay><i class="glyphicon glyphicon-play"></i></div>
			</div>
		</div>
		<b><?=$block["title"]?></b>
	<? if ($block["url"]){ ?></a><? } ?>
</div>