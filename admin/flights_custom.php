<? include "system/_handler.php";

$mysqltable = "flights_custom";
$base_name = basename($_SERVER["SCRIPT_FILENAME"], ".php");
checkPermissions($base_name);

//==== DELETE Record ====
if ($delete){
	mysqlQuery("DELETE FROM $mysqltable WHERE id=$delete");
	if (mysqlAffectedRows()){ $success = readLanguage(records,deleted); } else { $error = readLanguage(records,unavailable); }

//==== ADD Record ====
} else if ($post["token"] && !$edit){
    //Build pricing array
    foreach($data_flight_classes AS $key => $value) { $pricing[$key] = json_decode($post["pricing-$key"], true); }
    $penalties['change'] = json_decode($post['penalties_change'], true);
    $penalties['cancel'] = json_decode($post['penalties_cancel'], true);

	$query = "INSERT INTO $mysqltable (
        flight_number,
        date,
        currency,
        pricing,
        penalties,
        luggage_number,
        luggage_weight,
        origin,
        destination,
        takeoff,
        landing,
        airline,
        plane_type,
        takeoff_hall,
        landing_hall,
        distance,
        duration
	) VALUES (
        '{$post['flight_number']}',
        '" . getTimestamp($post['date']) . "',
        '{$post['currency']}',
        '" . json_encode($pricing, true) . "',
        '" . json_encode($penalties, true) . "',
        '{$post['luggage_number']}',
        '{$post['luggage_weight']}',
        '{$post['origin']}',
        '{$post['destination']}',
        '" . getTimestamp($post["takeoff"], "h:i A") . "',
        '" . getTimestamp($post["landing"], "h:i A") . "',
        '{$post['airline']}',
        '{$post['plane_type']}',
        '{$post['takeoff_hall']}',
        '{$post['landing_hall']}',
        '{$post['distance']}',
        '{$post['duration']}'
	)";
	mysqlQuery($query);

	$success = readLanguage(records, added);

//==== EDIT Record ====
} else if ($post["token"] && $edit) {
    //Build pricing array
    foreach($data_flight_classes AS $key => $value) { $pricing[$key] = json_decode($post["pricing-$key"], true); }
    $penalties['change'] = json_decode($post['penalties_change'], true);
    $penalties['cancel'] = json_decode($post['penalties_cancel'], true);
	
	$query = "UPDATE $mysqltable SET
		flight_number='{$post['flight_number']}',
		date='" . getTimestamp($post['date']) . "',
		currency='{$post['currency']}',
		pricing='" . json_encode($pricing, true) . "',
		penalties='" . json_encode($penalties, true) . "',
		luggage_number='{$post['luggage_number']}',
		luggage_weight='{$post['luggage_weight']}',
		origin='{$post['origin']}',
		destination='{$post['destination']}',
		takeoff='" . getTimestamp($post["takeoff"], "h:i A") . "',
		landing='" . getTimestamp($post["landing"], "h:i A") . "',
		airline='{$post['airline']}',
		plane_type='{$post['plane_type']}',
		takeoff_hall='{$post['takeoff_hall']}',
		landing_hall='{$post['landing_hall']}',
		distance='{$post['distance']}',
		duration='{$post['duration']}'
	WHERE id=$edit";
	mysqlQuery($query);

	$success = readLanguage(records, updated);
}

//Read and Set Operation
if ($edit){
	$entry = getID($edit,$mysqltable);
	if (!$entry){ $error = readLanguage(records,unavailable); $edit = null; }
}
if ($edit){
	$button = readLanguage(records,update);
	$action = "$base_name.php" . rebuildQueryParameters(array("delete","token"));
} else {
	$button = readLanguage(records,add);
	$action = "$base_name.php" . rebuildQueryParameters(array("delete","token","edit"));
	if ($error){ foreach ($_POST as $key => $value){ $entry[$key] = $value; } }
}
if ($success){ $message = "<div class='alert alert-success'>$success</div>"; }
if ($error){ $message = "<div class='alert alert-danger'>$error</div>"; }

include "_header.php" ?>

<script src="../plugins/fixed-data.js?v=<?=$system_settings["system_version"]?>"></script>

<div class=title><?=getPageTitle($base_name)?></div>
<?=$message?>

<form method=post enctype="multipart/form-data" action="<?=$action?>">
<input type=hidden name=token value="<?=$token?>">

<div class=subtitle>معلومات الرحلة</div>
<table class=data_table>
<tr>
    <td class=title>رقم الرحلة: <i class=requ></i></td>
    <td><input type=text name=flight_number value="<?=$entry['flight_number']?>" data-validation=required></td>
    <td class=title>تاريخ الرحلة: <i class=requ></i></td>
    <td>
        <input type=text name=date id=date readonly class=date_field data-validation=required>
		<? if ($entry["date"]){ $date = $entry["date"] . "000"; } ?>
		<script>createCalendar("date", new Date(<?=$date?>))</script>
    </td>
</tr>
<tr>
    <td class=title>صالة المغادرة: <i class=requ></i></td>
    <td><input type=text name=takeoff_hall value="<?=$entry['takeoff_hall']?>" data-validation=required></td>
    <td class=title>صالة الوصول: <i class=requ></i></td>
    <td><input type=text name=landing_hall value="<?=$entry['landing_hall']?>" data-validation=required></td>
</tr>
<tr>
    <td class=title>المسافة: <i class=requ></i></td>
    <td><div class=input-addon><input type=number name=distance value="<?=$entry["distance"]?>" data-validation=number><span after>ميل</span></div></td>
    <td class=title>وقت الرحلة: <i class=requ></i></td>
    <td><div class=input-addon><input type=number name=duration value="<?=$entry["duration"]?>" data-validation=number><span after>دقيقة</span></div></td>
</tr>
</table>

<div class=subtitle>معلومات الطيران</div>
<table class=data_table>
<tr>
    <td class=title>شركة الطيران: <i class=requ></i></td>
    <td>
        <? $input="airline"; $value=$entry['airline']; $conditions=null; $mandatory=true; $removable=true; ?>
        <? include "includes/select_airline.php"; ?>
    </td>
    <td class=title>نوع الطائرة: <i class=requ></i></td>
	<td>
		<select name=plane_type id=plane_type>
		<?=populateData("SELECT * FROM system_database_planes", "iata", "ar_name")?>
		</select>
        <script>
            <? if ($entry["plane_type"]){ ?>setSelectValue("#plane_type", "<?=$entry["plane_type"]?>");<? } ?>
            $("#plane_type").select2();
        </script>
	</td>
</tr>
<tr>
    <td class=title>مطار الإقلاع: <i class=requ></i></td>
    <td>
        <? $input="origin"; $value=$entry['origin']; $conditions=null; $mandatory=true; $removable=true; ?>
        <? include "includes/select_airport.php"; ?>
    </td>
    <td class=title>وقت الإقلاع: <i class=requ></td>
    <td>
        <input type=text name=takeoff id=takeoff class=time_field data-validation=required readonly>
		<? if ($entry["takeoff"]){ $takeoff = $entry["takeoff"] . "000"; } ?>
		<script>createTime("#takeoff", new Date(<?=$takeoff?>))</script>
    </td>
</tr>
<tr>
    <td class=title>مطار الهبوط: <i class=requ></i></td>
    <td>
        <? $input="destination"; $value=$entry['destination']; $conditions=null; $mandatory=true; $removable=true; ?>
        <? include "includes/select_airport.php"; ?>
    </td>
    <td class=title>وقت الهبوط: <i class=requ></i></td>
    <td>
        <input type=text name=landing id=landing class=time_field data-validation=required readonly>
		<? if ($entry["landing"]){ $landing = $entry["landing"] . "000"; } ?>
		<script>createTime("#landing", new Date(<?=$landing?>))</script>
    </td>
</tr>
<tr>
    <td class=title>عدد الأمتعة المسموحة: <i class=requ></i></td>
    <td><input type=number name=luggage_number value="<?=$entry["luggage_number"]?>" data-validation=number></td>
    <td class=title>الوزن المسموح: <i class=requ></i></td>
    <td><div class=input-addon><input type=number name=luggage_weight value="<?=$entry["luggage_weight"]?>" data-validation=number><span after>كيلو</span></div></td>
</tr>
</table>

<div class=subtitle>التسعير<small>اترك حقول الفئة فارغة لعدم ظهورها في البحث</small></div>
<table class=data_table>
<tr>
	<td class=title>العملة: <i class=requ></i></td>
	<td>
		<select name=currency id=currency>
		<?=populateData("SELECT * FROM system_payment_currencies", "code", "ar_name")?>
		</select>
		<? if ($entry["currency"]){ ?><script>setSelectValue("#currency", "<?=$entry["currency"]?>")</script><? } ?>
	</td>
</tr>
<? foreach($data_flight_classes AS $class_key => $class_value) { ?>
<tr>
    <td class=title>الدرجة <?=$class_value?>: <i class=requ></i></td>
    <td colspan=3>
        <input type=hidden name=pricing-<?=$class_key?> id=pricing-<?=$class_key?>>
        <div class=d-flex json-fixed-data=pricing-<?=$class_key?>>
			<table class="fancy square"><thead>
				<th width=25%>الفئة</th>
				<th width=25%>السعر الأساسي</th>
				<th width=25%>العمولة</th>
				<th width=25%>المقاعد المتاحة</th>
			</thead>
			<? foreach ($data_passenger_types AS $passenger_key=>$passenger_value){ ?>
			<tr>
				<td><b><?=$passenger_value?></b></td>
				<td><input type=number data-name="<?=$passenger_key . "-price"?>"></td>
				<td><input type=number data-name="<?=$passenger_key . "-commission"?>"></td>
				<td><input type=number data-name="<?=$passenger_key . "-seats"?>"></td>
			</tr>
			<? } ?>
			</table>
        </div>
        <? if ($entry["pricing"]){ ?><script>fixedDataRead("pricing-<?=$class_key?>", <?=json_encode(json_decode($entry["pricing"], true)[$class_key])?>)</script><? } ?>
    </td>
</tr>
<? } ?>
</table>

<? $policy_types = "<option value=0>مبلغ</option><option value=1>نسبة</option>"; ?>
<style>
select[data-name] {
	height: 32px;
	border-right: 0;
	width: 60px;
}
</style>
<div class=subtitle>سياسة الإلغاء والتعديل</div>
<table class=data_table>
<tr>
    <td class=title>سياسة الإلغاء: <i class=requ></i></td>
    <td colspan=3>
        <input type=hidden name=penalties_cancel id=penalties_cancel>
        <div class=d-flex json-fixed-data=penalties_cancel>
            <div class="input-addon flex-grow-1"><span before>بالغ</span><input type=number data-name=adt data-validation=number><select data-name=adt-policy><?=$policy_types?></select></div>&nbsp;&nbsp;
            <div class="input-addon flex-grow-1"><span before>طفل</span><input type=number data-name=cnn data-validation=number><select data-name=cnn-policy><?=$policy_types?></select></div>&nbsp;&nbsp;
            <div class="input-addon flex-grow-1"><span before>رضيع</span><input type=number data-name=inf data-validation=number><select data-name=inf-policy><?=$policy_types?></select></div>
        </div>
        <? if ($entry["penalties"]){ ?><script>fixedDataRead("penalties_cancel", <?=json_encode(json_decode($entry["penalties"], true)['cancel'])?>)</script><? } ?>
    </td>
</tr>
<tr>
    <td class=title>سياسة التعديل: <i class=requ></i></td>
    <td colspan=3>
        <input type=hidden name=penalties_change id=penalties_change>
        <div class=d-flex json-fixed-data=penalties_change>
            <div class="input-addon flex-grow-1"><span before>بالغ</span><input type=number data-name=adt data-validation=number><select data-name=adt-policy><?=$policy_types?></select></div>&nbsp;&nbsp;
            <div class="input-addon flex-grow-1"><span before>طفل</span><input type=number data-name=cnn data-validation=number><select data-name=cnn-policy><?=$policy_types?></select></div>&nbsp;&nbsp;
            <div class="input-addon flex-grow-1"><span before>رضيع</span><input type=number data-name=inf data-validation=number><select data-name=inf-policy><?=$policy_types?></select></div>
        </div>
        <? if ($entry["penalties"]){ ?><script>fixedDataRead("penalties_change", <?=json_encode(json_decode($entry["penalties"], true)['change'])?>)</script><? } ?>
    </td>
</tr>
</table>

<div class=submit_container><input type=button class=submit value="<?=$button?>"></div>
</form>

<div class=crud_separator></div>

<?
$crud_data["buttons"] = array(true, true, false, true, true); //Add - Search - View - Edit - Delete
$crud_data["columns"] = array( //Filter - Search - Copy Enabled
    array("origin", "مطار الإقلاع", "220px", "center", "getData('system_database_airports','iata','%s', 'ar_name')", true, true),
    array("destination", "مطار الهبوط", "220px", "center", "getData('system_database_airports','iata','%s', 'ar_name')", true, true),
    array("date", "تاريخ الرحلة", "220px", "center", "dateLanguage('l, d M Y', '%s')", false, false),
    array("takeoff", "وقت الإقلاع", "150px", "center", "dateLanguage('h:i A','%s')", false, true),
    array("landing", "وقت الهبوط", "150px", "center", "dateLanguage('h:i A','%s')", false, true),
    array("luggage_number", "عدد الأمتعة", "120px", "center", null, false, true),
    array("luggage_weight", "الوزن المسموح", "120px", "center", null, false, true),
	array("flight_number", "رقم الرحلة", "120px", "center", null, false, true),
    array("airline", "شركة الطيران", "220px", "center", "getData('system_database_airlines','iata','%s', 'ar_name')", true, true),
    array("plane_type", "نوع الطائرة", "220px", "center", "getData('system_database_planes','iata','%s', 'ar_name')", true, true),
    array("takeoff_hall", "صالة الإقلاع", "120px", "center", null, false, true),
    array("landing_hall", "صالة الهبوط", "120px", "center", null, false, true),
    array("distance", "المسافة", "120px", "center", "'%s كم'", false, true),
    array("duration", "وقت الرحلة", "120px", "center", "'%s دقيقة'", false, true),
);
require_once("crud/crud.php");
?>

<? include "_footer.php" ?>