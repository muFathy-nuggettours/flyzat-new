<? if (!$on_mobile){ $share_uniqid = uniqid() . rand(1000, 9999); ?>
<div class="module_share <?=$share_uniqid?>" style="margin: 0 -3px 0 -3px"></div>
<script>
$(".<?=$share_uniqid?>").jsSocials({
    showCount: false,
    showLabel: false,
	shareIn: "popup",
	text: "<?=$page_title?>",
    shares: ["email","facebook","twitter","linkedin","pinterest"]
});
</script>

<? } else { ?>
<center>
	<button class="btn btn-primary btn-insert btn-block" onclick='sendApplicationMessage("Share-Page", ["<?=rawurlencode(str_replace('&#34;','"',$page_title))?>", "<?=rawurlencode($page_image)?>", "<?=rawurlencode($page_url)?>"])'>
		<i class="fas fa-share-alt"></i>&nbsp;&nbsp;<?=readLanguage(general,share)?>
	</button>
</center>

<? } ?>