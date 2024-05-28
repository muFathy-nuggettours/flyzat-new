<? include "system/_handler.php";

$mysqltable = $suffix . "module_slider";
$base_name = basename($_SERVER["SCRIPT_FILENAME"], ".php");
checkPermissions($base_name);

//==== DELETE Record ====
if ($delete){
	mysqlQuery("DELETE FROM $mysqltable WHERE id=$delete");
	if (mysqlAffectedRows()){ $success = readLanguage(records,deleted); } else { $error = readLanguage(records,unavailable); }

//==== ADD Record ====
} else if ($post["token"] && !$edit){
	$query = "INSERT INTO $mysqltable (
		title,
		subtitle,
		image,
		priority
	) VALUES (
		'" . $post["title"] . "',
		'" . $post["subtitle"] . "',
		'" . imgUpload($_FILES[image], "../uploads/slider/") . "',
		'" . newRecordID($mysqltable) . "'
	)";
	mysqlQuery($query);
	$success = readLanguage(records,added);

//==== EDIT Record ====
} else if ($post["token"] && $edit){//EDIT
	$record_data = getID($edit,$mysqltable);
	$query = "UPDATE $mysqltable SET
		title='" . $post["title"] . "',
		subtitle='" . $post["subtitle"] . "',
		image='" . imgUpload($_FILES[image], "../uploads/slider/", $record_data["image"]) . "'
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
	$action = $base_name . ".php?edit=" . $edit;
} else {
	$button = readLanguage(records,add);
	$action = $base_name . ".php";
	if ($error){ foreach ($_POST as $key => $value){ $entry[$key] = $value; } }
}
if ($success){ $message = "<div class='alert alert-success'>" . $success . "</div>"; }
if ($error){ $message = "<div class='alert alert-danger'>" . $error . "</div>"; }

//Additional Requirements
if (!$edit){
	$required = "required";
}

include "_header.php"; ?>

<div class=title><?=getPageTitle($base_name)?></div>
<?=$message?>

<form method=post enctype="multipart/form-data" action="<?=$action?>">
<input type=hidden name=token value="<?=$token?>">

<table class=data_table>
<tr>
	<td class=title>عنوان رئيسي:</td>
	<td><input type=text name=title value="<?=$entry["title"]?>"></td>
</tr>
<tr>
	<td class=title>عنوان فرعي:</td>
	<td><input type=text name=subtitle value="<?=$entry["subtitle"]?>"></td>
</tr>
<tr>
	<td class=title>الصورة: <i class=requ></i></td>
	<td>
		<table class=attachment><tr>
		<td>
			<input type=file name=image id=image accept="image/*" data-mandatory="<?=$required?>">
		</td>
		<td width=150>
			<? $path = ($entry["image"] ? "../uploads/slider/" . $entry["image"] : "images/placeholder.png") ?>
			<a data-fancybox=images href="<?=$path?>"><img class=sample_img image-placeholder=image src="<?=$path?>"></a>
		</td>
		</tr></table>
		<!-- Used Only For Upload -->
		<script>$(document).ready(function(){ bindImage("image") })</script>
	</td>
</tr>
</table>

<div class=submit_container><input type=button class=submit value="<?=$button?>"></div>
</form>

<div class=crud_separator></div>
<?
$crud_data["order_field"] = "priority";
$crud_data["order_by"] = "priority DESC";
$crud_data["delete_record_message"] = "title";
$crud_data["buttons"] = array(true,true,false,true,true); //Add - Search - View - Edit - Delete
$crud_data["columns"] = array(
	array("title","عنوان رئيسي","50%","center",null,false,true),
	array("subtitle","عنوان فرعي","50%","center",null,false,true),
	array("image","الصورة","150px","center","imgThumb('../uploads/slider/%s','../uploads/slider/%s')",false,false)
);
require_once("crud/crud.php");
?>

<? include "_footer.php"; ?>