<? include "system/_handler.php";

$mysqltable = "users_balance";
$base_name = basename($_SERVER["SCRIPT_FILENAME"], ".php");
checkPermissions($base_name);

include "_header.php"; ?>

<div class=title><?=getPageTitle($base_name)?></div>
<?=$message?>

<?
$crud_data["buttons"] = array(true,true,false,true,true); //Add - Search - View - Edit - Delete
$crud_data["columns"] = array( //Filter - Search - Copy Enabled
	array("manual","يدوي","120px","center","hasVal(%s,'لا','نعم')",true,false),
	array("user_id","ملف المستخدم","250px","center","getCustomData('name','users_database','id','%s','_view_user')",true,true),
	array("title","العنوان","250px","center",null,false,true),
	array("amount","المبلغ","120px","center",null,false,true),
	array("currency","العملة","120px","center",null,true,false),
	array("reservation_id","الحجز","120px","center","hasVal(%s,'',viewButton('_view_reservation.php?id=%s','" . readLanguage(operations,view) . "','btn-primary','fas fa-search'))",false,false),
	array("payment_record_id","سجل الدفع","120px","center","hasVal(%s,'',viewButton('_view_payment.php?id=%s','" . readLanguage(operations,view) . "','btn-primary','fas fa-search'))",false,false),
	array("date","التاريخ","250px","center","dateLanguage('l, d M Y h:i A','%s')",false,false),
);
require_once("crud/crud.php");
?>

<? include "_footer.php"; ?>