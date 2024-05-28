<? include "system/_handler.php";

$multiple_languages = false;
$mysqltable = "users_database";
$base_name = basename($_SERVER["SCRIPT_FILENAME"], ".php");
checkPermissions($base_name);

//==== ADD Record ====
if ($post["token"] && !$edit){
	//Input handling
	$new_record_id = newRecordID($mysqltable);
	$mobile_phone_code = getData("system_database_countries", "code", $post["country"], "phone_code");
	$mobile_prefix = "+" . $mobile_phone_code;
	$mobile = $mobile_prefix . cltrim($post["mobile"], "0");
	$mobile_conventional = "0" . cltrim($post["mobile"], "0");
	$email = strtolower($post["email"]);
	$hash = md5(uniqid($new_record_id,true));
	
	//Server Side Validation
	$errors = array();
	if (mysqlNum(mysqlQuery("SELECT id FROM $mysqltable WHERE email='$email'"))){
		array_push($errors, readLanguage(users,registered_email));
	}
	if (mysqlNum(mysqlQuery("SELECT * FROM $mysqltable WHERE mobile='$mobile' OR mobile_conventional='$mobile_conventional'"))){
		array_push($errors, readLanguage(users,registered_mobile));
	}
	
	if (!$errors){
		$user_id = generateUserID($mysqltable);
		
		//Set payment currency
		$user_currencyCode = getData("system_database_countries", "code", $post["user_country"], "currency_code");
		$user_paymentCurrency = getData("system_payment_currencies", "code", $user_currencyCode);
		if (!$user_paymentCurrency){
			$user_paymentCurrency = getData("system_payment_currencies", "code", "USD");	
		}

		$query = "INSERT INTO $mysqltable (
			user_id,
			user_country,
			user_currency,
			name,
			email,
			country,
			mobile_prefix,
			mobile,
			mobile_conventional,
			image,
			password,
			attachments,
			banned,
			hash,
			date
		) VALUES (
			'" . $user_id . "',
			'" . $post["user_country"] . "',
			'" . $user_paymentCurrency["code"] . "',
			'" . $post["name"] . "',
			'" . $email . "',
			'" . $post["country"] . "',
			'" . $mobile_prefix . "',
			'" . $mobile . "',
			'" . $mobile_conventional . "',
			'" . imgUploadBase64($_POST["image_base64"], "../uploads/users/", null, $user_id . "_") . "',
			'" . password_hash($post["password"], PASSWORD_DEFAULT) . "',
			'" . $post["attachments"] . "',
			'" . $post["banned"] . "',
			'" . $hash . "',
			'" . time() . "'
		)";
		mysqlQuery($query);
		$success = readLanguage(records,added);
	} else {
		$error = "<ul><li>" . implode("</li><li>", $errors) . "</li></ul>";
	}
	
//==== EDIT Record ====
} else if ($post["token"] && $edit){
	//Input handling
	$mobile_phone_code = getData("system_database_countries", "code", $post["country"], "phone_code");
	$mobile_prefix = "+" . $mobile_phone_code;
	$mobile = $mobile_prefix . cltrim($post["mobile"], "0");
	$mobile_conventional = "0" . cltrim($post["mobile"], "0");
	$email = strtolower($post["email"]);
	
	//Server Side Validation
	$errors = array();
	if (mysqlNum(mysqlQuery("SELECT id FROM $mysqltable WHERE email!='' AND email='$email' AND id!=$edit"))){
		array_push($errors, readLanguage(users,registered_email));
	}
	if (mysqlNum(mysqlQuery("SELECT * FROM $mysqltable WHERE (mobile='$mobile' OR mobile_conventional='$mobile_conventional') AND id!=$edit"))){
		array_push($errors, readLanguage(users,registered_mobile));
	}

	if (!$errors){
		$record_data = getID($edit,$mysqltable);
		
		//Set payment currency
		$user_currencyCode = getData("system_database_countries", "code", $post["user_country"], "currency_code");
		$user_paymentCurrency = getData("system_payment_currencies", "code", $user_currencyCode);
		if (!$user_paymentCurrency){
			$user_paymentCurrency = getData("system_payment_currencies", "code", "USD");	
		}
		
		$query = "UPDATE $mysqltable SET
			user_country='" . $post["user_country"] . "',
			user_currency='" . $user_paymentCurrency["code"] . "',
			name='" . $post["name"] . "',
			email='" . $email . "',
			country='" . $post["country"] . "',
			mobile_prefix='" . $mobile_prefix . "',
			mobile='" . $mobile . "',
			mobile_conventional='" . $mobile_conventional . "',
			image='" . imgUploadBase64($_POST["image_base64"], "../uploads/users/", $record_data["image"], $record_data["user_id"] . "_") . "',
			password='" . ($post["password"] ? password_hash($post["password"], PASSWORD_DEFAULT) : $record_data["password"]) . "',
			attachments='" . $post["attachments"] . "',
			banned='" . $post["banned"] . "'
		WHERE id=$edit";
		mysqlQuery($query);
		$success = readLanguage(records,updated);
	} else {
		$error = "<ul><li>" . implode("</li><li>", $errors) . "</li></ul>";
	}
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

include "_header.php"; ?>

<script src="../plugins/croppie.min.js?v=<?=$system_settings["system_version"]?>"></script>
<link href="../plugins/croppie.min.css?v=<?=$system_settings["system_version"]?>" rel="stylesheet">

<div class=title><?=getPageTitle($base_name)?></div>
<?=$message?>

<form method=post enctype="multipart/form-data" action="<?=$action?>">
<input type=hidden name=token value="<?=$token?>">

<table class=data_table>
<tr>
	<td class=title><?=readLanguage(users,name)?>: <i class=requ></i></td>
	<td><input type=text name=name value="<?=$entry["name"]?>" autocomplete=new-password data-validation=required></td>
	<td class=title>دولة الحساب: <i class=requ></i></td>
	<td>
		<? $country_fixed = ($edit ? mysqlNum(mysqlQuery("SELECT id FROM flights_reqeusts WHERE user_id=$edit")) + mysqlNum(mysqlQuery("SELECT id FROM flights_reservations WHERE user_id=$edit")) : false); ?>
		<select name=user_country id=user_country <?=($country_fixed ? "dummy class=clear-padding" : "")?>>
		<? $country_result = mysqlQuery("SELECT code, phone_code, en_name, ar_name FROM system_database_countries ORDER BY code ASC");
		while ($country_entry = mysqlFetch($country_result)){
			print "<option value='" . $country_entry["code"] . "' data-phone-code='+" . $country_entry["phone_code"] . "' data-name='" . $country_entry[$panel_language . "_name"] . "'>" . $country_entry[$panel_language . "_name"] . "</option>";
		} ?>
		</select>
		<script>
		//Set default value
		$(document).ready(function(){
			setSelectValue("#user_country", "<?=($entry["user_country"] ? $entry["user_country"] : "eg")?>");
			$("#user_country").trigger("change");
		});
		
		//Initialize Select2
		<? if (!$country_fixed){ ?>
		$("#user_country").select2({
			dropdownAutoWidth: true,
			templateResult: function(state){
				var element = $(state.element);
				return $("<div class='d-flex align-items-center'><img src='../images/countries/" + $(element).val() + ".gif'>&nbsp;" + $(element).attr("data-name") + "</div>");
			},
			templateSelection: function(state){
				var element = $(state.element);
				return $("<div class='d-flex align-items-center'><img src='../images/countries/" + $(element).val() + ".gif'>&nbsp;" + $(element).attr("data-name") + "</div>");
			}
		});
		<? } ?>
		</script>
	</td>
</tr>
<tr>
	<td class=title><?=readLanguage(users,email)?>: <i class=requ></i></td>
	<td><input type=email name=email value="<?=$entry["email"]?>" autocomplete=new-password data-validation=required></td>
	<td class=title><?=readLanguage(users,mobile)?>: <i class=requ></i></td>
	<td>
		<div class="flex-center input force-ltr" data-icon="&#xf3cd;">
			<select name=country id=country>
			<? $country_result = mysqlQuery("SELECT code, phone_code, ar_name FROM system_database_countries ORDER BY phone_code ASC");
			while ($country_entry = mysqlFetch($country_result)){
				print "<option value='" . $country_entry["code"] . "' data-name='" . $country_entry["ar_name"] . "' data-phone-code='+" . $country_entry["phone_code"] . "'>+" . $country_entry["phone_code"] . " " . $country_entry["ar_name"] . "</option>";
			} ?>
			</select>
			&nbsp;&nbsp;<input type=number name=mobile value="<?=str_replace($entry["mobile_prefix"], null, $entry["mobile"])?>" maxlength=11 data-validation=validateMobile>
		</div>
		<script>
			//Set Default Value
			setSelectValue("#country", "<?=($entry["country"] ? $entry["country"] : "eg")?>");
			
			//Initialize Select2
			$("#country").select2({
				dir: "ltr",
				width: "25%",
				dropdownAutoWidth: true,
				templateResult: function(state){
					var element = $(state.element);
					return $("<div class='d-flex align-items-center'><img src='../images/countries/" + $(element).val() + ".gif'>&nbsp;&nbsp;<span><b>(" + $(element).data("phone-code") + ")</b>&nbsp;&nbsp;" + $(element).attr("data-name") + "</span></div>");
				},
				templateSelection: function(state){
					var element = $(state.element);
					return $("<div class='d-flex align-items-center'><img src='../images/countries/" + $(element).val() + ".gif'>" + "&nbsp;<span>" + $(element).data("phone-code") + "</span></div>");
				}
			});
			
			//Validate Editor
			$.formUtils.addValidator({
				name: "validateMobile",
				validatorFunction: function(value, $el, config, language, $form){
					var valid_mobile =  false;
					switch ($("#country").val()){
						case "eg":
							valid_mobile = (value.match(/^((010|011|012|015)[0-9]{8})|((10|11|12|15)[0-9]{8})$/g)==value);
						break;
						
						case "sa":
							valid_mobile = (value.match(/^((05)[0-9]{8})|((5)[0-9]{8})$/g)==value);
						break;
						
						default:
							valid_mobile = true;
					}
					return (value ? true : false) && valid_mobile;
				},
				errorMessage: "برجاء إدخال رقم الجوال بشكل صحيح"
			});
		</script>
	</td>
</tr>
<tr>
	<td class=title><?=readLanguage(users,password)?>: <? if (!$edit){ print "<i class=requ></i>"; } ?></td>
	<td colspan=3>
		<input type=password name=password autocomplete=new-password data-validation-optional=<?=($edit ? "true" : "false")?> data-validation=custom data-validation-regexp="^(.{8,})$" data-validation-error-msg="<?=readLanguage(pages,user_password_requirements)?>">
		<div class=input_description><?=($edit ? readLanguage(users,password_empty) : readLanguage(pages,user_password_requirements))?></div>
	</td>
</tr>
<tr>
	<td class=title><?=readLanguage(users,status_banned)?>:</td>
	<td colspan=3><div class=switch><label><?=readLanguage(plugins,message_no)?><input type=checkbox name=banned value=1 <?=($entry["banned"] ? "checked" : "")?>><span class=lever></span><?=readLanguage(plugins,message_yes)?></label></div></td>
</tr>
<tr>
	<td class=title><?=readLanguage(inputs,image)?>:</td>
	<td colspan=3>
		<table class=attachment><tr>
		<td>
			<input type=file name=image id=image accept="image/*">
		</td>
		<td width=100>
			<? $path = ($entry["image"] ? "../uploads/users/" . $entry["image"] : "images/user.png") ?>
			<a data-fancybox=images href="<?=$path?>"><img class=sample_img image-placeholder=image src="<?=$path?>"></a>
		</td>
		</tr></table>
		<!-- Used Only For Croppie -->
		<input type=hidden name=image_base64>
		<script>$(document).ready(function(){ bindCroppie("image") })</script>
	</td>
</tr>
<tr>
	<td class=title><?=readLanguage(inputs,attachments)?>:</td>
	<td data-token="<?=$token?>" data-attachments=attachments data-upload-path="../uploads/users/" colspan=3>
		<div class=attachment-button>
			<input type=hidden name=attachments value="<?=$entry["attachments"]?>">
			<label class="btn btn-primary btn-lrg btn-upload"><?=readLanguage(inputs,attachments_insert)?><input type=file id=attachments accept="pdf/*" multiple></label>
			<div><i class="fas fa-spinner fa-spin"></i><?=readLanguage(inputs,uploading)?></div>
		</div>
		<ul sortable class=attachments-list></ul><div style="clear:both"></div>
		<? if ($entry["attachments"]){ ?>
		<script>
		var jsonArray = <?=$entry["attachments"]?>;
		jsonArray.forEach(function(entry){ attachmentsLoadFile(entry,"attachments"); });	
		</script>
		<? } ?>
	</td>
</tr>
</table>

<div class=submit_container><input type=button class=submit value="<?=$button?>"></div>
</form>

<div class=crud_separator></div>
<?
$crud_data["buttons"] = array(true,true,false,true,false); //Add - Search - View - Edit - Delete
$crud_data["columns"] = array( //Filter - Search - Copy Enabled
	array("id",readLanguage(users,serial),"80px","center",null,false,true),
	array("user_id",readLanguage(users,user_id),"120px","center",null,false,true),
	array("id",readLanguage(users,profile),"120px","center","viewButton('_view_user.php?id=%s','" . readLanguage(operations,view) . "','btn-primary','fas fa-user')",false,false),
	array("name",readLanguage(users,name),"300px","center","",false,true),
	array("email",readLanguage(users,email),"300px","center",null,false,true,true),
	array("mobile",readLanguage(users,mobile),"150px","force-ltr",null,false,true,true),
	array("user_country","دولة الحساب","200px","center","'<img src=\"../images/countries/%s.gif\">&nbsp;' . getData('system_database_countries','code','%s','" . $panel_language . "_name')",true,false),
	array("user_currency","عملة الحساب","200px","center",null,true,false),
	array("banned",readLanguage(users,status_banned),"100px","center","hasVal(%s,'" . readLanguage(plugins,message_no) . "','" . readLanguage(plugins,message_yes) . "')",true,false),
	array("date",readLanguage(users,registration_date),"250px","center","dateLanguage('l, d M Y h:i A',%s)",false,false),
);
require_once("crud/crud.php");
?>

<? include "_footer.php"; ?>