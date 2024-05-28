<? include "system/_handler.php";

$mysqltable = $suffix . "website_seo";
$base_name = basename($_SERVER["SCRIPT_FILENAME"], ".php");
checkPermissions($base_name);

//==== DELETE Record ====
if ($delete) {
	mysqlQuery("DELETE FROM $mysqltable WHERE id=$delete");
	if (mysqlAffectedRows()){ $success = readLanguage(records,deleted); } else { $error = readLanguage(records,unavailable); }

//==== ADD Record ====
} else if ($post["token"] && !$edit) {
	$query = "INSERT INTO $mysqltable (
        route,
        route_expression,
        priority
	) VALUES (
        '{$post['route']}',
        '" . preg_replace('/{[^}]+}/', '(.+)', createCanonical($post['route'])) . "',
        " . count(explode("-", createCanonical($post['route']))) . "
	)";
	mysqlQuery($query);
	$success = readLanguage(records, added);

//==== EDIT Record ====
} else if ($post["token"] && $edit) {
	$query = "UPDATE $mysqltable SET
        route='{$post['route']}',
        route_expression='" . preg_replace('/{[^}]+}/', '(.+)', createCanonical($post['route'])) . "',
        priority=" . count(explode("-", createCanonical($post['route']))) . "
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

include "_header.php" ?>

<div class=title><?=getPageTitle($base_name)?></div>
<?=$message?>

<form method=post enctype="multipart/form-data" action="<?=$action?>">
<input type=hidden name=token value="<?=$token?>">

<table class=data_table>
<tr>
    <td class=title>جملة البحث: <i class=requ></i></td>
    <td colspan=3>
        <input type=text name=route value="<?=$entry['route']?>" data-validation=required>
		<div class=input_description>
			<b>{1}</b> مطار او بلد الإقلاع | <b>{2}</b> مطار او بلد الوجهة
		</div>
    </td>
</tr>
</table>

<div class=submit_container><input type=button class=submit value="<?=$button?>"></div>
</form>

<div class=crud_separator></div>

<?
$crud_data["buttons"] = array(true, true, false, true, true); //Add - Search - View - Edit - Delete
$crud_data["columns"] = array( //Filter - Search - Copy Enabled
    array("route", "جملة البحث", "100%", "center", null, false, true),
);
require_once("crud/crud.php");
?>

<? include "_footer.php" ?>