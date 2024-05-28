<?
$root_path = $_SERVER['DOCUMENT_ROOT'] . '/' . $_SERVER['DEVELOPMENT_FOLDER'];
$panel_path = $root_path . $_SERVER['PANEL_FOLDER'];

require_once("_config.php");
require_once("_validator.php");
require_once("_functions.php");
require_once("_security.php");
require_once("_users.php");
require_once("_languages.php");
require_once("variables.php");
require_once("functions.php");
require_once("sections.php");

//Fetch website data & settings
$website_information = fetchData($panel_language . "_website_information");
$website_theme = fetchData("website_theme");
$system_settings = fetchData("system_settings");

//Check current platform
$current_platform = checkPlatform();
$on_mobile = ($current_platform == "Android_Application" || $current_platform == "iOS_Application" ? true : false);
$on_mobile = (isset($_SERVER["DEVELOPMENT_FOLDER"]) && strpos($_SERVER["HTTP_REFERER"],"mobile") !== false ? true : $on_mobile); //Always true on production [Fails on post]

//Set inline page if available in get request
if (isset($get["inline"])){
	$inline_page = true;
}

//Include custom handler
include "website/handler.php";

//Start code compression
require_once "$panel_path/snippets/HTMLMinifier.php";
if (!$skip_compress){
	ob_start("HTMLMinifier::process");
}
?>