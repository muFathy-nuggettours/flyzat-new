<? include "system/_handler.php";

$mysqltable = "users_balance";
$base_name = basename($_SERVER["SCRIPT_FILENAME"], ".php");
checkPermissions($base_name);

//==== DELETE Record ====
if ($delete) {
	mysqlQuery("DELETE FROM $mysqltable WHERE manual=1 AND id=$delete");
	if (mysqlAffectedRows()){ $success = readLanguage(records,deleted); } else { $error = readLanguage(records,unavailable); }

//==== ADD Record ====
} else if ($post["token"] && !$edit) {
	$query = "INSERT INTO $mysqltable (
		manual,
		user_id,
		title,
		amount,
		currency,
		date
	) VALUES (
		'1',
		'{$post['user_id']}',
		'{$post['title']}',
		'{$post['amount']}',
		'" . getID($post["user_id"], "users_database", "user_currency") . "',
		'" . time() . "'
	)";
	mysqlQuery($query);
	$success = readLanguage(records, added);

//==== EDIT Record ====
} else if ($post["token"] && $edit) {
	$query = "UPDATE $mysqltable SET
		user_id='{$post['user_id']}',
		title='{$post['title']}',
		amount='{$post['amount']}',
		currency='" . getID($post["user_id"], "users_database", "user_currency") . "'
	WHERE manual=1 AND id=$edit";
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

<div class=title><?=getPageTitle($base_name)?></div>
<?=$message?>

<form method=post enctype="multipart/form-data" action="<?=$action?>">
    <input type=hidden name=token value="<?=$token?>">

    <table class=data_table>
    <tr>
        <td class=title>عنوان السجل: <i class=requ></i></td>
        <td colspan=3><input type=text name=title value="<?=$entry["title"]?>" data-validation=required></td>
    </tr>
    <tr>
        <? $input = "user_id"; $value = $entry["user_id"]; $conditions = null; $mandatory = true; $removable = true;?>
        <? include "includes/select_user.php";?>
		<script>
		function onSelectProfile_user_id(data){
			$("#record_currency").text(data.entry.user_currency);
		}
		function onUnselectProfile_user_id(){
			$("#record_currency").text("لم تقم بإدخال ملف المستخدم");
		}
		</script>
    </tr>
    <tr>
        <td class=title>المبلغ: <i class=requ></i></td>
        <td><input type=number step=0.01 name=amount value="<?=$entry["amount"]?>" data-validation=number data-validation-allowing="range[-999999;999999],float,negative"></td>
		<td class=title>العملة:</td>
		<td class=valign-middle><div id=record_currency><?=(!$edit ? "لم تقم بإدخال ملف المستخدم" : $entry["currency"])?></div></td>
    </tr>	
	</table>
    <div class=submit_container><input type=button class=submit value="<?=$button?>"></div>
</form>

<div class=crud_separator></div>

<?
$crud_data["where_statement"] = "manual=1";
$crud_data["buttons"] = array(true,true,false,true,true); //Add - Search - View - Edit - Delete
$crud_data["columns"] = array( //Filter - Search - Copy Enabled
	array("user_id","ملف المستخدم","250px","center","getCustomData('name','users_database','id','%s','_view_user')",true,true),
	array("title","العنوان","250px","center",null,false,true),
	array("amount","المبلغ","120px","center",null,false,true),
	array("currency","العملة","120px","center",null,true,false),
	array("date","التاريخ","250px","center","dateLanguage('l, d M Y h:i A','%s')",false,false),
);
require_once("crud/crud.php");
?>

<? include "_footer.php"; ?>