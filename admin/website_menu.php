<? include "system/_handler.php";

$mysqltable = $suffix . "website_menu";
$base_name = basename($_SERVER["SCRIPT_FILENAME"], ".php");
checkPermissions($base_name);

/* Common Pages
	- website_menu.php
	- website_links.php
	- mobile_menu.php
*/

switch ($base_name){
	case "website_menu":
		$type = 0;
	break;
	
	case "website_links":
		$type = 1;
	break;
	
	case "mobile_menu":
		$type = 2;
	break;
}

//==== DELETE Record ====
if ($delete){
	mysqlQuery("DELETE FROM $mysqltable WHERE id=$delete");
	if (mysqlAffectedRows()){ $success = readLanguage(records,deleted); } else { $error = readLanguage(records,unavailable); }

//==== ADD Record ====
} else if ($post["token"] && !$edit){
	if ($post["sub_menus_type"]==1){
		$sub_menus = str_replace(str_replace("/","\\/",$base_url), "", $post["sub_menus_custom"]);
	} else if ($post["sub_menus_type"]==2){
		$sub_menus = $post["sub_menus_page"];
	} else if ($post["sub_menus_type"]==3){
		$sub_menus = $post["sub_menus_section"];
	}
	$query = "INSERT INTO $mysqltable (
		type,
		title,
		url,
		sub_menus_type,
		sub_menus,
		sub_menus_side,
		sub_menus_children,
		icon,
		image,
		class,
		priority
	) VALUES (
		'" . $type . "',
		'" . $post["title"] . "',
		'" . str_replace($base_url, "", $post["url"]) . "',
		'" . $post["sub_menus_type"] . "',
		'" . $sub_menus . "',
		'" . ($post["sub_menus_type"] && $post["sub_menus_type"]!=1 ? $post["sub_menus_side"] : "") . "',
		'" . ($post["sub_menus_type"] && $post["sub_menus_type"]!=1 ? $post["sub_menus_children"] : "") . "',
		'" . $post["icon"] . "',
		'" . imgUpload($_FILES[image], "../uploads/menu/") . "',
		'" . $post["class"] . "',
		'" . newRecordID($mysqltable) . "'
	)";
	mysqlQuery($query);
	$success = readLanguage(records,added);

//==== EDIT Record ====
} else if ($post["token"] && $edit){
	$record_data = getID($edit, $mysqltable);
	if ($post["sub_menus_type"]==1){
		$sub_menus = str_replace(str_replace("/","\\/",$base_url), "", $post["sub_menus_custom"]);
	} else if ($post["sub_menus_type"]==2){
		$sub_menus = $post["sub_menus_page"];
	} else if ($post["sub_menus_type"]==3){
		$sub_menus = $post["sub_menus_section"];
	}
	$query = "UPDATE $mysqltable SET
		title='" . $post["title"] . "',
		url='" . str_replace($base_url, "", $post["url"]) . "',
		sub_menus_type='" . $post["sub_menus_type"] . "',
		sub_menus='" . $sub_menus . "',
		sub_menus_side='" . ($post["sub_menus_type"] && $post["sub_menus_type"]!=1 ? $post["sub_menus_side"] : "") . "',
		sub_menus_children='" . ($post["sub_menus_type"] && $post["sub_menus_type"]!=1 ? $post["sub_menus_children"] : "") . "',
		icon='" . $post["icon"] . "',
		image='" . imgUpload($_FILES[image], "../uploads/menu/", $record_data["image"]) . "',
		class='" . $post["class"] . "'
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

//Tables schema
include "system/schema.php";

include "_header.php"; ?>

<script src="../plugins/iconpicker.min.js?v=<?=$system_settings["system_version"]?>"></script>
<link href="../plugins/iconpicker.min.css?v=<?=$system_settings["system_version"]?>" rel="stylesheet">

<script src="../plugins/domenu.min.js?v=<?=$system_settings["system_version"]?>"></script>
<link href="../plugins/domenu.min.css?v=<?=$system_settings["system_version"]?>" rel="stylesheet">

<div class=title><?=getPageTitle($base_name)?></div>
<?=$message?>

<form method=post enctype="multipart/form-data" action="<?=$action?>">
<input type=hidden name=token value="<?=$token?>">

<div class=data_table_container><table class=data_table>
<tr>
	<td class=title><?=readLanguage(inputs,title)?>: <i class=requ></i></td>
	<td colspan=3><input type=text name=title value="<?=$entry["title"]?>" data-validation=required></td>
</tr>
<tr>
	<td class=title><?=readLanguage(inputs,url)?>: <i class=requ></i></td>
	<td class=ltr-input colspan=3>
		<div class="input-addon input-addon-ltr">
			<span before><?=$base_url?></span>
			<select name=url id=url><?=$data_menu_items?></select>
			<script>
			<? if ($entry){ ?>
			if (!$("#url option[value='<?=$entry["url"]?>']").length){
				$("#url").append("<option value='<?=$entry["url"]?>'><?=$entry["url"]?></option>");
			}
			setSelectValue("#url","<?=$entry["url"]?>");
			<? } ?>
			$("#url").select2({ tags: true });
			</script>
		</div>
	</td>
</tr>
<tr>
	<td class=title><?=readLanguage(plugins,icon)?>:</td>
	<td colspan=3>
		<div class=flex_content>
			<i data-icon=icon class="<?=$entry["icon"]?>"></i>
			<input type=text name=icon value="<?=$entry["icon"]?>" onkeyup="$('[data-icon=icon]').attr('class',this.value)" autocomplete=off>
			<button type=button class="btn btn-default btn-sm btn-square flex-center" onclick="bindIconSearch('icon')"><i class="fas fa-search"></i>&nbsp;<?=readLanguage(operations,select)?></button>
		</div>
		<div class=input_description>Powered by <a href="https://fontawesome.com/icons?m=free" target=_blank>Font Awesome</a> and <a href="https://getbootstrap.com/docs/3.3/components/" target=_blank>Bootstrap Glyphicons</a>
	</td>
</tr>
<tr>
	<td class=title><?=readLanguage(inputs,image)?>:</td>
	<td colspan=3>
		<table class=attachment><tr>
		<td>
			<input type=file name=image id=image accept="image/*" data-validation=mime data-validation-allowing="image/bmp,image/jpeg,image/png,image/gif">
			<div class=input_description><?=readLanguage(inputs,instructions_design)?></div>
		</td>
		<td width=150>
			<? $path = ($entry["image"] ? "../uploads/menu/" . $entry["image"] : "images/placeholder.png") ?>
			<a data-fancybox=images href="<?=$path?>"><img class=sample_img image-placeholder=image src="<?=$path?>"></a>
		</td>
		</tr></table>
		<!-- Used Only For Upload -->
		<script>$(document).ready(function(){ bindImage("image") })</script>
	</td>
</tr>
<tr>
	<td class=title><?=readLanguage(builder,css_class)?>:</td>
	<td>
		<div class=class_input class-bind=class class-properties="<?=$entry["class"]?>"></div>
	</td>
	<td class=title><?=readLanguage(pages,menu_sub_menus)?>:</td>
	<td>
		<select name=sub_menus_type id=sub_menus_type onchange="toggleVisibility(this)">
			<option value=0><?=readLanguage(builder,none)?></option>
			<option value=1><?=readLanguage(builder,custom)?></option>
			<option value=2><?=readLanguage(pages,pages_custom)?></option>
			<option value=3><?=readLanguage(pages,pages_built_in)?></option>
		</select>
		<script>
		setSelectValue("#sub_menus_type", "<?=$entry["sub_menus_type"]?>");
		$(document).ready(function(){
			toggleVisibility($("#sub_menus_type")[0]);
		});
		</script>
	</td>
</tr>
<tr visibility-control=sub_menus_type visibility-value=1>
	<td class=title><?=readLanguage(pages,menu_sub_menus)?>:</td>
	<td colspan=3>
		<input type=hidden name=sub_menus_custom id=sub_menus_custom domenu-input="#domenu">
		<div class=dd id=domenu>
			<button type=button class="dd-new-item btn btn-primary btn-sm"><span class="fas fa-plus"></span>&nbsp;&nbsp;<?=readLanguage(operations,insert)?></button>
			<li class=dd-item-blueprint>
				<div class="dd-handle dd3-handle"></div>
				<div class=dd3-content>
					<div>
						<span><small><?=readLanguage(inputs,title)?> <i class=requ></i></small><input category-item=title type=text data-validation=requiredVisible></span>
						<span><small><?=readLanguage(plugins,icon)?></small><div class=d-flex><input category-item=icon type=text>&nbsp;<input type=button select-icon class="btn btn-default btn-sm" value="<?=readLanguage(operations,select)?>"></div></span>
						<span><small><?=readLanguage(inputs,url)?></small><select category-item=url><?=$data_menu_items?></select></span>
					</div>
					<span class=buttons>
						<button type=button class="item-remove btn-danger"><span class="fas fa-times"></span></button>
						<button type=button class="item-add btn-success"><span class="fas fa-plus"></span></button>
					</span>
				</div>
			</li>
			<ol class=dd-list></ol>
		</div>	
	</td>
</tr>
<tr visibility-control=sub_menus_type visibility-value=2,3>
	<td class=title><?=readLanguage(pages,menu_sub_menus)?>:</td>
	<td visibility-control=sub_menus_type visibility-value=2>
		<select name=sub_menus_page id=sub_menus_page>
		<? $result_pages = null; $result = mysqlQuery("SELECT * FROM " . $suffix . "website_pages_custom WHERE type=2 ORDER BY priority DESC");
		if (mysqlNum($result)){
			while ($page = mysqlFetch($result)){
				$count = mysqlNum(mysqlQuery("SELECT id FROM " . $suffix . "website_pages_custom WHERE parent=" . $page["id"]));
				$result_pages .= "<option value='" . $page["id"] . "' data-count='$count'>" . customPagePathRender(customPagePath($page["id"])) . "</option>";	
			}
		}
		print $result_pages; ?>
		</select>
		<script>
			<? if ($edit && $entry["sub_menus_type"]==2){ ?>setSelectValue("#sub_menus_page", "<?=$entry["sub_menus"]?>");<? } ?>
			$("#sub_menus_page").select2({
				templateResult: function(state){
					var $state = $("<div>" + state.text + "&nbsp;&nbsp;<small style='color:#808080'>(" + $(state.element).data("count") + " <?=readLanguage(builder,pages)?>)</small></div>");
					return $state;
				}
			});
		</script>
	</td>
	<td visibility-control=sub_menus_type visibility-value=3>
		<select name=sub_menus_section id=sub_menus_section>
		<? $section_pages = null;
		foreach ($schema AS $table=>$parameters){
			$section_title = $parameters["label"];
			$parent_exists = mysqlNum(mysqlQuery("SHOW COLUMNS FROM $table LIKE 'parent'"));
			if ($parent_exists){
				$section_pages .= "<optgroup label='$section_title'>";
					//Primary Page
					$count = mysqlNum(mysqlQuery("SELECT id FROM $table WHERE parent=0"));
					$section_pages .= "<option value='$table,0' data-count='$count'>$section_title</option>";
					
					//Child Pages
					$child_pages = customPageChildren(0, $table);
					$result_pages = null; $result = mysqlQuery("SELECT * FROM $table WHERE id IN (" . implode(",", $child_pages) . ") ORDER BY parent ASC, priority DESC");
					if (mysqlNum($result)){
						while ($page_entry = mysqlFetch($result)){
							$count = mysqlNum(mysqlQuery("SELECT id FROM $table WHERE parent=" . $page_entry["id"]));
							if ($count){
								$result_pages .= "<option value='$table," . $page_entry["id"] . "' data-count='$count'>" . $section_title . " » " . customPagePathRender(customPagePath($page_entry["id"], $table), $table) . "</option>";
							}
						}
					}
					$section_pages .= $result_pages;
				$section_pages .= "</optgroup>";
			} else {
				//Direct Page
				$count = mysqlNum(mysqlQuery("SELECT id FROM $table"));
				$section_pages .= "<option value='$table'>$section_title</option>";
			}
		}
		print $section_pages; ?>
		</select>
		<script>
			<? if ($edit && $entry["sub_menus_type"]==3){ ?>setSelectValue("#sub_menus_section", "<?=$entry["sub_menus"]?>");<? } ?>
			$("#sub_menus_section").select2({
				templateResult: function(state){
					var count = $(state.element).data("count");
					var $state = $("<div>" + state.text + (count ? "&nbsp;&nbsp;<small style='color:#808080'>(" + count + " <?=readLanguage(builder,pages)?>)</small>" : "") + "</div>");
					return $state;
				}
			});
		</script>
	</td>	
	<td class=title><?=readLanguage(pages,menu_graphic)?>:</td>
	<td>
		<select name=sub_menus_side id=sub_menus_side>
			<option value=""><?=readLanguage(builder,none)?></option>
			<option value="icon"><?=readLanguage(plugins,icon)?></option>
			<option value="cover"><?=readLanguage(inputs,cover_image)?></option>
		</select>	
		<script>
		var sub_menus_side_selection = "<?=$entry["sub_menus_side"]?>";
		if (!$("#sub_menus_side").find("option[value='" + sub_menus_side_selection + "']").length){
			$("#sub_menus_side").append("<option value='" + sub_menus_side_selection + "'>" + sub_menus_side_selection + "</option>");
		}
		setSelectValue("#sub_menus_side", "<?=$entry["sub_menus_side"]?>");
		$("#sub_menus_side").select2({ tags: true });
		</script>
	</td>
</tr>
<tr visibility-control=sub_menus_type visibility-value=2,3>
	<td class=title><?=readLanguage(builder,page_children_show)?>:</td>
	<td colspan=3>
		<div class=switch><label><?=$data_no_yes[0]?><input type=checkbox name=sub_menus_children value=1 <?=($entry["sub_menus_children"] ? "checked" : "")?>><span class=lever></span><?=$data_no_yes[1]?></label></div>
	</td>
</tr>
</table></div>

<div class=submit_container><input type=button class=submit value="<?=$button?>"></div>
</form>

<!-- Include class selection snippet -->
<? include "includes/_select_class.php"; ?>

<script>
$("#domenu").domenu({
	data: '<?=($entry["sub_menus_type"]==1 && $entry["sub_menus"] ? unescapeJson($entry["sub_menus"]) : "[]")?>'
	
}).onItemAdded(function(item, content){
	//Bind icon search
	$(item).find("[select-icon]").on("click", function(){
		bindIconSearch($(item).find("[category-item=icon]"));
	});
	
	//Bind URL Select2
	var url = $(item).find("[category-item=url]");
	if (typeof content.url !== "undefined"){
		if (!url.find("option[value='" + content.url + "']").length){
			url.append("<option value='" + content.url + "' selected>" + content.url + "</option>");
		} else {
			url.val(content.url);
		}
	}
	url.select2({ tags: true });
}).parseJson();
</script>

<div class=crud_separator></div>
<?
$crud_data["where_statement"] = "type=$type";
$crud_data["order_field"] = "priority";
$crud_data["order_by"] = "priority DESC";
$crud_data["delete_record_message"] = "title";
$crud_data["buttons"] = array(true,true,false,true,true); //Add - Search - View - Edit - Delete
$crud_data["columns"] = array(
	array("title", readLanguage(inputs,title), "100%", "center", null, false, true),
	array("icon", readLanguage(plugins,icon), "50px", "center", "'<i class=\"%s\" style=\"font-size:20px\"></i>'", false, false)
);
require_once("crud/crud.php");
?>

<? include "_footer.php"; ?>