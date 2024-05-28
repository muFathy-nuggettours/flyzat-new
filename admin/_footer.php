<!-- Footer Copyrights (Only if not in Fancybox frame) -->
<? if (!$inline_page){ ?>
<div class=footer_copyrights>
	<?=readLanguage(general,copyrights_reserved)?> © <?=$website_information["website_name"]?> <?=date("Y",time())?>
	<? if (!$white_label){
		print "<br><small>" . readLanguage(general,developed_by) . ": <b><a href='http://web.prismatecs.com/' target=_blank>Prismatecs Web Solutions</a></b></small>";
	} ?>
</div>
<? } ?>

<!-- Notifications (Only if not in Fancybox frame & for logged users) -->
<? if (!$inline_page && $logged_user){ ?>
<? if ($notifications){
	print "<div class=footer_notifications_icon show-notifications>
		<i class='fas fa-bell'></i>
		<div class=count>$total_notifications</div>
	</div>";
	print "<div class=footer_notifications><ul class=footer_notifications_list>";
	foreach ($notifications AS $key=>$value){
		print "<li><a href='" . ($value[2] ? $value[2] : $key . ".php") . "'>{$value[0]}</a></li>";
	}
	print "</ul></div>";
} ?>

<script>
$("[show-notifications]").on("click", function (){
	$.alert({
		title: "<?=readLanguage(general,notifications)?>",
		icon: "fas fa-exclamation-triangle",
		type: "orange",
		content: $(".footer_notifications").html()
	});
});
</script>
<? } ?>

<!-- End Body & Page Containers --></div></div>

<? if ($inline_page){ //Only if in Fancybox frame ?>
<!-- Hide loading cover -->
<script>hideLoadingCover();</script>
<? } ?>

<!-- Javascript Setup -->
<script src="../core/_setup.js?v=<?=$system_settings["system_version"]?>"></script>
<script src="core/_setup.js?v=<?=$system_settings["system_version"]?>"></script>
<script src="website/setup.js?v=<?=$system_settings["system_version"]?>"></script>

</body></html>
<? if ($connection){ mysqlClose(); } ob_end_flush(); ?>