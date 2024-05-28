<? include "system/_handler.php";

$mysqltable = $suffix . "website_destinations";
$base_name = basename($_SERVER["SCRIPT_FILENAME"], ".php");
checkPermissions($base_name);

//==== DELETE Record ====
if ($delete){
	mysqlQuery("DELETE FROM $mysqltable WHERE id=$delete");
	if (mysqlAffectedRows()){ $success = readLanguage(records,deleted); } else { $error = readLanguage(records,unavailable); }

//==== ADD Record ====
} else if ($post["token"] && !$edit){
	if (mysqlNum(mysqlQuery("SELECT * FROM $mysqltable WHERE canonical='" . $post["canonical"] . "'"))){
		$error = readLanguage(records,exists);
	} else if (!validateCanonical($post["canonical"])){
		$error = readLanguage(records,invalid_canonical);
	} else {
		$query = "INSERT INTO $mysqltable (
			airport,
			title,
			canonical,
			description,
			cover_image,
			header_image,
			content,
			gallery,
			priority
		) VALUES (
			'" . $post["airport"] . "',
			'" . $post["title"] . "',
			'" . $post["canonical"] . "',
			'" . $post["description"] . "',
			'" . imgUpload($_FILES["cover_image"], "../uploads/destinations/", null, "cover_") . "',
			'" . imgUpload($_FILES["header_image"], "../uploads/destinations/", null, "header_") . "',	
			'" . $post["content"] . "',
			'" . $post["gallery"] . "',
			'" . newRecordID($mysqltable) . "'
		)";
		mysqlQuery($query);
		$success = readLanguage(records,added);
	}

//==== EDIT Record ====	
} else if ($post["token"] && $edit){
	if (mysqlNum(mysqlQuery("SELECT * FROM $mysqltable WHERE canonical='" . $post["canonical"] . "' AND id!=$edit"))){
		$error = readLanguage(records,exists);
	} else if (!validateCanonical($post["canonical"])){
		$error = readLanguage(records,invalid_canonical);
	} else {
		$record_data = getID($edit,$mysqltable);
		$query = "UPDATE $mysqltable SET
			airport='" . $post["airport"] . "',
			title='" . $post["title"] . "',
			canonical='" . $post["canonical"] . "',
			description='" . $post["description"] . "',
			cover_image='" . imgUpload($_FILES["cover_image"], "../uploads/destinations/", $record_data["cover_image"], "cover_") . "',
			header_image='" . imgUpload($_FILES["header_image"], "../uploads/destinations/", $record_data["header_image"], "header_") . "',
			content='" . $post["content"] . "',
			gallery='" . $post["gallery"] . "'
		WHERE id=$edit";
		mysqlQuery($query);
		$success = readLanguage(records,updated);
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
if ($success){ $message = "<div class='alert alert-success'>" . $success . "</div>"; }
if ($error){ $message = "<div class='alert alert-danger'>" . $error . "</div>"; }

include "_header.php"; ?>

<div class=title><?=getPageTitle($base_name)?></div>
<?=$message?>

<form method=post enctype="multipart/form-data" action="<?=$action?>">
<input type=hidden name=token value="<?=$token?>">

<table class=data_table>
<tr>
	<td class=title><?=readLanguage(inputs,title)?>: <i class=requ></i></td>
	<td>
		<input type=text name=title onkeyup="createCanonical(this.value,'canonical')" value="<?=$entry["title"]?>" data-validation=required>
	</td>
	<td class=title><?=readLanguage(inputs,canonical)?>: <i class=requ></i></td>
	<td>
		<input type=text name=canonical id=canonical value="<?=$entry["canonical"]?>" placeholder="<?=readLanguage(inputs,canonical_placeholder)?>" data-validation=required>
	</td>
</tr>
<tr>
	<td class=title>المطار: <i class=requ></i></td>
	<td colspan=3>
		<? $input = "airport"; $value = $entry["airport"]; $conditions = null; $mandatory = true; $removable = true; ?>
		<script>
		function onSelectAirport_<?=$input?>(data){
			//On selecting profile function
		}
		function onUnselectAirport_<?=$input?>(){
			//On unselecting profile function
		}
		</script>
		<? include "includes/select_airport.php"; ?>
	</td>
</tr>
<tr>
	<td class=title><?=readLanguage(inputs,description)?>:</td>
	<td colspan=3><textarea name=description style="min-height:initial; height:55px"><?=$entry["description"]?></textarea></td>
</tr>
<tr>
	<td class=title><?=readLanguage(inputs,header_image)?>:</td>
	<td>
		<table class=attachment><tr>
		<td>
			<input type=file name=header_image id=header_image accept="image/*" data-validation=mime data-validation-allowing="image/bmp,image/jpeg,image/png,image/gif">
			<div class=input_description><?=readLanguage(inputs,instructions_design)?></div>
		</td>
		<td width=150>
			<? $path = ($entry["header_image"] ? "../uploads/destinations/" . $entry["header_image"] : "images/placeholder.png") ?>
			<a data-fancybox=images href="<?=$path?>"><img class=sample_img image-placeholder=header_image src="<?=$path?>"></a>
		</td>
		</tr></table>
		<!-- Used Only For Upload -->
		<script>$(document).ready(function(){ bindImage("header_image") })</script>
	</td>
	<td class=title><?=readLanguage(inputs,cover_image)?>:</td>
	<td>
		<table class=attachment><tr>
		<td>
			<input type=file name=cover_image id=cover_image accept="image/*" data-validation=mime data-validation-allowing="image/bmp,image/jpeg,image/png,image/gif">
			<div class=input_description><?=readLanguage(inputs,instructions_design)?></div>
		</td>
		<td width=150>
			<? $path = ($entry["cover_image"] ? "../uploads/destinations/" . $entry["cover_image"] : "images/placeholder.png") ?>
			<a data-fancybox=images href="<?=$path?>"><img class=sample_img image-placeholder=cover_image src="<?=$path?>"></a>
		</td>
		</tr></table>
		<!-- Used Only For Upload -->
		<script>$(document).ready(function(){ bindImage("cover_image") })</script>
	</td>
</tr>
<tr>
	<td class=title><?=readLanguage(inputs,content)?>:</td>
	<td colspan=3><textarea class=contentEditor style="height:400px" name=content id=content><?=$entry["content"]?></textarea></td>
</tr>
<tr>
	<td class=title><?=readLanguage(inputs,gallery)?>:</td>
	<td colspan=3 data-token="<?=$token?>" data-attachments=gallery data-upload-path="../uploads/destinations/">
		<div class=attachment-button>
			<input type=hidden name=gallery value="<?=$entry["gallery"]?>">
			<label class="btn btn-primary btn-lrg btn-upload"><?=readLanguage(inputs,gallery_insert)?><input type=file id=gallery accept="image/*" multiple></label>
			<div><i class="fas fa-spinner fa-spin"></i><?=readLanguage(inputs,uploading)?></div>
		</div>
		<ul sortable class=attachments-list></ul><div style="clear:both"></div>
		<? if ($entry["gallery"]){ ?>
		<script>
		var jsonArray = jQuery.parseJSON(JSON.stringify(<?=$entry["gallery"]?>));
		jsonArray.forEach(function(entry){ attachmentsLoadFile(entry,"gallery"); });	
		</script>
		<? } ?>
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
	array("title","العنوان","100%","center",null,false,true),
	array("airport","المطار","100px","center",null,true,true),
	array("canonical",readLanguage(inputs,url),"300px","center","pageURL('{$base_url}destinations/%s/')",false,true),
);
require_once("crud/crud.php");
?>

<? include "_footer.php"; ?>