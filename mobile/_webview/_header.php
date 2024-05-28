<!DOCTYPE html>
<html lang="<?=$website_language?>" dir="<?=$language["dir"]?>">
<head>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=Edge">
<meta http-equiv="content-type" content="text/html">
<meta name="robots" content="noindex,nofollow">
<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
<base href="<?=$base_url . ($get["language"] ? $get["language"] : "")?>">

<!-- Variables passed from PHP to JS -->
<script>
var user_token = "<?=$token?>";
var current_platform = "<?=$current_platform?>";
var on_mobile = <?=($on_mobile ? 1 : 0)?>;
var enable_localization = <?=($enable_localization ? 1 : 0)?>;
</script>

<!-- Read entire language file -->
<script>
var readLanguage = <?=file_get_contents("../core/languages/$website_language.json")?>;
<? if (file_exists("../website/languages/$website_language.json")){ ?>
var websiteLanguage = <?=file_get_contents("../website/languages/$website_language.json")?>;
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

<!-- Application Specific -->
<script src="mobile/plugins/waves.min.js?v=<?=$system_settings["system_version"]?>"></script>
<link href="mobile/plugins/waves.min.css?v=<?=$system_settings["system_version"]?>" rel="stylesheet">
<script src="plugins/croppie.min.js?v=<?=$system_settings["system_version"]?>"></script>
<link href="plugins/croppie.min.css?v=<?=$system_settings["system_version"]?>" rel="stylesheet">
<link href="mobile/_webview/_webview.css?v=<?=$system_settings["system_version"]?>" rel="stylesheet">
<script src="mobile/_webview/_webview.js?v=<?=$system_settings["system_version"]?>"></script>
<script src="mobile/website/webview.js?v=<?=$system_settings["system_version"]?>"></script>

<!-- Load classes separately when not in production -->
<? if (!file_exists("website/webview.min.css")){ ?>
	<link href="mobile/_webview/_theme.css?v=<?=$website_theme["version"]?>" rel="stylesheet">
<? } else { ?>
	<link href="mobile/website/webview.min.css?v=<?=$website_theme["version"]?>" rel="stylesheet">
<? } ?>
<link href="mobile/website/webview.css?v=<?=$website_theme["version"]?>" rel="stylesheet">
</head>

<body>

<!-- Load Panels -->
<? $panels = array_diff(scandir("panels"), array('.', '..'));
foreach ($panels AS $panel){
	include "panels/$panel";
} ?>

<!-- Main Frame -->
<iframe class="mainWebview initial"></iframe>
<div id=webviewsWrapper></div>