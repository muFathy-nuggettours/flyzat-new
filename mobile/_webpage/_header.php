<!DOCTYPE html>
<html lang="<?=$website_language?>" dir="<?=$language["dir"]?>">
<head>
<?
$page_url = sanitizeString(urldecode((isset($_SERVER["HTTPS"]) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"));
$page_title = ($section_title ? $section_title . " | " . $website_information["website_name"] : $website_information["website_name"]);
$page_description = ($section_description ? $section_description : $website_information["short_description"]);
$header_image = ($section_header_image ? $section_header_image : "uploads/_website/" . $website_information["header_image"]);
$cover_image = ($section_cover_image ? $section_cover_image : "uploads/_website/" . $website_information["cover_image"]);
?>

<title><?=$page_title?></title>

<!-- Standard Tags -->
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=Edge">
<meta http-equiv="content-type" content="text/html">
<meta name="robots" content="index,follow">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
<meta name=description content="<?=$page_description?>">

<!-- Social Media -->
<? $meta_image = $base_url . $cover_image; ?>
<meta itemprop="name" content="<?=$page_title?>">
<meta itemprop="description" content="<?=$page_description?>">
<meta itemprop="image" content="<?=$meta_image?>">
<meta property="og:url" content="<?=$page_url?>">
<meta property="og:title" content="<?=$page_title?>">
<meta property="og:description" content="<?=$page_description?>"> 
<meta property="og:image" content="<?=$meta_image?>">
<meta property="og:site_name" content="<?=$website_information["website_name"]?>">
<meta property="og:see_also" content="<?=$base_url?>">
<meta property="og:type" content="website">
<meta name="twitter:card" content="summary">
<meta name="twitter:url" content="<?=$page_url?>">
<meta name="twitter:title" content="<?=$page_title?>">
<meta name="twitter:description" content="<?=$page_description?>">
<meta name="twitter:image" content="<?=$meta_image?>">

<!-- Header Bar Theme -->
<? $base_color = explode(",", $website_theme["colors"])[5]; ?>
<meta name="theme-color" content="<?=$base_color?>">
<meta name="msapplication-navbutton-color" content="<?=$base_color?>">
<meta name="apple-mobile-web-app-status-bar-style" content="<?=$base_color?>">

<!-- Customization -->
<link rel="icon" type="image/png" href="uploads/_website/<?=$website_information["website_icon"]?>">
<base href="<?=$base_url . (in_array($website_language,$supported_languages) && $website_language!=$supported_languages[0] ? $get["language"] : "")?>">

<!-- Variables passed from PHP to JS -->
<script>
var user_token = "<?=$token?>";
var current_platform = "<?=$current_platform?>";
var on_mobile = <?=($on_mobile ? 1 : 0)?>;
var enable_localization = <?=($enable_localization ? 1 : 0)?>;
var file_size_limit = <?=parseSize(ini_get("upload_max_filesize")) / 1024?>;
var theme_version = "<?=$website_theme["version"]?>";
</script>

<!-- Read entire language file -->
<script>
var readLanguage = <?=file_get_contents("core/languages/$website_language.json")?>;
<? if (file_exists("website/languages/$website_language.json")){ ?>
var websiteLanguage = <?=file_get_contents("website/languages/$website_language.json")?>;
readLanguage = Object.assign(readLanguage, data);
<? } ?>
</script>

<!-- Base Scripts -->
<script src="core/_jquery.js?v=<?=$system_settings["system_version"]?>"></script>
<script src="core/_bootstrap.js?v=<?=$system_settings["system_version"]?>"></script>
<script src="core/_functions.js?v=<?=$system_settings["system_version"]?>"></script>
<script src="core/_plugins.js?v=<?=$system_settings["system_version"]?>"></script>

<!-- Manually initialize localizations as the language js file is not included -->
<script>
$(document).ready(function(){
	if (typeof initializeLocalization == "function"){
		initializeLocalization();
	}
});
</script>

<!-- Base Sheets -->
<link href="core/_bootstrap-<?=$language["dir"]?>.css?v=<?=$system_settings["system_version"]?>" rel="stylesheet">
<link href="core/_fontawesome.css?v=<?=$system_settings["system_version"]?>" rel="stylesheet">
<link href="core/_core.css?v=<?=$system_settings["system_version"]?>" rel="stylesheet">
<link href="core/_plugins.css?v=<?=$system_settings["system_version"]?>" rel="stylesheet">

<!-- Website Plugins -->
<script src="plugins/animate-os.min.js?v=<?=$system_settings["system_version"]?>"></script><link href="plugins/animate-os.min.css?v=<?=$system_settings["system_version"]?>" rel="stylesheet">
<script src="plugins/preloader.js?v=<?=$system_settings["system_version"]?>"></script><link href="plugins/preloader.css?v=<?=$system_settings["system_version"]?>" rel="stylesheet">
<script src="plugins/swiper.min.js?v=<?=$system_settings["system_version"]?>"></script><link href="plugins/swiper.min.css?v=<?=$system_settings["system_version"]?>" rel="stylesheet">

<!-- Load classes separately when not in production -->
<? if (!file_exists("website/website.min.css")){ ?>
	<link href="website/_theme.css?v=<?=$website_theme["version"]?>" rel="stylesheet">
	<link href="website/_classes.css?v=<?=$website_theme["version"]?>" rel="stylesheet">
	<link href="website/_modules.css?v=<?=$website_theme["version"]?>" rel="stylesheet">	
	<link href="website/loading.css?v=<?=$website_theme["version"]?>" rel="stylesheet">
	<link href="website/header.css?v=<?=$website_theme["version"]?>" rel="stylesheet">
	<link href="website/section_header.css?v=<?=$website_theme["version"]?>" rel="stylesheet">
	<link href="website/footer.css?v=<?=$website_theme["version"]?>" rel="stylesheet">
	
	<? $blocks_directory = glob("blocks/*.{css}", GLOB_BRACE);
	foreach ($blocks_directory AS $key=>$value){
		echo "<link href=\"$value?v=" . $website_theme["version"] . "\" rel=\"stylesheet\">";
	} ?>

	<? $blocks_directory = glob("modules/*.{css}", GLOB_BRACE);
	foreach ($blocks_directory AS $key=>$value){
		echo "<link href=\"$value?v=" . $website_theme["version"] . "\" rel=\"stylesheet\">";
	} ?>
	
	<? $blocks_directory = glob("website/templates/*.{css}", GLOB_BRACE);
	foreach ($blocks_directory AS $key=>$value){
		echo "<link href=\"$value?v=" . $website_theme["version"] . "\" rel=\"stylesheet\">";
	} ?>
<? } else { ?>
	<link href="website/website.min.css?v=<?=$website_theme["version"]?>" rel="stylesheet">
<? } ?>

<!-- Website Specific -->
<link href="website/template.css?v=<?=$website_theme["version"]?>" rel="stylesheet">
<script src="website/functions.js?v=<?=$website_theme["version"]?>"></script>

<!-- Application Specific -->
<script src="mobile/plugins/pull-refresh.js?v=<?=$system_settings["system_version"]?>"></script>
<link href="mobile/plugins/pull-refresh.css?v=<?=$system_settings["system_version"]?>" rel="stylesheet">
<link href="mobile/_webpage/_webpage.css?v=<?=$system_settings["system_version"]?>" rel="stylesheet">
<script src="mobile/_webpage/_functions.js?v=<?=$system_settings["system_version"]?>"></script>
<link href="mobile/website/webpage.css?v=<?=$system_settings["system_version"]?>" rel="stylesheet">

<!-- Custom Website Meta Data -->
<? include "website/metadata.php"; ?>

<!-- Custom Application Meta Data -->
<? include "mobile/website/metadata.php"; ?>
</head>

<body class="application <?=($inline_page ? "inline" : "body")?> <?=basename($_SERVER["SCRIPT_FILENAME"], ".php")?> <?=$canonical?>" keep-history=<?=$keep_history?>>

<!-- Body Headers -->
<? if (!$inline_page){ ?><div id=applicationContainer class=applicationContainer><? } ?>