<? include "system/_handler.php";

setCurrentPageRedirect();
$section_information = mysqlFetch(mysqlQuery("SELECT * FROM " . $suffix . "website_pages WHERE page='" . basename($_SERVER["SCRIPT_FILENAME"], ".php") . "'"));
$section_title = readLanguage(general,search);
$section_description = $section_information["description"];

include "system/header.php";
include "website/section_header.php"; ?>

<!-- Start Tags --><div class=page_container>

<? if ($system_settings["google_cse"]){ ?>
<script>
(function(){
	var cx = "<?=$system_settings["google_cse"]?>";
	var gcse = document.createElement("script");
	gcse.type = "text/javascript";
	gcse.async = true;
	gcse.src = "https://cse.google.com/cse.js?cx=" + cx;
	var s = document.getElementsByTagName("script")[0];
	s.parentNode.insertBefore(gcse, s);
})();
</script>
<gcse:search></gcse:search>

<? } else { print noContent(); } ?>

<!-- End Tags --></div>

<? include "website/section_footer.php";
include "system/footer.php"; ?>