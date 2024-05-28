<? include "system/_handler.php";

$mysqltable = $suffix . "website_pages_custom";
$base_name = basename($_SERVER["SCRIPT_FILENAME"], ".php");
checkPermissions($base_name);

//Read and verify page set
if ($get["page"]){
	$page = getID($get["page"], $mysqltable, "id");
}

//==== DELETE Record ====
if ($delete){
	$children = customPageChildren($delete);
	mysqlQuery("DELETE FROM $mysqltable WHERE id IN (" . implode(",", $children) . ")");
	if (mysqlAffectedRows()){ $success = readLanguage(records,deleted); } else { $error = readLanguage(records,unavailable); }

//==== ADD Record ====
} else if ($post["token"] && !$edit){
	if (mysqlNum(mysqlQuery("SELECT * FROM $mysqltable WHERE canonical!='' AND canonical='" . $post["canonical"] . "'"))){
		$error = readLanguage(records,exists);
	} else if (!validateCanonical($post["canonical"])){
		$error = readLanguage(records,invalid_canonical);
	} else {
		//Mapping page extras
		$child_extras = json_decode($post["child_extras"], true);
		foreach ($child_extras AS $key=>$value){
			$mapped_extras[$value["attribute"]] = $value["value"];
		}
		$mapped_extras = json_encode($mapped_extras, JSON_UNESCAPED_UNICODE);
		
		$query = "INSERT INTO $mysqltable (
			parent,
			tags,
			title,
			url_target,
			url_attributes,
			canonical,
			description,
			content,
			gallery,
			videos,
			attachments,
			date,
			cover_image,
			header_image,
			page_content_displays,
			blocks_show,
			blocks_template,
			blocks_spacing,
			blocks_per_page,
			blocks_per_row,
			blocks_class,
			blocks_animation,
			blocks_grid_justify,
			blocks_grid_align,
			child_subtitle,
			child_icon,
			child_extras,
			child_color,
			child_content_module,
			child_header,
			child_footer,
			foreign_pages,
			hidden,
			priority
		) VALUES (
			'" . ($post["parent"] ? $post["parent"] : $page) . "',
			'" . implode(",", $post["tags"]) . "',
			'" . $post["title"] . "',
			'" . $post["url_target"] . "',
			'" . ($post["url_target"]!=3 ? $post["url_attributes"] : "") . "',
			'" . (!$post["url_target"] ? $post["canonical"] : ($post["url_target"]==2 ? $post["url_external"] : str_replace($base_url, "", $post["url"]))) . "',
			'" . $post["description"] . "',
			'" . $post["content"] . "',
			'" . $post["gallery"] . "',
			'" . $post["videos"] . "',
			'" . $post["attachments"] . "',
			'" . getTimestamp($post["date"], "j/n/Y H:i") . "',
			'" . imgUpload($_FILES["cover_image"], "../uploads/pages/", null, "cover_") . "',
			'" . imgUpload($_FILES["header_image"], "../uploads/pages/", null, "header_") . "',	
			'" . $post["page_content_displays"] . "',
			'" . $post["blocks_show"] . "',
			'" . ($post["blocks_show"] ? $post["blocks_template"] : "") . "',
			'" . ($post["blocks_show"] ? $post["blocks_spacing"] : "") . "',
			'" . ($post["blocks_show"] ? $post["blocks_per_page"] : "") . "',
			'" . ($post["blocks_show"] ? $post["blocks_per_row"] : "") . "',
			'" . ($post["blocks_show"] ? $post["blocks_class"] : "") . "',
			'" . ($post["blocks_show"] ? $post["blocks_animation"] : "") . "',
			'" . ($post["blocks_show"] ? $post["blocks_grid_justify"] : "") . "',
			'" . ($post["blocks_show"] ? $post["blocks_grid_align"] : "") . "',
			'" . $post["child_subtitle"] . "',
			'" . $post["child_icon"] . "',
			'" . $mapped_extras . "',
			'" . $post["child_color"] . "',
			'" . $post["child_content_module"] . "',
			'" . $post["child_header"] . "',
			'" . $post["child_footer"] . "',
			'" . implode(",", $post["foreign_pages"]) . "',
			'" . $post["hidden"] . "',
			'" . newRecordID($mysqltable) . "'
		)";
		mysqlQuery($query);
		$success = readLanguage(records,added);
	}

//==== EDIT Record ====	
} else if ($post["token"] && $edit){
	if (mysqlNum(mysqlQuery("SELECT * FROM $mysqltable WHERE canonical!='' AND canonical='" . $post["canonical"] . "' AND id!=$edit"))){
		$error = readLanguage(records,exists);
	} else if (!validateCanonical($post["canonical"])){
		$error = readLanguage(records,invalid_canonical);
	} else {
		$record_data = getID($edit,$mysqltable);
		
		//Mapping page extras
		$child_extras = json_decode($post["child_extras"], true);
		foreach ($child_extras AS $key=>$value){
			$mapped_extras[$value["attribute"]] = $value["value"];
		}
		$mapped_extras = json_encode($mapped_extras, JSON_UNESCAPED_UNICODE);
		
		$query = "UPDATE $mysqltable SET
			parent='" . ($post["parent"] ? $post["parent"] : $page) . "',
			tags='" . implode(",", $post["tags"]) . "',
			title='" . $post["title"] . "',
			url_target='" . $post["url_target"] . "',
			url_attributes='" . ($post["url_target"]!=3 ? $post["url_attributes"] : "") . "',
			canonical='" . (!$post["url_target"] ? $post["canonical"] : ($post["url_target"]==2 ? $post["url_external"] : str_replace($base_url, "", $post["url"]))) . "',
			description='" . $post["description"] . "',
			content='" . $post["content"] . "',
			gallery='" . $post["gallery"] . "',
			videos='" . $post["videos"] . "',
			attachments='" . $post["attachments"] . "',
			date='" . getTimestamp($post["date"], "j/n/Y H:i") . "',
			cover_image='" . imgUpload($_FILES["cover_image"], "../uploads/pages/", $record_data["cover_image"], $record_data["page"] . "_cover_") . "',
			header_image='" . imgUpload($_FILES["header_image"], "../uploads/pages/", $record_data["header_image"], $record_data["page"] . "_header_") . "',
			page_content_displays='" . $post["page_content_displays"] . "',
			blocks_show='" . $post["blocks_show"] . "',
			blocks_template='" . ($post["blocks_show"] ? $post["blocks_template"] : "") . "',
			blocks_spacing='" . ($post["blocks_show"] ? $post["blocks_spacing"] : "") . "',
			blocks_per_page='" . ($post["blocks_show"] ? $post["blocks_per_page"] : "") . "',
			blocks_per_row='" . ($post["blocks_show"] ? $post["blocks_per_row"] : "") . "',
			blocks_class='" . ($post["blocks_show"] ? $post["blocks_class"] : "") . "',
			blocks_animation='" . ($post["blocks_show"] ? $post["blocks_animation"] : "") . "',
			blocks_grid_justify='" . ($post["blocks_show"] ? $post["blocks_grid_justify"] : "") . "',
			blocks_grid_align='" . ($post["blocks_show"] ? $post["blocks_grid_align"] : "") . "',
			child_subtitle='" . $post["child_subtitle"] . "',
			child_icon='" . $post["child_icon"] . "',
			child_extras='" . $mapped_extras . "',
			child_color='" . $post["child_color"] . "',
			child_content_module='" . $post["child_content_module"] . "',
			child_header='" . $post["child_header"] . "',
			child_footer='" . $post["child_footer"] . "',
			foreign_pages='" . implode(",", $post["foreign_pages"]) . "',
			hidden='" . $post["hidden"] . "'
		WHERE id=$edit";
		mysqlQuery($query);
		$success = readLanguage(records,updated);
	}
}

//Child pages IDs
if ($page){
	$child_pages = customPageChildren($page);
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
	$clone = true;
	$entry = getID($get["clone"], $mysqltable);
}

include "_header.php"; ?>

<script src="../plugins/iconpicker.min.js?v=<?=$system_settings["system_version"]?>"></script>
<link href="../plugins/iconpicker.min.css?v=<?=$system_settings["system_version"]?>" rel="stylesheet">
<script src="../plugins/fixed-data.js?v=<?=$system_settings["system_version"]?>"></script>
<script src="../plugins/jscolor.min.js?v=<?=$system_settings["system_version"]?>"></script>

<div class=title><?=getPageTitle($base_name)?></div>
<?=$message?>

<!-- Content Page -->
<? if ($page){ ?>
<div class="alert alert-title"><?=getID($page, $mysqltable, "title")?></div>

<form method=post enctype="multipart/form-data" action="<?=$action?>">
<input type=hidden name=token value="<?=$token?>">

<div class=subtitle><?=readLanguage(builder,page_settings)?></div>
<table class=data_table>
<tr>
	<td class=title><?=readLanguage(inputs,title)?>: <i class=requ></i></td>
	<td>
		<input type=text name=title onkeyup="createCanonical(this.value, 'canonical')" value="<?=$entry["title"]?>" data-validation=required>
	</td>
	<td class=title><?=readLanguage(builder,page_url_target)?>: <i class=requ></i></td>
	<td>
		<select name=url_target id=url_target onchange="toggleVisibility(this)">
			<option value=0><?=readLanguage(builder,page_content)?></option>
			<option value=1><?=readLanguage(builder,custom)?></option>
			<option value=2><?=readLanguage(pages,menu_external_url)?></option>
			<option value=3><?=readLanguage(builder,none)?></option>
		</select>
		<script>
		setSelectValue("#url_target", "<?=$entry["url_target"]?>");
		$(document).ready(function(){
			toggleVisibility($("#url_target")[0]);
		});
		</script>
	</td>
</tr>
<tr visibility-control=url_target visibility-value=0>
	<td class=title><?=readLanguage(inputs,canonical)?>: <i class=requ></i></td>
	<td colspan=3>
		<div class=d-flex>
			<input type=text name=canonical id=canonical value="<?=($edit && !$entry["url_target"] ? $entry["canonical"] : "")?>" placeholder="<?=readLanguage(inputs,canonical_placeholder)?>" data-validation=requiredVisible>
			&nbsp;&nbsp;<a class="btn btn-default btn-sm btn-square flex-center canonical_lock_button" onclick="toggleCanonicalLock('canonical', this)"><i class="fal fa-fw fa-lock"></i></a>
		</div>
		<? if ($edit && !$entry["url_target"]){ ?><script>toggleCanonicalLock("canonical", $(".canonical_lock_button"))</script><? } ?>
	</td>
</tr>
<tr visibility-control=url_target visibility-value=1>
	<td class=title><?=readLanguage(inputs,url)?>: <i class=requ></i></td>
	<td colspan=3 class=ltr-input>
		<div class="input-addon input-addon-ltr">
			<span before><?=$base_url?></span>
			<select name=url id=url><?=$data_menu_items?></select>
			<script>
			<? if ($edit && $entry["url_target"]){ ?>
			if (!$("#url option[value='<?=$entry["canonical"]?>']").length){
				$("#url").append("<option value='<?=$entry["canonical"]?>'><?=$entry["canonical"]?></option>");
			}
			setSelectValue("#url","<?=$entry["canonical"]?>");
			<? } ?>
			$("#url").select2({ tags: true });
			</script>
		</div>
	</td>
</tr>
<tr visibility-control=url_target visibility-value=2>
	<td class=title><?=readLanguage(inputs,url)?>: <i class=requ></i></td>
	<td colspan=3>
		<input type=text name=url_external id=url_external value="<?=($edit && $entry["url_target"]==2 ? $entry["canonical"] : "")?>" data-validation=requiredVisible>
	</td>
</tr>
<tr visibility-control=url_target visibility-value="0,1,2">
	<td class=title><?=readLanguage(builder,attributes)?>:</td>
	<td colspan=3 data-multiple=url_attributes>
		<button type=button class="btn btn-primary btn-sm" onclick="multipleDataCreate('url_attributes')"><?=readLanguage(operations,insert)?></button>
		<input type=hidden name=url_attributes>
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
		<? if ($entry["url_attributes"]){ ?>
		<script>
		var jsonArray = <?=$entry["url_attributes"]?>;
		jsonArray.forEach(function(entry){ multipleDataCreate("url_attributes", entry); });
		</script>
		<? } ?>
	</td>
</tr>
<tr>
	<td class=title><?=readLanguage(builder,page_parent)?>:</td>
	<td colspan=3>
		<select name=parent id=parent>
		<? $result_pages = null;
		$result = mysqlQuery("SELECT * FROM " . $suffix . "website_pages_custom WHERE id IN (" . implode(",", array_diff($child_pages, ($edit ? customPageChildren($edit) : [0]))) . ") ORDER BY parent ASC, priority DESC");
		if (mysqlNum($result)){
			while ($page_entry = mysqlFetch($result)){
				$result_pages .= "<option value='" . $page_entry["id"] . "'>" . customPagePathRender(customPagePath($page_entry["id"])) . "</option>";	
			}
		}
		print $result_pages; ?>
		</select>
		<script>
			setSelectValue("#parent", "<?=($entry["parent"] ? $entry["parent"] : $page)?>");
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
	<td colspan=3><textarea name=description><?=$entry["description"]?></textarea></td>
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

<!-- Page Content -->
<div class=margin-top>
<div class=subtitle><?=readLanguage(builder,page_content)?></div>
<table class=data_table>
<tr>
	<td class=title><?=readLanguage(inputs,content)?>:</td>
	<td><textarea class=contentEditor style="height: 400px" name=content id=content><?=$entry["content"]?></textarea></td>
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
</div>

<!-- Custom Content -->
<div class=subtitle><?=readLanguage(builder,blocks_content_custom)?><small><?=readLanguage(builder,blocks_page_note)?></small></div>
<table class=data_table>
<tr>
	<td class=title><?=readLanguage(inputs,subtitle)?>:</td>
	<td>
		<input type=text name=child_subtitle value="<?=$entry["child_subtitle"]?>">
	</td>
	<td class=title><?=readLanguage(builder,color)?>:</td>
	<td>
		<input type=text name=child_color data-jscolor value="<?=$entry["child_color"]?>">
	</td>
</tr>
<tr>
	<td class=title><?=readLanguage(plugins,icon)?>:</td>
	<td colspan=3>
		<div class=flex_content>
			<i data-icon=child_icon class="<?=$entry["child_icon"]?>"></i>
			<input type=text name=child_icon value="<?=$entry["child_icon"]?>" onkeyup="$('[data-icon=child_icon]').attr('class',this.value)" autocomplete=off>
			<button type=button class="btn btn-default btn-sm btn-square flex-center" onclick="bindIconSearch('child_icon')"><i class="fas fa-search"></i>&nbsp;<?=readLanguage(operations,select)?></button>
		</div>
		<div class=input_description>Powered by <a href="https://fontawesome.com/icons?m=free" target=_blank>Font Awesome</a> and <a href="https://getbootstrap.com/docs/3.3/components/" target=_blank>Bootstrap Glyphicons</a></div>
	</td>
</tr>
<tr>
	<td class=title><?=readLanguage(builder,page_extras)?>:</td>
	<td colspan=3 data-multiple=child_extras>
		<div class="d-flex align-items-center">
			<div class=flex-grow-1>
				<button type=button class="btn btn-primary btn-sm" onclick="multipleDataCreate('child_extras')"><?=readLanguage(operations,insert)?></button>
			</div>
			<div class="d-flex align-items-center">
				<a onclick="insertExtras(['number','prefix','suffix','increment'])"><?=readLanguage(builder,page_extras_numeric)?></a>&nbsp;&nbsp;
				<a onclick="insertExtras(['rating'])"><?=readLanguage(builder,page_extras_rating)?></a>
			</div>
		</div>
		<input type=hidden name=child_extras>
		<ul multiple-sortable>
			<li data-template>
				<div class="d-flex align-items-center">
					<input type=text class=ltr-input data-name=attribute data-validation=required placeholder="<?=readLanguage(builder,attributes_attribute)?>" disabled>&nbsp;&nbsp;
					<input type=text class=ltr-input data-name=value placeholder="<?=readLanguage(builder,attributes_value)?>">&nbsp;&nbsp;
					<a class="btn btn-danger btn-sm remove"><i class="fas fa-times"></i></a>
				</div>
			</li>
		</ul>
		<script>
		<? if ($entry["child_extras"]){ ?>
		var jsonObject = <?=$entry["child_extras"]?>;
		for (const [key, value] of Object.entries(jsonObject)){
			var entry = {
				"attribute": key,
				"value": value
			};
			multipleDataCreate("child_extras", entry);
		}
		<? } ?>
		
		//Insert pre-defined extras
		function insertExtras(attributes){
			$("[data-multiple=child_extras] ul li:not([data-template])").remove();
			attributes.forEach(function(item, index){
				var entry = {
					"attribute": item
				};
				multipleDataCreate("child_extras", entry);
			});
		}
		</script>
	</td>
</tr>
</table>

<!-- Page Content Layout -->
<div class=margin-top visibility-control=url_target visibility-value=0>
<div class=subtitle><?=readLanguage(builder,layout)?></div>
<div class=data_table_container>
<table class=data_table>
<tr>
	<td class=title><?=readLanguage(builder,page_content_layout)?>:</td>
	<td colspan=3>
		<select name=page_content_module id=page_content_module>
			<option value=""><?=readLanguage(builder,inherit)?></option>
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
			<option value=""><?=readLanguage(builder,inherit)?></option>
			<option value="none"><?=readLanguage(builder,none)?></option>
		</select>
		<? if ($edit){ ?><script>setSelectValue("#page_header", "<?=$entry["page_header"]?>")</script><? } ?>
	</td>
	<td class=title><?=readLanguage(builder,layout_footer)?>:</td>
	<td>
		<select name=page_footer id=page_footer>
			<option value=""><?=readLanguage(builder,inherit)?></option>
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
</div>

<!-- Child Pages Content Layout -->
<div class=margin-top visibility-control=url_target visibility-value=0>
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

<div class=crud_separator></div>
<?
$crud_data["order_field"] = "priority";
$crud_data["order_by"] = "parent ASC, priority DESC";
$crud_data["where_statement"] = "type=0 AND id IN (" . implode(",", $child_pages) . ")";
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

<!-- Home Page -->
<? } else { ?>
<?
$crud_data["where_statement"] = "type=2";
$crud_data["order_by"] = "priority DESC";
$crud_data["buttons"] = array(false,true,false,false,false); //Add - Search - View - Edit - Delete
$crud_data["columns"] = array(
	array("id", readLanguage(operations,manage), "120px", "center", "'<a class=\"btn btn-primary btn-sm btn-block\" href=\"" . $base_name . ".php?page=%s\">" . readLanguage(operations,manage) . "</a>'", false, true),
	array("parent", readLanguage(builder,page_parent), "50%", "center", "customPagePathRender(customPagePath(%s))", true, false),
	array("title", readLanguage(inputs,title), "50%", "center", null, false, true),
	array("canonical", readLanguage(inputs,url), "300px", "center", "hasVal('%s', null, pageURL(customPageURL(%d)))", false, true),
	array("date", readLanguage(inputs,date), "200px", "center", "dateLanguage('l, d M Y',%s)", false, false)
);
require_once("crud/crud.php");
?>

<? } ?>

<? include "_footer.php"; ?>