<? include "system/_handler.php";

$mysqltable = "flights_warnings";
$base_name = basename($_SERVER["SCRIPT_FILENAME"], ".php");
checkPermissions($base_name);

//==== DELETE Record ====
if ($delete) {
	mysqlQuery("DELETE FROM $mysqltable WHERE id=$delete");
	if (mysqlAffectedRows()){ $success = readLanguage(records,deleted); } else { $error = readLanguage(records,unavailable); }

//==== ADD Record ====
} else if ($post["token"] && !$edit){
    if (mysqlNum(mysqlQuery("SELECT id FROM $mysqltable WHERE origin='{$post['origin']}' AND destination='{$post['destination']}'"))) $error = "تم تسجيل هذا التنبيه من قبل";

	if (!$error){
        $query = "INSERT INTO $mysqltable (
            origin,
            destination,
            message
        ) VALUES (
            '{$post['origin']}',
            '{$post['destination']}',
            '{$post['message']}'
        )";
        mysqlQuery($query);
    }

	$success = readLanguage(records, added);

//==== EDIT Record ====
} else if ($post["token"] && $edit){
    if (mysqlNum(mysqlQuery("SELECT id FROM $mysqltable WHERE id!=$edit AND origin='{$post['origin']}' AND destination='{$post['destination']}'"))) $error = "تم تسجيل هذا التنبيه من قبل";

	if (!$error){
        $query = "UPDATE $mysqltable SET
            origin='{$post['origin']}',
            destination='{$post['destination']}',
            message='{$post['message']}'
        WHERE id=$edit";
        mysqlQuery($query);
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

<div class=title><?=getPageTitle($base_name)?></div>
<?=$message?>

<!-- Contact Information -->
<form method=post enctype="multipart/form-data" action="<?=$action?>">
<input type=hidden name=token value="<?=$token?>">

<table class=data_table>
<tr>
    <td class=title>دولة الإقلاع: <i class=requ></i></td>
    <td>
        <select name=origin id=origin data-validation=required>
            <? $country_result = mysqlQuery("SELECT code, phone_code, en_name, ar_name FROM system_database_countries ORDER BY code ASC");
            while ($country_entry = mysqlFetch($country_result)) {
                print "<option value='" . $country_entry["code"] . "' data-name='" . $country_entry[$panel_language . "_name"] . "'>" . $country_entry[$panel_language . "_name"] . "</option>";
            }?>
        </select>
        <script>
            //Set default value
            <? if ($entry['origin']) { ?>setSelectValue("#origin", "<?=$entry["origin"]?>");<? } ?>

            //Initialize Select2
            $("#origin").select2({
                dropdownAutoWidth: true,
                templateResult: function(state) {
                    var element = $(state.element);
                    return $("<div class='d-flex align-items-center'><img src='../images/countries/" + $(element).val() + ".gif'>&nbsp;" + $(element).attr("data-name") + "</div>");
                },
                templateSelection: function(state) {
                    var element = $(state.element);
                    return $("<div class='d-flex align-items-center'><img src='../images/countries/" + $(element).val() + ".gif'>&nbsp;" + $(element).attr("data-name") + "</div>");
                }
            });
        </script>
    </td>
    <td class=title>دولة الوصول: <i class=requ></i></td>
    <td>
        <select name=destination id=destination data-validation=required>
            <? $country_result = mysqlQuery("SELECT code, phone_code, en_name, ar_name FROM system_database_countries ORDER BY code ASC");
            while ($country_entry = mysqlFetch($country_result)) {
                print "<option value='" . $country_entry["code"] . "' data-name='" . $country_entry[$panel_language . "_name"] . "'>" . $country_entry[$panel_language . "_name"] . "</option>";
            }?>
        </select>
        <script>
            //Set default value
            <? if ($entry['destination']) { ?>setSelectValue("#destination", "<?=$entry["destination"]?>");<? } ?>

            //Initialize Select2
            $("#destination").select2({
                dropdownAutoWidth: true,
                templateResult: function(state) {
                    var element = $(state.element);
                    return $("<div class='d-flex align-items-center'><img src='../images/countries/" + $(element).val() + ".gif'>&nbsp;" + $(element).attr("data-name") + "</div>");
                },
                templateSelection: function(state) {
                    var element = $(state.element);
                    return $("<div class='d-flex align-items-center'><img src='../images/countries/" + $(element).val() + ".gif'>&nbsp;" + $(element).attr("data-name") + "</div>");
                }
            });
        </script>
    </td>
</tr>
<tr>
    <td class=title>رسالة التنبيه: <i class=requ></i></td>
    <td colspan=3>
        <textarea class=mceEditor name=message data-validation=validateEditor><?=$entry["message"]?></textarea>
    </td>
</tr>
</table>

<div class=submit_container><input type=button class=submit value="<?=$button?>"></div>
</form>

<div class=crud_separator></div>

<?
$crud_data["buttons"] = array(true, true, false, true, true); //Add - Search - View - Edit - Delete
$crud_data["columns"] = array( //Filter - Search - Copy Enabled
    array("origin", "الإقلاع", "50%", "center", "getData('system_database_countries','code','%s', 'ar_name')", true, true),
    array("destination", "الوصول", "50%", "center", "getData('system_database_countries','code','%s', 'ar_name')", true, true),
);
require_once("crud/crud.php");
?>

<? include "_footer.php" ?>