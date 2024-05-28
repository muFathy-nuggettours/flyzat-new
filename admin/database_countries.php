<? include "system/_handler.php";

$mysqltable = "system_database_countries";
$base_name = basename($_SERVER["SCRIPT_FILENAME"], ".php");
checkPermissions($base_name);

//==== EDIT Record ====
if ($post["token"] && $edit){
	$record_data = getID($edit, $mysqltable);
	$query = "UPDATE $mysqltable SET
		ar_name='" . $post["ar_name"] . "',
		en_name='" . $post["en_name"] . "',
		ar_slug='" . createCanonical($post["ar_name"]) . "',
		en_slug='" . createCanonical($post["en_name"]) . "',
		phone_code='" . $post["phone_code"] . "',
		continent='" . $post["continent"] . "',
		currency_symbol='" . $post["currency_symbol"] . "',
		currency_code='" . strtoupper($post["currency_code"]) . "',
		ar_currency_name='" . $post["ar_currency_name"] . "',
		en_currency_name='" . $post["en_currency_name"] . "',
		language='" . $post["language"] . "',
		slugs='" . createSlugs($post) . "',
		alias='" . $post["alias"] . "',
		keywords='" . createKeywords($post) . "',
		publish='" . $post["publish"] . "',
		cover_image='" . imgUpload($_FILES["cover_image"], "../uploads/database/", $record_data["cover_image"], "country_cover_") . "',
		header_image='" . imgUpload($_FILES["header_image"], "../uploads/database/", $record_data["header_image"], "country_header_") . "',
		ar_content='" . $post["ar_content"] . "',
		en_content='" . $post["en_content"] . "'
	WHERE id=$edit";
	mysqlQuery($query);
	$success = readLanguage(records,updated);
}

//Read and Set Operation [Custom]
if ($edit){
	$entry = getID($edit,$mysqltable);
	if (!$entry){ $error = readLanguage(records,unavailable); $edit = null; }
}
if ($edit){
	$button = readLanguage(records,update);
	$action = "$base_name.php" . rebuildQueryParameters(array("delete","token"));
}
if ($success){ $message = "<div class='alert alert-success'>" . $success . "</div>"; }
if ($error){ $message = "<div class='alert alert-danger'>" . $error . "</div>"; }

include "_header.php"; ?>

<div class=title><?=getPageTitle($base_name)?></div>
<?=$message?>

<? if ($edit){ ?>
<form method=post enctype="multipart/form-data" action="<?=$action?>">
<input type=hidden name=token value="<?=$token?>">

<table class=data_table>
<tr>
	<td class=title>الكود: <i class=requ></i></td>
	<td><input type=text name=code value="<?=$entry["code"]?>" dummy></td>
	<td class=title>القارة: <i class=requ></i></td>
	<td>
		<select name=continent id=continent>
		<? $options_result = mysqlQuery("SELECT continent FROM $mysqltable GROUP BY continent ORDER BY continent ASC");
		while ($options_entry = mysqlFetch($options_result)){
			print "<option value='" . $options_entry["continent"] . "'>" . $options_entry["continent"] . "</option>";
		} ?>
		</select>
		<script>setSelectValue("#continent", "<?=$entry["continent"]?>")</script>
	</td>
</tr>
<tr>
	<td class=title>الإسم بالعربية: <i class=requ></i></td>
	<td><input type=text name=ar_name value="<?=$entry["ar_name"]?>" data-validation=required></td>
	<td class=title>الإسم بالإنجليزية: <i class=requ></i></td>
	<td><input type=text name=en_name value="<?=$entry["en_name"]?>" data-validation=required></td>
</tr>
<tr>
	<td class=title>كود الهاتف: <i class=requ></i></td>
	<td><input type=number name=phone_code value="<?=$entry["phone_code"]?>" data-validation=required></td>
	<td class=title>كود العملة: <i class=requ></i></td>
	<td><input type=text name=currency_code value="<?=$entry["currency_code"]?>" data-validation=required></td>
</tr>
<tr>
	<td class=title>إسم العملة بالعربية: <i class=requ></i></td>
	<td><input type=text name=ar_currency_name value="<?=$entry["ar_currency_name"]?>" data-validation=required></td>
	<td class=title>إسم العملة بالإنجليزية: <i class=requ></i></td>
	<td><input type=text name=en_currency_name value="<?=$entry["en_currency_name"]?>" data-validation=required></td>
</tr>
<tr>
	<td class=title>رمز العملة: <i class=requ></i></td>
	<td><input type=text name=currency_symbol value="<?=$entry["currency_symbol"]?>" data-validation=required></td>
	<td class=title>اللغة: <i class=requ></i></td>
	<td>
		<select name=language id=language>
		<? $options_result = mysqlQuery("SELECT language FROM $mysqltable GROUP BY language ORDER BY language ASC");
		while ($options_entry = mysqlFetch($options_result)){
			print "<option value='" . $options_entry["language"] . "'>" . $options_entry["language"] . "</option>";
		} ?>
		</select>
		<script>setSelectValue("#language", "<?=$entry["language"]?>")</script>
	</td>
</tr>
<tr>
	<td class=title>مسميات إضافية:</td>
	<td colspan=3><input type=text name=alias value="<?=$entry["alias"]?>"></td>
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
<? } ?>

<?
$crud_data["order_field"] = "priority";
$crud_data["order_by"] = "priority DESC";
$crud_data["buttons"] = array(false,true,false,true,false); //Add - Search - View - Edit - Delete
$crud_data["columns"] = array(
	array("code","العلم","50px","center","'<img src=\"../images/countries/%s.gif\">'",false,false),
	array("code","الكود","100px","center",null,false,true),
	array("ar_name","الإسم بالعربية","250px","center",null,false,true),
	array("en_name","الإسم بالإنجليزية","250px","center",null,false,true),
	array("alias","مسميات إضافية","250px","center",null,false,true),
	array("phone_code","كود الهاتف","100px","center",null,false,true),
	array("continent","القارة","100px","center",null,false,true),
	array("currency_code","كود العملة","100px","center",null,false,true),
	array("currency_symbol","رمز العملة","100px","center",null,false,true),
	array("ar_currency_name","إسم العملة بالعربية","200px","center",null,false,true),
	array("en_currency_name","إسم العملة بالإنجليزية","200px","center",null,false,true),
	array("language","اللغة","100px","center",null,false,true),
	array("publish","نشر","100px","center","hasVal('%s','لا','نعم')",true,false),
);
require_once("crud/crud.php");
?>

<? include "_footer.php"; ?>