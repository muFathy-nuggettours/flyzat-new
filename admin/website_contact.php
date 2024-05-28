<? include "system/_handler.php";

$mysqltable = "website_contact";
$base_name = basename($_SERVER["SCRIPT_FILENAME"], ".php");
checkPermissions($base_name);

//==== DELETE Record ====
if ($delete) {
	mysqlQuery("DELETE FROM $mysqltable WHERE id=$delete");
	if (mysqlAffectedRows()){ $success = readLanguage(records,deleted); } else { $error = readLanguage(records,unavailable); }

//==== ADD Record ====
} else if ($post["token"] && !$edit) {
	$query = "INSERT INTO $mysqltable (
        mobile,
        landline,
        email,
        whatsapp,
        primary_number,
        country
	) VALUES (
        '{$post['mobile']}',
        '{$post['landline']}',
        '{$post['email']}',
        '{$post['whatsapp']}',
        '{$post['primary_number']}',
        '{$post['country']}'
	)";
	mysqlQuery($query);
	$success = readLanguage(records, added);

//==== EDIT Record ====
} else if ($post["token"] && $edit) {
	$query = "UPDATE $mysqltable SET
        mobile='{$post['mobile']}',
        landline='{$post['landline']}',
        email='{$post['email']}',
        whatsapp='{$post['whatsapp']}',
        primary_number='{$post['primary_number']}',
        country='{$post['country']}'
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

<script src="../plugins/tagify.js?v=<?=$system_settings["system_version"]?>"></script>
<link href="../plugins/tagify.css?v=<?=$system_settings["system_version"]?>" rel="stylesheet">

<script src="https://maps.googleapis.com/maps/api/js?sensor=false<?=($system_settings["google_maps_key"] ? "&key=" . $system_settings["google_maps_key"] : "")?>"></script>
<script src="../plugins/location-picker.min.js?v=<?=$system_settings["system_version"]?>"></script>

<div class=title><?=getPageTitle($base_name)?></div>
<?=$message?>

<form method=post enctype="multipart/form-data" action="<?=$action?>">
<input type=hidden name=token value="<?=$token?>">

<table class=data_table>
<tr>
    
    <td class=title>الدولة: </td>
    <td>
        <select name=country id=country data-validation=required>
            <? $country_result = mysqlQuery("SELECT code, phone_code, en_name, ar_name FROM system_database_countries ORDER BY code ASC");
            while ($country_entry = mysqlFetch($country_result)) {
                print "<option value='" . $country_entry["code"] . "' data-name='" . $country_entry[$panel_language . "_name"] . "'>" . $country_entry[$panel_language . "_name"] . "</option>";
            }?>
        </select>
        <script>
            //Set default value
            $(document).ready(function() {
                setSelectValue("#country", "<?=($entry["country"] ? $entry["country"] : "eg")?>");
                $("#country").trigger("change");
            });

            //Initialize Select2
            $("#country").select2({
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
    <td class=title><?=readLanguage(pages,info_contact_email)?>:</td>
    <td>
        <input type=text name=email value="<?=$entry["email"]?>" data-validation=email data-validation-optional=true>
    </td>
</tr>
<tr>
    <td class=title><?=readLanguage(pages,info_contact_landline)?>:</td>
    <td>
        <textarea class=tagarea data-tags=landline data-separator="{NewLine}" data-class=tag-box-block name=landline placeholder="<?=readLanguage(plugins,tags_enter)?>"><?=$entry["landline"]?></textarea>
    </td>
    <td class=title><?=readLanguage(pages,info_contact_mobile)?>:</td>
    <td>
        <textarea class=tagarea data-tags=mobile data-separator="{NewLine}" data-class=tag-box-block name=mobile placeholder="<?=readLanguage(plugins,tags_enter)?>"><?=$entry["mobile"]?></textarea>
    </td>
</tr>
<tr>
    <td class=title><?=readLanguage(pages,info_contact_primary)?>:</td>
    <td>
        <input type=text name=primary_number value="<?=$entry["primary_number"]?>">
    </td>
    <td class=title><?=readLanguage(pages,info_contact_whatsapp)?>:</td>
    <td>
        <input type=text name=whatsapp value="<?=$entry["whatsapp"]?>">
    </td>
</tr>
</table>

<div class=submit_container><input type=button class=submit value="<?=$button?>"></div>
</form>

<div class=crud_separator></div>
<?
$crud_data["buttons"] = array(true, true, false, true, true); //Add - Search - View - Edit - Delete
$crud_data["columns"] = array( //Filter - Search - Copy Enabled
    array("country", "الدولة", "100%", "center", "getData('system_database_countries','code','%s','" . $panel_language . "_name')", true, true),
    array("primary_number", readLanguage(pages,info_contact_primary), "200px", "center", null, false, true),
    array("email", readLanguage(pages,info_contact_email), "300px", "center", null, false, true),
    array("whatsapp", readLanguage(pages,info_contact_whatsapp), "200px", "center", null, false, true),
);
require_once("crud/crud.php");
?>

<? include "_footer.php" ?>