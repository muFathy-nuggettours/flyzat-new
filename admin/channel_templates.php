<? include "system/_handler.php";

$multiple_languages = false;
$mysqltable = "channel_templates";
$base_name = basename($_SERVER["SCRIPT_FILENAME"], ".php");
checkPermissions($base_name);

//==== EDIT Record ====
if ($post["token"] && $edit){
	$record_data = getID($edit,$mysqltable);
	$query = "UPDATE $mysqltable SET
		email='" . $post["email"] . "',
		email_subject='" . ($post["email"] ? $post["email_subject"] : "") . "',
		email_message='" . ($post["email"] ?$post["email_message"] : "") . "',
		sms='" . $post["sms"] . "',
		sms_message='" . ($post["sms"] ? $post["sms_message"] : "") . "',
		push='" . $post["push"] . "',
		push_title='" . ($post["push"] ? $post["push_title"] : "") . "',
		push_message='" . ($post["push"] ? $post["push_message"] : "") . "'
	WHERE id=$edit";
	mysqlQuery($query);
	$success = readLanguage(records,updated);
}

//Read and Set Operation
if ($success){ $message = "<div class='alert alert-success'>$success</div>"; }

include "_header.php"; ?>

<div class=title><?=getPageTitle($base_name)?></div>
<?=$message?>

<? if ($edit){ ?>
<form method=post enctype="multipart/form-data" action="<?=$action?>">
<input type=hidden name=token value="<?=$token?>">

<? $entry = mysqlFetch(mysqlQuery("SELECT * FROM $mysqltable WHERE id=$edit")); ?>
<div class=subtitle><?=$entry["title"]?></div>

<? if ($entry["placeholders"]){ ?>
<div class="alert alert-warning"><?=nl2br($entry["placeholders"])?></div>
<? } ?>

<div class=data_table_container><table class=data_table>
<tr>
	<td class=title><?=readLanguage(channels,sms)?>:</td>
	<td><div class=switch><label><?=$data_disabled_enabled[0]?><input type=checkbox name=sms id=sms onchange="toggleVisibility(this)" value=1 <?=($entry["sms"] ? "checked" : "")?>><span class=lever></span><?=$data_disabled_enabled[1]?></label></div></td>
</tr>
<tr visibility-control=sms visibility-value=1 style="display:none">
	<td class=title><?=readLanguage(channels,message)?>: <i class=requ></i></td>
	<td>
		<input type=text name=sms_message value="<?=$entry["sms_message"]?>" data-validation=requiredVisible>
		<script>toggleVisibility($("#sms")[0])</script>
	</td>
</tr>
<tr>
	<td class=title><?=readLanguage(channels,email)?>:</td>
	<td><div class=switch><label><?=$data_disabled_enabled[0]?><input type=checkbox name=email id=email onchange="toggleVisibility(this)" value=1 <?=($entry["email"] ? "checked" : "")?>><span class=lever></span><?=$data_disabled_enabled[1]?></label></div></td>
</tr>
<tr visibility-control=email visibility-value=1 style="display:none">
	<td class=title><?=readLanguage(channels,subject)?>: <i class=requ></i></td>
	<td><input type=text name=email_subject value="<?=$entry["email_subject"]?>" data-validation=requiredVisible></td>
</tr>
<tr visibility-control=email visibility-value=1 style="display:none">
	<td class=title><?=readLanguage(channels,message)?>: <i class=requ></i></td>
	<td>
		<textarea class=mceEditorLimited name=email_message><?=$entry["email_message"]?></textarea>
		<script>toggleVisibility($("#email")[0])</script>
	</td>
</tr>
<tr>
	<td class=title><?=readLanguage(channels,push)?>:</td>
	<td><div class=switch><label><?=$data_disabled_enabled[0]?><input type=checkbox name=push id=push onchange="toggleVisibility(this)" value=1 <?=($entry["push"] ? "checked" : "")?>><span class=lever></span><?=$data_disabled_enabled[1]?></label></div></td>
</tr>
<tr visibility-control=push visibility-value=1 style="display:none">
	<td class=title><?=readLanguage(channels,subject)?>: <i class=requ></i></td>
	<td>
		<input type=text name=push_title value="<?=$entry["push_title"]?>" data-validation=requiredVisible>
	</td>
</tr>
<tr visibility-control=push visibility-value=1 style="display:none">
	<td class=title><?=readLanguage(channels,message)?>: <i class=requ></i></td>
	<td>
		<input type=text name=push_message value="<?=$entry["push_message"]?>" data-validation=requiredVisible>
		<script>toggleVisibility($("#push")[0])</script>
	</td>
</tr>
</table></div>

<div class=submit_container><input type=button class=submit value="<?=readLanguage(records,update)?>"></div>
</form>

<div class=crud_separator></div>
<? } ?>

<?
$crud_data["buttons"] = array(false,true,false,true,false); //Add - Search - View - Edit - Delete
$crud_data["columns"] = array(
	array("title",readLanguage(channels,target),"100%","center",null,false,true),
	array("sms",readLanguage(channels,sms),"120px","center","returnStatusLabel('data_disabled_enabled',%s)",true,false),
	array("email",readLanguage(channels,email),"120px","center","returnStatusLabel('data_disabled_enabled',%s)",true,false),
	array("push",readLanguage(channels,push),"120px","center","returnStatusLabel('data_disabled_enabled',%s)",true,false),
);
require_once("crud/crud.php");
?>

<? include "_footer.php"; ?>