<? include "system/_handler.php";


$multiple_languages = false;
$mysqltable = "users_database where is_active = 0 ";
$base_name = basename($_SERVER["SCRIPT_FILENAME"], ".php");
checkPermissions($base_name);

include "_header.php"; ?>

<div class=title><?= getPageTitle($base_name) ?></div>
<div class="message"><?= isset($get["message"]) ? '<div class="alert alert-success">' . $get["message"] . '</div>' : '' ?></div>
<?
$crud_data["buttons"] = array(false, false, false, false, false); //Add - Search - View - Edit - Delete
$crud_data["columns"] = array( //Filter - Search - Copy Enabled
	array("id",readLanguage('operations','manage'),"120px","center","activeUser(%s)",false,false),
    array("id", readLanguage('users', 'serial'), "80px", "center", null, false, true),
    array("user_id", readLanguage('users', 'user_id'), "120px", "center", null, false, true),
    array("name", readLanguage('users', 'name'), "300px", "center", "", false, true),
    array("mobile", readLanguage('users', 'mobile'), "150px", "force-ltr", null, false, true, true),
    array("country", readLanguage('users', 'country'), "200px", "center", "'<img src=\"../images/countries/%s.gif\">&nbsp;' . getData('system_database_countries','code','%s','" . $panel_language . "_name')", true, false),
    array("email", readLanguage('users', 'email'), "300px", "center", null, false, true, true),
    array("date", readLanguage('users', 'registration_date'), "250px", "center", "dateLanguage('l, d M Y h:i A',%s)", false, false),
);
require_once("crud/crud.php");
?>

<? include "_footer.php"; ?>