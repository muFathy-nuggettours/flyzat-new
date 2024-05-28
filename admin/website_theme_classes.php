<? $skip_compress=true; include "system/_handler.php";

$multiple_languages = false;
$mysqltable = "website_theme_classes";
$base_name = basename($_SERVER["SCRIPT_FILENAME"], ".php");
checkPermissions($base_name);

//Upload background image
if ($post["token"] && $post["image"]){
	$path = "../uploads/classes/";
	$image = imgUpload($_FILES["image"], $path);
	if ($image){
		exit($path . $image);
	} else {
		header("HTTP/1.1 400 Bad Request");
		exit(readLanguage(plugins,upload_error));
	}
}

//==== DELETE Record ====
if ($delete){
	mysqlQuery("DELETE FROM $mysqltable WHERE id=$delete");
	
	//Build classes CSS
	buildCSSClasses();
	
	//Build Theme
	if (file_exists("../website/website.min.css")){
		buildWebsiteTheme();
	}
	
	if (mysqlAffectedRows()){ $success = readLanguage(records,deleted); } else { $error = readLanguage(records,unavailable); }

//==== ADD Record ====
} else if ($post["token"] && !$edit){
	$query = "INSERT INTO $mysqltable (
		placeholder,
		tags,
		class,
		json,
		css,
		custom_css,
		priority
	) VALUES (
		'" . $post["placeholder"] . "',
		'" . implode(",", $post["tags"]) . "',
		'" . $post["class"] . "',
		'" . $post["json"] . "',
		'" . $post["css"] . "',
		'" . $post["custom_css"] . "',
		'" . newRecordID($mysqltable) . "'
	)";
	mysqlQuery($query);
	
	//Build classes CSS
	buildCSSClasses();
	
	//Build Theme
	if (file_exists("website/website.min.css")){
		buildWebsiteTheme();
	}
	
	$success = readLanguage(records,added);

//==== EDIT Record ====	
} else if ($post["token"] && $edit){
	$record_data = getID($edit,$mysqltable);
	$query = "UPDATE $mysqltable SET
		placeholder='" . $post["placeholder"] . "',
		tags='" . implode(",", $post["tags"]) . "',
		json='" . $post["json"] . "',
		css='" . $post["css"] . "',
		custom_css='" . $post["custom_css"] . "'
	WHERE id=$edit";
	mysqlQuery($query);
	
	//Build classes CSS
	buildCSSClasses();
	
	//Build Theme
	if (file_exists("website/website.min.css")){
		buildWebsiteTheme();
	}
	
	$success = readLanguage(records,updated);
}

//Read and Set Operation
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

//Set class name
$class_name = ($edit ? $entry["class"] : "class-" . uniqid());

//Read cloned class
if ($get["clone"]){
	$entry = getID($get["clone"], $mysqltable);
	$entry["custom_css"] = str_replace($entry["class"], $class_name, $entry["custom_css"]);
}

include "_header.php"; ?>

<div class=title><?=getPageTitle($base_name)?></div>
<?=$message?>

<form method=post enctype="multipart/form-data" action="<?=$action?>">
<input type=hidden name=token value="<?=$token?>">
<input type=hidden name=json>
<input type=hidden name=class value="<?=$class_name?>">

<table class="data_table margin-bottom">
<tr>
	<td class=title><?=readLanguage(builder,class_name)?>: <i class=requ></i></td>
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
</table>

<!-- CSS Editor -->
<? include "includes/_class_builder.php"; ?>

<div class=submit_container><input type=button class=submit value="<?=$button?>"></div>
</form>

<div class=crud_separator></div>
<?
$custom_list = array(
	["label"=>readLanguage(builder,duplicate), "icon"=>"fas fa-clone", "href"=>"$base_name.php?clone=%s"],
	["label"=>readLanguage(crud,button_export), "icon"=>"fas fa-download", "click"=>"exportBuilder('css', '%d[class]', '%d[placeholder]')"],
);
$custom_list = htmlentities(json_encode($custom_list, JSON_UNESCAPED_UNICODE));

$crud_data["order_field"] = "priority";
$crud_data["order_by"] = "priority DESC";
$crud_data["delete_record_message"] = "placeholder";
$crud_data["buttons"] = array(true,true,false,true,true); //Add - Search - View - Edit - Delete
$crud_data["columns"] = array(
	array("class",readLanguage(builder,selector),"200px","center",null,false,true),
	array("placeholder",readLanguage(builder,placeholder),"100%","center",null,false,true),
	array("tags",readLanguage(builder,tags),"300px","center","implodeVariable('%s')",true,false),
	array("id", readLanguage(crud,button_operations), "140px", "fixed-right", "customDropdown(\"$custom_list\", '<i class=\"fas fa-cogs\"></i>&nbsp;" . readLanguage(crud,button_operations) . "')", false, true),
);
require_once("crud/crud.php");
?>

<script>
<? if ($entry["json"]){ ?>
setClass('<?=$entry["json"]?>');
<? } ?>

//Save json class before validation
function onBeforeValidation(){
	var json = {};
	for (const [pseudo, values] of Object.entries(css_class)){
		if (values.object){
			json[pseudo] = values.object;
		}
	};
	$("[name=json]").val(JSON.stringify(json));
}
</script>

<? include "_footer.php"; ?>