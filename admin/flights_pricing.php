<? include "system/_handler.php";

$mysqltable = "flights_pricing";
$base_name = basename($_SERVER["SCRIPT_FILENAME"], ".php");
checkPermissions($base_name);

//==== DELETE Record ====
if ($delete) {
	mysqlQuery("DELETE FROM $mysqltable WHERE id=$delete");
	if (mysqlAffectedRows()){ $success = readLanguage(records,deleted); } else { $error = readLanguage(records,unavailable); }

//==== ADD Record ====
} else if ($post["token"] && !$edit) {
	if (!$errors){
        $query = "INSERT INTO $mysqltable (
            origin,
            destination,
            fixed,
            percentage
        ) VALUES (
            '{$post['origin']}',
			'{$post['destination']}',
            '{$post['fixed']}',
            '{$post['percentage']}'
        )";
        mysqlQuery($query);
    } else {
		$error = "<ul><li>" . implode("</li><li>", $errors) . "</li></ul>";
	}

	$success = readLanguage(records, added);

//==== EDIT Record ====
} else if ($post["token"] && $edit) {
	if (!$errors){
        $query = "UPDATE $mysqltable SET
            origin='{$post['origin']}',
            destination='{$post['destination']}',
            fixed='{$post['fixed']}',
            percentage='{$post['percentage']}'
        WHERE id=$edit";
        mysqlQuery($query);
    } else {
		$error = "<ul><li>" . implode("</li><li>", $errors) . "</li></ul>";
	}

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

<script src="../plugins/fixed-data.js?v=<?=$system_settings["system_version"]?>"></script>

<div class=title><?=getPageTitle($base_name)?></div>
<?=$message?>

<form method=post enctype="multipart/form-data" action="<?=$action?>">
<input type=hidden name=token value="<?=$token?>">

<table class=data_table>
<tr>
    <td class=title>مطار الإقلاع: <i class=requ></i></td>
	<td>
		<? $input = "origin"; $value = $entry["origin"]; $conditions = null; $mandatory = true; $removable = true; ?>
		<? include "includes/select_airport.php"; ?>
	</td>
</tr>
<tr>
    <td class=title>مطار الوصول: <i class=requ></i></td>
	<td>
		<? $input = "destination"; $value = $entry["destination"]; $conditions = null; $mandatory = true; $removable = true; ?>
		<? include "includes/select_airport.php"; ?>
	</td>
</tr>
<tr>
    <td class=title>مبلغ ثابت:</td>
	<td>
		<input type=hidden name=fixed id=fixed>
		<ul class=inline_input json-fixed-data=fixed>
		<? $result = mysqlQuery("SELECT * FROM system_payment_currencies");
		while ($currency = mysqlFetch($result)){ ?>
			<li style="flex-basis:100px">
				<div class=input-addon><input type=number data-name="<?=$currency["code"]?>" data-validation=number data-validation-optional=true data-validation-allowing="range[0;9999],float"><span after><?=$currency["ar_name"]?></span></div>
			</li>
		<? } ?>
		</ul>
		<? if ($entry["fixed"]){ ?><script>fixedDataRead("fixed", <?=$entry["fixed"]?>)</script><? } ?>
	</td>
</tr>
<tr>
    <td class=title>نسبة العمولة:</td>
    <td>
		<div class=input-addon><input type=number name=percentage data-validation=number data-validation-optional=true data-validation-allowing="range[0;99],float" value="<?=$entry['percentage']?>"><span after>%</span></div>
	</td>
</tr>
</table>

<div class=submit_container><input type=button class=submit value="<?=$button?>"></div>
</form>

<div class=crud_separator></div>
<?
$crud_data["buttons"] = array(true, true, false, true, true); //Add - Search - View - Edit - Delete
$crud_data["columns"] = array( //Filter - Search - Copy Enabled
    array("origin", "مطار الإقلاع", "50%", "center", null, true, true),
    array("destination", "مطار الوصول", "50%", "center", null, true, true),
);
require_once("crud/crud.php");
?>

<? include "_footer.php" ?>