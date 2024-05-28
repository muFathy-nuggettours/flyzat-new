<?
$root_path = $_SERVER['DOCUMENT_ROOT'] . '/' . $_SERVER['DEVELOPMENT_FOLDER'];
$panel_path = $root_path . $_SERVER['PANEL_FOLDER'];
require_once("$panel_path/system/_config.php");
require_once("$panel_path/system/_validator.php");
require_once("$panel_path/system/_functions.php");
require_once("$panel_path/system/_security.php");
require_once($root_path . "system/_users.php");
require_once($root_path . "system/_languages.php");
require_once("$panel_path/system/variables.php");
require_once("$panel_path/system/functions.php");

//Fetch website data & settings
$website_information = fetchData($website_language . "_website_information");
$website_theme = fetchData("website_theme");
$system_settings = fetchData("system_settings");

//Check current platform
$current_platform = checkPlatform();
$on_mobile = ($current_platform == "Android_Application" || $current_platform == "iOS_Application" ? true : false);
$on_mobile = (isset($_SERVER["DEVELOPMENT_FOLDER"]) && strpos($_SERVER["HTTP_REFERER"], "/mobile/") !== false ? true : $on_mobile); //Always true on production [Fails on post]

//Include custom handler
include $root_path . "website/handler.php";

//Start code compression
require_once "$panel_path/snippets/HTMLMinifier.php";
if (!$skip_compress){
	ob_start("HTMLMinifier::process");
}
?>