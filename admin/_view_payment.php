<? include "system/_handler.php";
$inline_page = true;

checkPermissions("finance_payment_records");
$entry = getID($get["id"], "payment_records");
if (!$entry){ brokenLink(); }

include "_header.php"; ?>

<div id=view_content><!-- Start View Content -->

<!-- Header -->
<div class=title>عملية دفع إلكتروني</div>

<div class="info_header header_content_icon">
	<div class=info_icon><i class="fas fa-money-bill-alt"></i></div>
	
	<div class=info_title>
		<div>
			<b><?=getID($entry["user_id"], "users_database", "name")?></b>
			<br><small><?=($entry["reservation_id"] ? "حجز" : "شحن رصيد")?></small>
		</div>
		<div class="info_buttons hide_pdf">
			<button type=button class="btn btn-danger btn-sm" onclick="exportHTML('<?=$entry["name"]?>', $('#view_content div.title').text(), '#view_content')"><i class="fas fa-file-pdf"></i>&nbsp;&nbsp;<?=readLanguage(general,export_pdf)?></button>
		</div>
	</div>
	
	<div class=info_blocks>
		<div class=info_block_item style="flex-grow:1"><p><?=readLanguage(users,registration_date)?></p><span><?=dateLanguage("l, d M Y h:i A",$entry["date"])?></span></div>
		<div class=info_block_item style="flex-basis:10%"><p><?=readLanguage(users,serial)?></p><span><?=$entry["id"]?></span></div>
	</div>
	
	<div style="clear:both"></div>
</div>	

<!-- Baisc Information -->
<div class=pdf_section><?=readLanguage(users,info_basic)?></div>
<div id=info class="tab-pane fade in active">
<table class=data_table>
	<tr>
		<td class=title>حساب المستحدم</td>
		<td><?=naRes($entry["user_id"], getCustomData("name","users_database","id",$entry["user_id"],"_view_user"))?></td>
		<td class=title>وسيلة الدفع</td>
		<td class=valign-middle><?=$data_payment_methods[$entry["method"]]?></td>
	</tr>
	<tr>
		<td class=title>نوع العملية</td>
		<td class=valign-middle colspan=<?=($entry["reservation_id"] ? 1 : 3)?>><?=($entry["reservation_id"] ? "حجز" : "شحن رصيد")?></td>
		<? if ($entry["reservation_id"]){ ?>
			<td class=title>رقم الحجز</td>
			<td><?=getCustomData("code","flights_reservations","id",$entry["reservation_id"],"_view_reservation")?></td>
		<? } ?>
	</tr>	
	<tr>
		<td class=title>المبلغ</td>
		<td><?=number_format($entry["amount"], 2)?></td>
		<td class=title>العملة</td>
		<td><?=$entry["currency"]?></td>
	</tr>
</table>

<div class="subtitle margin-top">بيانات العملية</div>
<? $transaction = json_decode($entry["transaction"], true); ?>
<table class=data_table>
	<tr>
		<td class=title>معرف العملية</td>
		<td><?=naRes($transaction["id"])?></td>
	</tr>
</table>

</div>

</div><!-- End View Content -->

<? include "_footer.php"; ?>