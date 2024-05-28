<? include "system/_handler.php";

$multiple_languages = false;
$mysqltable = "flights_reservations";
$base_name = basename($_SERVER["SCRIPT_FILENAME"], ".php");
checkPermissions($base_name);

//تأكيد الدفع
if ($post["token"] && $post["action"]=="pay"){
	mysqlQuery("UPDATE payment_records SET transaction='" . $post["receipt"] . "' WHERE reservation_id=" . $post["reservation"]);
	mysqlQuery("UPDATE $mysqltable SET status=1 WHERE id=" . $post["reservation"]);
	$success = readLanguage(records,updated);
}

//PNR
if ($post["token"] && $post["action"]=="pnr"){
	mysqlQuery("UPDATE $mysqltable SET status=2, pnr='" . $post["pnr"] . "' WHERE id=" . $post["reservation"]);
	$success = readLanguage(records,updated);
}

//تنفيذ
if ($post["token"] && $post["action"]=="confirm"){
	mysqlQuery("UPDATE $mysqltable SET status=3, personnel='" . $post["personnel"] . "' WHERE id=" . $post["reservation"]);
	$success = readLanguage(records,updated);
}

//جاري التعديل
if ($post["token"] && $post["action"]=="pending_update"){
	mysqlQuery("UPDATE $mysqltable SET status=4, update_reason='" . $post["reason"] . "', update_date='" . time() . "' WHERE id=" . $post["reservation"]);
	$success = readLanguage(records,updated);
}

//جاري الإلغاء
if ($post["token"] && $post["action"]=="pending_cancellation"){
	mysqlQuery("UPDATE $mysqltable SET status=5, cancellation_reason='" . $post["reason"] . "', cancellation_date='" . time() . "' WHERE id=" . $post["reservation"]);
	$success = readLanguage(records,updated);
}

//إلغاء
if ($post["token"] && $post["action"]=="cancel"){
	mysqlQuery("UPDATE $mysqltable SET status=6 WHERE id=" . $post["reservation"]);
	$success = readLanguage(records,updated);
}

//إعادة فتح
if ($post["token"] && $post["action"]=="reopen"){
	$record_data = getID($post["reservation"], $mysqltable);
	$status = ($record_data["pnr"] ? 2 : (getData("payment_records", "reservation_id", $record_data["id"], "transaction") ? 1 : 0));
	mysqlQuery("UPDATE $mysqltable SET status=$status, cancellation_reason='', cancellation_date=0 WHERE id=" . $post["reservation"]);
	$success = readLanguage(records,updated);
}

switch ($base_name){
	case "reservations_pending_payment":
		$condition = "status=0";
	break;
	
	case "reservations_pending":
		$condition = "status=1";
	break;

	case "reservations_confirmed":
		$condition = "status=2";
	break;

	case "reservations_executed":
		$condition = "status=3";
	break;

	case "reservations_pending_update":
		$condition = "status=4";
	break;
	
	case "reservations_pending_cancel":
		$condition = "status=5";
	break;
	
	case "reservations_cancelled":
		$condition = "status=6";
	break;

	case "reservations_database":
		$condition = null;
	break;	
}

include "_header.php"; ?>

<div class=title><?=getPageTitle($base_name)?></div>

<?
$crud_data["where_statement"] = $condition;
$crud_data["buttons"] = array(false,true,false,false,false); //Add - Search - View - Edit - Delete
$crud_data["columns"] = array( //Filter - Search - Copy Enabled
	array("id","إدارة","120px","center","reservationManage(%d)",false,false),
	array("id","تفاصيل الحجز","120px","center","viewButton('_view_reservation.php?id=%s','" . readLanguage(operations,view) . "','btn-primary','fas fa-search')",false,false),
	array("status","حالة الحجز","120px","center","returnStatusLabel('data_reservation_status', %s)",true,false),
	array("so_platform","منصة الحجز","120px","center","getVariable('data_platforms')[%s]",true,false),
	array("user_id","حساب المستخدم","250px","center","getCustomData('name','users_database','id','%s','_view_user')",false,true),
	array("user_ip","آي بي المستخدم","120px",null,false,false),
	array("pnr","رقم الحجز","120px","center",null,false,true),
	array("date","تاريخ الحجز","250px","center","dateLanguage('l, d M Y h:i A','%s')",false,true),
	array("so_trips","عدد الرحلات","120px","center",null,false,true),
	array("so_passengers","عدد المسافرين","120px","center",null,false,true),
	array("so_start","تاريخ اول رحلة","250px","center","dateLanguage('l, d M Y h:i A','%s')",false,true),
	array("so_end","تاريخ آخر رحلة","250px","center","dateLanguage('l, d M Y h:i A','%s')",false,true),
	array("so_price","سعر الحجز","120px","center",null,false,true),
	array("so_currency","عملة الحجز","120px","center",null,false,true),
);
require_once("crud/crud.php");
?>

<script>
function reservationPay(id){
	$.confirm({
		theme: "light-noborder",
		title: "تأكيد دفع حجز",
		content: "<input type=text id=receipt style='border-radius:3px' placeholder='قم بإدخال رقم العملية'>",
		buttons: {
			formSubmit: {
				text: "تسجيل رقم عملية الدفع",
				btnClass: "btn-green",
				action: function (){
					var receipt = this.$content.find("#receipt").val();
					if (!receipt){ 
						this.$content.find("#receipt").css("border","1px solid rgb(185, 74, 72)");
						return false;
					} else {
						postForm({
							action: "pay",
							reservation: id,
							receipt: receipt
						});
					}
				}
			},
			cancel: {
				text: readLanguage.plugins.message_cancel
			},
		}
	});	
}

function reservationPNR(id){
	$.confirm({
		theme: "light-noborder",
		title: "إدخال رقم الحجز",
		content: "<input type=text id=pnr style='border-radius:3px' placeholder='قم بإدخال رقم الحجز'>",
		buttons: {
			formSubmit: {
				text: "تسجيل رقم الحجز",
				btnClass: "btn-green",
				action: function (){
					var pnr = this.$content.find("#pnr").val();
					if (!pnr){ 
						this.$content.find("#pnr").css("border","1px solid rgb(185, 74, 72)");
						return false;
					} else {
						postForm({
							action: "pnr",
							reservation: id,
							pnr: pnr
						});
					}
				}
			},
			cancel: {
				text: readLanguage.plugins.message_cancel
			},
		}
	});
}

function reservationConfirm(id){
	$.confirm({
		theme: "light-noborder",
		title: "تأكيد الحجز",
		content: "<input type=text id=personnel style='border-radius:3px' placeholder='الموظف المسؤول'>",
		buttons: {
			formSubmit: {
				text: "تأكيد الحجز",
				btnClass: "btn-green",
				action: function (){
					var personnel = this.$content.find("#personnel").val();
					if (!personnel){ 
						this.$content.find("#personnel").css("border","1px solid rgb(185, 74, 72)");
						return false;
					} else {
						postForm({
							action: "confirm",
							reservation: id,
							personnel: personnel
						});
					}
				}
			},
			cancel: {
				text: readLanguage.plugins.message_cancel
			},
		}
	});
}

function reservationPendingUpdate(id){
	$.confirm({
		theme: "light-noborder",
		title: "تعديل حجز",
		content: "<textarea id=reason style='border-radius:3px' placeholder='قم بكتابة التعديل المطلوب'></textarea>",
		buttons: {
			formSubmit: {
				text: "تعديل الحجز",
				btnClass: "btn-green",
				action: function (){
					var reason = this.$content.find("#reason").val();
					if (!reason){ 
						this.$content.find("#reason").css("border","1px solid rgb(185, 74, 72)");
						return false;
					} else {
						postForm({
							action: "pending_update",
							reservation: id,
							reason: reason
						});
					}
				}
			},
			cancel: {
				text: readLanguage.plugins.message_cancel
			},
		}
	});	
}

function reservationPendingCancel(id){
	$.confirm({
		theme: "light-noborder",
		title: "إلغاء الحجز",
		content: "<input type=text id=reason style='border-radius:3px' placeholder='قم بإدخال سبب إلغاء الحجز'>",
		buttons: {
			formSubmit: {
				text: "إلغاء الحجز",
				btnClass: "btn-red",
				action: function (){
					var reason = this.$content.find("#reason").val();
					if (!reason){ 
						this.$content.find("#reason").css("border","1px solid rgb(185, 74, 72)");
						return false;
					} else {
						postForm({
							action: "pending_cancellation",
							reservation: id,
							reason: reason
						});
					}
				}
			},
			cancel: {
				text: readLanguage.plugins.message_cancel
			},
		}
	});	
}

function reservationCancel(id){
	$.confirm({
		title: "الغاء حجز",
		content: "هل انت متأكد من رغبتك في إلغاء هذا الحجز؟",
		buttons: {
			confirm: {
				text: readLanguage.plugins.message_yes,
				btnClass: "btn-red",
				action: function (){
					postForm({
						action: "cancel",
						reservation: id
					});
				}
			},
			cancel: {
				text: readLanguage.plugins.message_cancel
			},
		}
	});	
}

function reservationReOpen(id){
	$.confirm({
		title: "إعادة فتح الحجز",
		content: "هل انت متأكد من رغبتك في إعادة فتح هذا الحجز؟",
		buttons: {
			confirm: {
				text: readLanguage.plugins.message_yes,
				btnClass: "btn-green",
				action: function (){
					postForm({
						action: "reopen",
						reservation: id
					});
				}
			},
			cancel: {
				text: readLanguage.plugins.message_cancel
			},
		}
	});		
}
</script>

<? include "_footer.php"; ?>