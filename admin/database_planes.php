<? include "system/_handler.php";

$mysqltable = "system_database_planes";
$base_name = basename($_SERVER["SCRIPT_FILENAME"], ".php");
checkPermissions($base_name);

$result = mysqlQuery("SELECT * FROM $mysqltable");
while ($entry = mysqlFetch($result)){
	mysqlQuery("UPDATE $mysqltable SET ar_slug='" . createCanonical($entry["ar_name"]) . "', en_slug='" . createCanonical($entry["en_name"]) . "' WHERE id=" . $entry["id"]);
}


//==== DELETE Record ====
if ($delete){
	mysqlQuery("DELETE FROM $mysqltable WHERE id=$delete");
	if (mysqlAffectedRows()){ $success = readLanguage(records,deleted); } else { $error = readLanguage(records,unavailable); }

//==== ADD Record ====
} else if ($post["token"] && !$edit){
	$post["iata"] = strtoupper($post["iata"]);
	if (mysqlNum(mysqlQuery("SELECT id FROM $mysqltable WHERE iata='" . $post["iata"] . "'"))){
		$error = "بيانات السجل التي ادخلتها مسجلة من قبل";
	} else {
		$query = "INSERT INTO $mysqltable (
			iata,
			ar_name,
			en_name,
			ar_slug,
			en_slug,
			slugs,
			publish,
			cover_image,
			header_image,
			ar_content,
			en_content,
			popularity,
			priority
		) VALUES (
			'" . $post["iata"] . "',
			'" . $post["ar_name"] . "',
			'" . $post["en_name"] . "',
			'" . createCanonical($post["ar_name"]) . "',
			'" . createCanonical($post["en_name"]) . "',
			'" . createSlugs($post) . "',
			'" . $post["publish"] . "',
			'" . imgUpload($_FILES["cover_image"], "../uploads/database/", null, "plane_cover_") . "',
			'" . imgUpload($_FILES["header_image"], "../uploads/database/", null, "plane_header_") . "',
			'" . $post["ar_content"] . "',
			'" . $post["en_content"] . "',	
			'" . $post["popularity"] . "',
			'" . newRecordID($mysqltable) . "'
		)";
		mysqlQuery($query);
		$success = readLanguage(records,added);
	}

//==== EDIT Record ====
} else if ($post["token"] && $edit){
	$post["iata"] = strtoupper($post["iata"]);
	$record_data = getID($edit, $mysqltable);
	if (mysqlNum(mysqlQuery("SELECT id FROM $mysqltable WHERE iata='" . $post["iata"] . "' AND id!=$edit"))){
		$error = "بيانات السجل التي ادخلتها مسجلة من قبل";
	} else {
		$query = "UPDATE $mysqltable SET
			iata='" . $post["iata"] . "',
			ar_name='" . $post["ar_name"] . "',
			en_name='" . $post["en_name"] . "',
			ar_slug='" . createCanonical($post["ar_name"]) . "',
			en_slug='" . createCanonical($post["en_name"]) . "',
			slugs='" . createSlugs($post) . "',
			publish='" . $post["publish"] . "',
			cover_image='" . imgUpload($_FILES["cover_image"], "../uploads/database/", $record_data["cover_image"], "plane_cover_") . "',
			header_image='" . imgUpload($_FILES["header_image"], "../uploads/database/", $record_data["header_image"], "plane_header_") . "',
			ar_content='" . $post["ar_content"] . "',
			en_content='" . $post["en_content"] . "',
			popularity='" . $post["popularity"] . "'
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
	<td class=title>كود IATA: <i class=requ></i></td>
	<td colspan=3><input type=text name=iata value="<?=$entry["iata"]?>" data-validation=required></td>
</tr>
<tr>
	<td class=title>الإسم بالعربية: <i class=requ></i></td>
	<td><input type=text name=ar_name value="<?=$entry["ar_name"]?>" data-validation=required></td>
	<td class=title>الإسم بالإنجليزية: <i class=requ></i></td>
	<td><input type=text name=en_name value="<?=$entry["en_name"]?>" data-validation=required></td>
</tr>
<tr>
	<td class=title>درجة الترويج:</td>
	<td colspan=3><input type=number name=popularity value="<?=($entry["popularity"] ? $entry["popularity"] : "")?>"></td>
</tr>
</table>

<!-- Publish -->
<div class="subtitle margin-top">إعدادات النشر</div>
<div class=data_table_container><table class=data_table>
<tr>
	<td class=title>نشر علي الموقع:</td>
	<td colspan=3>
		<div class=switch><label>لا<input type=checkbox name=publish id=publish onchange="toggleVisibility(this)" value=1 <?=($entry["publish"] ? "checked" : "")?>><span class=lever></span>نعم</label></div>
		<script>
		$(document).ready(function(){
			toggleVisibility($("#publish")[0])
		});
		</script>
	</td>
</tr>
<tr visibility-control=publish visibility-value=1>
	<td class=title><?=readLanguage(inputs,header_image)?>:</td>
	<td>
		<table class=attachment><tr>
		<td>
			<input type=file name=header_image id=header_image accept="image/*" data-validation=mime data-validation-allowing="image/bmp,image/jpeg,image/png,image/gif">
			<div class=input_description><?=readLanguage(inputs,instructions_design)?></div>
		</td>
		<td width=150>
			<? $path = ($entry["header_image"] ? "../uploads/database/" . $entry["header_image"] : "images/placeholder.png") ?>
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
			<? $path = ($entry["cover_image"] ? "../uploads/database/" . $entry["cover_image"] : "images/placeholder.png") ?>
			<a data-fancybox=images href="<?=$path?>"><img class=sample_img image-placeholder=cover_image src="<?=$path?>"></a>
		</td>
		</tr></table>
		<!-- Used Only For Upload -->
		<script>$(document).ready(function(){ bindImage("cover_image") })</script>
	</td>
</tr>
<tr visibility-control=publish visibility-value=1>
	<td class=title><?=readLanguage(inputs,content)?> بالعربية:</td>
	<td colspan=3><textarea class=contentEditor style="height:400px" name=ar_content id=ar_content><?=$entry["ar_content"]?></textarea></td>
</tr>
<tr visibility-control=publish visibility-value=1>
	<td class=title><?=readLanguage(inputs,content)?> بالإنجليزية:</td>
	<td colspan=3><textarea class=contentEditor style="height:400px" name=en_content id=en_content><?=$entry["en_content"]?></textarea></td>
</tr>
</table></div>

<div class=submit_container><input type=button class=submit value="<?=$button?>"></div>
</form>

<div class=crud_separator></div>
<?
$crud_data["order_field"] = "priority";
$crud_data["order_by"] = "popularity DESC, priority DESC";
$crud_data["delete_record_message"] = "ar_name";
$crud_data["buttons"] = array(true,true,false,true,true); //Add - Search - View - Edit - Delete
$crud_data["columns"] = array(
	array("iata","IATA","100px","center",null,false,true),
	array("ar_name","الإسم بالعربية","250px","center",null,false,true),
	array("en_name","الإسم بالإنجليزية","250px","center",null,false,true),
	array("publish","نشر","100px","center","hasVal('%s','لا','نعم')",true,false),
);
require_once("crud/crud.php");
?>

<? include "_footer.php"; ?>