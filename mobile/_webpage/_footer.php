<? if (!$inline_page){ //Add only if not in Fancybox frame ?>
<!--Modals-->
<? include "_modals.php"; ?>

<!-- Handle floating button visibility -->
<script>
var lastScroll = $(window).scrollTop();
$(window).scroll(function(){
	var currentScroll = $(this).scrollTop();
	sendParentMessage("Update-Floating-Button-Visibility", (lastScroll > currentScroll ? true : false));
	lastScroll = currentScroll;
});
</script>

<!-- Set Parent Webview Attributes -->
<script>
if (typeof page_title === "undefined"){ var page_title = "<?=($section_title ? $section_title : $website_information["website_name"])?>"; }
if (typeof header_buttons === "undefined"){ var header_buttons = (typeof indexPage !== "undefined" && indexPage == true ? "menu,dropdown" : "menu,back"); }
if (typeof footer_buttons === "undefined"){ var footer_buttons = "home,account,contact,exit"; }
sendParentMessage("Set-Webview-Attributes", [page_title, header_buttons, footer_buttons]);
</script>

<!-- End applicationContainer -->
</div>

<? } else { //Add only if in Fancybox frame ?>
<!-- Hide loading cover -->
<script>hideLoadingCover()</script>
<? } ?>

<!-- Push Notifications -->
<? if ($system_settings["firebase_app_api_key"] && $system_settings["firebase_project_id"] && $system_settings["firebase_project_number"] && $system_settings["firebase_app_id"]){
	include "_inl_push_notifications.php";
} ?>

<!-- Javascript Setup -->
<script src="core/_setup.js?v=<?=$system_settings["system_version"]?>"></script>
<script src="mobile/_webpage/_setup.js?v=<?=$system_settings["system_version"]?>"></script>
<script src="mobile/website/webpage.setup.js?v=<?=$system_settings["system_version"]?>"></script>

</body></html>
<? if ($connection){ mysqlClose(); } ob_end_flush(); ?>