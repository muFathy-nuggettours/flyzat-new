<? include "system/_handler.php";

$mysqltable = $suffix . "website_pages_custom";
$base_name = basename($_SERVER["SCRIPT_FILENAME"], ".php");
checkPermissions($base_name);

//AJAX Request [Change parent]
if ($post["token"] && $post["action"]=="parent"){
	//Update parent
	mysqlQuery("UPDATE $mysqltable SET parent='" . $post["target"] . "' WHERE id='" . $post["source"] . "'");
	
	//Update priorites
	$priority_cases = array();
	$priorites = json_decode($post["priorities"], true);
	foreach ($priorites AS $key=>$value){
		array_push($priority_cases, "WHEN id=$key THEN " . $value);
	}
	$priority_cases = implode(" ", $priority_cases);
	mysqlQuery("UPDATE $mysqltable SET priority = CASE $priority_cases ELSE priority END;");
	
	exit();
}

//==== DELETE Record ====
if ($delete){
	$children = customPageChildren($delete);
	mysqlQuery("DELETE FROM $mysqltable WHERE id IN (" . implode(",", $children) . ")");
	if (mysqlAffectedRows()){ $success = readLanguage(records,deleted); } else { $error = readLanguage(records,unavailable); }

//==== ADD Record ====
} else if ($post["token"] && !$edit){
	if (mysqlNum(mysqlQuery("SELECT * FROM $mysqltable WHERE canonical='" . $post["canonical"] . "'"))){
		$error = readLanguage(records,exists);
	} else if (!validateCanonical($post["canonical"])){
		$error = readLanguage(records,invalid_canonical);
	} else {
		$query = "INSERT INTO $mysqltable (
			parent,
			type,
			tags,
			title,
			canonical,
			description,
			content,
			gallery,
			videos,
			attachments,
			date,
			cover_image,
			header_image,
			blocks_show,
			blocks_template,
			blocks_spacing,
			blocks_per_page,
			blocks_per_row,
			blocks_class,
			blocks_animation,
			blocks_grid_justify,
			blocks_grid_align,
			page_content_module,
			page_content_displays,
			page_header,
			page_footer,
			page_layout,
			child_content_module,
			child_header,
			child_footer,
			foreign_pages,
			priority
		) VALUES (
			'" . $post["parent"] . "',
			'" . $post["type"] . "',
			'" . implode(",", $post["tags"]) . "',
			'" . $post["title"] . "',
			'" . $post["canonical"] . "',
			'" . $post["description"] . "',
			'" . $post["content"] . "',
			'" . $post["gallery"] . "',
			'" . $post["videos"] . "',
			'" . $post["attachments"] . "',
			'" . getTimestamp($post["date"], "j/n/Y H:i") . "',
			'" . imgUpload($_FILES["cover_image"], "../uploads/pages/", null, "cover_") . "',
			'" . imgUpload($_FILES["header_image"], "../uploads/pages/", null, "header_") . "',			
			'" . ($post["type"]==2 ? $post["blocks_show"] : 0) . "',
			'" . ($post["type"]==2 && $post["blocks_show"] ? $post["blocks_template"] : "") . "',
			'" . ($post["type"]==2 && $post["blocks_show"] ? $post["blocks_spacing"] : "") . "',
			'" . ($post["type"]==2 && $post["blocks_show"] ? $post["blocks_per_page"] : "") . "',
			'" . ($post["type"]==2 && $post["blocks_show"] ? $post["blocks_per_row"] : "") . "',
			'" . ($post["type"]==2 && $post["blocks_show"] ? $post["blocks_class"] : "") . "',
			'" . ($post["type"]==2 && $post["blocks_show"] ? $post["blocks_animation"] : "") . "',
			'" . ($post["type"]==2 && $post["blocks_show"] ? $post["blocks_grid_justify"] : "") . "',
			'" . ($post["type"]==2 && $post["blocks_show"] ? $post["blocks_grid_align"] : "") . "',
			'" . $post["page_content_module"] . "',
			'" . $post["page_content_displays"] . "',
			'" . $post["page_header"] . "',
			'" . $post["page_footer"] . "',
			'" . $post["page_layout"] . "',	
			'" . ($post["type"]==2 ? $post["child_content_module"] : "") . "',
			'" . ($post["type"]==2 ? $post["child_header"] : "") . "',
			'" . ($post["type"]==2 ? $post["child_footer"] : "") . "',
			'" . implode(",", $post["foreign_pages"]) . "',
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
			parent='" . $post["parent"] . "',
			type='" . $post["type"] . "',
			tags='" . implode(",", $post["tags"]) . "',
			title='" . $post["title"] . "',
			canonical='" . $post["canonical"] . "',
			description='" . $post["description"] . "',
			content='" . $post["content"] . "',
			gallery='" . $post["gallery"] . "',
			videos='" . $post["videos"] . "',
			attachments='" . $post["attachments"] . "',
			date='" . getTimestamp($post["date"], "j/n/Y H:i") . "',
			cover_image='" . imgUpload($_FILES["cover_image"], "../uploads/pages/", $record_data["cover_image"], $record_data["page"] . "_cover_") . "',
			header_image='" . imgUpload($_FILES["header_image"], "../uploads/pages/", $record_data["header_image"], $record_data["page"] . "_header_") . "',
			blocks_show='" . ($post["type"]==2 ? $post["blocks_show"] : 0) . "',
			blocks_template='" . ($post["type"]==2 && $post["blocks_show"] ? $post["blocks_template"] : "") . "',
			blocks_spacing='" . ($post["type"]==2 && $post["blocks_show"] ? $post["blocks_spacing"] : "") . "',
			blocks_per_page='" . ($post["type"]==2 && $post["blocks_show"] ? $post["blocks_per_page"] : "") . "',
			blocks_per_row='" . ($post["type"]==2 && $post["blocks_show"] ? $post["blocks_per_row"] : "") . "',
			blocks_class='" . ($post["type"]==2 && $post["blocks_show"] ? $post["blocks_class"] : "") . "',
			blocks_animation='" . ($post["type"]==2 && $post["blocks_show"] ? $post["blocks_animation"] : "") . "',
			blocks_grid_justify='" . ($post["type"]==2 && $post["blocks_show"] ? $post["blocks_grid_justify"] : "") . "',
			blocks_grid_align='" . ($post["type"]==2 && $post["blocks_show"] ? $post["blocks_grid_align"] : "") . "',
			page_content_module='" . $post["page_content_module"] . "',
			page_content_displays='" . $post["page_content_displays"] . "',
			page_header='" . $post["page_header"] . "',
			page_footer='" . $post["page_footer"] . "',
			page_layout='" . $post["page_layout"] . "',
			child_content_module='" . ($post["type"]==2 ? $post["child_content_module"] : "") . "',
			child_header='" . ($post["type"]==2 ? $post["child_header"] : "") . "',
			child_footer='" . ($post["type"]==2 ? $post["child_footer"] : "") . "',
			foreign_pages='" . implode(",", $post["foreign_pages"]) . "',
			hidden='" . $post["hidden"] . "'
		WHERE id=$edit";
		mysqlQuery($query);
		$success = readLanguage(records,updated);
	}
}

//Read and Set Operation [Custom]
if ($edit){
	$entry = getID($edit,$mysqltable);
	if (!$entry){ $error = readLanguage(records,unavailable); $edit = null; }
}
if ($edit){
	$button = readLanguage(records,update);
	$action = "$base_name.php" . rebuildQueryParameters(array("delete","token","create"));
} else {
	$button = readLanguage(records,add);
	$action = "$base_name.php" . rebuildQueryParameters(array("delete","token","edit","create"));
	if ($error){ foreach ($_POST as $key => $value){ $entry[$key] = $value; } }
}
if ($success){ $message = "<div class='alert alert-success'>$success</div>"; }
if ($error){ $message = "<div class='alert alert-danger'>$error</div>"; }

include "_header.php"; ?>

<div class=title><?=getPageTitle($base_name)?></div>
<?=$message?>

<script src="../plugins/fixed-data.js?v=<?=$system_settings["system_version"]?>"></script>
<link href="../plugins/jstree/style.min.css?v=<?=$system_settings["system_version"]?>" rel="stylesheet">
<script src="../plugins/jstree/jstree.min.js?v=<?=$system_settings["system_version"]?>"></script>

<style>
.jstree {
	padding: 0;
	margin: 0;
}

.jstree-default .jstree-wholerow {
	border-radius: 3px;
}

.jstree-default .jstree-wholerow-hovered {
	background: #f8f8f8;
}

.jstree-default .jstree-wholerow-clicked {
	background: #eee;
}

.vakata-context,
.vakata-context ul {
	background: #fff;
	border: 1px solid rgba(0, 0, 0, 0.15);
	border-radius: 3px;
	box-shadow: 0 6px 12px rgb(0 0 0 / 18%);
	padding: 0;
}

.vakata-context li>a:hover,
.vakata-context .vakata-context-hover>a {
	box-shadow: initial;
	background: #eee;
}

.vakata-context li>a {
	text-shadow: initial;
}

.page_row {
	display: inline-flex;
	align-items: center;
	flex-grow: 1;
}

.page_row small {
	color: #aaa;
}

.tree_search {
	border-radius: 3px !important;
	box-shadow: initial !important;
	background: #fff !important;
}

.jstree-default .jstree-search {
	color: #000;
}
</style>

<!-- Tabs -->
<ul class="nav nav-tabs tab-inline-header margin-top">
	<li role=presentation><a href="#tree" data-toggle=tab role=tab><i class="fas fa-laptop"></i><span>&nbsp;&nbsp;<?=readLanguage(builder,page_tree)?></span></a></li>
	<li role=presentation><a href="#manager" data-toggle=tab role=tab><i class="fas fa-cogs"></i><span>&nbsp;&nbsp;<?=readLanguage(builder,page_manager)?></span></a></li>
</ul>

<div class=tab-content><!-- Start Tab Content -->

<!-- ===== Pages Tree ===== -->
<div class=tab-pane role=tabpanel id="tree">

<div class="toolbar margin-bottom align-items-end flex-wrap">
	<div class="subtitle flex-grow-1">
		<span><?=readLanguage(builder,page_operations)?><small><?=readLanguage(builder,page_operations_note)?></small></span>
	</div>
	<div class="d-flex margin-top-small">
		<div><input type=text class=tree_search placeholder="<?=readLanguage(general,search)?>"></div>
		<div><button class="btn btn-success btn-sm" onclick="pageCreate()"><i class="fas fa-plus"></i>&nbsp;&nbsp;<?=readLanguage(builder,page_create)?></button></div>
	</div>
</div>

<div class=page_container>
<ul id=pages>
<?
function pageChildren($id){
	global $mysqltable, $base_url, $data_pages_types;
	$return = null;
	$result = mysqlQuery("SELECT * FROM $mysqltable WHERE parent=$id AND type!=0 ORDER BY priority DESC");
	while ($entry = mysqlFetch($result)){
		$url = $base_url . ($entry["parent"] ? customPagePathRender(customPagePath($entry["parent"]), null, "/", "canonical") . "/" : "") . $entry["canonical"] . "/";
		$children = mysqlNum(mysqlQuery("SELECT id FROM $mysqltable WHERE parent=" . $entry["id"]));
		$i = 0;
		if ($i == 0){ $return.= "<ul>"; }
		$return .= "<li data-jstree='{\"icon\":\"far fa-file\"}'
			data-id='" . $entry["id"] . "'
			data-type='" . $entry["type"] . "'
			data-title='" . $entry["title"] . "' 
			data-url='" . $url . "'>
			<div class=page_row><div>" . $entry["title"] . "</div>&nbsp;&nbsp;<small>" . $data_pages_types[$entry["type"]] . ($entry["type"]==2 && $children ? "&nbsp;&nbsp;($children Pages)" : "") . "</small></div>" . pageChildren($entry["id"]) . "</li>";
		$i++;
		if ($i > 0){ $return.= "</ul>"; }
	}
	return $return;
}
echo pageChildren(0);
?>	
</ul>
</div>

<script>
//Initialize JSTree
$("#pages").jstree({
	core: {
		check_callback : true,
		themes: {
			variant: "large"
		},
		check_callback: function(operation, node, node_parent, node_position, more){
			if (operation == "move_node" && node_parent.data){
				return node_parent.data.type == "1"; //Only allow dropping inside standrad pages
			}
			return true; //Allow all other operations
		}
	},
	dnd: {
		copy: false
	},
	contextmenu : {         
		items: function($node){
			var tree = $("#tree").jstree(true);
			return {
				"Create": {
					eparator_before: false,
					separator_after: false,
					label: "<?=readLanguage(builder,page_create)?>",
					icon: "fal fa-plus",
					action: function (obj){
						pageCreate($node.data.id);
					}
				},
				"Edit": {
					separator_before: false,
					separator_after: false,
					label: "<?=readLanguage(builder,page_edit)?>",
					icon: "fal fa-edit",
					action: function (obj){ 
						setWindowLocation("<?=$base_name?>.php?edit=" + $node.data.id);
					}
				},                         
				"Delete": {
					separator_before: false,
					separator_after: true,
					label: "<?=readLanguage(builder,page_delete)?>",
					icon: "fal fa-trash",
					action: function (obj){
						$.confirm({
							title: "<?=readLanguage(crud,delete_title)?>",
							content: "<span class=crud_input><?=readLanguage(crud,delete_message)?> <b>" + $node.data.title + "</b>",
							icon: "fal fa-trash",
							buttons: {
								confirm: {
									text: "<?=readLanguage(crud,yes)?>",
									btnClass: "btn-red",
									action: function(){
										setWindowLocation("<?=$base_name?>.php?delete=" + $node.data.id + "&token=<?=$token?>");
									}
								},
								cancel: { text: "<?=readLanguage(crud,cancel)?>" }
							}
						});
					}
				},
				"Open": {
					separator_before: false,
					separator_after: false,
					label: "<?=readLanguage(builder,page_open)?>",
					icon: "fal fa-laptop",
					action: function (obj){ 
						window.open($node.data.url);
					}
				},
				"Copy": {
					separator_before: false,
					separator_after: false,
					label: "<?=readLanguage(builder,page_copy_url)?>",
					icon: "fal fa-copy",
					action: function (obj){
						var url = $node.data.url;
						copyText(url);
					}
				}
			};
		}
	},
	plugins : [
		"dnd",
		"search",
		"wholerow",
		"changed",
		"contextmenu"
	]
});

//Initialize search
$(".tree_search").keyup(function(){
	$("#pages").jstree("search", $(this).val());
});

//Update page parent on drag and drop
$("#pages").on("move_node.jstree", function (e, data){
	//Assign node parent
	try {
		var sourceID = data.node.data.id;
	} catch {
		var sourceID = 0;
	}
	try {
		var targetID = data.instance.get_node(data.node.parent).data.id;
	} catch {
		var targetID = 0;
	}
	
	//Re-arrange priorites
	var jsonNodes = $("#pages").jstree(true).get_json("#", { flat: true });
	var nodesLength = jsonNodes.length;
	var prioritySubtraction = 0;
	var priorites = {};
	$.each(jsonNodes, function (index, value){
		var node = $("#pages").jstree().get_node(value.id);
		priorites[node.data.id] = nodesLength - prioritySubtraction;
		prioritySubtraction++;
	});
	
	$.ajax({
		url: "<?=$base_name?>.php",
		type: "post",
		data: {
			token: "<?=$token?>",
			action: "parent",
			source: sourceID,
			target: targetID,
			priorities: JSON.stringify(priorites)
		},
		success: function(result){
			quickNotify("<?=readLanguage(builder,page_relocated)?>", result, "success", "fas fa-check fa-2x");
		}
	});
});

//Create new page
function pageCreate(parent=0){
	setWindowLocation("<?=$base_name?>.php?create=" + parent);
}
</script>

<div class=crud_separator></div>
<div class=subtitle><?=readLanguage(builder,page_table)?></div>
<?
$crud_data["where_statement"] = "type!=0";
$crud_data["delete_record_message"] = "title";
$crud_data["buttons"] = array(true,true,false,true,true); //Add - Search - View - Edit - Delete
$crud_data["columns"] = array(
	array("parent", readLanguage(builder,page_parent), "50%", "center", "customPagePathRender(customPagePath(%s))", true, false),
	array("title", readLanguage(inputs,title), "50%", "center", null, false, true),
	array("canonical", readLanguage(inputs,url), "300px", "center", "pageURL(customPageURL(%d))", false, true),
	array("date", readLanguage(inputs,date), "200px", "center", "dateLanguage('l, d M Y',%s)", false, false)
);
require_once("crud/crud.php");
?>

</div>

<!-- ===== Page Manager ===== -->
<div class=tab-pane role=tabpanel id="manager">

<form method=post enctype="multipart/form-data" action="<?=$action?>">
<input type=hidden name=token value="<?=$token?>">
<input type=hidden name=page_layout>

<!-- Page Settings -->
<div class=subtitle><?=readLanguage(builder,page_settings)?></div>
<table class=data_table>
<tr>
	<td class=title><?=readLanguage(builder,page_type)?>: <i class=requ></i></td>
	<td colspan=3>
		<? if ($edit && mysqlNum(mysqlQuery("SELECT id FROM $mysqltable WHERE parent='" . $entry["id"] . "'"))){ ?>
			<input type=hidden name=type value="<?=$entry["type"]?>">
			<div><?=$data_pages_types[$entry["type"]]?></div>
			<div class=input_description><?=readLanguage(builder,page_type_note)?></div>
		<? } else { ?>
			<div class=radio_container id=type>
				<? foreach ($data_pages_types AS $key=>$value){
					if ($key){
						print "<label><input name=type type=radio value='$key'><span>$value</span></label>";
					}
				} ?>
			</div>
			<script>
			$("#type").find("[value='<?=($edit ? $entry["type"] : 1)?>']").prop("checked",true);
			$("#type input[type=radio]").on("change", function(){
				toggleVisibility($(this));
			});
			$(document).ready(function(){
				$("#type").find("[value='<?=($edit ? $entry["type"] : 1)?>']").prop("checked", true).trigger("change");
			});
			</script>
		<? } ?>
	</td>
</tr>
<tr>
	<td class=title><?=readLanguage(inputs,title)?>: <i class=requ></i></td>
	<td>
		<input type=text name=title onkeyup="createCanonical(this.value, 'canonical')" value="<?=$entry["title"]?>" data-validation=required>
	</td>
	<td class=title><?=readLanguage(inputs,canonical)?>: <i class=requ></i></td>
	<td>
		<div class=d-flex>
			<input type=text name=canonical id=canonical value="<?=$entry["canonical"]?>" placeholder="<?=readLanguage(inputs,canonical_placeholder)?>" data-validation=required>
			&nbsp;&nbsp;<a class="btn btn-default btn-sm btn-square flex-center canonical_lock_button" onclick="toggleCanonicalLock('canonical', this)"><i class="fal fa-fw fa-lock"></i></a>
		</div>
		<? if ($edit && $entry["canonical"]){ ?><script>toggleCanonicalLock("canonical", $(".canonical_lock_button"))</script><? } ?>
	</td>
</tr>
<tr>
	<td class=title><?=readLanguage(builder,page_parent)?>:</td>
	<td colspan=3>
		<select name=parent id=parent>
			<option value=0><?=readLanguage(builder,none)?></option>
			<? $children = ($edit ? implode(",", customPageChildren($edit)) : 0);
			$parent_result = mysqlQuery("SELECT * FROM $mysqltable WHERE type=1 AND id NOT IN (" . ($children ? $children : 0) . ") ORDER BY priority DESC");
			while ($parent = mysqlFetch($parent_result)){ ?>
			<option value="<?=$parent["id"]?>"><?=customPagePathRender(customPagePath($parent["id"]))?></option>
			<? } ?>
		</select>
		<script>
		setSelectValue("#parent","<?=($edit ? $entry["parent"] : $get["create"])?>");
		$("#parent").select2();
		</script>
	</td>
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
	<td class=title><?=readLanguage(inputs,date)?>:</td>
	<td>
		<div class=p-relative>
			<input type=text name=date id=date readonly class=date_field>
			<? if ($entry["date"]){ $date = $entry["date"] . "000"; } ?>
			<script>createCalendar("date", new Date(<?=$date?>), null, null, null, null, true, true)</script>
		</div>
	</td>
	<td class=title><?=readLanguage(inputs,hidden)?>:</td>
	<td><div class=switch><label><?=$data_no_yes[0]?><input type=checkbox name=hidden value=1 <?=($entry["hidden"] ? "checked" : "")?>><span class=lever></span><?=$data_no_yes[1]?></label></div></td>
</tr>
<tr>
	<td class=title><?=readLanguage(builder,foreign_pages)?>:</td>
	<td colspan=3>
		<select name=foreign_pages[] id=foreign_pages multiple>
			<? $children = ($edit ? implode(",", customPageChildren($edit)) : 0);
			$foreign_pages_result = mysqlQuery("SELECT * FROM $mysqltable WHERE id NOT IN (" . ($children ? $children : 0) . ") ORDER BY priority DESC");
			while ($foreign_page = mysqlFetch($foreign_pages_result)){ ?>
			<option value="<?=$foreign_page["id"]?>"><?=customPagePathRender(customPagePath($foreign_page["id"]))?></option>
			<? } ?>
		</select>
		<script>
		<? if ($entry["foreign_pages"]){ ?>$("#foreign_pages").val([<?=$entry["foreign_pages"]?>]);<? } ?>
		$("#foreign_pages").select2();
		</script>
	</td>
</tr>
</table>

<!-- Page Contents -->
<div class=subtitle><?=readLanguage(builder,page_content)?></div>
<table class=data_table>
<tr>
	<td class=title><?=readLanguage(inputs,content)?>:</td>
	<td><textarea class=contentEditor style="height:400px" name=content id=content><?=$entry["content"]?></textarea></td>
</tr>
<tr>
	<td class=title><?=readLanguage(inputs,gallery)?>:</td>
	<td data-token="<?=$token?>" data-attachments=gallery data-upload-path="../uploads/pages/">
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
<tr>
	<td class=title><?=readLanguage(inputs,videos)?>:</td>
	<td data-multiple=videos>
		<button type=button class="btn btn-primary btn-upload" onclick="multipleDataCreate('videos')"><?=readLanguage(inputs,videos_insert)?></button>
		<input type=hidden name=videos>
		<ul multiple-sortable>
			<li data-template>
				<table class=multiple_data_table><tr>
				<td>
					<a class="btn btn-success btn-sm add" onclick="multipleDataCreate('videos')"><i class="fas fa-plus"></i></a>
					<a class="btn btn-danger btn-sm remove"><i class="fas fa-times"></i></a>
				</td>
				<td>
					<div class=form-item>
						<b><?=readLanguage(inputs,title)?></b><div class=input><input type=text data-name=title data-validation=required disabled></div>
					</div>
					<div class=form-item>
						<b><?=readLanguage(inputs,url)?></b><div class=input><input type=text data-name=url data-validation=validateYouTube disabled></div>
					</div>
				</td>
				</tr></table>
			</li>
		</ul>
		<? if ($entry["videos"]){ ?>
		<script>
		var jsonArray = jQuery.parseJSON(JSON.stringify(<?=$entry["videos"]?>));
		jsonArray.forEach(function(entry){ multipleDataCreate("videos",entry); });	
		</script>
		<? } ?>
	</td>
</tr>
<tr>
	<td class=title><?=readLanguage(inputs,attachments)?>:</td>
	<td data-token="<?=$token?>" data-attachments=attachments data-upload-path="../uploads/pages/">
		<div class=attachment-button>
			<input type=hidden name=attachments value="<?=$entry["attachments"]?>">
			<label class="btn btn-primary btn-lrg btn-upload"><?=readLanguage(inputs,attachments_insert)?><input type=file id=attachments accept="pdf/*" multiple></label>
			<div><i class="fas fa-spinner fa-spin"></i><?=readLanguage(inputs,uploading)?></div>
		</div>
		<ul sortable class=attachments-list></ul><div style="clear:both"></div>
		<? if ($entry["attachments"]){ ?>
		<script>
		var jsonArray = jQuery.parseJSON(JSON.stringify(<?=$entry["attachments"]?>));
		jsonArray.forEach(function(entry){ attachmentsLoadFile(entry,"attachments"); });	
		</script>
		<? } ?>
	</td>
</tr>
<tr>
	<td class=title><?=readLanguage(builder,layout_displays)?>:</td>
	<td colspan=3>
		<input type=hidden name=page_content_displays id=page_content_displays>
		<ul class=inline_input json-fixed-data=page_content_displays clear-empty>
			<? $content_displays = array("gallery", "videos", "attachments");
			$content_displays_list = populateData("SELECT * FROM " . $suffix . "website_custom_displays WHERE source=4", "uniqid", "placeholder");
			foreach ($content_displays AS $content){ ?>
				<li style="flex-basis:100px">
					<p><?=readLanguage(inputs,$content)?></p>
					<select data-name="<?=$content?>">
						<option value=""><?=readLanguage(builder,basic)?></option>
						<option value="none"><?=readLanguage(builder,none)?></option>
						<?=$content_displays_list?>
					</select>
				</li>
			<? } ?>
		</ul>
		<? if ($entry["page_content_displays"]){ ?><script>fixedDataRead("page_content_displays", <?=$entry["page_content_displays"]?>)</script><? } ?>
	</td>
</tr>
</table>

<!-- Page Sections -->
<div class=subtitle><?=readLanguage(builder,layout_modules)?></div>
<? $modules_input = "[name=page_layout]";
$modules_entry = $entry["page_layout"];
$modules_content = true;
$modules_type = 1;
include "includes/_select_modules.php"; ?>

<!-- Page Content Layout -->
<div class=subtitle><?=readLanguage(builder,layout)?></div>
<div class=data_table_container>
<table class=data_table>
<tr>
	<td class=title><?=readLanguage(builder,page_content_layout)?>:</td>
	<td colspan=3>
		<select name=page_content_module id=page_content_module>
			<option value="none"><?=readLanguage(builder,none)?></option>
			<?=populateData("SELECT * FROM " . $suffix . "website_modules_custom WHERE FIND_IN_SET(0,type)", "uniqid", "placeholder")?>
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
<div visibility-control=type visibility-value=2>
<table class=data_table>
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
	<td class=title><?=readLanguage(builder,blocks_page)?>:</td>
	<td>
		<input type=number name=blocks_per_page value="<?=($edit ? $entry["blocks_per_page"] : 12)?>">
	</td>
</tr>
<tr visibility-control=blocks_show visibility-value=1>
	<td class=title><?=readLanguage(builder,blocks_row)?>:</td>
	<td>
		<input type=hidden name=blocks_per_row id=blocks_per_row>
		<ul class=inline_input json-fixed-data=blocks_per_row>
		<? foreach ($data_screen_sizes AS $size=>$icon){
			print "<li><div class=input-addon><span before><i class='$icon'></i></span><select data-name='$size'>"; ?>
			<option value=1>1</option><option value=2>2</option><option value=3>3</option><option value=4>4</option>
			<option value=5>5</option><option value=6>6</option>
			<? print "</select></div></li>";
		} ?>
		</ul>
		<? if ($entry["blocks_per_row"]){ ?><script>fixedDataRead("blocks_per_row", <?=$entry["blocks_per_row"]?>)</script><? } ?>
	</td>
	<td class=title><?=readLanguage(builder,blocks_spacing)?>:</td>
	<td>
		<div class="d-flex align-items-center">
			<input type=hidden name=blocks_spacing id=blocks_spacing>
			<ul class=inline_input json-fixed-data=blocks_spacing>
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
			<? if ($entry["blocks_spacing"]){ ?>fixedDataRead("blocks_spacing", <?=$entry["blocks_spacing"]?>);<? } ?>
			<? if (!$edit){ ?>
			$("[json-fixed-data=blocks_spacing] li:first-child select").on("change", function(){
				$("[json-fixed-data=blocks_spacing] li:not(:first-child) select").val($(this).val());
			});
			<? } ?>
			</script>
		</div>
	</td>
</tr>
<tr visibility-control=blocks_show visibility-value=1>
	<td class=title><?=readLanguage(builder,grid_blocks_class)?>:</td>
	<td>
		<div class=class_input class-bind=blocks_class class-properties="<?=$entry["blocks_class"]?>"></div>
	</td>
	<td class=title><?=readLanguage(builder,animation)?>:</td>
	<td>
		<div class=animation_input animation-bind=blocks_animation animation-properties="<?=base64_encode($entry["blocks_animation"])?>"></div>
	</td>
</tr>
<tr visibility-control=blocks_show visibility-value=1>
	<td class=title><?=readLanguage(builder,flex_justify)?>:</td>
	<td>
		<select block-options name=blocks_grid_justify id=blocks_grid_justify>
			<option value=flex-start><?=readLanguage(builder,flex_justify_start)?></option>
			<option value=flex-end><?=readLanguage(builder,flex_justify_end)?></option>
			<option value=center><?=readLanguage(builder,flex_justify_center)?></option>
			<option value=space-between><?=readLanguage(builder,flex_justify_space_between)?></option>
			<option value=space-around><?=readLanguage(builder,flex_justify_space_around)?></option>
			<option value=space-evenly><?=readLanguage(builder,flex_justify_space_evenly)?></option>
		</select>
		<? if ($entry["blocks_grid_justify"]){ ?><script>setSelectValue("#blocks_grid_justify","<?=$entry["blocks_grid_justify"]?>")</script><? } ?>
	</td>
	<td class=title><?=readLanguage(builder,flex_align)?>:</td>
	<td>
		<select block-options name=blocks_grid_align id=blocks_grid_align>
			<option value=stretch><?=readLanguage(builder,flex_align_stretch)?></option>
			<option value=center><?=readLanguage(builder,flex_align_center)?></option>
			<option value=flex-start><?=readLanguage(builder,flex_align_start)?></option>
			<option value=flex-end><?=readLanguage(builder,flex_align_end)?></option>
			<option value=baseline><?=readLanguage(builder,flex_align_baseline)?></option>
		</select>
		<? if ($entry["blocks_grid_align"]){ ?><script>setSelectValue("#blocks_grid_align","<?=$entry["blocks_grid_align"]?>")</script><? } ?>
	</td>
</tr>
</table>
</div>
</div>

<!-- Child Pages Content Layout -->
<div visibility-control=type visibility-value=2 class=margin-top>
<div class=subtitle><?=readLanguage(builder,page_child_content_layout)?></div>
<div class=data_table_container><table class=data_table>
<tr>
	<td class=title><?=readLanguage(builder,page_content_layout)?>:</td>
	<td colspan=3>
		<select name=child_content_module id=child_content_module>
			<option value="none"><?=readLanguage(builder,none)?></option>
			<?=populateData("SELECT * FROM " . $suffix . "website_modules_custom WHERE FIND_IN_SET(0,type)", "uniqid", "placeholder")?>
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
</table></div>
</div>

<div class=submit_container><input type=button class=submit value="<?=$button?>"></div>
</form>

<!-- Include class selection snippet -->
<? include "includes/_select_class.php"; ?>

<!-- Include animation selection snippet -->
<? include "includes/_select_animation.php"; ?>

</div>

</div><!-- End Tab Content -->

<script>
//Set active tab
<? $target = ($edit || isset($get["create"]) ? "manager" : "tree"); ?>
$("a[href='#<?=$target?>'][data-toggle=tab]").click();
</script>

<? include "_footer.php"; ?>