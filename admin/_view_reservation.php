<? include "system/_handler.php";
$inline_page = true;

checkPermissions("reservations_database");
$reservation = getID($get["id"], "flights_reservations");
if (!$reservation){ brokenLink(); }

$trip = json_decode($reservation["search_object"], true);
$trips = $trip["trips"];

include "_header.php"; ?>

<div id=view_content><!-- Start View Content -->

<!-- Header -->
<div class=title>حجز طيران</div>

<div class="info_header header_content_icon">
	<div class=info_icon><i class="fas fa-plane"></i></div>
	
	<div class=info_title>
		<div>
			<b>حجز #<?=$reservation["code"]?></b>
			<br><small><?=$data_platforms[$trip["platform"]]?></small>
		</div>
		<div class="info_buttons hide_pdf">
			<button type=button class="btn btn-danger btn-sm" onclick="exportHTML('حجز #<?=$reservation["code"]?>', $('#view_content div.title').text(), '#view_content')"><i class="fas fa-file-pdf"></i>&nbsp;&nbsp;<?=readLanguage(general,export_pdf)?></button>
		</div>
	</div>
	
	<div class=info_blocks>
		<div class=info_block_item style="flex-grow:1"><p><?=readLanguage(users,registration_date)?></p><span><?=dateLanguage("l, d M Y h:i A",$reservation["date"])?></span></div>
		<div class=info_block_item><p>IP</p><?=naRes($reservation["user_ip"])?></div>
		<div class=info_block_item><p>رقم الحجز</p><?=naRes($reservation["pnr"])?></div>
		<div class=info_block_item><p><?=readLanguage(users,status)?></p><?=returnStatusLabel("data_reservation_status", $reservation["status"])?></div>
	</div>
	
	<div style="clear:both"></div>
</div>	

<ul class="nav nav-tabs tab-inline-header hide_pdf">
    <li class=active><a data-toggle=tab href="#info"><i class="fas fa-info"></i>&nbsp;&nbsp;بيانات الحجز</a></li>
	<li><a data-toggle=tab href="#payment"><i class="fas fa-money-bill-alt"></i>&nbsp;&nbsp;سجلات الدفع</a></li>
	<? foreach(range(0, count($trips) - 1) as $index){
		$trip_label = "رحلة (" . ($index + 1) . ") " . $trips[$index]["from"]["airport"]["ar_short_name"] . " - " . $trips[$index]["to"]["airport"]["ar_short_name"]; ?>
        <li><a data-toggle=tab href="#trip-<?=$index?>"><i class="fas fa-plane"></i>&nbsp;&nbsp;<?=$trip_label?></a></li>
    <? } ?>
</ul>

<? if ($reservation["status"]==4 && $reservation["update_reason"]){ ?>
<div class="alert alert-warning"><?=nl2br($reservation["update_reason"])?></div>
<? } ?>

<? if (($reservation["status"]==5 || $reservation["status"]==6) && $reservation["cancellation_reason"]){ ?>
<div class="alert alert-danger"><?=nl2br($reservation["cancellation_reason"])?></div>
<? } ?>

<div class=tab-content>

<div class=pdf_section>بيانات الحجز</div>
<div id="info" class="tab-pane fade in active">
	<table class=data_table>
	<tr>
		<td class=title>رقم الحجز</td>
		<td><b style="font-size:14px"><?=naRes($reservation["pnr"])?></b></td>
		<td class=title>الموظف</td>
		<td><b><?=naRes($reservation["personnel"])?></b></td>
	</tr>
	<tr>
		<td class=title>حساب المستحدم</td>
		<td><?=naRes($reservation["user_id"], getCustomData("name","users_database","id",$reservation["user_id"],"_view_user"))?></td>
		<td class=title>عدد المسافرين</td>
		<td class=valign-middle><?=count(explode(",", $reservation['passengers']))?></td>
	</tr>
	<tr>
		<td class=title>نوع الرحلة</td>
		<td>
			<? if (count($trips)==1){
				print "ذهاب فقط";
			} else if (count($trips)==2 && $trips[0]["from"]["airport"]["code"]==$trips[1]["to"]["airport"]["code"]){
				print "ذهاب و عودة";
			} else {
				print "وجهات متعددة";
			} ?>
		</td>
		<td class=title>الدرجة</td>
		<td><?=$trips[0]['flights'][0]['cabin']?></td>
	</tr>
	<tr>
		<td class=title>السعر</td>
		<td><?=$trip['price']?></td>
		<td class=title>العملة</td>
		<td><?=$trip['currency']?></td>
	</tr>
	<tr>
		<td class=title>ملاحظات</td>
		<td colspan=3><?=naRes($trip['notes'], nl2br($trip['notes']))?></td>
	</tr>
	</table>
	
	<div class="subtitle margin-top">المسافرين</div>
	<table class=fancy>
		<tr>
			<th>#</th>
			<th>الإسم</th>
			<th>تاريخ الميلاد</th>
			<th>دولة الجنسية</th>
			<th>رقم الباسبور / الهوية</th>
			<th>تاريخ الإنتهاء</th>
			<th>طلبات خاصة</th>
			<th>وجبات خاصة</th>
		</tr>	
		<? $passengers = explode(",", $reservation["passengers"]);
		foreach ($passengers AS $passenger_id){ $serial++;
			$passenger = getID($passenger_id, "users_passengers"); ?>
				<tr>
					<td class=center-large><?=$serial?></td>
					<td class=center-large><?=$data_passenger_names_prefix[$passenger["name_prefix"]] . " " . $passenger["first_name"] . " " . $passenger["last_name"]?></td>
					<td class=center-large><?=date("d/m/Y", $passenger["birth_date"])?> (<?=$data_passenger_types[$passenger["type"]]?>)</td>
					<td class=center-large><?=getData("system_database_countries", "code", $passenger["nationality"], "ar_name")?></td>
					<td class=center-large><?=($passenger["passport"] ? "<a href='../uploads/passports/" . $passenger["passport"] . "' data-fancybox>" . $passenger["ssn"] . "</a>" : $passenger["ssn"])?></td>
					<td class=center-large><?=date("d/m/Y", $passenger["ssn_end"])?></td>
					<td class=center-large><?=naRes($data_special_needs[$passenger["special_needs"]])?></td>
					<td class=center-large><?=naRes($data_special_meals[$passenger["special_meals"]])?></td>
				</tr>
		<? } ?>
	</table>
	
	<div class="subtitle margin-top">تفاصيل السعر</div>
	<table class=fancy>
		<tr>
			<th>التذكرة</th>
			<th class=center-large>السعر</th>
			<th>الضرائب</th>
			<th>العمولة</th>
			<th class=center-large>العدد</th>
			<th>المبلغ</th>
		</tr>
		<? foreach ($trips[0]["pricing"] AS $type=>$pricing){ ?>
		<tr>
			<td class=center-large><?=getDictionary($type)?></td>
			<td class=center-large><?=number_format($pricing["base"], 2)?></td>
			<td class=center-large><?=$pricing["taxes"]?></td>
			<td class=center-large><?=$pricing["commission"]?></td>
			<td class=center-large><?=$pricing["count"]?></td>
			<td class=center-large><b><?=number_format($pricing["total"], 2)?></b> <small><?=$itinerary["currency"]?></small></td>
		</tr>
		<? } ?>
	</table>
</div>

<div class=pdf_section>سجلات الدفع</div>
<div id="payment" class="tab-pane fade in">
	<table class="fancy square">
		<thead>
			<th>وسيلة الدفع</th>
			<th width=200>المبلغ</th>
			<th width=200>العملة</th>
		</thead>
		
		<? $result = mysqlQuery("SELECT * FROM users_balance WHERE reservation_id=" . $reservation["id"] . " ORDER BY date DESC");
		if (mysqlNum($result)){ ?>
			<? while ($entry = mysqlFetch($result)){?>
			<tr>
				<td class=center-large>سحب من الرصيد</td>
				<td class=center-large><b><?=number_format(abs($entry["amount"]), 2)?></b></td>
				<td class=center-large><?=$entry["currency"]?></td>
			</tr>
			<? } ?>
		<? } ?>
		
		<? $result = mysqlQuery("SELECT * FROM payment_records WHERE reservation_id=" . $reservation["id"] . " ORDER BY date DESC");
		if (mysqlNum($result)){ ?>
			<? while ($entry = mysqlFetch($result)){?>
			<tr>
				<td class=center-large><a href="_view_payment.php?id=<?=$entry["id"]?>" data-fancybox data-type=iframe><?=$data_payment_methods[$entry["method"]]?></a></td>
				<td class=center-large><b><?=number_format($entry["amount"], 2)?></b></td>
				<td class=center-large><?=$entry["currency"]?></td>
			</tr>
			<? } ?>		
		<? } ?>
	</table>
</div>

<!-- الرحلات -->
<? foreach($trips as $index => $trip){
$trip_label = "رحلة (" . ($index + 1) . ") " . $trips[$index]["from"]["airport"]["ar_short_name"] . " - " . $trips[$index]["to"]["airport"]["ar_short_name"];	?>
<div class=pdf_section><?=$trip_label?></div>
<div id="trip-<?=$index?>" class="tab-pane fade in">
	<? foreach($trip["flights"] as $flight){ ?>
		<div class=subtitle><?=$flight["from"][$suffix . "short_name"]?> - <?=$flight["to"][$suffix . "short_name"]?></div>
		<table class=data_table>
		<tr>
			<td class=title>مطار الإقلاع</td>
			<td><?=naRes($flight["from"][$suffix . "name"])?> (<?=$flight["from"]["iata"]?>)</td>
			<td class=title>دولة الإقلاع</td>
			<td><?=naRes($flight["from"]["country"], getData("system_database_countries", "code", $flight["from"]["country"], $suffix . "name"))?></td>
		</tr>
		<tr>
			<td class=title>مطار الهبوط</td>
			<td><?=naRes($flight["to"][$suffix . "name"])?> (<?=$flight["to"]["iata"]?>)</td>
			<td class=title>دولة الهبوط</td>
			<td><?=naRes($flight["to"]["country"], getData("system_database_countries", "code", $flight["to"]["country"], $suffix . "name"))?></td>
		</tr>
		<tr>
			<td class=title>المدة</td>
			<td><?=naRes($flight["duration"], getDuration($flight["duration"] * 60))?></td>
			<td class=title>المسافة</td>
			<td><?=naRes($flight["distance"], convertDistance($flight["distance"]))?></td>
		</tr>
		<tr>
			<td class=title>خط الطيران</td>
			<td><?=naRes($flight["airline"][$suffix . "name"])?></td>
			<td class=title>الطائرة</td>
			<td><?=naRes($flight["equipment"][$suffix . "name"])?></td>
		</tr>
		<tr>
			<td class=title>رقم الرحلة</td>
			<td><?=$flight["airline"]["iata"]?>-<?=$flight["trip"]?></td>
			<td class=title>درجة الرحلة</td>
			<td><?=getDictionary($flight["cabin"])?></td>
		</tr>
		<tr>
			<td class=title>وقت الإقلاع</td>
			<td><?=naRes(dateLanguage('l, d M Y h:i A', $flight["takeoff"]["time"]))?></td>
			<td class=title>محطة الإقلاع</td>
			<td><?=naRes($flight["takeoff"]["terminal"])?></td>
		</tr>
		<tr>
			<td class=title>وقت الهبوط</td>
			<td><?=naRes(dateLanguage('l, d M Y h:i A', $flight["landing"]["time"]))?></td>
			<td class=title>محطة الهبوط</td>
			<td><?=naRes($flight["landing"]["terminal"])?></td>
		</tr>
		<tr>
			<td class=title>العدد المسموح للأمتعة</td>
			<td><?=naRes($flight["luggage"]["pieces"])?></td>
			<td class=title>الوزن المسموح للأمتعة</td>
			<td><?=naRes($flight["luggage"]["weight"], $flight["luggage"]["weight"] . " " . $flight["luggage"]["unit"])?></td>
		</tr>
		</table>
	<? } ?>
	
	<div class=subtitle>سياسة الإلغاء و التغيير</div>
	<table class=fancy>
	<thead>
		<th>المسافر</th>
		<th>سياسة الإلغاء</th>
		<th>سياسة التغيير</th>
	</thead>
	<? foreach ($trip["penalties"] AS $key=>$value){ ?>
	<tr>
		<td class=center-large><?=getDictionary($key)?></td>
		<td class=center-large>
		<?
			$target = $value["cancel"];
			switch ($target["amount"]){
				case "100%": $amount = "غير قابلة للتغيير"; break;
				case "0%": $amount = "قابلة للتغيير"; break;
				default: $amount = naRes($target["amount"], $target["amount"]);
			}
			print $amount . ($target["applies"] ? " (" . getDictionary($target["applies"]) . ")" : "");
		?>
		</td>
		<td class=center-large>
		<?
			$target = $value["change"];
			switch ($target["amount"]){
				case "100%": $amount = "غير قابلة للتغيير"; break;
				case "0%": $amount = "قابلة للتغيير"; break;
				default: $amount = naRes($target["amount"], $target["amount"]);
			}
			print $amount . ($target["applies"] ? " (" . getDictionary($target["applies"]) . ")" : "");
		?>		
		</td>
	</tr>
	<? } ?>
	</table>
</div>
<? } ?>
	
</div>

</div>

<? include "_footer.php"; ?>