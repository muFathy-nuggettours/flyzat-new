<? include "system/_handler.php";

$mysqltable = $suffix . "website_popup";
$base_name = basename($_SERVER["SCRIPT_FILENAME"], ".php");
checkPermissions($base_name);

//==== DELETE Record ====
if ($delete){
	mysqlQuery("DELETE FROM $mysqltable WHERE id=$delete");
	if (mysqlAffectedRows()){ $success = readLanguage(records,deleted); } else { $error = readLanguage(records,unavailable); }

//==== ADD Record ====
} else if ($post["token"] && !$edit){
	if ($post["status"]){
		mysqlQuery("UPDATE $mysqltable SET status=0");
	}
	$query = "INSERT INTO $mysqltable (
		title,
		content,
		appearance,
		padding,
		center,
		status,
		hash
	) VALUES (
		'" . $post["title"] . "',
		'" . $post["content"] . "',
		'" . $post["appearance"] . "',
		'" . $post["padding"] . "',
		'" . $post["center"] . "',
		'" . $post["status"] . "',
		'" . md5(rand(100,999) . newRecordID($mysqltable) . rand(100,999)) . "'
	)";
	mysqlQuery($query);
	$success = readLanguage(records,added);

//==== EDIT Record ====
} else if ($post["token"] && $edit){
	$record_data = getID($edit,$mysqltable);
	if ($post["status"]){
		mysqlQuery("UPDATE $mysqltable SET status=0");
	}
	$query = "UPDATE $mysqltable SET
		title='" . $post["title"] . "',
		content='" . $post["content"] . "',
		appearance='" . $post["appearance"] . "',
		padding='" . $post["padding"] . "',
		center='" . $post["center"] . "',
		status='" . $post["status"] . "'
	WHERE id=$edit";
	mysqlQuery($query);
	$success = readLanguage(records,updated);
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

<div class=title><?=getPageTitle($base_name)?></div>
<?=$message?>

<form method=post enctype="multipart/form-data" action="<?=$action?>">
<input type=hidden name=token value="<?=$token?>">

<table class=data_table>
<tr>
	<td class=title><?=readLanguage(inputs,title)?>: <i class=requ></i></td>
	<td colspan=3>
		<input type=text name=title value="<?=$entry["title"]?>" data-validation=required>
	</td>
</tr>
<tr>
	<td class=title><?=readLanguage(inputs,content)?>: <i class=requ></i></td>
	<td colspan=3>
		<textarea class=mceEditor name=content id=content data-validation=validateEditor><?=$entry["content"]?></textarea>
	</td>
</tr>
<tr>
	<td class=title><?=readLanguage(pages,popup_reappear)?>:</td>
	<td>
		<div class=input-addon><input type=number name=appearance value="<?=($edit ? $entry["appearance"] : "24")?>"><span after><?=readLanguage(pages,popup_hours)?></span></div>
	</td>
	<td class=title><?=readLanguage(pages,popup_border)?>:</td>
	<td>
		<div class=input-addon><input type=number name=padding value="<?=($edit ? $entry["padding"] : "10")?>"><span after><?=readLanguage(pages,popup_pixel)?></span></div>
	</td>
</tr>
<tr>
	<td class=title><?=readLanguage(pages,popup_center)?>:</td>
	<td>
		<div class=switch><label><?=$data_no_yes[0]?><input type=checkbox name=center value=1 <?=($entry["center"] ? "checked" : "")?>><span class=lever></span><?=$data_no_yes[1]?></label></div>
	</td>
	<td class=title><?=readLanguage(inputs,status)?>:</td>
	<td>
		<div class=switch><label><?=$data_disabled_enabled[0]?><input type=checkbox name=status value=1 <?=($entry["status"] ? "checked" : "")?>><span class=lever></span><?=$data_disabled_enabled[1]?></label></div>
	</td>
</tr>
</table>

<div class=submit_container><input type=button class=submit value="<?=$button?>"></div>
</form>

<div class=crud_separator></div>
<?
$crud_data["delete_record_message"] = "title";
$crud_data["buttons"] = array(true,true,false,true,true); //Add - Search - View - Edit - Delete
$crud_data["columns"] = array(
	array("title",readLanguage(inputs,title),"100%","center",null,false,true),
	array("status",readLanguage(inputs,status),"120px","center","returnStatusLabel('data_disabled_enabled',%s)",true,false),
);
require_once("crud/crud.php");
?>

<? include "_footer.php"; ?>