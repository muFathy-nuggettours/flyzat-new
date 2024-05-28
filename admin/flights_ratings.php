<? include "system/_handler.php";

$mysqltable = "flights_ratings";
$base_name = basename($_SERVER["SCRIPT_FILENAME"], ".php");
checkPermissions($base_name);

//==== DELETE Record ====
if ($delete) {
	mysqlQuery("DELETE FROM $mysqltable WHERE id=$delete");
	if (mysqlAffectedRows()){ $success = readLanguage(records,deleted); } else { $error = readLanguage(records,unavailable); }

//==== ADD Record ====
} else if ($post["token"] && !$edit) {
	$query = "INSERT INTO $mysqltable (
        flight,
        rating_flight,
        comment_flight,
        airport,
        rating_airport,
        comment_airport,
        airline,
        rating_airline,
        comment_airline,
        date
	) VALUES (
        " . intval($post['flight']) . ",
        " . intval($post['rating_flight']) . ",
        '{$post['comment_flight']}',
        '{$post['airport']}',
        " . intval($post['rating_airport']) . ",
        '{$post['comment_airport']}',
        '{$post['airline']}',
        " . intval($post['rating_airline']) . ",
        '{$post['comment_airline']}',
        " . time() . "
	)";
	mysqlQuery($query);

	$success = readLanguage(records, added);

//==== EDIT Record ====
} else if ($post["token"] && $edit){
	$query = "UPDATE $mysqltable SET
        flight=" . intval($post['flight']) . ",
        rating_flight=" . intval($post['rating_flight']) . ",
        comment_flight='{$post['comment_flight']}',
        airport='{$post['airport']}',
        rating_airport=" . intval($post['rating_airport']) . ",
        comment_airport='{$post['comment_airport']}',
        airline='{$post['airline']}',
        rating_airline=" . intval($post['rating_airline']) . ",
        comment_airline='{$post['comment_airline']}'
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

<style>
.rating_container .fa {
    color: #c5c5c5;
    cursor: pointer;
    transition: 150ms ease-in-out;
    margin: auto 5px;
}

.rating_container input[type='radio']:checked ~ .fa-angry {
    color: #E12025;
}

.rating_container input[type='radio']:checked ~ .fa-frown {
    color: #F47950;
}

.rating_container input[type='radio']:checked ~ .fa-meh {
    color: #FCB040;
}

.rating_container input[type='radio']:checked ~ .fa-grin {
    color: #91CA61;
}

.rating_container input[type='radio']:checked ~ .fa-grin-stars {
    color: #3AB549;
}
</style>

<div class=title><?=getPageTitle($base_name)?></div>
<?=$message?>

<form method=post enctype="multipart/form-data" action="<?=$action?>">
<input type=hidden name=token value="<?=$token?>">

<div class=subtitle>تقييم خط الطيران</div>
<table class=data_table>
<tr>
    <td class=title>خط الطيران: <i class=requ></i></td>
    <td>
        <? $input="airline"; $value=$entry['airline']; $conditions=null; $mandatory=true; $removable=true; ?>
        <? include "includes/select_airline.php"; ?>
    </td>
    <td class=title>التقييم: <i class=requ></i></td>
    <td class=valign-middle>
        <div class="d-flex rating_container" id=rating_airline>
            <label><input type=radio name=rating_airline class=d-none value=1><i class="fa fa-angry fa-2x"></i></label>
            <label><input type=radio name=rating_airline class=d-none value=2><i class="fa fa-frown fa-2x"></i></label>
            <label><input type=radio name=rating_airline class=d-none value=3><i class="fa fa-meh fa-2x"></i></label>
            <label><input type=radio name=rating_airline class=d-none value=4><i class="fa fa-grin fa-2x"></i></label>
            <label><input type=radio name=rating_airline class=d-none value=5 checked><i class="fa fa-grin-stars fa-2x"></i></label>
        </div>
		<script>$("#rating_airline").find("[value='<?=$entry["rating_airline"][0]?>']").prop("checked",true);</script>
    </td>
</tr>
<tr>
    <td class=title>التعليق: </td>
    <td colspan=3><input type=text name=comment_airline value="<?=$entry['comment_airline']?>"></td>
</tr>
</table>

<div class=subtitle>تقييم الرحلة</div>
<table class=data_table>
<tr>
    <td class=title>رقم الرحلة: <i class=requ></i></td>
    <td><input type=number name=flight value="<?=$entry['flight']?>" data-validation=number></td>
    <td class=title>التقييم: <i class=requ></i></td>
    <td class=valign-middle>
        <div class="d-flex rating_container" id=rating_flight>
            <label><input type=radio name=rating_flight class=d-none value=1><i class="fa fa-angry fa-2x"></i></label>
            <label><input type=radio name=rating_flight class=d-none value=2><i class="fa fa-frown fa-2x"></i></label>
            <label><input type=radio name=rating_flight class=d-none value=3><i class="fa fa-meh fa-2x"></i></label>
            <label><input type=radio name=rating_flight class=d-none value=4><i class="fa fa-grin fa-2x"></i></label>
            <label><input type=radio name=rating_flight class=d-none value=5 checked><i class="fa fa-grin-stars fa-2x"></i></label>
        </div>
		<script>$("#rating_flight").find("[value='<?=$entry["rating_flight"][0]?>']").prop("checked",true);</script>
    </td>
</tr>
<tr>
    <td class=title>التعليق: </td>
    <td colspan=3><input type=text name=comment_flight value="<?=$entry['comment_flight']?>"></td>
</tr>
</table>

<div class=subtitle>تقييم مطار الإقلاع</div>
<table class=data_table>
<tr>
    <td class=title>المطار: <i class=requ></i></td>
    <td>
        <? $input="airport"; $value=$entry['airport']; $conditions=null; $mandatory=true; $removable=true; ?>
        <? include "includes/select_airport.php"; ?>
    </td>
    <td class=title>التقييم: <i class=requ></i></td>
    <td class=valign-middle>
        <div class="d-flex rating_container" id=rating_airport>
            <label><input type=radio name=rating_airport class=d-none value=1><i class="fa fa-angry fa-2x"></i></label>
            <label><input type=radio name=rating_airport class=d-none value=2><i class="fa fa-frown fa-2x"></i></label>
            <label><input type=radio name=rating_airport class=d-none value=3><i class="fa fa-meh fa-2x"></i></label>
            <label><input type=radio name=rating_airport class=d-none value=4><i class="fa fa-grin fa-2x"></i></label>
            <label><input type=radio name=rating_airport class=d-none value=5 checked><i class="fa fa-grin-stars fa-2x"></i></label>
        </div>
		<script>$("#rating_airport").find("[value='<?=$entry["rating_airport"][0]?>']").prop("checked",true);</script>
    </td>
</tr>
<tr>
    <td class=title>التعليق: </td>
    <td colspan=3><input type=text name=comment_airport value="<?=$entry['comment_airport']?>"></td>
</tr>
</table>

<div class=submit_container><input type=button class=submit value="<?=$button?>"></div>
</form>

<div class=crud_separator></div>

<?
$crud_data["buttons"] = array(true, true, false, true, true); //Add - Search - View - Edit - Delete
$crud_data["columns"] = array( //Filter - Search - Copy Enabled
    array("flight", "رقم الرحلة", "220px", "center", null, true, true),
    array("rating_flight", "التقييم", "120px", "center", null, true, true),
	array("airport", "المطار", "220px", "center", "getData('system_database_airports','iata','%s', 'ar_name')", true, true),
	array("rating_airport", "تالتقييم", "120px", "center", null, true, true),
    array("airline", "خط الطيران", "220px", "center", "getData('system_database_airlines','iata','%s', 'ar_name')", true, true),
    array("rating_airline", "التقييم", "120px", "center", null, true, true),
    array("date", "التاريخ", "220px", "center", "dateLanguage('l, d M Y', '%s')", true, true),
);
require_once("crud/crud.php");
?>

<? include "_footer.php" ?>