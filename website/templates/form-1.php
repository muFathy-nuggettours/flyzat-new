<div class="<?=$input["width"]?> grid-item">
	<? if ($input["label"]){ ?>
		<label>
			<? if ($input["icon"]){ ?><i class="<?=$input["icon"]?>"></i><? } ?>
			<?=$input["label"]?>
			<? if ($input["required"]){ ?><i class=requ></i><? } ?>
		</label>
	<? } ?>
	
	<? if ($input["dom"]){ ?>
		<div><?=$input["dom"]?></div>
	<? } ?>
	
	<? if ($input["description"]){ ?>
		<small><?=$input["description"]?></small>
	<? } ?>
</div>