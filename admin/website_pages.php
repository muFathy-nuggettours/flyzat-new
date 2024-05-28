<? include "system/_handler.php";

$mysqltable = $suffix . "website_pages";
$base_name = basename($_SERVER["SCRIPT_FILENAME"], ".php");
checkPermissions($base_name);

//==== EDIT Record ====
if ($post["token"] && $edit){
	$record_data = getID($edit,$mysqltable);
	$query = "UPDATE $mysqltable SET
		title='" . ($post["title"] ? $post["title"] : $record_data["title"]) . "',
		description='" . $post["description"] . "',
		cover_image='" . imgUpload($_FILES["cover_image"], "../uploads/pages/", $record_data["cover_image"], $record_data["page"] . "_cover_") . "',
		header_image='" . imgUpload($_FILES["header_image"], "../uploads/pages/", $record_data["header_image"], $record_data["page"] . "_header_") . "',
		page_header='" . $post["page_header"] . "',
		page_footer='" . $post["page_footer"] . "',
		page_layout='" . $post["page_layout"] . "',
		hidden='" . $post["hidden"] . "'
	WHERE id=$edit";
	mysqlQuery($query);
	$success = readLanguage(records,updated);
}

//Read and Set Operation [Edit only]
if ($edit){
	$entry = getID($edit,$mysqltable);
	if (!$entry){ $error = readLanguage(records,unavailable); $edit = null; }
}
if ($edit){
	$button = readLanguage(records,update);
	$action = "$base_name.php" . rebuildQueryParameters(array("delete","token"));
}
if ($success){ $message = "<div class='alert alert-success'>$success</div>"; }
if ($error){ $message = "<div class='alert alert-danger'>$error</div>"; }

include "_header.php"; ?>

<div class=title><?=getPageTitle($base_name)?></div>
<?=$message?>

<? if ($edit){ ?>
<form method=post enctype="multipart/form-data" action="<?=$action?>">
<input type=hidden name=token value="<?=$token?>">
<input type=hidden name=page_layout>

<div class="alert alert-title"><?=$entry["placeholder"]?></div>

<? $exclude_pages = ["index", "mobile"];
if (!in_array($entry["page"], $exclude_pages)){ ?>
<!-- Page data -->
<table class=data_table>
<tr>
	<td class=title><?=readLanguage(inputs,title)?>: <i class=requ></i></td>
	<td colspan=3><input type=text name=title value="<?=$entry["title"]?>" data-validation=required></td>
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
			<? $path = ($entry["header_image"] ? "../uploads/pages/" . $entry["header_image"] : "images/placeholder.png") ?>
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
			<? $path = ($entry["cover_image"] ? "../uploads/pages/" . $entry["cover_image"] : "images/placeholder.png") ?>
			<a data-fancybox=images href="<?=$path?>"><img class=sample_img image-placeholder=cover_image src="<?=$path?>"></a>
		</td>
		</tr></table>
		<!-- Used Only For Upload -->
		<script>$(document).ready(function(){ bindImage("cover_image") })</script>
	</td>
</tr>
<tr>
	<td class=title><?=readLanguage(inputs,hidden)?>:</td>
	<td colspan=3><div class=switch><label><?=$data_no_yes[0]?><input type=checkbox name=hidden value=1 <?=($entry["hidden"] ? "checked" : "")?>><span class=lever></span><?=$data_no_yes[1]?></label></div></td>
</tr>
</table>

<!-- Page Content Layout -->
<div class=subtitle><?=readLanguage(builder,layout)?></div>
<div class=data_table_container><table class=data_table>
<tr>
	<td class=title><?=readLanguage(builder,layout_banner)?>:</td>
	<td>
		<select name=page_header id=page_header>
			<option value=""><?=readLanguage(builder,basic)?></option>
			<option value="none"><?=readLanguage(builder,none)?></option>
		</select>
		<? if ($edit){ ?><script>setSelectValue("#page_header", "<?=$entry["page_header"]?>")</script><? } ?>
	</td>
	<td class=title><?=readLanguage(builder,layout_footer)?>:</td>
	<td>
		<select name=page_footer id=page_footer>
			<option value=""><?=readLanguage(builder,basic)?></option>
			<option value="none"><?=readLanguage(builder,none)?></option>
		</select>
		<? if ($edit){ ?><script>setSelectValue("#page_footer", "<?=$entry["page_footer"]?>")</script><? } ?>
	</td>
</tr>
</table></div>
<? } ?>

<!-- Page Sections -->
<div class=subtitle><?=readLanguage(builder,layout_modules)?></div>
<? $modules_input = "[name=page_layout]";
$modules_entry = $entry["page_layout"];
$modules_content = ($edit!=1 ? true : false);
$modules_type = 1;
include "includes/_select_modules.php"; ?>

<div class=submit_container><input type=button class=submit value="<?=readLanguage(records,update)?>"></div>
</form>

<div class=crud_separator></div>
<? } ?>

<?
$crud_data["order_by"] = "id ASC";
$crud_data["buttons"] = array(false,true,false,true,false); //Add - Search - View - Edit - Delete
$crud_data["columns"] = array(
	array("placeholder",readLanguage(builder,placeholder),"50%","center",null,false,true),
	array("title",readLanguage(inputs,title),"50%","center",null,false,true),
	array("hidden",readLanguage(inputs,hidden),"100px","center","getVariable('data_no_yes')[%s]",true,false),
);
require_once("crud/crud.php");
?>

<? include "_footer.php"; ?>