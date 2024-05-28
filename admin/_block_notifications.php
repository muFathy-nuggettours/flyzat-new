<? $notifications = array();

//Contact Form Requests
if (checkPermissions("channel_requests_custom",2)){
	$channel_requests_custom = mysqlNum(mysqlQuery("SELECT * FROM channel_requests WHERE status=0"));
	if ($channel_requests_custom){ $notifications["channel_requests_custom"] = array("يوجد <b>$channel_requests_custom</b> طلب تواصل جديد من خلال الموقع الإلكتروني",$channel_requests_custom); }
}

//الحجوزات بانتظار الدفع
if (checkPermissions("reservations_pending_payment",2)){
	$reservations_pending_payment = mysqlNum(mysqlQuery("SELECT * FROM flights_reservations WHERE status=0"));
	if ($reservations_pending_payment){ $notifications["reservations_pending_payment"] = array("يوجد <b>$reservations_pending_payment</b> حجز بانتظار مندوب للدفع",$reservations_pending_payment); }
}

//الحجوزات المعلقة
if (checkPermissions("reservations_pending",2)){
	$reservations_pending = mysqlNum(mysqlQuery("SELECT * FROM flights_reservations WHERE status=1"));
	if ($reservations_pending){ $notifications["reservations_pending"] = array("يوجد <b>$reservations_pending</b> حجز معلق بانتظار استخراج رقم الحجز",$reservations_pending); }
}

//الحجوزات المؤكدة
if (checkPermissions("reservations_confirmed",2)){
	$reservations_confirmed = mysqlNum(mysqlQuery("SELECT * FROM flights_reservations WHERE status=2"));
	if ($reservations_confirmed){ $notifications["reservations_confirmed"] = array("يوجد <b>$reservations_confirmed</b> حجز مؤكد بانتظار التنفيذ",$reservations_confirmed); }
}

//الحجوزات المدفوعة
if (checkPermissions("reservations_pending_update",2)){
	$reservations_pending_update = mysqlNum(mysqlQuery("SELECT * FROM flights_reservations WHERE status=4"));
	if ($reservations_pending_update){ $notifications["reservations_pending_update"] = array("يوجد <b>$reservations_pending_update</b> حجز بحاجة للتعديل",$reservations_pending_update); }
}

//الحجوزات المدفوعة
if (checkPermissions("reservations_pending_cancel",2)){
	$reservations_pending_cancel = mysqlNum(mysqlQuery("SELECT * FROM flights_reservations WHERE status=5"));
	if ($reservations_pending_cancel){ $notifications["reservations_pending_cancel"] = array("يوجد <b>$reservations_pending_cancel</b> حجز بحاجة للالغاء",$reservations_pending_cancel); }
}

//===== Total Notifications =====
foreach ($notifications as $key=>$value){
	$total_notifications += $value[1];
} ?>