<?
error_reporting(1);
date_default_timezone_set("Africa/Cairo");

//PHP Settings
ini_set("default_charset", "UTF-8");
ini_set("max_execution_time", "600");
ini_set("memory_limit", "128M");

//Prismatecs CMS Version
$cms_version = "2.7.2";

//Supported Languages [Default Language At Index 0]
$supported_languages = array("ar","en");
$panel_language = "ar";

//Database Connection
$mysqlname="zj7bfwgv_flyzat";
$mysqlpass="StrongPassZat";
$mysqldatabase="zj7bfwgv_flyzat";
$mysqlserver="localhost";

//Set development mode variable (false on localhost & true on production)
$development_mode = isset($_SERVER["DEVELOPMENT_FOLDER"]);

//MySQL Connection
$connection = mysqli_connect($mysqlserver, $mysqlname, $mysqlpass, $mysqldatabase);
mysqli_set_charset($connection, "utf8");

//Base URL
$base_url = "https://www.flyzat.com/";

//Encryption Key
$private_key = hash("sha256", $mysqlname . $mysqlpass . $mysqldatabase);

//Panel Folder Name
$panel_folder = "admin";

//Sessions & Cookies
$session_prefix = "FLYZAT_";

//Sessions
$panel_session = $session_prefix . "ADMIN";
$recaptcha_session = $session_prefix . "RECAPTCHA";
$user_session = $session_prefix . "USER";
$csrf_session = $session_prefix . "CSRF";
$redirect_session = $session_prefix . "REDIRECT";
$database_language_session = $session_prefix . "LANGUAGE";

//Cookies
$panel_cookie = $session_prefix . "ADMIN";
$user_cookie = $session_prefix . "USER";
$csrf_cookie = $session_prefix . "CSRF";
$popup_cookie = $session_prefix . "POPUP";

//Custom Settings
$white_label = true;
$upload_allow = array("txt");

//Start Sessions
session_start();

//Start GZip Compression
ob_start("ob_gzhandler");
?>