<? include "system/_handler.php";

$multiple_languages = false;
$mysqltable = "flights_reservations";
$base_name = basename($_SERVER["SCRIPT_FILENAME"], ".php");
checkPermissions($base_name);

include "_header.php"; ?>

<div class=title><?=getPageTitle($base_name)?></div>

<?
$crud_data["where_statement"] = "status IN (3)";
$crud_data["buttons"] = array(false,true,false,false,false); //Add - Search - View - Edit - Delete
$crud_data["columns"] = array( //Filter - Search - Copy Enabled
	array("id","تفاصيل الحجز","120px","center","viewButton('_view_reservation.php?id=%s','" . readLanguage(operations,view) . "','btn-primary','fas fa-search')",false,false),
	array("user_id","حساب المستخدم","250px","center","getCustomData('name','users_database','id','%s','_view_user')",false,true),
	array("user_ip","آي بي المستخدم","120px",null,false,false),
	array("pnr","رقم الحجز","120px","center",null,false,true),
	array("personnel","الموظف","300px","center",null,false,true),
	array("date","تاريخ الحجز","250px","center","dateLanguage('l, d M Y h:i A','%s')",false,true),
	array("so_platform","منصة الحجز","120px","center","getVariable('data_platforms')[%s]",true,false),
	array("so_trips","عدد الرحلات","120px","center",null,false,true),
	array("so_passengers","عدد المسافرين","120px","center",null,false,true),
	array("so_start","تاريخ اول رحلة","250px","center","dateLanguage('l, d M Y h:i A','%s')",false,true),
	array("so_end","تاريخ آخر رحلة","250px","center","dateLanguage('l, d M Y h:i A','%s')",false,true),
	array("so_currency","عملة الحجز","120px","center",null,false,true),
	array("search_object","السعر الأساسي","120px","center","getReservationPricing('%s')['base']",false,true),
	array("search_object","سعر الضريبة","120px","center","getReservationPricing('%s')['taxes']",false,true),
	array("search_object","صافي الربح","120px","center","getReservationPricing('%s')['commission']",false,true),
	array("so_price","السعر الإجمالي","120px","center",null,false,true),
);
require_once("crud/crud.php");
?>

<? include "_footer.php"; ?>