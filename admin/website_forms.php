<? include "system/_handler.php";

$mysqltable = $suffix . "website_forms";
$base_name = basename($_SERVER["SCRIPT_FILENAME"], ".php");
checkPermissions($base_name);

//==== DELETE Record ====
if ($delete) {
	mysqlQuery("DELETE FROM $mysqltable WHERE id=$delete");
	if (mysqlAffectedRows()){ $success = readLanguage(records,deleted); } else { $error = readLanguage(records,unavailable); }

//==== ADD Record ====
} else if ($post["token"] && !$edit) {
	if (mysqlNum(mysqlQuery("SELECT * FROM $mysqltable WHERE canonical='$post[canonical]'"))) {
		$error = readLanguage(records,exists);
	} else if (!validateCanonical($post["canonical"])) {
		$error = readLanguage(records,invalid_canonical);
	} else {
		$query = "INSERT INTO $mysqltable (
			uniqid,
			placeholder,
			tags,
			form,
			form_template,
			form_class,
			form_spacing,
			form_attributes,
			btn_text,
			btn_class,
			btn_container_class,
			success_message,
			closed,
			closed_message,
			require_login,
			login_message,
			once_user,
			once_device,
			once_ip,
			priority
		) VALUES (
			'$post[uniqid]',
			'$post[placeholder]',
			'" . implode(",", $post["tags"]) . "',
			'$post[form]',
			'$post[form_template]',
			'$post[form_class]',
			'$post[form_spacing]',
			'$post[form_attributes]',
			'$post[btn_text]',
			'$post[btn_class]',
			'$post[btn_container_class]',
			'$post[success_message]',
			'$post[closed]',
			'" . ($post["closed"] ? $post["closed_message"] : "") . "',
			'$post[require_login]',
			'" . ($post["require_login"] ? $post["login_message"] : "") . "',
			'" . ($post["require_login"] ? $post["once_user"] : 0) . "',
			'$post[once_device]',
			'$post[once_ip]',
			'" . newRecordID($mysqltable) . "'
		)";

		mysqlQuery($query);
		$success = readLanguage(records, added);
	}

//==== EDIT Record ====
} else if ($post["token"] && $edit) {
	if (mysqlNum(mysqlQuery("SELECT * FROM $mysqltable WHERE canonical='$post[canonical]' AND id!=$edit"))) {
		$error = readLanguage(records,exists);
	} else if (!validateCanonical($post["canonical"])) {
		$error = readLanguage(records,invalid_canonical);
	} else {
		$query = "UPDATE $mysqltable SET
			placeholder='$post[placeholder]',
			tags='" . implode(",", $post["tags"]) . "',
			form='$post[form]',
			form_template='$post[form_template]',
			form_class='$post[form_class]',
			form_spacing='$post[form_spacing]',
			form_attributes='$post[form_attributes]',
			btn_text='$post[btn_text]',
			btn_class='$post[btn_class]',
			btn_container_class='$post[btn_container_class]',
			success_message='$post[success_message]',
			closed='$post[closed]',
			closed_message='" . ($post["closed"] ? $post["closed_message"] : "") . "',
			require_login='$post[require_login]',
			login_message='" . ($post["require_login"] ? $post["login_message"] : "") . "',
			once_user='" . ($post["require_login"] ? $post["once_user"] : 0) . "',
			once_device='$post[once_device]',
			once_ip='$post[once_ip]'
		WHERE id=$edit";

		mysqlQuery($query);
		$success = readLanguage(records, added);
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
if ($success){ $message = "<div class='alert alert-success'>$success</div>"; }
if ($error){ $message = "<div class='alert alert-danger'>$error</div>"; }


/*
width, attributes, label, content, icon, placeholder, description, default
maxlength, verbose, min_date, max_date, min_date_fixed, max_date_fixed, min_date_custom, max_date_custom,
mandatory, error_msg, regex, min_number, max_number, allow_float, step
options, value_source, source_target, allow_other, allow_search, min_selections, max_selections
*/

$elements = array(
	"heading" => array(
		"title" => readLanguage(forms,heading),
		"icon" => "fa fa-heading",
		"properties" => array("width", "attributes", "label"),
	),
	"plain" => array(
		"title" => readLanguage(forms,paragraph),
		"icon" => "fa fa-paragraph",
		"properties" => array("width", "attributes", "content"),
	),
	"text" => array(
		"title" => readLanguage(forms,text_field),
		"icon" => "fa fa-i-cursor",
		"properties" => array("width", "attributes", "label", "icon", "placeholder", "description", "default", "maxlength", "verbose", "mandatory", "error_msg", "regex"),
	),
	"textarea" => array(
		"title" => readLanguage(forms,text_area),
		"icon" => "fa fa-file-alt",
		"properties" => array("width", "attributes", "label", "icon", "placeholder", "description", "default", "maxlength", "verbose", "mandatory", "error_msg", "regex"),
	),
	"number" => array(
		"title" => readLanguage(forms,number_field),
		"icon" => "fa fa-hashtag",
		"properties" => array("width", "attributes", "label", "icon", "placeholder", "description", "default", "maxlength", "verbose", "mandatory", "error_msg", "min_number", "max_number", "step", "allow_float"),
	),
	"email" => array(
		"title" => readLanguage(forms,email_field),
		"icon" => "fa fa-envelope",
		"properties" => array("width", "attributes", "label", "icon", "placeholder", "description", "default", "maxlength", "verbose", "mandatory", "error_msg"),
	),
	"mobile" => array(
		"title" => readLanguage(forms,mobile_field),
		"icon" => "fa fa-mobile-alt",
		"properties" => array("width", "attributes", "label", "icon", "placeholder", "description", "default", "maxlength", "verbose", "mandatory", "error_msg"),
	),
	"file" => array(
		"title" => readLanguage(forms,file_field),
		"icon" => "fa fa-file",
		"properties" => array("width", "attributes", "label", "icon", "description", "allowed_mimes", "mandatory", "error_msg"),
	),
	"radio" => array(
		"title" => readLanguage(forms,radio_group),
		"icon" => "fa fa-dot-circle",
		"properties" => array("width", "attributes", "label", "icon", "description", "options", "value_source", "source_target", "mandatory", "error_msg"),
	),
	"checkbox" => array(
		"title" => readLanguage(forms,checkbox_group),
		"icon" => "fa fa-check-double",
		"properties" => array("width", "attributes", "label", "icon", "description", "options", "value_source", "source_target", "min_selections", "max_selections", "mandatory", "error_msg"),
	),
	"single_select" => array(
		"title" => readLanguage(forms,single_select),
		"icon" => "fa fa-tag",
		"properties" => array("width", "attributes", "label", "icon", "placeholder", "description", "options", "value_source", "source_target", "allow_other", "allow_search", "mandatory", "error_msg"),
	),
	"multiple_select" => array(
		"title" => readLanguage(forms,multiple_select),
		"icon" => "fa fa-tags",
		"properties" => array("width", "attributes", "label", "icon", "placeholder", "description", "options", "value_source", "source_target", "allow_other", "allow_search", "min_selections", "max_selections", "mandatory", "error_msg"),
	),
	"date" => array(
		"title" => readLanguage(forms,date_field),
		"icon" => "fa fa-calendar",
		"properties" => array("width", "attributes", "label", "icon", "placeholder", "description", "min_date", "max_date", "min_date_fixed", "max_date_fixed", "min_date_custom", "max_date_custom", "mandatory", "error_msg"),
	)
);

//Set name variable
$module_uniqid = ($edit ? $entry["uniqid"] : "form-" . uniqid());

//Tables schema
include "system/schema.php";

//Fetch built-in value sources
$available_queries = array(); //Available queries for toggling visibility
$system_queries_result = mysqlQuery("SELECT id, title, target FROM system_queries");
if (mysqlNum($system_queries_result)){
	$value_sources .= "<optgroup label='System Queries'>";
	while ($system_queries_entry = mysqlFetch($system_queries_result)){
		array_push($available_queries, $system_queries_entry["id"]);
		$value_sources .= "<option value='" . $system_queries_entry["id"] . "' source-targets='" . json_encode($schema[$system_queries_entry["target"]]["fields"], JSON_UNESCAPED_UNICODE) . "'>" . $system_queries_entry["title"] . "</option>";
	}
	$value_sources .=" </optgroup>";
}
$system_variables_result = mysqlQuery("SELECT variable, placeholder FROM system_variables");
if (mysqlNum($system_variables_result)){
	$value_sources .= "<optgroup label='System Variables'>";
	while ($system_variables_entry = mysqlFetch($system_variables_result)){
		$value_sources .= "<option value='" . $system_variables_entry["variable"] . "'>" . $system_variables_entry["placeholder"] . "</option>";
	}
	$value_sources .=" </optgroup>";	
}

//Read cloned parameter
if ($get["clone"]){
	$entry = getID($get["clone"], $mysqltable);
	$entry["custom_css"] = str_replace($entry["uniqid"], $module_uniqid, $entry["custom_css"]);
}

include "_header.php"; ?>

<script src="../plugins/iconpicker.min.js?v=<?=$system_settings["system_version"]?>"></script>
<link href="../plugins/iconpicker.min.css?v=<?=$system_settings["system_version"]?>" rel="stylesheet">

<div class=title><?=getPageTitle($base_name)?></div>
<?=$message?>

<form method=post enctype="multipart/form-data" action="<?=$action?>">
<input type=hidden name=token value="<?=$token?>">
<input type=hidden name=uniqid value="<?=$module_uniqid?>">

<!-- Tabs -->
<ul class="nav nav-tabs tab-inline-header">
	<li class=active><a data-toggle=tab href="#settings"><i class="fas fa-cogs"></i><span>&nbsp;&nbsp;<?=readLanguage(forms,form_settings)?></a></li>
	<li><a data-toggle=tab href="#inputs"><i class="fas fa-keyboard"></i><span>&nbsp;&nbsp;<?=readLanguage(forms,form_inputs)?></a></li>
</ul>

<!-- Content -->
<div class="tab-content tab-inline">

<!-- Form Settings -->
<div id=settings class="tab-pane in active">
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
	<td class=title><?=readLanguage(forms,submit_button_text)?>: <i class=requ></i></td>
	<td colspan=3><input type=text name=btn_text value="<?=$entry['btn_text']?>" data-validation=required></td>
</tr>
<tr>
	<td class=title><?=readLanguage(forms,submit_button_class)?>:</td>
	<td><div class=class_input class-bind=btn_class class-properties="<?=$entry["btn_class"]?>"></div></td>
	<td class=title><?=readLanguage(forms,submit_container_class)?>: </td>
	<td><div class=class_input class-bind=btn_container_class class-properties="<?=$entry["btn_container_class"]?>"></div></td>
</tr>
<tr>
	<td class=title><?=readLanguage(forms,success_message)?>: <i class=requ></i></td>
	<td colspan=3><textarea class=mceEditor name=success_message data-validation=validateEditor><?=$entry["success_message"]?></textarea></td>
</tr>
<tr>
	<td class=title><?=readLanguage(forms,form_template)?>: <i class=requ></i></td>
	<td colspan=3>
		<select name=form_template id=form_template data-validation=required>
		<option>-- <?=readLanguage(operations,select)?> --</option>
		<? $built_in_blocks = retrieveDirectoryFiles("../website/templates/", "php", "form-*");
		foreach ($built_in_blocks AS $block){
			$block_selector = basename($block, ".php");
			print "<option value='$block_selector'>$block_selector</option>";
		} ?>
		</select>
		<? if ($entry["form_template"]){ ?><script>setSelectValue("#form_template", "<?=$entry["form_template"]?>")</script><? } ?>
	</td>
</tr>
<tr>
	<td class=title><?=readLanguage(forms,form_spacing)?>:</td>
	<td>
		<select block-options name=form_spacing id=form_spacing>
			<option value=0><?=readLanguage(builder,none)?></option>
			<option value=5>5</option><option value=10>10</option>
			<option value=15>15</option><option value=20>20</option>
			<option value=25>25</option><option value=30>30</option>
			<option value=40>40</option><option value=50>50</option>
		</select>
		<? if ($entry["form_spacing"]){ ?><script>setSelectValue("#form_spacing","<?=$entry["form_spacing"]?>")</script><? } ?>
	</td>
	<td class=title><?=readLanguage(forms,form_class)?>:</td>
	<td><div class=class_input class-bind=form_class class-properties="<?=$entry["form_class"]?>"></div></td>	
</tr>
<tr>
	<td class=title><?=readLanguage(forms,closed)?>:</td>
	<td>
		<div class=switch><label><?=readLanguage(plugins,message_no)?><input type=checkbox name=closed id=closed onchange="toggleVisibility(this)" value=1 <?=($entry["closed"] ? "checked" : "")?>><span class=lever></span><?=readLanguage(plugins,message_yes)?></label></div>
		<script>
		$(document).ready(function(){
			toggleVisibility($("#closed")[0])
		});
		</script>
	</td>
	<td class=title><?=readLanguage(forms,require_login)?>: </td>
	<td>
		<div class=switch><label><?=readLanguage(plugins,message_no)?><input type=checkbox name=require_login id=require_login	onchange="toggleVisibility(this)" value=1 <?=($entry["require_login"] ? "checked" : "")?>><span class=lever></span><?=readLanguage(plugins,message_yes)?></label></div>
		<script>
		$(document).ready(function(){
			toggleVisibility($("#require_login")[0])
		});
		</script>
	</td>
</tr>
<tr visibility-control=require_login visibility-value=1>
	<td class=title><?=readLanguage(forms,login_message)?>:</td>
	<td colspan=3><textarea class=mceEditor name=login_message><?=$entry["login_message"]?></textarea></td>
</tr>
<tr visibility-control=closed visibility-value=1>
	<td class=title><?=readLanguage(forms,closed_message)?>:</td>
	<td colspan=3><textarea class=mceEditor name=closed_message><?=$entry["closed_message"]?></textarea></td>
</tr>
<tr>
	<td class=title><?=readLanguage(forms,submit_conditions)?>:</td>
	<td colspan=3>
		<div class=check_container id=checkboxes>
			<label visibility-control=require_login visibility-value=1><input type=checkbox name=once_user class=filled-in value=1 <?=($entry["once_user"] ? "checked" : "")?>><span><?=readLanguage(forms,once_per_user)?></span></label>
			<label><input type=checkbox name=once_device class=filled-in value=1 <?=($entry["once_device"] ? "checked" : "")?>><span><?=readLanguage(forms,once_per_device)?></span></label>
			<label><input type=checkbox name=once_ip class=filled-in value=1 <?=($entry["once_ip"] ? "checked" : "")?>><span><?=readLanguage(forms,once_per_ip)?></span></label>
		</div>
	</td>
</tr>
<tr>
	<td class=title><?=readLanguage(builder,attributes)?>:</td>
	<td colspan=3 data-multiple=form_attributes>
		<button type=button class="btn btn-primary btn-sm" onclick="multipleDataCreate('form_attributes')"><?=readLanguage(operations,insert)?></button>
		<input type=hidden name=form_attributes>
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
		<? if ($entry["form_attributes"]){ ?>
		<script>
		var jsonArray = <?=$entry["form_attributes"]?>;
		jsonArray.forEach(function(entry){ multipleDataCreate("form_attributes", entry); });
		</script>
		<? } ?>
	</td>
</tr>
</table>
</div>

<!-- Form Inputs -->
<div id=inputs class=tab-pane>
<input type=hidden name=form value="<?=$entry["form"]?>">
<div class="row grid-container">
	<div class="col-md-4 grid-item">
		<ul class=elements id=elements>
			<? foreach ($elements as $type => $element) { ?>
			<li class=element-item data-type="<?=$type?>">
				<div class=element-title><i class="<?=$element['icon']?> fa-fw"></i>&nbsp;<span><?=$element['title']?></span></div>
				<div class="element-controls d-none">
					<button type=button class="btn btn-sm btn-secondary" onclick="showModal($(this).closest('li'))"><i class="fa fa-cog"></i></button>&nbsp;
					<button type=button class="btn btn-sm btn-info" onclick="cloneElement($(this).closest('li'))"><i class="fa fa-copy"></i></button>&nbsp;
					<button type=button class="btn btn-sm btn-danger" onclick="$(this).closest('.element-item').remove()"><i class="fa fa-times"></i></button>
				</div>
			</li>
			<? } ?>
		</ul>
	</div>
	<div class="col-md-16 grid-item">
		<ul class=form id=form></ul>
	</div>
</div>
</div>

</div><!-- End Tabs Content -->

<div class=submit_container><input type=button class=submit value="<?=$button?>"></div>
</form>

<!-- Include class selection snippet -->
<? include "includes/_select_class.php"; ?>

<!-- Form Builder -->
<style>
.elements {
	background: #fff;
	border-radius: 5px;
	overflow: hidden;
	border: 1px solid #ccc;
	margin: 0;
	padding: 0;
}

.elements li {
	padding: 10px;
	background: #fff;
	border-bottom: 1px solid #ccc;
	cursor: grab;
}

.elements li:active {
	cursor: grabbing;
}

.elements li:last-child {
	border-bottom: 0;
}

.element-item {
	display: flex;
	align-items: center;
}

.form {
	min-height: 100%;
	margin: 0;
	padding: 0;
	display: flex;
	align-items: flex-start;
	justify-content: flex-start;
	align-content: flex-start;
	flex-wrap: wrap;
	width: calc(100% + 10px);
}

.form li {
	background: #fff;
	width: 100%;
	border-radius: 5px;
	border: 1px solid #eee;
	box-shadow: 2px 2px 8px rgba(0, 0, 0, .1);
	height: 40px !important;
	padding: 10px 5px 10px 10px;
	max-width: 100% !important;
	margin: 0 10px 10px 0;
	cursor: grab;
}

.form li:active {
	cursor: grabbing;
}

@media screen and (max-width: 768px) {
	.form li {
		min-width: calc(50% - 10px);
	}
}

.form li:lang(ar) {
	padding: 10px 10px 10px 5px;
	margin: 0 0 10px 10px;
}

.form li .element-title {
	flex-grow: 1;
	display: flex;
	align-items: center;
	max-width: calc(100% - 105px);
}

.form li .element-title span {
	display: block;
	white-space: nowrap;
	overflow: hidden;
	text-overflow: ellipsis;
}

.form li .element-controls {
	display: flex !important;
}

.form:empty {
	border: 2px dashed #ddd;
	border-radius: 5px;
	display: flex;
	align-items: center;
	justify-content: center;
}


/* Tabs */

#fieldModal .tab-inline-header {
	background: #f8f8f8;
}

.inputs .tab-pane {
	display: flex;
	flex-wrap: wrap;
	margin-bottom: -10px;
	width: calc(100% + 10px);
}

.inputs .tab-pane:not(.active) {
	display: none;
}

.inputs .tab-pane>div {
	display: flex;
	flex-direction: column;
	width: calc(50% - 10px);
	flex-grow: 1;
	margin: 0 10px 10px 0;
}

.inputs:lang(ar) .tab-pane>div {
	margin: 0 0 10px 10px;
}

.inputs .tab-pane>div.grow {
	flex-basis: 100%;
}

.inputs .tab-pane>div b {
	display: flex;
	align-items: center;
	margin-bottom: 5px;
}

.inputs .tab-pane>div b span {
	flex-grow: 1;
}

.inputs .tab-pane [data-icon=icon] {
	min-width: 30px;
}

.pika-single {
	z-index: 9999999999 !important;
}

.select2-container--open {
	z-index: 9999999999 !important;
}


/* UI Helpers */

.builder-element-helper {
	background: #fff;
	width: 100%;
	border-radius: 5px;
	border: 1px solid #eee;
	box-shadow: 2px 2px 8px rgba(0, 0, 0, .1);
	cursor: grabbing !important;
}

.builder-form-highlight {
	width: 100%;
	height: 40px !important;
	min-width: 30px;
	min-height: 40px;
	margin: 0 10px 10px 0;
	border-radius: 3px;
}

.builder-form-highlight:lang(ar) {
	margin: 0 0 10px 10px;
}
</style>

<!-- [Modal] Form Builder Elements -->
<div id=fieldModal class="modal fade" data-backdrop=static><div class=modal-dialog><div class=modal-content>
<input type=hidden data-name=uniqueId>

<!-- ===== Modal Header ===== -->
<div class=modal-header>
	<button type=button class=close data-dismiss=modal>&times;</button>
	<h4 class=modal-title><?=readLanguage(forms,edit)?> <span></span></h4>
</div>

<!-- ===== Modal Body ===== -->
<div class="modal-body inputs">
	<!-- Start Tabs -->
	<ul class="nav nav-tabs tab-inline-header">
		<li class=active><a data-toggle=tab href="#properties"><?=readLanguage(forms,properties)?></a></li>
		<li><a data-toggle=tab href="#options"><?=readLanguage(forms,options)?></a></li>
		<li><a data-toggle=tab href="#validations"><?=readLanguage(forms,validations)?></a></li>
		<li><a data-toggle=tab href="#attributes"><?=readLanguage(builder,attributes_attribute)?></a></li>
	</ul>
	<div class="tab-content tab-inline">

	<!-- ===== [Properties] ===== -->
	<div id=properties class="tab-pane in active">

		<!-- Label -->
		<div data-prop-container>
			<b><?=readLanguage(forms,label)?></b>
			<span><input type=text data-prop=label></span>
		</div>

		<!-- Width -->
		<div data-prop-container>
			<b><?=readLanguage(builder,width)?></b>
			<span><select data-prop=width>
			<? foreach ($data_module_widths AS $key => $value){ ?>
				<option value="<?=$key?>"><?=$key?>%</option>
			<? } ?>
			</select></span>
		</div>

		<!-- Icon -->
		<div data-prop-container>
			<b><?=readLanguage(plugins,icon)?></b>
			<span class="d-flex">
				<i data-icon=icon></i>&nbsp;
				<input type=text name=icon data-prop=icon onkeyup="$('[data-icon=icon]').attr('class',this.value)" autocomplete=off>&nbsp;
				<button type=button class="btn btn-default btn-sm btn-square flex-center" onclick="bindIconSearch('icon')"><i class="fas fa-search"></i>&nbsp;<?=readLanguage(operations,select)?></button>
			</span>
		</div>

		<!-- Placeholder -->
		<div data-prop-container>
			<b><?=readLanguage(builder,placeholder)?></b>
			<span><input type=text data-prop=placeholder></span>
		</div>

		<!-- Max Length -->
		<div data-prop-container>
			<b><?=readLanguage(forms,max_length)?></b>
			<span class=d-flex><input type=number data-prop=maxlength></span>
		</div>

		<!-- Verbose -->
		<div data-prop-container>
			<b><?=readLanguage(forms,verbose)?></b>
			<span class=check_container><label><input type=checkbox class=filled-in data-prop=verbose><span><?=readLanguage(forms,show_max_option)?></span></label></span>
		</div>

		<!-- Content -->
		<div class=grow data-prop-container>
			<b><?=readLanguage(inputs,content)?></b>
			<span><textarea data-prop=content></textarea></span>
		</div>

		<!-- Description -->
		<div data-prop-container style="width: 100%;">
			<b><?=readLanguage(plugins,marker_description)?></b>
			<span><input type=text data-prop=description></span>
		</div>

		<!-- Default Value -->
		<div data-prop-container>
			<b><?=readLanguage(forms,default_value)?></b>
			<span><input type=text data-prop=default></span>
		</div>

		<!-- Min Date -->
		<div data-prop-container>
			<b><?=readLanguage(forms,min_date)?></b>
			<span><select data-prop=min_date name=min_date onchange="toggleVisibility(this)">
			<option value=""><?=readLanguage(builder,none)?></option>
			<option value="fixed"><?=readLanguage(forms,fixed)?></option>
			<option value="custom"><?=readLanguage(builder,custom)?></option>
			</select></span>
		</div>

		<!-- Max Date -->
		<div data-prop-container>
			<b><?=readLanguage(forms,max_date)?></b>
			<span><select data-prop=max_date name=max_date onchange="toggleVisibility(this)">
			<option value=""><?=readLanguage(builder,none)?></option>
			<option value="fixed"><?=readLanguage(forms,fixed)?></option>
			<option value="custom"><?=readLanguage(builder,custom)?></option>
			</select></span>
		</div>

		<!-- Min Date Fixed -->
		<div data-prop-container visibility-control=min_date visibility-value="fixed">
			<b><?=readLanguage(forms,min_date_fixed)?></b>
			<span><input type=text data-prop=min_date_fixed id=min_date_fixed class=date_field readonly></span>
			<script>var min_date_fixed = createCalendar('min_date_fixed', new Date());</script>
		</div>

		<!-- Max Date Fixed -->
		<div data-prop-container visibility-control=max_date visibility-value="fixed">
			<b><?=readLanguage(forms,max_date_fixed)?></b>
			<span><input type=text data-prop=max_date_fixed id=max_date_fixed class=date_field readonly></span>
			<script>var max_date_fixed = createCalendar('max_date_fixed', new Date());</script>
		</div>
		
		<!-- Min Date Custom -->
		<div data-prop-container visibility-control=min_date visibility-value="custom">
			<b><?=readLanguage(forms,min_date_custom)?></b>
			<span><input type=text data-prop=min_date_custom></span>
		</div>

		<!-- Max Date Custom -->
		<div data-prop-container visibility-control=max_date visibility-value="custom">
			<b><?=readLanguage(forms,max_date_custom)?></b>
			<span><input type=text data-prop=max_date_custom></span>
		</div>

		<!-- Allowed Mimes -->
		<div class=grow data-prop-container>
			<b><?=readLanguage(forms,allowed_mimes)?></b>
			<span><select data-prop=allowed_mimes multiple>
			<? foreach ($allowed_extensions as $key => $value) { ?>
				<option value="<?=$key?>"><?=$value?></option>
			<? } ?>
			</select></span>
			<script>
			$('[data-prop=allowed_mimes]').select2({tags: true, placeholder: readLanguage.forms.leave_empty});
			</script>
		</div>

	</div>

	<!-- ===== [Options] ===== -->
	<div id=options class=tab-pane>

		<!-- Value source -->
		<div data-prop-container>
			<b><?=readLanguage(forms,value_source)?></b>
			<span><select data-prop=value_source name=value_source onchange="toggleVisibility(this); renderSourceTargets(this)">
			<option value=""><?=readLanguage(builder,custom)?></option>
			<?=$value_sources?>
			</select></span>
		</div>
		
		<!-- Value source -->
		<div data-prop-container visibility-control=value_source visibility-value="<?=($available_queries ? implode(",", $available_queries) : "undefined")?>">
			<b><?=readLanguage(forms,source_target)?></b>
			<span><select data-prop=source_target name=source_target></select></span>
		</div>		

		<!-- Allow Other -->
		<div data-prop-container>
			<b><?=readLanguage(forms,enable_other)?></b>
			<span class=check_container><label><input type=checkbox class=filled-in data-prop=allow_other><span><?=readLanguage(forms,user_enter_ul)?></span></label></span>
		</div>

		<!-- Allow Search -->
		<div data-prop-container>
			<b><?=readLanguage(forms,enable_search)?></b>
			<span class=check_container><label><input type=checkbox class=filled-in data-prop=allow_search><span><?=readLanguage(forms,users_search_selections)?></span></label></span>
		</div>

		<!-- Options -->
		<div class=grow data-prop-container data-multiple=options visibility-control=value_source visibility-value="">
			<b class=clear-margin>
				<span><?=readLanguage(forms,values)?></span>
				<div class=d-flex>
					<button type=button class="btn btn-secondary btn-sm" onclick="$('[data-multiple=options]').find('input:not([type=text])').prop('checked', false)"><?=readLanguage(forms,uncheck_all)?></button>&nbsp;
					<button type=button class="btn btn-primary btn-sm" onclick="multipleDataCreate('options')"><?=readLanguage(forms,add_item)?></button>
				</div>
			</b>
			<span>
			<input type=hidden name=options data-prop=options>
			<ul multiple-sortable>
				<li data-template>
					<div class=d-flex>
						<div class="grabbable grabbable_icon"><i class="fas fa-bars"></i></div>&nbsp;&nbsp;
						<input type=text data-name=value placeholder=<?=readLanguage(forms,enter_option)?>>&nbsp;&nbsp;
						<div class="radio_container check_container"><label><input type=radio data-type=checkbox name=default data-name=default class=filled-in><span><?=readLanguage(builder,basic)?></span></label></div>&nbsp;&nbsp;
						<a class="btn btn-success btn-sm add" onclick="multipleDataCreate('options')"><i class="fas fa-plus"></i></a>&nbsp;
						<a class="btn btn-danger btn-sm remove"><i class="fas fa-times"></i></a>
					</div>
				</li>
			</ul>
			</span>
		</div>
	</div>

	<!-- ===== [Attributes] ===== -->
	<div id=attributes class=tab-pane>

		<!-- Custom Attributes -->
		<div class=grow data-prop-container data-multiple=attributes>
			<b class=clear-margin>
				<span><?=readLanguage(builder,attributes)?></span>
				<button type=button class="btn btn-primary btn-sm" onclick="multipleDataCreate('attributes')"><?=readLanguage(forms,add_item)?></button>
			</b>
			<span>
				<input type=hidden name=attributes data-prop=attributes>
				<ul multiple-sortable>
					<li data-template>
						<div class=d-flex>
						<div class="grabbable grabbable_icon"><i class="fas fa-bars"></i></div>&nbsp;&nbsp;
						<input type=text data-name=attribute placeholder=<?=readLanguage(builder,attributes_attribute)?>>&nbsp;&nbsp;
						<input type=text data-name=value placeholder=<?=readLanguage(builder,attributes_value)?>>&nbsp;&nbsp;
						<a class="btn btn-success btn-sm add" onclick="multipleDataCreate('attributes')"><i class="fas fa-plus"></i></a>&nbsp;
						<a class="btn btn-danger btn-sm remove"><i class="fas fa-times"></i></a>
						</div>
					</li>
				</ul>
			</span>
		</div>
	</div>

	<!-- ===== [Validations] ===== -->
	<div id=validations class=tab-pane>
		<!-- Mandatory -->
		<div data-prop-container>
			<b><?=readLanguage(forms,mandatory)?></b>
			<span class=check_container><label><input type=checkbox class=filled-in data-prop=mandatory><span><?=readLanguage(forms,mandatory_placeholder)?></span></label></span>
		</div>
		
		<!-- Error Message -->
		<div data-prop-container>
			<b><?=readLanguage(forms,error_message)?></b>
			<span><input type=text data-prop=error_msg></span>
		</div>

		<!-- Regex -->
		<div data-prop-container>
			<b><?=readLanguage(forms,regular_expression)?></b>
			<span><input type=text data-prop=regex></span>
		</div>

		<!-- Float -->
		<div data-prop-container>
			<b><?=readLanguage(forms,allow_float)?></b>
			<span class=check_container><label><input type=checkbox class=filled-in data-prop=allow_float><span><?=readLanguage(forms,allow_float_values)?></span></label></span>
		</div>

		<!-- Step -->
		<div data-prop-container>
			<b><?=readLanguage(forms,step)?></b>
			<span><input type=number data-prop=step placeholder="<?=readLanguage(forms,leave_empty_default)?>"></span>
		</div>

		<!-- Minimum Value -->
		<div data-prop-container>
			<b><?=readLanguage(forms,min_value)?></b>
			<span><input type=number data-prop=min_number placeholder="<?=readLanguage(forms,leave_empty_default)?>"></span>
		</div>

		<!-- Maximum Value -->
		<div data-prop-container>
			<b><?=readLanguage(forms,max_value)?></b>
			<span><input type=number data-prop=max_number placeholder="<?=readLanguage(forms,leave_empty_default)?>"></span>
		</div>

		<!-- Minimum Selections -->
		<div data-prop-container>
			<b><?=readLanguage(forms,min_selections)?></b>
			<span><input type=number data-prop=min_selections placeholder="<?=readLanguage(forms,leave_empty_default)?>"></span>
		</div>

		<!-- Maximum Selections -->
		<div data-prop-container>
			<b><?=readLanguage(forms,max_selections)?></b>
			<span><input type=number data-prop=max_selections placeholder="<?=readLanguage(forms,leave_empty_default)?>"></span>
		</div>
	</div>

	<!-- End Tabs -->
	</div>
</div>

<!-- ===== Modal Footer ===== -->
<div class=modal-footer>
	<button type=button class="btn btn-primary" onclick="saveElement()"><?=readLanguage(plugins,message_save)?></button>
	<button type=button class="btn btn-default" data-dismiss=modal><?=readLanguage(plugins,message_close)?></button>
</div>
</div></div></div>

<script>
const fieldModal = $("#fieldModal");
const elements = JSON.parse('<?=json_encode($elements, true)?>');
const multipleOptions = ['multiple_select', 'checkbox'];

//Render source targets from custom query
function renderSourceTargets(select){
	var option = $(select).find("option:selected").attr("source-targets");
	$("[data-prop=source_target]").empty();
	var json = validateJSON(option);
	console.log(json);
	if (json){
		for (const [key, value] of Object.entries(json)) {
			$("[data-prop=source_target]").append("<option value='" + key+ "'>" + value.label + "</option>");
		}
	}
}

//Create object tree with tab-id => tab-props
const tabsMap = Array.from($('.tab-pane')).reduce((acc, cur) => {
	return ({...acc, [cur.id]: Array.from($(cur).find('[data-prop]')).map(el => el.dataset.prop)});
}, {});

//Show edit modal
function showModal(element){
	const type = element.attr('data-type');
	const props = elements[type].properties;
	const content = element.attr('data-content') ? JSON.parse(element.attr('data-content')) : [];
	
	//Toggle tabs visibility
	Object.values(tabsMap).forEach((arr, i) => {
		let tabId = Object.keys(tabsMap)[i];
		if (!arr.reduce((acc, cur) => props.includes(cur) ? acc + 1 : acc,  0)) fieldModal.find(`.nav-tabs a[href='#${tabId}']`).closest('li').hide();
		else fieldModal.find(`.nav-tabs a[href='#${tabId}']`).closest('li').show();
	});

	//Set modal title
	fieldModal.find('.modal-title span').text(element.find('.element-title span').text());

	//Toggle props visibility
	fieldModal.find('[data-prop]').each((i, el) => {
		let prop = $(el).attr('data-prop');

		if (props.includes(prop)){
			
			$(el).closest('[data-prop-container]').show();

			switch (prop){
				//Width
				case 'width':
					$(el).val(content[prop] ?? '100');
				break;
				
				//Icon
				case 'icon':
					$(el).val(content[prop] ?? '');
					$(el).siblings('[data-icon=icon]').attr('class', content[prop] ?? '');
				break;

				//Options
				case 'options':
					let options = content[prop] || [];
					$(el).val(content[prop] ?? '');
					$('[data-multiple=options] li:not([data-template])').remove();
					$('[data-multiple=options] li').find('input:not([type=text])').prop('type', multipleOptions.includes(type) ? 'checkbox' : 'radio');
					options.forEach(option => multipleDataCreate('options', option));
				break;

				//Attributes
				case 'attributes':
					let attributes = content[prop] || [];
					$(el).val(content[prop] ?? '');
					$('[data-multiple=attributes] li:not([data-template])').remove();
					attributes.forEach(attribute => multipleDataCreate('attributes', attribute));
				break;

				//Allowed Mimes
				case 'allowed_mimes':
					$(el).val(content[prop]).trigger('change');
				break;

				//Calendar (Minimum Date)
				case 'min_date_fixed':
					if (content[prop]){
						var split = content[prop].split('/');
						var min_date = new Date(split[2], split[1] - 1, split[0], 12, 0, 0);
					}
					min_date_fixed.setDate((min_date ?? new Date()));
				break;

				//Calendar (Maximum Date)
				case 'max_date_fixed':
					if (content[prop]){
						var split = content[prop].split('/');
						var max_date = new Date(split[2], split[1] - 1, split[0], 12, 0, 0);
					}
					max_date_fixed.setDate((max_date ?? new Date()));
				break;

				//Source Target
				case 'source_target':
				fieldModal.on('shown.bs.modal', function(){
					$(el).val(content[prop]);
				});
				break;

				//Other
				default:
					$(el).val(content[prop] ? decodeHTML(content[prop]) : '');
				break;
			}

			if (['radio', 'checkbox'].includes($(el).attr('type'))){
				$(el).prop('checked', +content[prop] ? true : false).val(+content[prop] ? 1 : 0);
			}
		} else {
			$(el).closest('[data-prop-container]').hide();
		}
	});

	fieldModal.find('.nav-tabs li:first-child a').trigger('click');
	fieldModal.find('[data-name=uniqueId]').val(element.attr('data-id'));
	fieldModal.modal('show');

	//Apply ToggleVisibility
	fieldModal.find('[visibility-control]').each((i, el) => {
		let controller = $(`[name=${$(el).attr('visibility-control')}]`);
		if (props.includes(controller.attr('data-prop'))) controller.trigger('change');
	});
}

//Clone element
function cloneElement(element){
	const clonedElement = element.clone().attr('data-id', uniqid(`${element.attr('data-type')}-`));
	element.after(clonedElement);
	showModal(clonedElement);
}

//Save element
function saveElement(){
	const element = $(`[data-id='${fieldModal.find('[data-name=uniqueId]').val()}']`);
	const props = elements[element.attr('data-type')].properties;

	multipleDataBuild();
	let content = props.reduce((acc, cur) => {
		const propElement = fieldModal.find(`[data-prop=${cur}]`);
		const value = ['radio', 'checkbox'].includes(propElement.attr('type')) ? +propElement.is(':checked') : propElement.val();
		return ({...acc, [cur]: validateJSON(value) || value});
	}, {});

	console.log(content);
	element.css("width", `calc(${content.width}% - 10px)`);
	element.attr("data-content", JSON.stringify(content));
	element.find("span").html((content.label ? content.label : $('#elements').find("[data-type='" + element.attr('data-type') + "'] span").text()) + (content.mandatory==1 ? " <i class=requ></i>" : ""));
	fieldModal.modal('hide');
}

//Build form
function buildForm(){
	let jsonData = [];
	$('#form').find('li').each((i, el) => {
		let {id, type, content = ''} = el.dataset;
		if (content.length) jsonData.push({id, type, properties: JSON.parse(content, (key, value) => validateJSON(value) || value)});
	});
	$('[name=form]').val(jsonData.length ? JSON.stringify(jsonData) : null);
}

//Load form elements on edit
<? if ($entry['form']){ ?>
	function parseObjectHTML(object){
		for (let i in object){
			if (typeof object[i] == "object" && object[i] !== null){
				parseObjectHTML(object[i]);
			} else {
				object[i] = decodeHTML(object[i]);
			}
		}
	}

	$(document).ready(() => {
		let form = validateJSON('<?=$entry['form']?>') || [];
		form.forEach(entry => {
			let {id, type, properties} = entry;
			parseObjectHTML(properties);
			var clone = $('#elements').find(`[data-type='${type}']`).clone().css("width", `calc(${properties.width}% - 10px)`).attr({"data-id": id, "data-content": JSON.stringify(properties)});
			clone.find("span").html((properties.label ? properties.label : $('#elements').find("[data-type='" + clone.attr('data-type') + "'] span").text()) + (properties.mandatory==1 ? " <i class=requ></i>" : ""));
			$('#form').append(clone);
		});
	});
<? } ?>

//Initialize draggable
$("#elements li").draggable({
	revert: false,
	helper: 'clone',
	connectToSortable: '#form',
    start: function(event, ui){
        $(ui.helper).addClass('builder-element-helper');
    },
	stop:function(event,ui){
		$(ui.helper).removeClass('builder-element-helper');
	}
});

//Initialize sortable
$("#form").sortable({
	connectWith: '#elements',
	receive: function (e, {helper}){
		helper.attr('data-id', uniqid(`${helper.attr('data-type')}-`)).css("width", "100%");
		showModal(helper);
	},
	placeholder: {
		element: function(element){
			let width = (element.attr("data-id") ? `${element.width()}px !important` : "100%");
			return $("<div class='builder-form-highlight ui-state-highlight' style='width:" + width + "'></div>");
		},
		update: function(){
			return;
		}
	}
});

//Disable selection on elements and form
$("#elements, #form").disableSelection();

//Build form before validation
function onBeforeValidation(){
	buildForm();
}
</script>

<div class=crud_separator></div>
<?
$custom_list = array(
	["label"=>readLanguage(builder,duplicate), "icon"=>"fas fa-clone", "href"=>"$base_name.php?clone=%s"],
	["label"=>readLanguage(crud,button_export), "icon"=>"fas fa-download", "click"=>"exportBuilder('form', '%d[uniqid]', '%d[placeholder]')"],
);
$custom_list = htmlentities(json_encode($custom_list, JSON_UNESCAPED_UNICODE));

$crud_data["delete_record_message"] = "title";
$crud_data["buttons"] = array(true,true,false,true,true); //Add - Search - View - Edit - Delete
$crud_data["columns"] = array(
	array("uniqid",readLanguage(builder,selector),"200px","center",null,false,true),
	array("placeholder",readLanguage(builder,placeholder),"100%","center",null,false,true),
	array("tags",readLanguage(builder,tags),"300px","center","implodeVariable('%s')",true,false),
	array("id", readLanguage(crud,button_operations), "140px", "fixed-right", "customDropdown(\"$custom_list\", '<i class=\"fas fa-cogs\"></i>&nbsp;" . readLanguage(crud,button_operations) . "')", false, true),
);

require_once("crud/crud.php");
include "_footer.php"; ?>