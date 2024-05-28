<? include "system/_handler.php";

$multiple_languages = false;
$mysqltable = "system_queries";
$base_name = basename($_SERVER["SCRIPT_FILENAME"], ".php");
checkPermissions($base_name);

//Replace queries placeholders
function decodeQueryStatements($rules){
	global $conditions;
	foreach ($rules as $rule){
		if ($rule['data']){
			$conditions = str_replace($rule['field'], base64_decode($rule['data'][0]), $conditions);
		} else if ($rule['rules']){
			decodeQueryStatements($rule['rules']);
		}
	}
}

//Tables array
include "system/schema.php";

//==== DELETE Record ====
if ($delete){
	mysqlQuery("DELETE FROM $mysqltable WHERE id=$delete");
	if (mysqlAffectedRows()){ $success = readLanguage(records,deleted); } else { $error = readLanguage(records,unavailable); }

//==== ADD Record ====
} else if ($post["token"] && !$edit){
	$conditions = html_entity_decode($post["conditions"], ENT_QUOTES);
	
	//Replace date with timestamp
	preg_replace_callback('^\\d{1,2}/\\d{1,2}/\\d{4}^', function ($e){
		global $conditions;
		$conditions = str_replace("\'{$e[0]}\'", getTimestamp($e[0]), $conditions);
	}, $conditions);

	$conditions_json = json_decode($post["conditions_json"], true);
	decodeQueryStatements($conditions_json["rules"]);
	$conditions = str_replace("@", "", $conditions);

	$query = "INSERT INTO $mysqltable (
		title,
		target,
		conditions,
		conditions_json,
		sort_column,
		sort_method
	) VALUES (
		'$post[title]',
		'$post[target]',
		'$conditions',
		'$post[conditions_json]',
		'$post[sort_column]',
		'$post[sort_method]'
	)";

	mysqlQuery($query);
	$success = readLanguage(records, added);

//==== EDIT Record ====
} else if ($post["token"] && $edit){
	$conditions = html_entity_decode($post["conditions"], ENT_QUOTES);
	
	//Replace date with timestamp
	preg_replace_callback('^\\d{1,2}/\\d{1,2}/\\d{4}^', function ($e){
		global $conditions;
		$conditions = str_replace("\'{$e[0]}\'", getTimestamp($e[0]), $conditions);
	}, $conditions);

	$conditions_json = json_decode($post["conditions_json"], true);
	decodeQueryStatements($conditions_json["rules"]);
	$conditions = str_replace("@", "", $conditions);

	$query = "UPDATE $mysqltable SET
		title='$post[title]',
		target='$post[target]',
		conditions='$conditions',
		conditions_json='$post[conditions_json]',
		sort_column='$post[sort_column]',
		sort_method='$post[sort_method]'
	WHERE id=$edit";

	mysqlQuery($query);
	$success = readLanguage(records, updated);
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

include "_header.php"; ?>

<script src="../plugins/query-builder.min.js?v=<?= $rafed_settings["rafed_version"] ?>"></script>
<link rel="stylesheet" href="../plugins/query-builder.min.css?v=<?= $rafed_settings["rafed_version"] ?>">

<style>
.query-builder .rules-group-header {
	display: flex;
	flex-wrap: wrap;
}

.query-builder .group-actions {
	flex-grow: 1;
}

@media screen and (max-width: 576px){
	.query-builder .group-actions {
		margin-bottom: 5px !important;
	}
}

.query-builder .rule-value-container {
	border: 0;
	padding: 0;
}

.query-builder .btn {
	display: flex;
	align-items: center;
}

.query-builder .btn .fas {
	margin: 0 5px 0 0;
}

.query-builder .btn .fas {
	margin: 0 5px 0 0;
}

.query-builder .has-error .error-container {
	display: none !important;
}

.rule-container {
	display: flex;
	flex-wrap: wrap;
	align-items: center;
	padding-bottom: 0 !important;
}

.rule-container > div {
	margin-bottom: 5px !important;
}

.rule-container input,
.rule-container select {
	border-radius: 3px;
}

.rule-value-container {
	display: flex !important;
	align-items: flex-end;
}

.rules-list:empty {
	margin-top: -5px;
}

.query-builder:lang(ar) .btn .fas {
	margin: 0 0 0 5px;
}

.query-builder .rule-actions .btn {
	margin: 0 5px 0 0;
}

.query-builder .rules-group-container {
	border-color: #ccc;
	background-color: rgba(0,0,0,.025);
}
</style>

<div class=title><?=getPageTitle($base_name)?></div>
<?=$message?>

<? if (!$schema){ ?>
<div class="alert alert-warning align-center"><?=readLanguage(crud,records_empty)?></div>

<? } else { ?>
<form method=post enctype="multipart/form-data" action="<?=$action?>">
<input type=hidden name=token value="<?=$token?>">
<input type=hidden name=conditions id=conditions>
<input type=hidden name=conditions_json id=conditions_json>

<div class=data_table_container>
<table class=data_table>
<tr>
	<td class=title><?=readLanguage(builder,placeholder)?>: <i class=requ></i></td>
	<td><input type=text name=title value="<?=$entry["title"]?>" data-validation=required></td>
	<td class=title><?=readLanguage(builder,query_table)?>: <i class=requ></i></td>
	<td>
		<select name=target id=target>
			<? foreach ($schema AS $table => $props){ ?>
				<option value="<?=$table?>"><?=$props['label']?></option>
			<? } ?>
		</select>
		<script>
		<? if ($entry["target"]){ ?>setSelectValue("#target", "<?=$entry["target"]?>");<? } ?>
		$("#target").select2();
		</script>
	</td>
</tr>
<tr>
	<td class=title><?=readLanguage(builder,quert_sort_column)?>: <i class=requ></i></td>
	<td>
		<select name=sort_column id=sort_column></select>
	</td>
	<td class=title><?=readLanguage(builder,query_sort_method)?>: <i class=requ></i></td>
	<td>
		<select name=sort_method id=sort_method>
			<option value="ASC"><?=readLanguage(builder,query_sort_asc)?></option>
			<option value="DESC"><?=readLanguage(builder,query_sort_desc)?></option>
		</select>
		<? if ($entry["sort_method"]){ ?><script>setSelectValue("#sort_method", "<?=$entry["sort_method"]?>")</script><? } ?>
	</td>
</tr>
<tr>
	<td class=title><?=readLanguage(builder,query_conditions)?>:</td>
	<td colspan=3><div id=builder></div></td>
</tr>
</table>
</div>

<div class=submit_container><input type=button class=submit value="<?=$button?>"></div>
</form>

<script>
//Read schema
var schema = <?=json_encode($schema, JSON_UNESCAPED_UNICODE)?>;

//Datepicker
$.fn.datePicker = function(){
	$(this).attr('id', "_" + (Math.random() * 1000).toFixed(0)).attr('readonly', true);
	return createCalendar($(this).attr('id'), new Date());
};

//Declaring constants
const tables_filters = JSON.parse('<?=json_encode(buildQueryFilters($schema), true)?>');
const defaultOptions = {
	lang_code: "<?=$language["code"]?>",
	allow_groups: 1,
	rules: null,
	default_filter: null,
	display_empty_filter: false,
	display_errors: true,
	allow_empty: true,
	icons: {
		add_group: "fas fa-plus-square",
		add_rule: "fas fa-plus",
		remove_group: "fas fa-minus-square",
		remove_rule: "fas fa-minus",
		error: "fas fa-exclamation-triangle"
	}
};

//On table selection change
$("#target").on("change", function (){
	//Destroy current builder if exists
	if ($("#builder").data("queryBuilder")){
		$("#builder").data("queryBuilder").destroy();
	}
	
	//Re-start query builder with new table parameters
	let tableFilters = tables_filters[$(this).val()];
	$("#builder").queryBuilder({filters: tableFilters, ...defaultOptions});
	
	//Restart columns for sort operation
	$("#sort_column").empty();
	var targetFields = schema[$("#target option:selected").val()]["fields"];
	for (const [key, value] of Object.entries(targetFields)){
		$("#sort_column").append(`<option value='${key}'>${value.label}</option>`);
	}
});

//Trigger table change on start
$("#target").trigger("change");

<? if ($edit){ ?>
//Update sort column on edit
setSelectValue("#sort_column", "<?=$entry["sort_column"]?>");
<? } ?>

//Read saved conditions on edit
<? if ($entry["conditions_json"]){ ?>
$("#builder").queryBuilder("setRules", JSON.parse('<?=$entry['conditions_json']?>'));
<? } ?>

//Build conditions
function onBeforeValidation(){
	//JSON (For Reading)
	let jsonResult = $("#builder").queryBuilder("getRules");
	if (!$.isEmptyObject(jsonResult)) $("#conditions_json").val(JSON.stringify(jsonResult));

	//SQL
	let sqlResult = $("#builder").queryBuilder("getSQL", false);
	if (!$.isEmptyObject(sqlResult)) $("#conditions").val(sqlResult.sql);
}
</script>

<div class=crud_separator></div>
<?
$crud_data["buttons"] = array(true, true, false, true, true); //Add - Search - View - Edit - Delete
$crud_data["columns"] = array( //Filter - Search - Copy Enabled
	array("title", readLanguage(builder,placeholder), "100%", "center", null, false, true),
);
require_once("crud/crud.php");
?>
<? } ?>

<? include "_footer.php"; ?>