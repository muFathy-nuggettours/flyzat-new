<? include "system/_handler.php";

$multiple_languages = false;
$mysqltable = "users_database";
$base_name = basename($_SERVER["SCRIPT_FILENAME"], ".php");
checkPermissions($base_name);

include "_header.php"; ?>

<div class=title><?=getPageTitle($base_name)?></div>

<?
$crud_data["buttons"] = array(false,true,false,false,false); //Add - Search - View - Edit - Delete
$crud_data["columns"] = array( //Filter - Search - Copy Enabled
	array("id",readLanguage(users,serial),"80px","center",null,false,true),
	array("user_id",readLanguage(users,user_id),"120px","center",null,false,true),
	array("id",readLanguage(users,profile),"120px","center","viewButton('_view_user.php?id=%s','" . readLanguage(operations,view) . "','btn-primary','fas fa-user')",false,false),
	array("name",readLanguage(users,name),"300px","center","",false,true),
	array("email",readLanguage(users,email),"300px","center",null,false,true,true),
	array("country",readLanguage(users,country),"200px","center","'<img src=\"../images/countries/%s.gif\">&nbsp;' . getData('system_database_countries','code','%s','" . $panel_language . "_name')",true,false),
	array("user_currency","عملة الحساب","200px","center","getData('system_payment_currencies','code','%s','ar_name')",true,false),
	array("mobile",readLanguage(users,mobile),"150px","force-ltr",null,false,true,true),
	array("banned",readLanguage(users,status_banned),"100px","center","hasVal(%s,'" . readLanguage(plugins,message_no) . "','" . readLanguage(plugins,message_yes) . "')",true,false),
	array("date",readLanguage(users,registration_date),"250px","center","dateLanguage('l, d M Y h:i A',%s)",false,false),
);
require_once("crud/crud.php");
?>

<? include "_footer.php"; ?>