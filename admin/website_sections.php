<? include "system/_handler.php";

$mysqltable = $suffix . "website_sections";
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
		blocks_show='" . $post["blocks_show"] . "',
		blocks_template='" . ($post["blocks_show"] ? $post["blocks_template"] : "") . "',
		blocks_spacing='" . ($post["blocks_show"] ? $post["blocks_spacing"] : "") . "',
		blocks_per_page='" . ($post["blocks_show"] ? $post["blocks_per_page"] : "") . "',
		blocks_per_row='" . ($post["blocks_show"] ? $post["blocks_per_row"] : "") . "',
		page_content_module='" . $post["page_content_module"] . "',
		page_header='" . $post["page_header"] . "',
		page_footer='" . $post["page_footer"] . "',
		page_layout='" . $post["page_layout"] . "',
		child_content_module='" . $post["child_content_module"] . "',
		child_header='" . $post["child_header"] . "',
		child_footer='" . $post["child_footer"] . "',
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

<!-- Page data -->
<? if ($edit!=1){ ?>
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
<? } ?>

<!-- Page Layout Modules -->
<div class=subtitle><?=readLanguage(builder,layout_modules)?></div>
<? $modules_input = "[name=page_layout]";
$modules_entry = $entry["page_layout"];
$modules_content = ($edit!=1 ? true : false);
$modules_type = 1;
include "includes/_select_modules.php"; ?>

<? if ($edit!=1 && $entry["layout_updatable"]){ ?>
<!-- Page Layout -->
<div class=subtitle><?=readLanguage(builder,layout)?></div>
<table class="data_table margin-bottom">
<tr>
	<td class=title><?=readLanguage(builder,page_content_layout)?>:</td>
	<td colspan=3>
		<select name=page_content_module id=page_content_module>
			<?=populateData("SELECT * FROM " . $suffix . "website_modules_custom WHERE FIND_IN_SET(0,type)", "uniqid", "placeholder")?>
			<option value="none"><?=readLanguage(builder,none)?></option>
		</select>
		<? if ($edit){ ?><script>setSelectValue("#page_content_module", "<?=$entry["page_content_module"]?>")</script><? } ?>
		<script>$("#page_content_module").select2()</script>
	</td>
</tr>
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
</table>

<!-- Children Layout -->
<div class=subtitle><?=readLanguage(builder,page_child_content_layout)?></div>
<table class=data_table>
<tr>
	<td class=title><?=readLanguage(builder,page_content_layout)?>:</td>
	<td colspan=3>
		<select name=child_content_module id=child_content_module>
			<?=populateData("SELECT * FROM " . $suffix . "website_modules_custom WHERE FIND_IN_SET(0,type)", "uniqid", "placeholder")?>
			<option value="none"><?=readLanguage(builder,none)?></option>
		</select>
		<? if ($edit){ ?><script>setSelectValue("#child_content_module", "<?=$entry["child_content_module"]?>")</script><? } ?>
		<script>$("#child_content_module").select2()</script>
	</td>
</tr>
<tr>
	<td class=title><?=readLanguage(builder,layout_banner)?>:</td>
	<td>
		<select name=child_header id=child_header>
			<option value=""><?=readLanguage(builder,basic)?></option>
			<option value="none"><?=readLanguage(builder,none)?></option>
		</select>
		<? if ($edit){ ?><script>setSelectValue("#child_header", "<?=$entry["child_header"]?>")</script><? } ?>
	</td>
	<td class=title><?=readLanguage(builder,layout_footer)?>:</td>
	<td>
		<select name=child_footer id=child_footer>
			<option value=""><?=readLanguage(builder,basic)?></option>
			<option value="none"><?=readLanguage(builder,none)?></option>
		</select>
		<? if ($edit){ ?><script>setSelectValue("#child_footer", "<?=$entry["child_footer"]?>")</script><? } ?>
	</td>
</tr>
</table>

<!-- Children Blocks -->
<div class=subtitle><?=readLanguage(builder,page_children_blocks)?></div>
<div class=data_table_container><table class=data_table>
<tr>
	<td class=title><?=readLanguage(builder,page_children_show)?>:</td>
	<td colspan=3>
		<div class=switch><label><?=$data_no_yes[0]?><input type=checkbox name=blocks_show id=blocks_show onchange="toggleVisibility(this)" value=1 <?=($entry["blocks_show"] ? "checked" : "")?>><span class=lever></span><?=$data_no_yes[1]?></label></div>
		<script>
		$(document).ready(function(){
			toggleVisibility($("#blocks_show")[0])
		});
		</script>
	</td>
</tr>
<tr visibility-control=blocks_show visibility-value=1>
	<td class=title><?=readLanguage(builder,blocks_template)?>:</td>
	<td>
		<select name=blocks_template id=blocks_template>
		<? $built_in_blocks = retrieveDirectoryFiles("../blocks/", "php");
		foreach ($built_in_blocks AS $block){
			$block_selector = basename($block, ".php");
			print "<option value='$block_selector'>$block_selector</option>";
		} ?>
		</select>
		<? if ($edit){ ?><script>setSelectValue("#blocks_template", "<?=$entry["blocks_template"]?>")</script><? } ?>
		<script>$("#blocks_template").select2()</script>
	</td>
	<td class=title><?=readLanguage(builder,blocks_spacing)?>:</td>
	<td>
		<select block-options name=blocks_spacing id=blocks_spacing>
			<option value=0><?=readLanguage(builder,none)?></option>
			<option value=5>5</option><option value=10>10</option>
			<option value=15>15</option><option value=20>20</option>
			<option value=25>25</option><option value=30>30</option>
		</select>
		<? if ($entry["blocks_spacing"]){ ?><script>setSelectValue("#blocks_spacing","<?=($edit ? $entry["blocks_spacing"] : 15)?>")</script><? } ?>
	</td>
</tr>
<tr visibility-control=blocks_show visibility-value=1>
	<td class=title><?=readLanguage(builder,blocks_row)?>:</td>
	<td>
		<select name=blocks_per_row id=blocks_per_row>
			<option value=1>1</option><option value=2>2</option><option value=3>3</option><option value=4>4</option>
			<option value=5>5</option><option value=6>6</option>
		</select>
		<? if ($edit){ ?><script>setSelectValue("#blocks_per_row", "<?=($edit ? $entry["blocks_per_row"] : 4)?>")</script><? } ?>
	</td>
	<td class=title><?=readLanguage(builder,blocks_page)?>:</td>
	<td>
		<input type=number name=blocks_per_page value="<?=($edit ? $entry["blocks_per_page"] : 12)?>">
	</td>
</tr>
</table></div>
<? } ?>

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