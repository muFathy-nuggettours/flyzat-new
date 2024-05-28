<?
//Set IFrame URL
$ifram_url = ($get["load"] && $_SERVER["SERVER_NAME"] == parse_url($get["load"], PHP_URL_HOST) ? $get["load"] : $base_url . ($get["language"] ? $get["language"] : ""));
?>

<script>
//Show Webview or Trigger Update
$(window).on("load", function(){
	sendApplicationMessage("Show-Webview");
	<? if ($get["version"] && $system_settings["application_build"] > $get["version"]){ ?>
		$("#webviewLoader").fadeOut(100);
		showOverlayPanel("new_update");
	<? } else { ?>
		startApplication("<?=$ifram_url?>", "<?=$website_information["website_name"]?>", "menu,dropdown", "home,account,contact,exit");
	<? } ?>
});
</script>

</body></html>
<? if ($connection){ mysqlClose(); } ob_end_flush(); ?>