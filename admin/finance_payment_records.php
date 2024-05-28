<? include "system/_handler.php";

$mysqltable = "payment_records";
$base_name = basename($_SERVER["SCRIPT_FILENAME"], ".php");
checkPermissions($base_name);

include "_header.php"; ?>

<div class=title><?=getPageTitle($base_name)?></div>
<?=$message?>

<?
$crud_data["buttons"] = array(true,true,false,true,true); //Add - Search - View - Edit - Delete
$crud_data["columns"] = array( //Filter - Search - Copy Enabled
	array("id","التفاصيل","120px","center","hasVal(%s,'',viewButton('_view_payment.php?id=%s','" . readLanguage(operations,view) . "','btn-primary','fas fa-search'))",false,false),
	array("user_id","ملف المستخدم","250px","center","getCustomData('name','users_database','id','%s','_view_user')",true,true),
	array("method","وسيلة الدفع","200px","center","getVariable('data_payment_methods')[%s]",true,false),
	array("amount","المبلغ","120px","center",null,false,true),
	array("currency","العملة","120px","center",null,true,false),
	array("transaction","العملية","120px","center","readRecordData(json_decode('%s', true),'transaction')",false,false),
	array("reservation_id","الحجز","120px","center","hasVal(%s,'',viewButton('_view_reservation.php?id=%s','" . readLanguage(operations,view) . "','btn-primary','fas fa-search'))",false,false),
	array("date","التاريخ","250px","center","dateLanguage('l, d M Y h:i A','%s')",false,false),
);
require_once("crud/crud.php");
?>

<? include "_footer.php"; ?>