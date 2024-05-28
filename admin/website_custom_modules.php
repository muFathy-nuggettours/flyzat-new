<? $skip_compress = true; include "system/_handler.php";

$mysqltable = $suffix . "website_modules_custom";
$mysqltable_custom = $suffix . "website_modules_components";
$base_name = basename($_SERVER["SCRIPT_FILENAME"], ".php");
checkPermissions($base_name);

//==== DELETE Record ====
if ($delete){
	$record_data = getID($delete, $mysqltable);
	mysqlQuery("DELETE FROM $mysqltable WHERE id=$delete");
	mysqlQuery("DELETE FROM $mysqltable_custom WHERE module_uniqid=" . $record_data["uniqid"]);
	if (mysqlAffectedRows()){ $success = readLanguage(records,deleted); } else { $error = readLanguage(records,unavailable); }

//==== ADD Record ====
} else if ($post["token"] && !$edit){
	$query = "INSERT INTO $mysqltable (
		uniqid,
		placeholder,
		type,
		tags,
		module_class,
		title_container_class,
		content_container_class,
		buttons_container_class,
		container,
		container_class,
		background_custom,
		background_file,
		background_attributes,
		title,
		title_class,
		title_animation,
		subtitle,
		subtitle_class,
		subtitle_animation,
		buttons,
		custom_attributes,
		custom_justify,
		custom_align,
		custom_wrap,
		custom_spacing,
		custom_separator,
		custom_css,
		priority
	) VALUES (
		'" . $post["uniqid"] . "',
		'" . $post["placeholder"] . "',
		'" . implode(",", $post["type"]) . "',
		'" . implode(",", $post["tags"]) . "',
		'" . $post["module_class"] . "',
		'" . $post["title_container_class"] . "',
		'" . $post["content_container_class"] . "',
		'" . $post["buttons_container_class"] . "',
		'" . $post["container"] . "',
		'" . ($post["container"] ? $post["container_class"] : "") . "',
		'" . $post["background_custom"] . "',
		'" . ($post["background_custom"] ? fileUpload($_FILES["background_file"], "../uploads/classes/", $post["background_custom_clone"]) : "") . "',		
		'" . ($post["background_custom"] ? $post["background_attributes"] : "") . "',		
		'" . $post["title"] . "',
		'" . $post["title_class"] . "',
		'" . $post["title_animation"] . "',
		'" . $post["subtitle"] . "',
		'" . $post["subtitle_class"] . "',
		'" . $post["subtitle_animation"] . "',
		'" . str_replace(str_replace("/","\\/",$base_url), "", $post["buttons"]) . "',
		'" . $post["custom_attributes"] . "',
		'" . $post["custom_justify"] . "',
		'" . $post["custom_align"] . "',
		'" . $post["custom_wrap"] . "',
		'" . $post["custom_spacing"] . "',
		'" . $post["custom_separator"] . "',
		'" . $post["custom_css"] . "',
		'" . newRecordID($mysqltable) . "'
	)";
	mysqlQuery($query);
	
	//Build modules CSS
	buildCSSModules();
	
	//Build Theme
	if (file_exists("website/website.min.css") && $post["custom_css"]){
		buildWebsiteTheme();
	}
	
	$success = readLanguage(records,added);

//==== EDIT Record ====
} else if ($post["token"] && $edit){
	$record_data = getID($edit, $mysqltable);
	$query = "UPDATE $mysqltable SET
		placeholder='" . $post["placeholder"] . "',
		type='" . implode(",", $post["type"]) . "',
		tags='" . implode(",", $post["tags"]) . "',
		module_class='" . $post["module_class"] . "',
		title_container_class='" . $post["title_container_class"] . "',
		content_container_class='" . $post["content_container_class"] . "',
		buttons_container_class='" . $post["buttons_container_class"] . "',
		container='" . $post["container"] . "',
		container_class='" . ($post["container"] ? $post["container_class"] : "") . "',
		background_custom='" . $post["background_custom"] . "',
		background_file='" . ($post["background_custom"] ? fileUpload($_FILES["background_file"], "../uploads/classes/", $record_data["background_file"]) : "") . "',
		background_attributes='" . ($post["background_custom"] ? $post["background_attributes"] : "") . "',
		title='" . $post["title"] . "',
		title_class='" . $post["title_class"] . "',
		title_animation='" . $post["title_animation"] . "',
		subtitle='" . $post["subtitle"] . "',
		subtitle_class='" . $post["subtitle_class"] . "',
		subtitle_animation='" . $post["subtitle_animation"] . "',
		buttons='" . str_replace(str_replace("/","\\/",$base_url), "", $post["buttons"]) . "',
		custom_attributes='" . $post["custom_attributes"] . "',
		custom_justify='" . $post["custom_justify"] . "',
		custom_align='" . $post["custom_align"] . "',
		custom_wrap='" . $post["custom_wrap"] . "',
		custom_spacing='" . $post["custom_spacing"] . "',
		custom_separator='" . $post["custom_separator"] . "',
		custom_css='" . $post["custom_css"] . "'
	WHERE id=$edit";
	mysqlQuery($query);
	
	//Build modules CSS
	buildCSSModules();
	
	//Build Theme
	if (file_exists("website/website.min.css") && $post["custom_css"]){
		buildWebsiteTheme();
	}
	
	$success = readLanguage(records,updated);
}

//==== Insert Blocks ====
if ($post["token"]){
	if ($post["blocks"]){
		$blocks = json_decode($post["blocks"], true);
		$retained_blocks = array();
		foreach ($blocks AS $key=>$value){
			if ($value["id"]){
				array_push($retained_blocks, $value["id"]);
				$query = "UPDATE $mysqltable_custom SET
					placeholder='" . $value["placeholder"] . "',
					width='" . json_encode($value["width"]) . "',
					class='" . $value["class"] . "',
					animation='" . html_entity_decode($value["animation"]) . "',
					attributes='" . html_entity_decode($value["attributes"]) . "',
					type='" . $value["type"] . "',
					content='" . $value["content"] . "',
					arrangement='" . $value["arrangement"] . "'
				WHERE id=" . $value["id"];
				mysqlQuery($query);
			} else {
				array_push($retained_blocks, newRecordID($mysqltable_custom));
				$query = "INSERT INTO $mysqltable_custom (
					module_uniqid,
					placeholder,
					width,
					class,
					animation,
					attributes,
					type,
					content,
					arrangement
				) VALUES (
					'" . $post["uniqid"] . "',
					'" . $value["placeholder"] . "',
					'" . json_encode($value["width"]) . "',
					'" . $value["class"] . "',
					'" . html_entity_decode($value["animation"]) . "',
					'" . html_entity_decode($value["attributes"]) . "',
					'" . $value["type"] . "',
					'" . $value["content"] . "',
					'" . $value["arrangement"] . "'
				)";
				mysqlQuery($query);
			}
		}
		mysqlQuery("DELETE FROM $mysqltable_custom WHERE module_uniqid='" . $post["uniqid"] . "' AND id NOT IN (" . implode(",", $retained_blocks) . ")");
	}
}

//Read and Set Operation [Custom]
if ($edit){
	$entry = getID($edit,$mysqltable);
	if (!$entry){ $error = readLanguage(records,unavailable); $edit = null; }
}
if ($edit){
	$button = readLanguage(records,update);
	$action = "$base_name.php" . rebuildQueryParameters(array("delete","token","clone"));
} else {
	$button = readLanguage(records,add);
	$action = "$base_name.php" . rebuildQueryParameters(array("delete","token","edit","clone"));
	if ($error){ foreach ($_POST as $key => $value){ $entry[$key] = $value; } }
}
if ($success){ $message = "<div class='alert alert-success'>$success</div>"; }
if ($error){ $message = "<div class='alert alert-danger'>$error</div>"; }

//Set name variable
$module_uniqid = ($edit ? $entry["uniqid"] : "module-" . uniqid());

//Read cloned parameter
if ($get["clone"]){
	$clone = true;
	$entry = getID($get["clone"], $mysqltable);
	$entry["custom_css"] = str_replace($entry["uniqid"], $module_uniqid, $entry["custom_css"]);
}

include "_header.php"; ?>

<style>
.module_rows {
	background: #fff;
	border: 1px solid #ccc;
	border-radius: 3px;
	margin: 10px 0 10px 0;
	box-shadow: 2px 2px 10px rgba(0, 0, 0, .05);
}

.blocks_container {
	display: flex;
	justify-content: flex-start;
	align-items: stretch;
	flex-wrap: wrap;
	position: relative;
	min-height: 50px;
}

.blocks_container .block {
	display: flex;
	flex-direction: column;
	font-size: 14px;
	background: #f8f8f8;
	border: 3px solid #ddd;
	padding: 5px;
	cursor: pointer;
	position: relative;
	transition: border 0.2s, background 0.2s;
}

.blocks_container .block>div.head {
	display: flex;
	align-items: center;
	max-width: 100%;
}

.blocks_container .block>div.head i {
	width: 30px;
	height: 30px;
	display: flex;
	justify-content: center;
	align-items: center;
	color: white;
}

.blocks_container .block>span {
	display: flex;
	justify-content: center;
	align-items: center;
	padding: 10px;
	border: 1px dotted #ccc;
	margin-top: 5px;
	word-break: break-all;
}

.blocks_container .block.active {
	background: #dfebf7;
	border: 3px solid #5a8caf;
}

textarea.css_code {
	height: 300px;
	box-shadow: initial;
	border-radius: 3px;
	background: #fff;
	border: 1px solid #c8c8c8 !important;
	direction: ltr;
	text-align: left;
}
</style>

<script src="../plugins/fixed-data.js?v=<?=$system_settings["system_version"]?>"></script>

<div class=title><?=getPageTitle($base_name)?></div>
<?=$message?>

<form method=post enctype="multipart/form-data" action="<?=$action?>">
<input type=hidden name=token value="<?=$token?>">
<input type=hidden name=uniqid value="<?=$module_uniqid?>">

<table class=data_table>
<tr>
	<td class=title><?=readLanguage(builder,placeholder)?>: <i class=requ></i></td>
	<td colspan=3><input type=text name=placeholder value="<?=$entry["placeholder"]?>" data-validation=required></td>
</tr>
<tr>
	<td class=title><?=readLanguage(builder,tags)?>:</td>
	<td colspan=3>
		<select name=tags[] id=tags multiple>
		<? $appended_tags = array();
		$available_tags = explode(",", mysqlFetch(mysqlQuery("SELECT tags, GROUP_CONCAT(DISTINCT tags) AS placeholders FROM $mysqltable WHERE tags!=''"))["placeholders"]);
		foreach ($available_tags AS $tag){
			if ($tag && !in_array($tag, $appended_tags)){
				array_push($appended_tags, $tag);
				print "<option value='" . $tag . "'>" . $tag . "</option>";
			}
		} ?>
		</select>
		<script>
			<? if ($entry["tags"]){ ?>$("#tags").val(["<?=implode('","', explode(",", $entry["tags"]))?>"]);<? } ?>
			$("#tags").select2({ tags: true });
		</script>	
	</td>
</tr>
<tr>
	<td class=title><?=readLanguage(builder,type)?>:</td>
	<td colspan=3>
		<div class=check_container id=type>
			<? foreach ($data_module_types AS $key=>$value){
				print "<label><input type=checkbox name=type[] class=filled-in value='$key'><span>$value</span></label>";
			} ?>
		</div>
		<script>
		var checkArray = "<?=$entry["type"]?>".split(",");
		$("#type").find("[value='" + checkArray.join("'],[value='") + "']").prop("checked",true);
		</script>
	</td>
</tr>
</table>

<!-- Tabs -->
<ul class="nav nav-tabs tab-inline-header margin-top">
	<li role=presentation class=active><a href="#settings" data-toggle=tab role=tab><i class="fas fa-cogs"></i><span>&nbsp;&nbsp;<?=readLanguage(builder,module_settings)?></span></a></li>
	<li role=presentation><a href="#content" data-toggle=tab role=tab><i class="fas fa-laptop"></i><span>&nbsp;&nbsp;<?=readLanguage(builder,module_content)?></span></a></li>
	<li role=presentation><a href="#css" data-toggle=tab role=tab><i class="fas fa-palette"></i><span>&nbsp;&nbsp;<?=readLanguage(builder,custom_css)?></span></a></li>
</ul>

<div class=tab-content><!-- Start Tab Content -->

<!-- ===== Settings ===== -->

<div class="tab-pane active" role=tabpanel id="settings">

<!-- Module Settings -->
<div class="data_table_container margin-bottom"><table class=data_table>
<tr>
	<td class=title><?=readLanguage(builder,class_module)?>:</td>
	<td>
		<div class=class_input class-bind=module_class class-properties="<?=$entry["module_class"]?>"></div>
	</td>
	<td class=title><?=readLanguage(builder,class_title)?>:</td>
	<td>
		<div class=class_input class-bind=title_container_class class-properties="<?=$entry["title_container_class"]?>"></div>
	</td>
</tr>
<tr>
	<td class=title><?=readLanguage(builder,class_content)?>:</td>
	<td>
		<div class=class_input class-bind=content_container_class class-properties="<?=$entry["content_container_class"]?>"></div>
	</td>
	<td class=title><?=readLanguage(builder,class_buttons)?>:</td>
	<td>
		<div class=class_input class-bind=buttons_container_class class-properties="<?=$entry["buttons_container_class"]?>"></div>
	</td>
</tr>
<tr>
	<td class=title><?=readLanguage(builder,attributes)?>:</td>
	<td colspan=3 data-multiple=custom_attributes>
		<button type=button class="btn btn-primary btn-sm" onclick="multipleDataCreate('custom_attributes')"><?=readLanguage(operations,insert)?></button>
		<input type=hidden name=custom_attributes>
		<ul multiple-sortable>
			<li data-template>
				<div class="d-flex align-items-center">
					<div class="grabbable grabbable_icon"><i class="fas fa-bars"></i></div>&nbsp;&nbsp;
					<input type=text class=ltr-input data-name=attribute data-validation=required placeholder="<?=readLanguage(builder,attributes_attribute)?>" disabled>&nbsp;&nbsp;
					<input type=text class=ltr-input data-name=value placeholder="<?=readLanguage(builder,attributes_value)?>">&nbsp;&nbsp;
					<a class="btn btn-danger btn-sm remove"><i class="fas fa-times"></i></a>
				</div>
			</li>
		</ul>
		<? if ($entry["custom_attributes"]){ ?>
		<script>
		var jsonArray = <?=$entry["custom_attributes"]?>;
		jsonArray.forEach(function(entry){ multipleDataCreate("custom_attributes", entry); });
		</script>
		<? } ?>
	</td>
</tr>
<tr>
	<td class=title><?=readLanguage(builder,container)?>:</td>
	<td>
		<div class=switch><label><?=$data_no_yes[0]?><input type=checkbox name=container id=container value=1 onchange="toggleVisibility(this)" <?=($entry["container"] || (!$edit && !$clone) ? "checked" : "")?>><span class=lever></span><?=$data_no_yes[1]?></label></div>
		<script>
		$(document).ready(function(){
			toggleVisibility($("#container")[0])
		});
		</script>
	</td>
	<td class=title><?=readLanguage(builder,background_custom)?>:</td>
	<td>
		<div class=switch><label><?=$data_no_yes[0]?><input type=checkbox name=background_custom id=background_custom value=1 onchange="toggleVisibility(this)" <?=($entry["background_custom"] ? "checked" : "")?>><span class=lever></span><?=$data_no_yes[1]?></label></div>
		<script>
		$(document).ready(function(){
			toggleVisibility($("#background_custom")[0])
		});
		</script>
	</td>
</tr>
<tr visibility-control=container visibility-value=1>
	<td class=title><?=readLanguage(builder,class_container)?>:</td>
	<td colspan=3>
		<div class=class_input class-bind=container_class class-properties="<?=$entry["container_class"]?>"></div>	
	</td>
</tr>
<tr visibility-control=background_custom visibility-value=1>
	<td class=title><?=readLanguage(builder,background_file)?>:</td>
	<td colspan=3><div class=attachment>
		<div>
			<input type=hidden name=background_custom_clone value="<?=$entry["background_file"]?>">
			<input type=file name=background_file accept=".bmp,.jpeg,.jpg,.png,.gif,.mp4" allowed-mimes="image/bmp,image/jpeg,image/png,image/gif,video/mp4">
		</div>
		<? if ($entry["background_file"]){ echo fileBlock("../uploads/classes/" . $entry["background_file"], readLanguage(builder,background_file)); } ?>
	</div></td>
</tr>
<tr visibility-control=background_custom visibility-value=1>
	<td class=title><?=readLanguage(builder,background_attributes)?>:</td>
	<td colspan=3 data-multiple=background_attributes>
		<button type=button class="btn btn-primary btn-sm" onclick="multipleDataCreate('background_attributes')"><?=readLanguage(operations,insert)?></button>
		<input type=hidden name=background_attributes>
		<ul multiple-sortable>
			<li data-template>
				<div class="d-flex align-items-center">
					<div class="grabbable grabbable_icon"><i class="fas fa-bars"></i></div>&nbsp;&nbsp;
					<input type=text class=ltr-input data-name=attribute data-validation=required placeholder="<?=readLanguage(builder,attributes_attribute)?>" disabled>&nbsp;&nbsp;
					<input type=text class=ltr-input data-name=value placeholder="<?=readLanguage(builder,attributes_value)?>">&nbsp;&nbsp;
					<a class="btn btn-danger btn-sm remove"><i class="fas fa-times"></i></a>
				</div>
			</li>
		</ul>
		<? if ($entry["background_attributes"]){ ?>
		<script>
		var jsonArray = <?=$entry["background_attributes"]?>;
		jsonArray.forEach(function(entry){ multipleDataCreate("background_attributes", entry); });
		</script>
		<? } ?>
	</td>
</tr>
</table></div>

<!-- Modile Title -->
<div class=subtitle><?=readLanguage(inputs,title)?></div>
<table class=data_table>
<tr>
	<td class=title><?=readLanguage(inputs,title)?>:</td>
	<td colspan=3><input type=text name=title value="<?=$entry["title"]?>"></td>
</tr>
<tr>
	<td class=title><?=readLanguage(builder,css_class)?>:</td>
	<td>
		<div class=class_input class-bind=title_class class-properties="<?=$entry["title_class"]?>"></div>
	</td>
	<td class=title><?=readLanguage(builder,animation)?>:</td>
	<td>
		<div class=animation_input animation-bind=title_animation animation-properties="<?=base64_encode($entry["title_animation"])?>"></div>
	</td>
</tr>
</table>

<!-- Modile Subtitle -->
<div class=subtitle><?=readLanguage(inputs,subtitle)?></div>
<table class=data_table>
<tr>
	<td class=title><?=readLanguage(inputs,subtitle)?>:</td>
	<td colspan=3><input type=text name=subtitle value="<?=$entry["subtitle"]?>"></td>
</tr>
<tr>
	<td class=title><?=readLanguage(builder,css_class)?>:</td>
	<td>
		<div class=class_input class-bind=subtitle_class class-properties="<?=$entry["subtitle_class"]?>"></div>
	</td>
	<td class=title><?=readLanguage(builder,animation)?>:</td>
	<td>
		<div class=animation_input animation-bind=subtitle_animation animation-properties="<?=base64_encode($entry["subtitle_animation"])?>"></div>
	</td>
</tr>
</table>

<!-- Modile Buttons -->
<div class=subtitle><?=readLanguage(builder,module_buttons)?></div>
<table class=data_table>
<tr>
	<td class=title><?=readLanguage(builder,module_buttons)?>:</td>
	<td data-multiple=buttons colspan=3>
		<button type=button class="btn btn-primary btn-sm" onclick="multipleDataCreate('buttons')"><?=readLanguage(operations,insert)?></button>
		<input type=hidden name=buttons>
		<ul multiple-sortable>
		<li data-template>
			<table class=multiple_data_table><tr>
			<td>
				<a class="btn btn-success btn-sm add" onclick="multipleDataCreate('multiple_data')"><i class="fas fa-plus"></i></a>
				<a class="btn btn-danger btn-sm remove"><i class="fas fa-times"></i></a>
			</td>
			<td>
				<ul class=inline_input>
					<li><p><small><?=readLanguage(inputs,title)?></small></p><input type=text data-name=title data-validation=required disabled></li>
					<li style="flex-basis:20%"><p><small><?=readLanguage(inputs,url)?></small></p><select data-name=url><?=$data_menu_items?></select></li>
					<li style="flex-basis:20%"><p><small><?=readLanguage(builder,css_class)?></small></p><div class=class_input button-class=class></div></li>
					<li style="flex-basis:20%"><p><small><?=readLanguage(builder,animation)?></small></p><div class=animation_input button-animation=animation></div></li>
				</ul>					
			</td>
			</tr></table>
		</li>
		</ul>
		<script>
		function onMultipleDataCreate_buttons(object, data){
			$(object).find("select[data-name=url]").select2({ tags: true });
			$(document).ready(function(){
				//===== Bind Animation Selection =====
				
				//Bind animation selection to animation input
				var animationInput = $(object).find("div[button-animation]");
				
				//Double decode first for button JSON and second for animation json
				var properties = (data ? btoa(decodeHTML(decodeHTML(data.animation))) : "");
				
				//Set properties and bind animation
				animationInput.attr("animation-bind", "animation").attr("animation-properties", properties);
				animationBind(animationInput);
				
				//===== Bind Class Selection =====
				
				//Bind class selection to class input
				var classInput = $(object).find("div[button-class]");
				var properties = (data ? data.class : "");
				
				//Set properties and bind class
				classInput.attr("class-bind", "class").attr("class-properties", properties);
				classBind(classInput);
			});
		}
		<? if ($entry["buttons"]){ ?>
		var jsonArray = <?=$entry["buttons"]?>;
		jsonArray.forEach(function(entry){ multipleDataCreate("buttons", entry); });	
		<? } ?>
		</script>
	</td>
</tr>
</table>
</div>

<!-- ===== Content ===== -->

<div class=tab-pane role=tabpanel id="content">

<input type=hidden name=blocks id=blocks data-validation=required>

<!-- Content Settings -->
<div class=subtitle><?=readLanguage(builder,module_content_settings)?></div>
<table class=data_table>
<tr>
	<td class=title><?=readLanguage(builder,flex_justify)?>:</td>
	<td>
		<select block-options name=custom_justify id=custom_justify>
			<option value=flex-start><?=readLanguage(builder,flex_justify_start)?></option>
			<option value=flex-end><?=readLanguage(builder,flex_justify_end)?></option>
			<option value=center><?=readLanguage(builder,flex_justify_center)?></option>
			<option value=space-between><?=readLanguage(builder,flex_justify_space_between)?></option>
			<option value=space-around><?=readLanguage(builder,flex_justify_space_around)?></option>
			<option value=space-evenly><?=readLanguage(builder,flex_justify_space_evenly)?></option>
		</select>
		<? if ($entry["custom_justify"]){ ?><script>setSelectValue("#custom_justify","<?=$entry["custom_justify"]?>")</script><? } ?>
	</td>
	<td class=title><?=readLanguage(builder,flex_align)?>:</td>
	<td>
		<select block-options name=custom_align id=custom_align>
			<option value=stretch><?=readLanguage(builder,flex_align_stretch)?></option>
			<option value=center><?=readLanguage(builder,flex_align_center)?></option>
			<option value=flex-start><?=readLanguage(builder,flex_align_start)?></option>
			<option value=flex-end><?=readLanguage(builder,flex_align_end)?></option>
			<option value=baseline><?=readLanguage(builder,flex_align_baseline)?></option>
		</select>
		<? if ($entry["custom_align"]){ ?><script>setSelectValue("#custom_align","<?=$entry["custom_align"]?>")</script><? } ?>
	</td>
</tr>
<tr>
	<td class=title><?=readLanguage(builder,module_spacings)?>:</td>
	<td>
		<input type=hidden name=custom_spacing id=custom_spacing>
		<ul class=inline_input json-fixed-data=custom_spacing>
		<? foreach ($data_screen_sizes AS $size=>$icon){
			print "<li><div class=input-addon><span before><i class='$icon'></i></span><select data-name='$size' block-options>";  ?>
				<option value=0><?=readLanguage(builder,none)?></option>
				<option value=5>5</option><option value=10>10</option>
				<option value=15>15</option><option value=20>20</option>
				<option value=25>25</option><option value=30>30</option>
				<option value=40>40</option><option value=50>50</option>
			<? print "</select></div></li>";
		} ?>
		</ul>
		<script>
		<? if ($entry["custom_spacing"]){ ?>fixedDataRead("custom_spacing", <?=$entry["custom_spacing"]?>);<? } ?>
		<? if (!$edit){ ?>
		$("[json-fixed-data=custom_spacing] li:first-child select").on("change", function(){
			$("[json-fixed-data=custom_spacing] li:not(:first-child) select").val($(this).val());
		});
		<? } ?>
		</script>
	</td>
	<td class=title><?=readLanguage(builder,module_separators)?>:</td>
	<td>
		<input type=hidden name=custom_separator id=custom_separator>
		<ul class=inline_input json-fixed-data=custom_separator>
		<? foreach ($data_screen_sizes AS $size=>$icon){
			print "<li><div class=input-addon><span before><i class='$icon'></i></span><select data-name='$size'>";  ?>
				<option value=""><?=readLanguage(builder,auto)?></option>
				<option value=0><?=readLanguage(builder,none)?></option>
				<option value=5>5</option><option value=10>10</option>
				<option value=15>15</option><option value=20>20</option>
				<option value=25>25</option><option value=30>30</option>
				<option value=40>40</option><option value=50>50</option>
			<? print "</select></div></li>";
		} ?>
		</ul>
		<script>
		<? if ($entry["custom_separator"]){ ?>fixedDataRead("custom_separator", <?=$entry["custom_separator"]?>);<? } ?>
		</script>
	</td>
</tr>
<tr>
	<td class=title><?=readLanguage(builder,flex_wrap)?>:</td>
	<td colspan=3>
		<select block-options name=custom_wrap id=custom_wrap><option value=wrap><?=$data_no_yes[1]?></option><option value=nowrap><?=$data_no_yes[0]?></option></select>
		<? if ($entry["custom_wrap"]){ ?><script>setSelectValue("#custom_wrap","<?=$entry["custom_wrap"]?>")</script><? } ?>
	</td>
</tr>
</table>

<!-- Content -->
<div class=subtitle><span><?=readLanguage(builder,module_content)?></span><a class="btn btn-primary btn-sm btn-insert" onclick="insertBlock()"><?=readLanguage(operations,insert)?></a></div>

<!-- Module Content -->
<div class=module_rows data-spacing=0>
	<div class=blocks_container></div>
</div>

<!-- Module Content Options -->
<div class="data_table_container block_content" style="display:none"><table class=data_table>
<tr>
	<td class=title><?=readLanguage(builder,placeholder)?>:</td>
	<td colspan=3><input type=text name=block_placeholder value="<?=readLanguage(builder,module_component)?>"></td>
</tr>
<tr>
	<td class=title><?=readLanguage(builder,width)?>:</td>
	<td>
		<ul class=inline_input>
		<? foreach ($data_screen_sizes AS $size=>$icon){
			print "<li><div class=input-addon><span before><i class='$icon'></i></span><select name='block_width_$size'>";
			foreach ($data_module_widths AS $key => $value){
				print "<option value='$key'>$key%</option>";
			}
			print "</select></div></li>";
		} ?>
		</ul>
	</td>
	<td class=title><?=readLanguage(builder,type)?>:</td>
	<td>
		<select name=block_type>
			<option value=0><?=readLanguage(builder,content_type_mixed)?></option>
			<option value=1><?=readLanguage(builder,content_type_built_in)?></option>
			<option value=2><?=readLanguage(builder,content_type_variable)?></option>
			<option value=3><?=readLanguage(builder,content_type_custom)?></option>
			<option value=4><?=readLanguage(builder,content_type_display)?></option>
			<option value=5><?=readLanguage(builder,content_type_form)?></option>
		</select>
	</td>
</tr>
<tr>
	<td class=title><?=readLanguage(builder,class_container)?>:</td>
	<td>
		<div class=class_input class-bind=block_class></div>
	</td>
	<td class=title><?=readLanguage(builder,animation)?>:</td>
	<td>
		<div class=animation_input animation-bind=block_animation></div>
	</td>
</tr>
<tr>
	<td class=title><?=readLanguage(builder,attributes)?>:</td>
	<td colspan=3 data-multiple=block_attributes>
		<button type=button class="btn btn-primary btn-sm" onclick="multipleDataCreate('block_attributes')"><?=readLanguage(operations,insert)?></button>
		<input type=hidden name=block_attributes>
		<ul multiple-sortable>
			<li data-template>
				<div class="d-flex align-items-center">
					<div class="grabbable grabbable_icon"><i class="fas fa-bars"></i></div>&nbsp;&nbsp;
					<input type=text class=ltr-input data-name=attribute data-validation=required placeholder="<?=readLanguage(builder,attributes_attribute)?>" disabled>&nbsp;&nbsp;
					<input type=text class=ltr-input data-name=value placeholder="<?=readLanguage(builder,attributes_value)?>">&nbsp;&nbsp;
					<a class="btn btn-danger btn-sm remove"><i class="fas fa-times"></i></a>
				</div>
			</li>
		</ul>
	</td>
</tr>

<!-- Mixed Content -->
<tr data-content=0>
	<td class=title><?=readLanguage(builder,content_type_mixed)?>:</td>
	<td colspan=3>
		<textarea class=contentEditor name=mixed_content id=mixed_content></textarea>
		<div class=input_description><?=readLanguage(builder,content_type_mixed_note)?></div>
	</td>
</tr>

<!-- Built-In Module -->
<tr data-content=1>
	<td class=title><?=readLanguage(builder,content_type_built_in)?>:</td>
	<td colspan=3>
		<select name=built_module id=built_module>
		<option value=""><?=readLanguage(builder,none)?></option>
		<? $built_in_modules = retrieveDirectoryFiles("../modules/", "php");
		foreach ($built_in_modules AS $module){
			$module_selector = basename($module, ".php");
			print "<option value='$module_selector'>$module_selector</option>";
		} ?>
		</select>
		<script>$("#built_module").select2()</script>
	</td>
</tr>

<!-- Page Variable -->
<tr data-content=2>
	<td class=title><?=readLanguage(builder,content_type_variable)?>:</td>
	<td colspan=3>
		<input type=text name=page_variable id=page_variable>
	</td>
</tr>

<!-- Custom Module -->
<tr data-content=3>
	<td class=title><?=readLanguage(builder,content_type_custom)?>:</td>
	<td colspan=3>
		<select name=custom_module id=custom_module>
		<option value=""><?=readLanguage(builder,none)?></option>
		<?=populateData("SELECT * FROM $mysqltable WHERE FIND_IN_SET(2,type) AND id!=" . ($edit ? $edit : 0), "uniqid", "placeholder")?>
		</select>
		<script>$("#custom_module").select2()</script>
	</td>
</tr>

<!-- Slider -->
<tr data-content=4>
	<td class=title><?=readLanguage(builder,content_type_display)?>:</td>
	<td colspan=3>
		<select name=custom_display id=custom_display>
		<option value=""><?=readLanguage(builder,none)?></option>
		<?=populateData("SELECT * FROM " . $suffix . "website_custom_displays", "uniqid", "placeholder")?>
		</select>
		<script>$("#custom_display").select2()</script>
	</td>
</tr>

<!-- Form -->
<tr data-content=5>
	<td class=title><?=readLanguage(builder,content_type_form)?>:</td>
	<td colspan=3>
		<select name=custom_form id=custom_form>
		<option value=""><?=readLanguage(builder,none)?></option>
		<?=populateData("SELECT * FROM " . $suffix . "website_forms", "uniqid", "placeholder")?>
		</select>
		<script>$("#custom_form").select2()</script>
	</td>
</tr>
</table></div>
</div>

<!-- ===== CSS Class ===== -->

<div class=tab-pane role=tabpanel id="css">
<div class="alert alert-warning class_selector"><?=readLanguage(builder,custom_css_selector)?> <b>.<?=$module_uniqid?></b></div>
<textarea class=css_code name=custom_css><?=$entry["custom_css"]?></textarea>
<script>
	$("[name=custom_css]").keydown(function(e){
		if (e.keyCode === 9){
			var start = this.selectionStart;
			var end = this.selectionEnd;
			var value = $(this).val();
			$(this).val(value.substring(0, start) + "\t" + value.substring(end));
			this.selectionStart = this.selectionEnd = start + 1;
			e.preventDefault();
		}
	});
</script>
</div>

</div><!-- End Tab Content -->

<div class=submit_container><input type=button class=submit value="<?=$button?>"></div>
</form>

<script>
//Initialize sortable
$(".blocks_container").sortable({
	placeholder: "ui-state-highlight",
	handle: "[move]",
	tolerance: "pointer",
    start: function(event, ui){
		ui.item.height("auto");
		$(".ui-state-highlight").height(ui.item.height()).width(ui.item.width());
    }
});

//Set active block
function setActiveBlock(object=null){
	//Save content for active block
	if ($(".block.active").length){
		var content = "";
		switch ($(".block.active").data("type")){
			case 0: content = tinymce.editors.mixed_content.getContent(); break;
			case 1: content = $("#built_module").val(); break;
			case 2: content = $("#page_variable").val(); break;
			case 3: content = $("#custom_module").val(); break;
			case 4: content = $("#custom_display").val(); break;
			case 5: content = $("#custom_form").val(); break;
		}
		$(".block.active").data("content", content);
		
		//Save attributes
		multipleDataBuild();
		$(".block.active").data("attributes", $("[name=block_attributes]").val());
	}
	
	//Set active block if set
	if (object.length){
		//Set active class
		$(".block").removeClass("active");
		object.addClass("active");
		
		//Set inputs data
		var widths = object.data("width");
		$("[name=block_width_md]").val(widths["md"]);
		$("[name=block_width_sm]").val(widths["sm"]);
		$("[name=block_width_xs]").val(widths["xs"]);
		$("[name=block_class]").val(object.data("class"));
		$("[name=block_type]").val(object.data("type")).trigger("change");
		$("[name=block_placeholder]").val(object.find("span").text());
		
		//Load attributes
		$("[data-multiple=block_attributes] [multiple-sortable] li:not([data-template])").remove();
		$("[name=block_attributes]").val(object.data("attributes"));
		if (object.data("attributes")){
			var jsonArray = JSON.parse(object.data("attributes"));
			jsonArray.forEach(function(entry){ multipleDataCreate("block_attributes", entry); });
		}

		//Set animation
		animationSetProperties($("[animation-bind=block_animation]"), btoa(object.data("animation")));
		
		//Set class
		classSetProperties($("[class-bind=block_class]"), object.data("class"));
		
		//Load block content
		switch (object.data("type")){
			case 0: tinymce.editors.mixed_content.setContent(object.data("content")); break;
			case 1: setSelectValue("#built_module", object.data("content")); $("#built_module").trigger("change"); break;
			case 2: $("#page_variable").val(object.data("content")); break;
			case 3: setSelectValue("#custom_module", object.data("content")); $("#custom_module").trigger("change"); break;
			case 4: setSelectValue("#custom_display", object.data("content")); $("#custom_display").trigger("change"); break;
			case 5: setSelectValue("#custom_form", object.data("content")); $("#custom_form").trigger("change"); break;
		}
		
		//Show content properties table
		$(".block_content").show();
	} else {
		$(".block_content").hide();
	}
}

//Insert new block
function insertBlock(data=null){
	var object = $("<div class=block><div class=head><i class='btn btn-primary btn-sm btn-square fas fa-arrows-alt' move></i>&nbsp;<i class='btn btn-danger btn-sm btn-square fas fa-times' remove></i></div><span></span></div>");
	
	//Existing block
	if (data){
		//Set block attributes and data
		object.attr("data-id", data.id);
		object.data("width", data.width);
		object.data("type", data.type);
		object.data("animation", atob(data.animation));
		object.data("class", data.class);
		object.data("attributes", atob(data.attributes));
		
		//Save available content
		var content = "";
		switch (data.type){
			case 0: content = $("<textarea>").html(data.content).text(); break;
			case 1: content = data.content; break;
			case 2: content = data.content; break;
			case 3: content = data.content; break;
			case 4: content = data.content; break;
			case 5: content = data.content; break;
		}
		object.data("content", content);
		
		//Set placeholder
		object.find("span").text(data.placeholder);
	
	//New block
	} else {
		object.attr("data-id", 0);
		object.data("width", { md: 100, sm: 100, xs: 100 });
		object.data("type", 0);
		object.data("animation", "");
		object.data("class", "");
		object.data("attributes", "");
		object.data("content", "");
		object.find("span").text("<?=readLanguage(builder,module_component)?>");	
		setActiveBlock(object);
	}

	//Append to container
	$(".blocks_container").append(object);
	
	//Update widths
	updateBlocksWidths();
}

//On block click
$(document).on("click", ".block", function(event){
	event.stopPropagation();
	setActiveBlock($(this));
});

//On block remove click
$(document).on("click", ".block [remove]", function(event){
	event.stopPropagation();
	$(this).parent().parent().remove();
	setActiveBlock($(".blocks_container .block:last-child"));
});

//===== Blocks content changes =====

//Block width change
$("[block-options]").on("change", function(){
	$(".blocks_container").css({
		"justify-content": $("[name=custom_justify]").val(),
		"align-items": $("[name=custom_align]").val(),
		"flex-wrap": $("[name=custom_wrap]").val()
	});
	$(".module_rows").attr("data-spacing", parseInt($("[json-fixed-data=custom_spacing] li:first-child select").val() / 2));
	updateBlocksWidths();
});

//Block placeholder change
$("[name=block_placeholder]").on("input", function(){
	$(".block.active").find("span").text($(this).val());
});

//Block width change
$("[name^=block_width]").on("change", function(){
	$(".block.active").data("width", {
		md: $("[name=block_width_md]").val(),
		sm: $("[name=block_width_sm]").val(),
		xs: $("[name=block_width_xs]").val()
	});
	updateBlocksWidths();
});

//Block class change
$("[name=block_class]").on("input", function(){
	$(".block.active").data("class", $(this).val());
});

//Block type change
$("[name=block_type]").on("change", function(){
	//If type has changed, reset current content
	var targetType = parseInt($(this).val());
	var currentType = $(".block.active").data("type");
	if (currentType && targetType != currentType){
		tinymce.activeEditor.setContent("");
		setSelectValue("custom_module", "");
		setSelectValue("built_module", "");
		$("#page_variable").val("");
	}
	
	//Set new type
	$(".block.active").data("type", parseInt($(this).val()));
	
	//Show relative content row
	$("[data-content]").hide();
	$("[data-content=" + $(this).val() + "]").show();
});

//Block animation change
$(document).on("change", "[name=block_animation]", function(){
	$(".block.active").data("animation", $(this).val());
});

//Block class change
$(document).on("change", "[name=block_class]", function(){
	$(".block.active").data("class", $(this).val());
});

//===== Functions =====

//Update block widths
function updateBlocksWidths(){
	var spacing = $(".module_rows").attr("data-spacing");
	$(".module_rows").css("padding", spacing + "px");
	$(".block").each(function(){
		var percentage = $(this).data("width")["md"];
		$(this).css({
			"width": "calc(" + percentage + "% - " + (spacing * 2) + "px",
			"margin": spacing + "px"
		});
	});
}

//===== Load & Build =====

//Load current module contents
<? if ($entry){
	$blocks_result = mysqlQuery("SELECT * FROM $mysqltable_custom WHERE module_uniqid='" . $entry["uniqid"] . "' ORDER BY arrangement ASC");
	while ($block_entry = mysqlFetch($blocks_result)){
		$data = json_encode($block_entry, JSON_UNESCAPED_UNICODE); ?>
		insertBlock({
			id: <?=($edit ? $block_entry["id"] : 0)?>,
			placeholder: "<?=$block_entry["placeholder"]?>",
			width: <?=$block_entry["width"]?>,
			type: <?=$block_entry["type"]?>,
			animation: "<?=base64_encode($block_entry["animation"])?>",
			class: "<?=$block_entry["class"]?>",
			attributes: "<?=base64_encode($block_entry["attributes"])?>",
			content: "<?=str_replace(array("\r\n","\r","\n"), "", $block_entry["content"])?>"
		});
	<? } ?>
<? } ?>

//Trigger change on block options and update widths
$("[block-options]").trigger("change");
updateBlocksWidths();

//===== Submit =====

//Build before validation
function onBeforeValidation(){
	//Blocks
	var blocks = [];
	
	//Re-trigger active block to save content changes
	setActiveBlock($(".block.active"));
	
	var arrangement = 0;
	$(".block").each(function(){
		var block_data = {};
		
		block_data.id = $(this).attr("data-id");
		block_data.placeholder = $(this).find("span").text();
		block_data.width = $(this).data("width");
		block_data.type = $(this).data("type");
		block_data.animation = $(this).data("animation");
		block_data.class = $(this).data("class");
		block_data.attributes = $(this).data("attributes");
		block_data.content = $(this).data("content");
		
		arrangement++;
		block_data.arrangement = arrangement;
		
		blocks.push(block_data);
	});
	
	$("#blocks").val(JSON.stringify(blocks));
}
</script>

<!-- Include animation selection snippet -->
<? include "includes/_select_animation.php"; ?>

<!-- Include class selection snippet -->
<? include "includes/_select_class.php"; ?>

<? if (!$inline_page){ ?>
<div class=crud_separator></div>
<?
$custom_list = array(
	["label"=>readLanguage(builder,duplicate), "icon"=>"fas fa-clone", "href"=>"$base_name.php?clone=%s"],
	["label"=>readLanguage(crud,button_export), "icon"=>"fas fa-download", "click"=>"exportBuilder('module', '%d[uniqid]', '%d[placeholder]')"],
);
$custom_list = htmlentities(json_encode($custom_list, JSON_UNESCAPED_UNICODE));

$crud_data["order_field"] = "priority";
$crud_data["order_by"] = "priority DESC";
$crud_data["delete_record_message"] = "placeholder";
$crud_data["buttons"] = array(true,true,false,true,true); //Add - Search - View - Edit - Delete
$crud_data["columns"] = array(
	array("uniqid",readLanguage(builder,selector),"200px","center",null,false,true),
	array("type",readLanguage(builder,type),"200px","center","implodeVariable('%s','data_module_types')",true,false),
	array("placeholder",readLanguage(builder,placeholder),"300px","center",null,false,true),
	array("tags",readLanguage(builder,tags),"300px","center","implodeVariable('%s')",true,false),
	array("id", readLanguage(crud,button_operations), "140px", "fixed-right", "customDropdown(\"$custom_list\", '<i class=\"fas fa-cogs\"></i>&nbsp;" . readLanguage(crud,button_operations) . "')", false, true),
);
require_once("crud/crud.php");
?>
<? } ?>

<? include "_footer.php"; ?>