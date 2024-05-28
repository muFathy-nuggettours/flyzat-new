<? include "system/_handler.php";

$multiple_languages = false;
$mysqltable = "system_settings";
$base_name = basename($_SERVER["SCRIPT_FILENAME"], ".php");
checkPermissions($base_name);

//==== EDIT Record ====
if ($post["token"]){
	mysqlQuery("UPDATE $mysqltable SET content = CASE
		WHEN title='travelport_user' THEN '" . $post["travelport_user"] . "'
		WHEN title='travelport_password' THEN '" . $post["travelport_password"] . "'
		WHEN title='travelport_branch' THEN '" . $post["travelport_branch"] . "'
		WHEN title='travelport_pcc' THEN '" . $post["travelport_pcc"] . "'
		
		WHEN title='misr_egp_merchant' THEN '" . $post["misr_egp_merchant"] . "'
		WHEN title='misr_egp_password' THEN '" . $post["misr_egp_password"] . "'
		WHEN title='misr_usd_merchant' THEN '" . $post["misr_usd_merchant"] . "'
		WHEN title='misr_usd_password' THEN '" . $post["misr_usd_password"] . "'		

		WHEN title='pricing_fixed' THEN '" . $post["pricing_fixed"] . "'
		WHEN title='pricing_percentage' THEN '" . $post["pricing_percentage"] . "'
		
		WHEN title='hyperpay_live' THEN '" . $post["hyperpay_live"] . "'
		WHEN title='hyperpay_access_token' THEN '" . $post["hyperpay_access_token"] . "'
		WHEN title='hyperpay_entity_mada' THEN '" . $post["hyperpay_entity_mada"] . "'
		WHEN title='hyperpay_entity_visa' THEN '" . $post["hyperpay_entity_visa"] . "'
		
		WHEN title='payment_cash' THEN '" . $post["payment_cash"] . "'
		WHEN title='payment_cash_message' THEN '" . $post["payment_cash_message"] . "'
		
		WHEN title='hide_cancel_fees' THEN '" . $post["hide_cancel_fees"] . "'
		WHEN title='hide_change_fees' THEN '" . $post["hide_change_fees"] . "'
		
		ELSE content
	END");
	$success = readLanguage(records,updated);
}

//Read and Set Operation
if ($success){ $message = "<div class='alert alert-success'>$success</div>"; }

//Reload Data
$system_settings = fetchData($mysqltable);

include "_header.php"; ?>

<script src="../plugins/fixed-data.js?v=<?=$system_settings["system_version"]?>"></script>
<script src="../plugins/wizard.min.js?v=<?=$system_settings["system_version"]?>"></script>
<link href="../plugins/wizard.css?v=<?=$system_settings["system_version"]?>" rel="stylesheet">

<div class=title><?=getPageTitle($base_name)?></div>
<?=$message?>

<form method=post enctype="multipart/form-data" action="<?=$action?>">
<input type=hidden name=token value="<?=$token?>">

<!--Navigation-->
<div class="wizard margin-top"><div class=wizard-inner><div class=connecting-line style="width:70%"></div><ul class="nav nav-tabs" role="tablist">
	<li class=active role=presentation>
		<a href="#settings_general" data-toggle=tab>
			<span class=round-tab><i class="fas fa-cogs"></i></span>
			<div class="tab-title hidden-sm hidden-xs">إعدادات عامة</div>
		</a>
	</li>
	<li role=presentation>
		<a href="#settings_air" data-toggle=tab>
			<span class=round-tab><i class="fas fa-plane"></i></span>
			<div class="tab-title hidden-sm hidden-xs">إعدادات الطيران</div>
		</a>
	</li>
	<li role=presentation>
		<a href="#settings_payment" data-toggle=tab>
			<span class=round-tab><i class="fas fa-credit-card"></i></span>
			<div class="tab-title hidden-sm hidden-xs">إعدادت الدفع</div>
		</a>
	</li>
	<li role=presentation>
		<a href="#settings_pricing" data-toggle=tab>
			<span class=round-tab><i class="fas fa-dollar-sign"></i></span>
			<div class="tab-title hidden-sm hidden-xs">إعدادت التسعير الإفتراضية</div>
		</a>
	</li>
</ul></div></div>

<div class=tab-title-container>
	<span>إعدادات عامة</span>
	<div class=tab-title-buttons>
		<button type=button class="btn btn-default btn-sm prev-step"><i class="glyphicon glyphicon-chevron-left"></i></button>&nbsp;
		<button type=button class="btn btn-default btn-sm next-step"><i class="glyphicon glyphicon-chevron-right"></i></button>
	</div>
	<div style="clear:both"></div>
</div>

<div class=tab-content>

<!-- General Settings -->
<div class="tab-pane active" id=settings_general>
<table class=data_table>
<tr>
	<td class=title>إخفاء سياسة الإلغاء:</td>
	<td>
		<div class=switch><label><?=$data_no_yes[0]?><input type=checkbox name=hide_cancel_fees value=1 <?=($system_settings["hide_cancel_fees"] ? "checked" : "")?>><span class=lever></span><?=$data_no_yes[1]?></label></div>
	</td>
</tr>
<tr>
	<td class=title>إخفاء سياسة التغيير:</td>
	<td>
		<div class=switch><label><?=$data_no_yes[0]?><input type=checkbox name=hide_change_fees value=1 <?=($system_settings["hide_change_fees"] ? "checked" : "")?>><span class=lever></span><?=$data_no_yes[1]?></label></div>
	</td>
</tr>
</table>
</div>

<!-- Air Settings -->
<div class=tab-pane id=settings_air>
<div class=subtitle>Travelport</div>
<table class=data_table>
<tr>
	<td class=title>Username: <i class=requ></i></td>
	<td><input type=text name=travelport_user data-validation=requiredVisible value="<?=$system_settings["travelport_user"]?>"></td>
</tr>
<tr>
	<td class=title>Password: <i class=requ></i></td>
	<td><input type=text name=travelport_password data-validation=requiredVisible value="<?=$system_settings["travelport_password"]?>"></td>
</tr>
	<tr><td class=title>Branch: <i class=requ></i></td>
	<td><input type=text name=travelport_branch data-validation=requiredVisible value="<?=$system_settings["travelport_branch"]?>"></td>
</tr>
<tr>
    <td class=title>PCC:</td>
	<td>
		<input type=hidden name=travelport_pcc id=travelport_pcc>
		<ul class=inline_input json-fixed-data=travelport_pcc>
		<? $result = mysqlQuery("SELECT * FROM system_payment_currencies");
		while ($currency = mysqlFetch($result)){ ?>
			<li style="flex-basis:100px">
				<div class=input-addon><input type=text data-name="<?=$currency["code"]?>"><span after><?=$currency["ar_name"]?></span></div>
			</li>
		<? } ?>
		</ul>
		<? if ($system_settings["travelport_pcc"]){ ?><script>fixedDataRead("travelport_pcc", <?=$system_settings["travelport_pcc"]?>)</script><? } ?>
	</td>
</tr>
</table>
</div>

<!-- Payment Settings -->
<div class=tab-pane id=settings_payment>
<!--
<div class=subtitle>بنك مصر (جنيه مصري)</div>
<table class=data_table>
<tr>
	<td class=title>Merchant ID: <i class=requ></i></td>
	<td><input type=text name=misr_egp_merchant data-validation=requiredVisible value="<?=$system_settings["misr_egp_merchant"]?>"></td>
</tr>
<tr>
	<td class=title>Password: <i class=requ></i></td>
	<td><input type=text name=misr_egp_password data-validation=requiredVisible value="<?=$system_settings["misr_egp_password"]?>"></td>
</tr>
</table>

<div class=subtitle>بنك مصر (دولار امريكي)</div>
<table class=data_table>
<tr>
	<td class=title>Merchant ID: <i class=requ></i></td>
	<td><input type=text name=misr_usd_merchant data-validation=requiredVisible value="<?=$system_settings["misr_usd_merchant"]?>"></td>
</tr>
<tr>
	<td class=title>Password: <i class=requ></i></td>
	<td><input type=text name=misr_usd_password data-validation=requiredVisible value="<?=$system_settings["misr_usd_password"]?>"></td>
</tr>
</table>
-->

<div class=subtitle>هايبر باي (ريال سعودي)</div>
<table class=data_table>
<tr>
	<td class=title>Status: <i class=requ></i></td>
	<td>
		<div class=radio_container id=hyperpay_live>
			<label><input name=hyperpay_live type=radio value=0 <?=(!$system_settings["hyperpay_live"] ? "checked" : "")?>><span>Test</span></label>
			<label><input name=hyperpay_live type=radio value=1 <?=($system_settings["hyperpay_live"] ? "checked" : "")?>><span>Live</span></label>
		</div>
	</td>
</tr>
<tr>
	<td class=title>Access Token: <i class=requ></i></td>
	<td><input type=text name=hyperpay_access_token data-validation=requiredVisible value="<?=$system_settings["hyperpay_access_token"]?>"></td>
</tr>
<tr>
	<td class=title>Entity (Mada): <i class=requ></i></td>
	<td><input type=text name=hyperpay_entity_mada data-validation=requiredVisible value="<?=$system_settings["hyperpay_entity_mada"]?>"></td>
</tr>
<tr>
	<td class=title>Entity (Visa / Master): <i class=requ></i></td>
	<td><input type=text name=hyperpay_entity_visa data-validation=requiredVisible value="<?=$system_settings["hyperpay_entity_visa"]?>"></td>
</tr>
</table>

<div class=subtitle>الدفع النقدي</div>
<table class=data_table>
	<tr>
		<td class=title>الدفع النقدي:</td>
		<td>
			<div class=switch><label><?=$data_disabled_enabled[0]?><input type=checkbox name=payment_cash value=1 <?=($system_settings["payment_cash"] ? "checked" : "")?>><span class=lever></span><?=$data_disabled_enabled[1]?></label></div>
		</td>
	</tr>
	<tr>
		<td class=title>تعليمات الدفع:</td>
		<td><textarea name=payment_cash_message class=mceEditor><?=$system_settings["payment_cash_message"]?></textarea></td>
	</tr>
</table>
</div>

<!-- Pricing Settings -->
<div class=tab-pane id=settings_pricing>
<table class=data_table>
<tr>
    <td class=title>مبلغ ثابت:</td>
	<td>
		<input type=hidden name=pricing_fixed id=pricing_fixed>
		<ul class=inline_input json-fixed-data=pricing_fixed>
		<? $result = mysqlQuery("SELECT * FROM system_payment_currencies");
		while ($currency = mysqlFetch($result)){ ?>
			<li style="flex-basis:100px">
				<div class=input-addon><input type=number data-name="<?=$currency["code"]?>" data-validation=number data-validation-optional=true data-validation-allowing="range[0;9999],float"><span after><?=$currency["ar_name"]?></span></div>
			</li>
		<? } ?>
		</ul>
		<? if ($system_settings["pricing_fixed"]){ ?><script>fixedDataRead("pricing_fixed", <?=$system_settings["pricing_fixed"]?>)</script><? } ?>
	</td>
</tr>
<tr>
    <td class=title>نسبة العمولة:</td>
    <td>
		<div class=input-addon><input type=number name=pricing_percentage data-validation=number data-validation-optional=true data-validation-allowing="range[0;99],float" value="<?=$system_settings['pricing_percentage']?>"><span after>%</span></div>
	</td>
</tr>
</table>
</div>

</div><div class=submit_container><input type=button class=submit value="<?=readLanguage(records,update)?>"></div>
</form>

<? include "_footer.php"; ?>