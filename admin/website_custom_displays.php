<? include "system/_handler.php";

$mysqltable = $suffix . "website_custom_displays";
$base_name = basename($_SERVER["SCRIPT_FILENAME"],".php");
checkPermissions($base_name);

//==== DELETE Record ====
if ($delete){
	mysqlQuery("DELETE FROM $mysqltable WHERE id=$delete");
	if (mysqlAffectedRows()){ $success = readLanguage(records,deleted); } else { $error = readLanguage(records,unavailable); }

//==== ADD Record ====
} else if ($post["token"] && !$edit){
	switch ($post["source"]){
		case 0: $source_content = $post["page_id"]; break;
		case 1: $source_content = implode(",", $post["pages_ids"]); break;
		case 2: $source_content = $post["modules_layout"]; break;
		case 3: $source_content = $post["custom_query"]; break;
	}
	$query = "INSERT INTO $mysqltable (
		uniqid,
		placeholder,
		tags,
		type,
		source,
		source_content,
		blocks_template,
		grid_justify,
		grid_align,
		grid_blocks_count,
		grid_blocks_per_row,
		grid_blocks_spacing,
		grid_blocks_class,
		grid_blocks_animation,
		slides_per_view,
		slides_per_column,
		slides_container_class,
		slides_space_between,
		slides_effect,
		slides_speed,
		slides_cover_flow,
		slides_creative,
		slides_auto_play,
		slides_center,
		slides_loop,
		slides_count,
		slide_class,
		slide_animation,
		slide_auto_height,
		slide_stretch_height,
		arrows_enable,
		arrows_class,
		arrows_spacing,
		bullets_enable,
		bullets_class,
		bullets_container_class,
		priority
	) VALUES (
		'" . $post["uniqid"] . "',
		'" . $post["placeholder"] . "',
		'" . implode(",", $post["tags"]) . "',
		'" . $post["type"] . "',
		'" . $post["source"] . "',
		'" . $source_content . "',
		'" . $post["blocks_template"] . "',
		'" . $post["grid_justify"] . "',
		'" . $post["grid_align"] . "',
		'" . $post["grid_blocks_count"] . "',
		'" . $post["grid_blocks_per_row"] . "',
		'" . $post["grid_blocks_spacing"] . "',
		'" . $post["grid_blocks_class"] . "',
		'" . $post["grid_blocks_animation"] . "',
		'" . $post["slides_per_view"] . "',
		'" . $post["slides_per_column"] . "',
		'" . $post["slides_container_class"] . "',
		'" . $post["slides_space_between"] . "',
		'" . $post["slides_effect"] . "',
		'" . $post["slides_speed"] . "',
		'" . ($post["slides_effect"]==2 ? $post["slides_cover_flow"] : "") . "',
		'" . ($post["slides_effect"]==1 ? $post["slides_creative"] : "") . "',
		'" . ($post["slides_auto_play_enable"] ? $post["slides_auto_play"] : "") . "',
		'" . $post["slides_center"] . "',
		'" . $post["slides_loop"] . "',
		'" . $post["slides_count"] . "',
		'" . $post["slide_class"] . "',
		'" . $post["slide_animation"] . "',
		'" . $post["slide_auto_height"] . "',
		'" . $post["slide_stretch_height"] . "',
		'" . $post["arrows_enable"] . "',
		'" . ($post["arrows_enable"] ? $post["arrows_class"] : "") . "',
		'" . ($post["arrows_enable"] ? $post["arrows_spacing"] : "") . "',
		'" . $post["bullets_enable"] . "',
		'" . ($post["bullets_enable"] ? $post["bullets_class"] : "") . "',
		'" . ($post["bullets_enable"] ? $post["bullets_container_class"] : "") . "',
		'" . newRecordID($mysqltable) . "'
	)";
	mysqlQuery($query);

	$success = readLanguage(records,added);

//==== EDIT Record ====
} else if ($post["token"] && $edit){
	$record_data = getID($edit, $mysqltable);
	switch ($post["source"]){
		case 0: $source_content = $post["page_id"]; break;
		case 1: $source_content = implode(",", $post["pages_ids"]); break;
		case 2: $source_content = $post["modules_layout"]; break;
		case 3: $source_content = $post["custom_query"]; break;
	}
	$query = "UPDATE $mysqltable SET
		placeholder='" . $post["placeholder"] . "',
		tags='" . implode(",", $post["tags"]) . "',
		type='" . $post["type"] . "',
		source='" . $post["source"] . "',
		source_content='" . $source_content . "',
		blocks_template='" . $post["blocks_template"] . "',
		grid_justify='" . $post["grid_justify"] . "',
		grid_align='" . $post["grid_align"] . "',
		grid_blocks_count='" . $post["grid_blocks_count"] . "',
		grid_blocks_per_row='" . $post["grid_blocks_per_row"] . "',
		grid_blocks_spacing='" . $post["grid_blocks_spacing"] . "',
		grid_blocks_class='" . $post["grid_blocks_class"] . "',
		grid_blocks_animation='" . $post["grid_blocks_animation"] . "',
		slides_per_view='" . $post["slides_per_view"] . "',
		slides_per_column='" . $post["slides_per_column"] . "',
		slides_container_class='" . $post["slides_container_class"] . "',
		slides_space_between='" . $post["slides_space_between"] . "',
		slides_effect='" . $post["slides_effect"] . "',
		slides_speed='" . $post["slides_speed"] . "',
		slides_cover_flow='" . ($post["slides_effect"]==2 ? $post["slides_cover_flow"] : "") . "',
		slides_creative='" . ($post["slides_effect"]==1 ? $post["slides_creative"] : "") . "',
		slides_auto_play='" . ($post["slides_auto_play_enable"] ? $post["slides_auto_play"] : "") . "',
		slides_center='" . $post["slides_center"] . "',
		slides_loop='" . $post["slides_loop"] . "',
		slides_count='" . $post["slides_count"] . "',
		slide_class='" . $post["slide_class"] . "',
		slide_animation='" . $post["slide_animation"] . "',
		slide_auto_height='" . $post["slide_auto_height"] . "',
		slide_stretch_height='" . $post["slide_stretch_height"] . "',
		arrows_enable='" . $post["arrows_enable"] . "',
		arrows_class='" . ($post["arrows_enable"] ? $post["arrows_class"] : "") . "',
		arrows_spacing='" . ($post["arrows_enable"] ? $post["arrows_spacing"] : "") . "',
		bullets_enable='" . $post["bullets_enable"] . "',
		bullets_class='" . ($post["bullets_enable"] ? $post["bullets_class"] : "") . "',
		bullets_container_class='" . ($post["bullets_enable"] ? $post["bullets_container_class"] : "") . "'
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
	$action = "$base_name.php" . rebuildQueryParameters(array("delete","token","clone"));
} else {
	$button = readLanguage(records,add);
	$action = "$base_name.php" . rebuildQueryParameters(array("delete","token","edit","clone"));
	if ($error){ foreach ($_POST as $key => $value){ $entry[$key] = $value; } }
}
if ($success){ $message = "<div class='alert alert-success'>$success</div>"; }
if ($error){ $message = "<div class='alert alert-danger'>$error</div>"; }

//Read cloned parameter
if ($get["clone"]){
	$entry = getID($get["clone"],$mysqltable);
}

//Set name variable
$display_uniqid = ($edit ? $entry["uniqid"] : "display-" . uniqid());

include "_header.php";?>

<script src="../plugins/fixed-data.js?v=<?=$system_settings["system_version"]?>"></script>

<div class=title><?=getPageTitle($base_name)?></div>
<?=$message?>

<form method=post enctype="multipart/form-data" action="<?=$action?>">
<input type=hidden name=token value="<?=$token?>">
<input type=hidden name=uniqid value="<?=$display_uniqid?>">
<input type=hidden name=modules_layout>

<div class=subtitle><?=readLanguage(builder,display_settings)?></div>
<div class=data_table_container><table class=data_table>
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
	<td class=title><?=readLanguage(builder,display_type)?>: <i class=requ></i></td>
	<td>
		<div class=radio_container id=type>
			<? foreach ($data_displays_types AS $key=>$value){
				print "<label><input name=type type=radio value='$key'><span>$value</span></label>";
			} ?>
		</div>
		<script>
		$("#type").find("[value='<?=($edit ? $entry["type"] : 0)?>']").prop("checked",true);
		$("#type input[type=radio]").on("change", function(){
			toggleVisibility($(this));
		});
		$(document).ready(function(){
			$("#type").find("[value='<?=($edit ? $entry["type"] : 0)?>']").prop("checked", true).trigger("change");
		});
		</script>
	</td>
	<td class=title><?=readLanguage(builder,display_source)?>: <i class=requ></i></td>
	<td>
		<select name=source id=source onchange="toggleVisibility(this)"><?=populateOptions($data_displays_sources)?></select>
		<script>
		setSelectValue("#source", "<?=$entry["source"]?>");
		$(document).ready(function(){
			toggleVisibility($("#source")[0]);
		});
		</script>
	</td>	
</tr>
<tr visibility-control=source visibility-value=0>
	<td class=title><?=readLanguage(builder,display_source)?>: <i class=requ></i></td>
	<td colspan=3>
		<select name=page_id id=page_id>
		<option value=""></option>
		<? $result_pages = null; $result = mysqlQuery("SELECT * FROM " . $suffix . "website_pages_custom t1 WHERE type=2 OR (type=0 AND (SELECT COUNT(id) FROM " . $suffix . "website_pages_custom WHERE parent=t1.id) > 0) ORDER BY parent ASC, priority DESC");
		if (mysqlNum($result)){
			while ($page = mysqlFetch($result)){
				$count = mysqlNum(mysqlQuery("SELECT id FROM " . $suffix . "website_pages_custom WHERE parent=" . $page["id"]));
				$result_pages .= "<option value='" . $page["id"] . "' data-count='$count'>" . customPagePathRender(customPagePath($page["id"])) . "</option>";	
			}
		}
		print $result_pages; ?>
		</select>
		<script>
			<? if ($entry["source"]==0){ ?>setSelectValue("#page_id", "<?=$entry["source_content"]?>");<? } ?>
			$("#page_id").select2({
				placeholder: "<?=readLanguage(operations,select)?>",
				templateResult: function(state){
					var $state = $("<div>" + state.text + "&nbsp;&nbsp;<small style='color:#808080'>(" + $(state.element).data("count") + " Pages)</small></div>");
					return $state;
				}
			});
		</script>
	</td>
</tr>
<tr visibility-control=source visibility-value=1>
	<td class=title><?=readLanguage(builder,display_source)?>: <i class=requ></i></td>
	<td colspan=3>
		<select name=pages_ids[] multiple id=pages_ids>
		<? $result_pages = null; $result = mysqlQuery("SELECT * FROM " . $suffix . "website_pages_custom ORDER BY parent ASC, priority DESC");
		if (mysqlNum($result)){
			while ($page = mysqlFetch($result)){
				$result_pages .= "<option value='" . $page["id"] . "'>" . customPagePathRender(customPagePath($page["id"])) . "</option>";	
			}
		}
		print $result_pages; ?>
		</select>
		<script>
			<? if ($entry["source"]==2){ ?>$("#pages_ids").val([<?=$entry["source_content"]?>]);<? } ?>
			$("#pages_ids").select2();
		</script>
	</td>
</tr>
<tr visibility-control=source visibility-value=3>
	<td class=title><?=readLanguage(builder,display_source)?>: <i class=requ></i></td>
	<td colspan=3>
		<select name=custom_query id=custom_query>
			<option value=""></option>
			<?=populateData("SELECT * FROM system_queries", "id", "title")?>
		</select>
		<script>
			<? if ($entry["source"]==3){ ?>$("#custom_query").val([<?=$entry["source_content"]?>]);<? } ?>
			$("#custom_query").select2({
				placeholder: "<?=readLanguage(operations,select)?>",
			});
		</script>
	</td>
</tr>
<tr visibility-control=source visibility-value="0,1,3,4">
	<td class=title><?=readLanguage(builder,blocks_template)?>: <i class=requ></i></td>
	<td colspan=3>
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
</tr>
</table></div>

<!-- Modules -->
<div visibility-control=source visibility-value=2 class=margin-top>
<div class=subtitle><?=readLanguage(builder,display_modules)?></div>
<? $modules_input = "[name=modules_layout]";
$modules_entry = ($entry["source"]==2 ? $entry["source_content"] : null);
$modules_content = false;
$modules_type = 2;
include "includes/_select_modules.php"; ?>
</div>

<!-- Grid -->
<div class="margin-top" visibility-control=type visibility-value=0>
<div class=subtitle><?=readLanguage(builder,display_grid_settings)?></div>
<table class=data_table>
<tr>
	<td class=title><?=readLanguage(builder,blocks_count)?>: <i class=requ></i></td>
	<td colspan=3>
		<input type=number name=grid_blocks_count value="<?=($edit ? $entry["grid_blocks_count"] : 5)?>" maxlength=3 data-validation=number data-validation-allowing="range[0;999]">
	</td>
</tr>
<tr>
	<td class=title><?=readLanguage(builder,blocks_row)?>: <i class=requ></i></td>
	<td>
		<input type=hidden name=grid_blocks_per_row id=grid_blocks_per_row>
		<ul class=inline_input json-fixed-data=grid_blocks_per_row>
		<? foreach ($data_screen_sizes AS $size=>$icon){
			print "<li><div class=input-addon><span before><i class='$icon'></i></span><select data-name='$size'>"; ?>
			<option value=1>1</option><option value=2>2</option><option value=3>3</option><option value=4>4</option>
			<option value=5>5</option><option value=6>6</option>
			<? print "</select></div></li>";
		} ?>
		</ul>
		<? if ($entry["grid_blocks_per_row"]){ ?><script>fixedDataRead("grid_blocks_per_row", <?=$entry["grid_blocks_per_row"]?>)</script><? } ?>
	</td>
	<td class=title><?=readLanguage(builder,blocks_spacing)?>:</td>
	<td>
		<div class="d-flex align-items-center">
			<input type=hidden name=grid_blocks_spacing id=grid_blocks_spacing>
			<ul class=inline_input json-fixed-data=grid_blocks_spacing>
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
			<? if ($entry["grid_blocks_spacing"]){ ?>fixedDataRead("grid_blocks_spacing", <?=$entry["grid_blocks_spacing"]?>);<? } ?>
			<? if (!$edit){ ?>
			$("[json-fixed-data=grid_blocks_spacing] li:first-child select").on("change", function(){
				$("[json-fixed-data=grid_blocks_spacing] li:not(:first-child) select").val($(this).val());
			});
			<? } ?>
			</script>
		</div>
	</td>
</tr>
<tr>
	<td class=title><?=readLanguage(builder,grid_blocks_class)?>:</td>
	<td>
		<div class=class_input class-bind=grid_blocks_class class-properties="<?=$entry["grid_blocks_class"]?>"></div>
	</td>
	<td class=title><?=readLanguage(builder,animation)?>:</td>
	<td>
		<div class=animation_input animation-bind=grid_blocks_animation animation-properties="<?=base64_encode($entry["grid_blocks_animation"])?>"></div>
	</td>
</tr>
<tr>
	<td class=title><?=readLanguage(builder,flex_justify)?>:</td>
	<td>
		<select block-options name=grid_justify id=grid_justify>
			<option value=flex-start><?=readLanguage(builder,flex_justify_start)?></option>
			<option value=flex-end><?=readLanguage(builder,flex_justify_end)?></option>
			<option value=center><?=readLanguage(builder,flex_justify_center)?></option>
			<option value=space-between><?=readLanguage(builder,flex_justify_space_between)?></option>
			<option value=space-around><?=readLanguage(builder,flex_justify_space_around)?></option>
			<option value=space-evenly><?=readLanguage(builder,flex_justify_space_evenly)?></option>
		</select>
		<? if ($entry["grid_justify"]){ ?><script>setSelectValue("#grid_justify","<?=$entry["grid_justify"]?>")</script><? } ?>
	</td>
	<td class=title><?=readLanguage(builder,flex_align)?>:</td>
	<td>
		<select block-options name=grid_align id=grid_align>
			<option value=stretch><?=readLanguage(builder,flex_align_stretch)?></option>
			<option value=center><?=readLanguage(builder,flex_align_center)?></option>
			<option value=flex-start><?=readLanguage(builder,flex_align_start)?></option>
			<option value=flex-end><?=readLanguage(builder,flex_align_end)?></option>
			<option value=baseline><?=readLanguage(builder,flex_align_baseline)?></option>
		</select>
		<? if ($entry["grid_align"]){ ?><script>setSelectValue("#grid_align","<?=$entry["grid_align"]?>")</script><? } ?>
	</td>
</tr>
</table>
</div>

<!-- Slider -->
<div class=margin-top visibility-control=type visibility-value=1>
<div class=subtitle><?=readLanguage(builder,display_slider_settings)?></div>
<div class=data_table_container><table class=data_table>
<tr>
	<td class=title><?=readLanguage(builder,slides_row)?>: <i class=requ></i></td>
	<td>
		<select name=slides_per_view id=slides_per_view>
			<option value=0><?=readLanguage(builder,auto)?></option>
			<option value=1>1</option><option value=2>2</option><option value=3>3</option><option value=4>4</option>
			<option value=5>5</option><option value=6>6</option><option value=7>7</option><option value=8>8</option>
		</select>
		<script>setSelectValue("#slides_per_view", "<?=($entry["slides_per_view"] ? $entry["slides_per_view"] : 4)?>")</script>
	</td>
	<td class=title><?=readLanguage(builder,slides_column)?>: <i class=requ></i></td>
	<td>
		<select name=slides_per_column id=slides_per_column>
			<option value=1>1</option><option value=2>2</option><option value=3>3</option><option value=4>4</option>
			<option value=5>5</option><option value=6>6</option><option value=7>7</option><option value=8>8</option>
		</select>
		<script>setSelectValue("#slides_per_column", "<?=$entry["slides_per_column"]?>")</script>
	</td>
</tr>
<tr>
	<td class=title><?=readLanguage(builder,slides_total)?>: <i class=requ></i></td>
	<td>
		<input type=number name=slides_count value="<?=($edit ? $entry["slides_count"] : 12)?>" maxlength=2 data-validation=number data-validation-allowing="range[1;99]">
	</td>
	<td class=title><?=readLanguage(builder,slides_spacing)?>: <i class=requ></i></td>
	<td>
		<input type=number name=slides_space_between value="<?=($edit ? $entry["slides_space_between"] : 20)?>" maxlength=3 data-validation=number data-validation-allowing="range[0;999]">
	</td>
</tr>
<tr>
	<td class=title><?=readLanguage(builder,slides_center)?>:</td>
	<td>
		<div class=switch><label><?=$data_disabled_enabled[0]?><input type=checkbox name=slides_center value=1 <?=($entry["slides_center"] ? "checked" : "")?>><span class=lever></span><?=$data_disabled_enabled[1]?></label></div>
	</td>
	<td class=title><?=readLanguage(builder,slides_loop)?>:</td>
	<td>
		<div class=switch><label><?=$data_disabled_enabled[0]?><input type=checkbox name=slides_loop value=1 <?=($entry["slides_loop"] ? "checked" : "")?>><span class=lever></span><?=$data_disabled_enabled[1]?></label></div>
	</td>
</tr>
<tr>
	<td class=title><?=readLanguage(builder,slides_auto_height)?>:</td>
	<td>
		<div class=switch><label><?=$data_disabled_enabled[0]?><input type=checkbox name=slide_auto_height value=1 <?=($entry["slide_auto_height"] ? "checked" : "")?>><span class=lever></span><?=$data_disabled_enabled[1]?></label></div>
	</td>
	<td class=title><?=readLanguage(builder,slides_stretch_height)?>:</td>
	<td>
		<div class=switch><label><?=$data_disabled_enabled[0]?><input type=checkbox name=slide_stretch_height value=1 <?=($entry["slide_stretch_height"] ? "checked" : "")?>><span class=lever></span><?=$data_disabled_enabled[1]?></label></div>
	</td>
</tr>
<tr>
	<td class=title><?=readLanguage(builder,slides_class)?>:</td>
	<td>
		<div class=class_input class-bind=slide_class class-properties="<?=$entry["slide_class"]?>"></div>
	</td>
	<td class=title><?=readLanguage(builder,animation)?>:</td>
	<td>
		<div class=animation_input animation-bind=slide_animation animation-properties="<?=base64_encode($entry["slide_animation"])?>"></div>
	</td>
</tr>
<tr>
	<td class=title><?=readLanguage(builder,slides_container_class)?>:</td>
	<td>
		<div class=class_input class-bind=slides_container_class class-properties="<?=$entry["slides_container_class"]?>"></div>
	</td>
	<td class=title><?=readLanguage(builder,slides_play)?>:</td>
	<td colspan=3>
		<div class=switch><label><?=$data_disabled_enabled[0]?><input type=checkbox name=slides_auto_play_enable id=slides_auto_play_enable onchange="toggleVisibility(this)" value=1 <?=($entry["slides_auto_play"] ? "checked" : "")?>><span class=lever></span><?=$data_disabled_enabled[1]?></label></div>
		<script>
		$(document).ready(function(){
			toggleVisibility($("#slides_auto_play_enable")[0])
		});
		</script>
	</td>
</tr>
<tr>
	<td class=title><?=readLanguage(builder,slides_effect)?>:</td>
	<td>
		<select name=slides_effect id=slides_effect onchange="toggleVisibility(this)">
		<?=populateOptions($data_displays_slides_effect)?>
		</select>
		<div visibility-control=slides_effect visibility-value=3 class=input_description><?=readLanguage(builder,slides_fade_note)?></div>
		<script>
		setSelectValue("#slides_effect", "<?=$entry["slides_effect"]?>");
		$(document).ready(function(){
			toggleVisibility($("#slides_effect")[0]);
		});
		</script>
	</td>
	<td class=title><?=readLanguage(builder,slides_speed)?>:</td>
	<td>
		<div class=input-addon><input type=number name=slides_speed value="<?=$entry["slides_speed"]?>"><span after>ms</span></div>
	</td>
</tr>
<tr visibility-control=slides_effect visibility-value=1>
	<td class=title><?=readLanguage(builder,slides_custom_settings)?>:</td>
	<td colspan=3>
		<input type=hidden name=slides_creative id=slides_creative>
		<ul class=inline_input json-fixed-data=slides_creative>
			<li style="flex-basis: 100px"><span><p><?=readLanguage(builder,slides_custom_limit)?></p><input type=number data-name=limit></span></li>
			<li style="flex-basis: 100px"><span><p><?=readLanguage(builder,slides_custom_perspective)?></p><select data-name=perspective><option value=0><?=$data_disabled_enabled[0]?></option><option value=1><?=$data_disabled_enabled[1]?></option></select></span></li>
			<li style="flex-basis: 100px"><span><p><?=readLanguage(builder,slides_custom_shadow_progress)?></p><select data-name=shadow><option value=0><?=$data_disabled_enabled[0]?></option><option value=1><?=$data_disabled_enabled[1]?></option></select></span></li>
			<li style="flex-basis:100%">
				<span>
					<p><b><?=readLanguage(builder,slides_custom_prev)?></b></p>
					<ul class=inline_input>
						<li style="flex-basis: 100px"><span><p><?=readLanguage(builder,slides_custom_translate)?></p><div class=d-flex><input type=text data-name=prev_translate_x placeholder="X">&nbsp;<input type=text data-name=prev_translate_y placeholder="Y">&nbsp;<input type=text data-name=prev_translate_z placeholder="Z"></div></span></li>
						<li style="flex-basis: 100px"><span><p><?=readLanguage(builder,slides_custom_rotate)?></p><div class=d-flex><input type=number data-name=prev_rotate_x placeholder="X">&nbsp;<input type=number data-name=prev_rotate_y placeholder="Y">&nbsp;<input type=number data-name=prev_rotate_z placeholder="Z"></div></span></li>
						<li style="flex-basis: 100px"><span><p><?=readLanguage(builder,slides_custom_opacity)?></p><input type=number data-name=prev_opacity></span></li>
						<li style="flex-basis: 100px"><span><p><?=readLanguage(builder,slides_custom_scale)?></p><input type=number data-name=prev_scale></span></li>
						<li style="flex-basis: 100px"><span><p><?=readLanguage(builder,slides_custom_shadow)?></p><select data-name=prev_shadow><option value=0><?=$data_disabled_enabled[0]?></option><option value=1><?=$data_disabled_enabled[1]?></option></select></span></li>
						<li style="flex-basis: 100px"><span><p><?=readLanguage(builder,slides_custom_origin)?></p><input type=text data-name=prev_origin></span></li>
					</ul>
				</span>
			</li>
			<li style="flex-basis:100%">
				<span>
					<p><b><?=readLanguage(builder,slides_custom_next)?></b></p>
					<ul class=inline_input>
						<li style="flex-basis: 100px"><span><p><?=readLanguage(builder,slides_custom_translate)?></p><div class=d-flex><input type=text data-name=next_translate_x placeholder="X">&nbsp;<input type=text data-name=next_translate_y placeholder="Y">&nbsp;<input type=text data-name=next_translate_z placeholder="Z"></div></span></li>
						<li style="flex-basis: 100px"><span><p><?=readLanguage(builder,slides_custom_rotate)?></p><div class=d-flex><input type=number data-name=next_rotate_x placeholder="X">&nbsp;<input type=number data-name=next_rotate_y placeholder="Y">&nbsp;<input type=number data-name=next_rotate_z placeholder="Z"></div></span></li>
						<li style="flex-basis: 100px"><span><p><?=readLanguage(builder,slides_custom_opacity)?></p><input type=number data-name=next_opacity></span></li>
						<li style="flex-basis: 100px"><span><p><?=readLanguage(builder,slides_custom_scale)?></p><input type=number data-name=next_scale></span></li>
						<li style="flex-basis: 100px"><span><p><?=readLanguage(builder,slides_custom_shadow)?></p><select data-name=next_shadow><option value=0><?=$data_disabled_enabled[0]?></option><option value=1><?=$data_disabled_enabled[1]?></option></select></span></li>
						<li style="flex-basis: 100px"><span><p><?=readLanguage(builder,slides_custom_origin)?></p><input type=text data-name=next_origin></span></li>
					</ul>
				</span>
			</li>
		</ul>
		<? if ($entry["slides_creative"]){ ?><script>fixedDataRead("slides_creative", <?=$entry["slides_creative"]?>)</script><? } ?>
	</td>
</tr>
<tr visibility-control=slides_effect visibility-value=2>
	<td class=title><?=readLanguage(builder,slides_3d_settings)?>:</td>
	<td colspan=3>
		<input type=hidden name=slides_cover_flow id=slides_cover_flow>
		<ul class=inline_input json-fixed-data=slides_cover_flow>
			<li style="flex-basis: 100px"><span><p><?=readLanguage(builder,slides_3d_depth)?></p><div class=input-addon><input type=number data-name=depth><span after>px</span></div></span></li>
			<li style="flex-basis: 100px"><span><p><?=readLanguage(builder,slides_3d_rotation)?></p><div class=input-addon><input type=number data-name=rotate><span after>deg</span></div></span></li>
			<li style="flex-basis: 100px"><span><p><?=readLanguage(builder,slides_3d_stretch)?></p><div class=input-addon><input type=number data-name=stretch><span after>px</span></div></span></li>
			<li style="flex-basis: 100px"><span><p><?=readLanguage(builder,slides_3d_shadow)?></p><select data-name=shadows><option value=0><?=$data_disabled_enabled[0]?></option><option value=1><?=$data_disabled_enabled[1]?></option></select></span></li>
		</ul>
		<? if ($entry["slides_cover_flow"]){ ?><script>fixedDataRead("slides_cover_flow", <?=$entry["slides_cover_flow"]?>)</script><? } ?>
	</td>
</tr>
<tr visibility-control=slides_auto_play_enable visibility-value=1>
	<td class=title><?=readLanguage(builder,slides_play_settings)?>:</td>
	<td colspan=3>
		<input type=hidden name=slides_auto_play id=slides_auto_play>
		<ul class=inline_input json-fixed-data=slides_auto_play>
			<li style="flex-basis: 100px"><span><p><?=readLanguage(builder,slides_play_delay)?></p><div class=input-addon><input type=number data-name=delay><span after>ms</span></div></span></li>
			<li style="flex-basis: 100px"><span><p><?=readLanguage(builder,slides_play_disable)?></p><select data-name=disable_interaction><option value=0><?=$data_disabled_enabled[0]?></option><option value=1><?=$data_disabled_enabled[1]?></option></select></span></li>
			<li style="flex-basis: 100px"><span><p><?=readLanguage(builder,slides_play_pause)?></p><select data-name=pause_mouse><option value=0><?=$data_disabled_enabled[0]?></option><option value=1><?=$data_disabled_enabled[1]?></option></select></span></li>
			<li style="flex-basis: 100px"><span><p><?=readLanguage(builder,slides_play_reverse)?></p><select data-name=reverse_direction><option value=0><?=$data_disabled_enabled[0]?></option><option value=1><?=$data_disabled_enabled[1]?></option></select></span></li>
		</ul>
		<? if ($entry["slides_auto_play"]){ ?><script>fixedDataRead("slides_auto_play", <?=$entry["slides_auto_play"]?>)</script><? } ?>
	</td>
</tr>
</table></div>

<div class=subtitle><?=readLanguage(builder,slides_arrows_bullets)?></div>
<div class=data_table_container><table class=data_table>
<tr>
	<td class=title><?=readLanguage(builder,slides_enable_arrows)?>:</td>
	<td>
		<div class=switch><label><?=$data_disabled_enabled[0]?><input type=checkbox name=arrows_enable id=arrows_enable onchange="toggleVisibility(this)" value=1 <?=($entry["arrows_enable"] ? "checked" : "")?>><span class=lever></span><?=$data_disabled_enabled[1]?></label></div>
		<script>
		$(document).ready(function(){
			toggleVisibility($("#arrows_enable")[0])
		});
		</script>
	</td>
	<td class=title><?=readLanguage(builder,slides_enable_bullets)?>:</td>
	<td>
		<div class=switch><label><?=$data_disabled_enabled[0]?><input type=checkbox name=bullets_enable id=bullets_enable onchange="toggleVisibility(this)" value=1 <?=($entry["bullets_enable"] ? "checked" : "")?>><span class=lever></span><?=$data_disabled_enabled[1]?></label></div>
		<script>
		$(document).ready(function(){
			toggleVisibility($("#bullets_enable")[0])
		});
		</script>
	</td>
</tr>
<tr visibility-control=arrows_enable visibility-value=1>
	<td class=title><?=readLanguage(builder,slides_arrows_class)?>:</td>
	<td>
		<div class=class_input class-bind=arrows_class class-properties="<?=$entry["arrows_class"]?>"></div>
	</td>
	<td class=title><?=readLanguage(builder,slides_arrows_spacing)?>:</td>
	<td>
		<div class=input-addon><input type=number name=arrows_spacing value="<?=$entry["arrows_spacing"]?>"><span after>px</span></div>
	</td>
</tr>
<tr visibility-control=bullets_enable visibility-value=1>
	<td class=title><?=readLanguage(builder,slides_bullets_class)?>:</td>
	<td>
		<div class=class_input class-bind=bullets_class class-properties="<?=$entry["bullets_class"]?>"></div>
	</td>
	<td class=title><?=readLanguage(builder,slides_bullets_container_class)?>:</td>
	<td>
		<div class=class_input class-bind=bullets_container_class class-properties="<?=$entry["bullets_container_class"]?>"></div>
	</td>
</tr>
</table></div>
</div>

<div class=submit_container><input type=button class=submit value="<?=$button?>"></div>
</form>

<!-- Include animation selection snippet -->
<? include "includes/_select_animation.php"; ?>

<!-- Include class selection snippet -->
<? include "includes/_select_class.php"; ?>

<div class=crud_separator></div>
<?
$custom_list = array(
	["label"=>readLanguage(builder,duplicate), "icon"=>"fas fa-clone", "href"=>"$base_name.php?clone=%s"],
	["label"=>readLanguage(crud,button_export), "icon"=>"fas fa-download", "click"=>"exportBuilder('display', '%d[uniqid]', '%d[placeholder]')"],
);
$custom_list = htmlentities(json_encode($custom_list, JSON_UNESCAPED_UNICODE));

$crud_data["order_field"] = "priority";
$crud_data["order_by"] = "priority DESC";
$crud_data["delete_record_message"] = "placeholder";
$crud_data["buttons"] = array(true,true,false,true,true); //Add - Search - View - Edit - Delete
$crud_data["columns"] = array(
	array("type", "Type", "120px", "center", "getVariable('data_displays_types')[%s]", true, false), 
	array("uniqid", readLanguage(builder,selector), "200px", "center", null, false, true), 
	array("placeholder", readLanguage(builder,placeholder), "100%", "center", null, false, true), 
	array("tags", readLanguage(builder,tags), "300px", "center", "implodeVariable('%s')", true, false), 
	array("id", readLanguage(crud,button_operations), "140px", "fixed-right", "customDropdown(\"$custom_list\", '<i class=\"fas fa-cogs\"></i>&nbsp;" . readLanguage(crud,button_operations) . "')", false, true), 
);
require_once("crud/crud.php");
?>

<? include "_footer.php";