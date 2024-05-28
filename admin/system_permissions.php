<? include "system/_handler.php";

$multiple_languages = false;
$mysqltable = "system_permissions";
$base_name = basename($_SERVER["SCRIPT_FILENAME"], ".php");
checkPermissions($base_name);

//==== DELETE Record ====
if ($delete){
	if (mysqlNum(mysqlQuery("SELECT * FROM system_administrators WHERE permission=$delete"))){
		$error = readLanguage(pages,permissions_error);
	} else {
		mysqlQuery("DELETE FROM $mysqltable WHERE id!=1 AND id=$delete");
		if (mysqlAffectedRows()){ $success = readLanguage(records,deleted); } else { $error = readLanguage(records,unavailable); }
	}
	
//==== ADD Record ====
} else if ($post["token"] && !$edit){
	$query = "INSERT INTO $mysqltable (
		title,
		permissions
	) VALUES (
		'" . $post["title"] . "',
		'" . implode(",",$post["permissions"]) . "'
	)";
	mysqlQuery($query);
	$success = readLanguage(records,added);
	
//==== EDIT Record ====
} else if ($post["token"] && $edit){
	$query = "UPDATE $mysqltable SET
		title='" . $post['title'] . "',
		permissions='" . implode(",", $post["permissions"]) . "'
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

//Additional Requirements
if ($edit){
	$explode = explode(",",$entry["permissions"]);
	foreach ($explode as $value){
		if ($value){ $checkd[$value] = "checked"; }
	}
}

include "_header.php"; ?>

<div class=title><?=getPageTitle($base_name)?></div>
<?=$message?>

<form method=post enctype="multipart/form-data" action="<?=$action?>">
<input type=hidden name=token value="<?=$token?>">

<table class=data_table>
<tr>
	<td class=title><?=readLanguage(inputs,title)?>: <i class=requ></i></td>
	<td><input type=text name=title value="<?=$entry["title"]?>" data-validation=required></td>
</tr>
<tr>
	<td class=title><?=readLanguage(pages,user_role)?>: <i class=requ></i></td>
	<td>
		<div>
		<? foreach ($panel_categories as $category_title => $category_panels){
			foreach ($category_panels AS $key => $panel_title){
				$sections_persmission = null;
				foreach($panel_section[$panel_title] AS $page_link => $page_title){
					if (checkPermissions($page_link,2)){
						$sections_persmission .= "<label><input type='checkbox' checkbox-panel='$panel_title' name=permissions[] class=filled-in value='" . $page_link . "' " . $checkd[$page_link] . "><span>" . $page_title ."</span></label>";
					}
				}
				if ($sections_persmission){
					$permissions .= "<div class=inline-subtitle>";
						$permissions .= "<b>" . $category_title . " » " . $panel_title . "</b>";
						$permissions .= "<span><a select-panel='$panel_title' select-value=1>" . readLanguage(operations,select_all) . "</a> | <a select-panel='$panel_title' select-value=0>" . readLanguage(operations,select_none) . "</a></span>";
					$permissions .= "</div>";	
					$permissions .= "<div class='check_container fixed-width'>";
						$permissions .= $sections_persmission;
					$permissions .= "</div>";
				}
			}
		}
		print $permissions; ?>
		</div>
	</td>
</tr>
</table>

<div class=submit_container><input type=button class=submit value="<?=$button?>"></div>
</form>

<div class=crud_separator></div>
<?
$crud_data["delete_record_message"] = "title";
$crud_data["buttons"] = array(true,true,false,true,true); //Add - Search - View - Edit - Delete
$crud_data["columns"] = array(
	//Column Name - Column Title - Width - Alignment - Function - Filter Enabled - Search Enabled - Copy Enabled
	array("title",readLanguage(inputs,title),"100%","center","",false,true),
);
require_once("crud/crud.php");
?>

<script>
$("[select-panel]").click(function(){
	$("[checkbox-panel='" + $(this).attr("select-panel") + "']").prop("checked", ($(this).attr("select-value")==1 ? true : false));
});
</script>

<? include "_footer.php"; ?>