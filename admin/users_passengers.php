<? include "system/_handler.php";

$multiple_languages = false;
$mysqltable = "users_passengers";
$base_name = basename($_SERVER["SCRIPT_FILENAME"], ".php");
checkPermissions($base_name);

include "_header.php"; ?>

<div class=title><?=getPageTitle($base_name)?></div>

<?
$crud_data["buttons"] = array(false,true,false,false,false); //Add - Search - View - Edit - Delete
$crud_data["columns"] = array( //Filter - Search - Copy Enabled
	array("user_id","المستخدم","220px","center","getCustomData('name','users_database','id','%s','_view_user')",false,true),
	array("name_prefix","اللقب","120px","center","getVariable('data_passenger_names_prefix')[%s]",true,false),
	array("first_name","الإسم الأول","200px","center",null,false,true),
	array("last_name","الإسم الأخير","200px","center",null,false,true),
	array("birth_date","تاريخ الميلاد","200px","center","dateLanguage('l, d M Y','%s')",false,true),
	array("type","النوع","160px","center","getVariable('data_passenger_types')[%s]",true,true),
	array("nationality","دولة الجنسية","150px","center","getData('system_database_countries', 'code', '%s', 'ar_name')",true,false),
	array("ssn","رقم الباسبور / الهوية","200px","center",null,false,true),
	array("ssn_end","تاريخ الإنتهاء","200px","center","dateLanguage('l, d M Y','%s')",false,true),
	array("special_needs","الطلبات الخاصة","200px","center","getVariable('data_special_needs')[%s]",false,true),
	array("special_meals","الوجبات الخاصة","200px","center","getVariable('data_special_meals')[%s]",false,true),
	array("removed","محذوف","120px","center","hasVal('%s','لا','نعم')",false,true),
);
require_once("crud/crud.php");
?>

<? include "_footer.php"; ?>