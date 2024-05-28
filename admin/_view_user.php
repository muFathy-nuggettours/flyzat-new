<? include "system/_handler.php";
$inline_page = true;

checkPermissions("users_database");
$user_data = getID($get["id"], "users_database");
if (!$user_data){ brokenLink(); }

$agent = getData("users_agents", "user_id", $user_data["id"]);

include "_header.php"; ?>

<div id=view_content><!-- Start View Content -->

<!-- Header -->
<div class=title><?=readLanguage(users,user_profile)?></div>

<div class="info_header header_content_<?=($user_data["image"] ? "image" : "icon")?>">
	<div class=info_icon><?=($user_data["image"] ? "<img src='../uploads/users/" . $user_data["image"] . "'>" : "<i class='fas fa-user'></i>")?></div>
	
	<div class=info_title>
		<div>
			<b><?=$user_data["name"]?></b>
			<br><small><?=readLanguage(users,user)?></small>
		</div>
		<div class="info_buttons hide_pdf">
			<button type=button class="btn btn-danger btn-sm" onclick="exportHTML('<?=$user_data["name"]?>', $('#view_content div.title').text(), '#view_content')"><i class="fas fa-file-pdf"></i>&nbsp;&nbsp;<?=readLanguage(general,export_pdf)?></button>
		</div>
	</div>
	
	<div class=info_blocks>
		<div class=info_block_item style="flex-basis:10%"><p><?=readLanguage(users,user_id)?></p><span><?=$user_data["user_id"]?></span></div>
		<div class=info_block_item style="flex-grow:1"><p><?=readLanguage(users,registration_date)?></p><span><?=dateLanguage("l, d M Y h:i A",$user_data["date"])?></span></div>
		<div class=info_block_item><p><?=readLanguage(users,status)?></p><?=($user_data["banned"] ? "<span class='label label-danger'>" . readLanguage(users,status_banned) . "</span>" : "<span class='label label-success'>" . readLanguage(users,status_active) . "</span>")?></div>
		<div class=info_block_item style="flex-basis:10%"><p><?=readLanguage(users,serial)?></p><span><?=$user_data["id"]?></span></div>
	</div>
	
	<div style="clear:both"></div>
</div>	

<!-- Tabs -->
<ul class="nav nav-tabs tab-inline-header hide_pdf">
	<li class=active><a data-toggle=tab href="#info"><i class="fas fa-info"></i>&nbsp;&nbsp;<?=readLanguage(users,info_basic)?></a></li>
	<li><a data-toggle=tab href="#passengers"><i class="fas fa-users"></i>&nbsp;&nbsp;المسافرين</a></li>
	<li><a data-toggle=tab href="#reservations"><i class="fas fa-plane"></i>&nbsp;&nbsp;الحجوزات</a></li>
	<? if ($agent){ ?>
	<li><a data-toggle=tab href="#agent"><i class="fas fa-user-tie"></i>&nbsp;&nbsp;حساب الوكيل</a></li>
	<? } ?>
</ul>

<div class=tab-content><!-- Start Tabs Content -->

<!-- Baisc Information -->
<div class=pdf_section><?=readLanguage(users,info_basic)?></div>
<div id=info class="tab-pane fade in active">
<table class=data_table>
	<tr>
		<td class=title><?=readLanguage(users,email)?></td>
		<td><?=$user_data["email"]?></td>
		<td class=title><?=readLanguage(users,mobile)?></td>
		<td><span class="d-inline-block force-ltr"><?=$user_data["mobile"]?></span></td>
	</tr>
	<tr>
		<td class=title>دولة الحساب</td>
		<td><img src="../images/countries/<?=$user_data["user_country"]?>.gif"> <?=getData("system_database_countries", "code", $user_data["user_country"], $panel_language . "_name")?></td>
		<td class=title>عملة الحساب</td>
		<td><?=$user_data["user_currency"]?></td>
	</tr>
	<tr>
		<td class=title>العنوان</td>
		<td colspan=3><?=naRes($user_data["address"], nl2br($user_data["address"]))?></td>
	</tr>	
	<tr class=hide_pdf>
		<td class=title><?=readLanguage(inputs,attachments)?></td>
		<td colspan=3>
		<?
		$attachments = json_decode($user_data["attachments"],true);
		if (count($attachments)){
			print "<ul class=list_grid>";
			foreach ($attachments AS $key=>$value){
				print "<li>" . fileBlock("../uploads/users/" . $value["url"], $value["title"]) . "</li>";
			}
			print "</ul>";
		} else {
			print "<i class=na>" . readLanguage(general,na) . "</i>";
		}
		?>
		</td>
	</tr>		
</table>

<div class="subtitle margin-top"><?=readLanguage(users,social_accounts)?></div>
<table class=data_table>
	<tr><td class=title><?=readLanguage(users,social_accounts_facebook)?></td><td><?=naRes($user_data["facebook"])?></td></tr>
	<tr><td class=title><?=readLanguage(users,social_accounts_google)?></td><td><?=naRes($user_data["google"])?></td></tr>
</table>
</div>

<!-- Passengers -->
<div class=pdf_section>المسافرين</div>
<div id=passengers class="tab-pane fade in">
<? $users_passengers_result = mysqlQuery("SELECT * FROM users_passengers WHERE user_id=" . $user_data["id"] . " ORDER BY removed ASC, id DESC");
if (mysqlNum($users_passengers_result)){
	while ($passenger = mysqlFetch($users_passengers_result)){
	$removed = $passenger["removed"]; ?>
	<div class=subtitle <?=($removed ? "style='color:red'" : "")?>>
		<?=$data_passenger_names_prefix[$passenger["name_prefix"]]?> <?=$passenger["first_name"]?> <?=$passenger["last_name"]?>
		<? if ($removed){ ?><small>محذوف</small><? } ?>
	</div>
	<table class=data_table>
	<tr>
		<td class=title>تاريخ الميلاد</td>
		<td><?=dateLanguage("l, d M Y",$passenger["birth_date"])?> (<?=$data_passenger_types[$passenger["type"]]?>)</td>
		<td class=title><?=readLanguage(users,country)?></td>
		<td><img src="../images/countries/<?=$passenger["nationality"]?>.gif"> <?=getData("system_database_countries", "code", $passenger["nationality"], $panel_language . "_name")?></td>
	</tr>
	<tr>
		<td class=title>رقم جواز السفر / الهوية</td>
		<td>
			<div class=flex-center>
				<span class=flex-grow-1><?=naRes($passenger["ssn"])?></span>
				<? if ($passenger["passport"]){ ?>
				<a class="btn btn-primary btn-sm hide_pdf" data-fancybox href="../uploads/passports/<?=$passenger["passport"]?>">معاينة المستند</a>
				<? } ?>
			</div>
		</td>
		<td class=title>تاريخ الإنتهاء</td>
		<td class=valign-middle><?=dateLanguage("l, d M Y",$passenger["ssn_end"])?></td>
	</tr>
	<tr>
		<td class=title>الإحتياجات الخاصة</td>
		<td><?=nares($data_special_needs[$passenger["special_needs"]])?></td>
		<td class=title>الطلبات الخاصة</td>
		<td><?=nares($data_special_meals[$passenger["special_meals"]])?></td>
	</tr>
	</table>
<? }
} else { ?>
	<div class=view_no_content>لا يوجد مسافرين مسجلين لهذا المستخدم</div>
<? } ?>
</div>

<div class=pdf_section>الحجوزات</div>
<div id=reservations class="tab-pane fade">
	<? $reservations_result = mysqlQuery("SELECT * FROM flights_reservations WHERE user_id=" . $user_data["id"]);
	if (mysqlNum($reservations_result)){ ?>
	<table class=fancy>
		<tr>
			<th>#</th>
			<th width=120>رقم الحجز</th>
			<th width=120>تفاصيل الحجز</th>
			<th>تاريخ الحجز</th>
			<th width=120>حالة الحجز</th>
			<th>منصة الحجز</th>
			<th width=120>عملة الحجز</th>
		</tr>	
		<? while ($reservation = mysqlFetch($reservations_result)){ $serial++; ?>
			<tr>
				<td class=center-large><?=$serial?></td>
				<td class=center-large><?=naRes($reservation["pnr"])?></td>
				<td class=center-large><?=viewButton('_view_reservation.php?id=' . $reservation["id"],'معاينة','btn-primary','fas fa-search')?></td>
				<td class=center-large><?=dateLanguage('l, d M Y h:i A',$reservation["date"])?></td>
				<td class=center-large><?=returnStatusLabel('data_reservation_status',$reservation["status"])?></td>
				<td class=center-large><?=$data_platforms[$reservation["so_platform"]]?></td>
				<td class=center-large><?=$reservation["so_currency"]?></td>
			</tr>
		<? } ?>
	</table>
	<? } else { ?>
	<div class=view_no_content>لا يوجد حجوزات لهذا المستخدم</div>
	<? } ?>
</div>

<!-- Agent -->
<? if ($agent){ ?>
<div class=pdf_section>حساب الوكيل</div>
<div id=agent class="tab-pane fade">
<table class=data_table>
	<tr>
		<td class=title>إسم الشركة</td>
		<td><?=$agent["company_name"]?></td>
		<td class=title>عنوان الشركة</td>
		<td><?=$agent["company_address"]?></td>
	</tr>		
</table>

<div class="subtitle margin-top">سياسة التسعير</div>
<table class=data_table>
	<tr>
		<td class=title>مبلغ ثابت</td>
		<td>
			<ul class=inline_tags>
				<? $percentages = json_decode($agent["fixed"], true);
				foreach ($percentages AS $key=>$value){
				$currency = getData("system_payment_currencies", "code", $key, "ar_name"); ?>
				<li><span><b><?=$currency?></b>&nbsp;<?=$value?></span></li>&nbsp;
				<? } ?>
			</ul>
		</td>
	</tr>
	<tr>
		<td class=title>نسبة العمولة</td>
		<td><?=$agent["percentage"]?>%</td>
	</tr>
</table>
</div>
<? } ?>

</div><!-- End Tabs Content -->

</div><!-- End View Content -->

<? include "_footer.php"; ?>