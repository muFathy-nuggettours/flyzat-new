<? include "system/_handler.php";

$mysqltable = "users_agents";
$base_name = basename($_SERVER["SCRIPT_FILENAME"], ".php");
checkPermissions($base_name);

//==== DELETE Record ====
if ($delete) {
	mysqlQuery("DELETE FROM $mysqltable WHERE id=$delete");
	if (mysqlAffectedRows()){ $success = readLanguage(records,deleted); } else { $error = readLanguage(records,unavailable); }

//==== ADD Record ====
} else if ($post["token"] && !$edit) {
	$exists = mysqlNum(mysqlQuery("SELECT id FROM $mysqltable WHERE user_id=" . $post["user_id"]));
	if ($exists){
		$error = "ملف المستخدم الذي اخترته معين لحساب وكيل آخر";
	} else {
		$query = "INSERT INTO $mysqltable (
			user_id,
			company_name,
			company_address,
			fixed,
			percentage
		) VALUES (
			'{$post['user_id']}',
			'{$post['company_name']}',
			'{$post['company_address']}',
			'{$post['fixed']}',
			'{$post['percentage']}'
		)";
		mysqlQuery($query);
		$success = readLanguage(records, added);
	}

//==== EDIT Record ====
} else if ($post["token"] && $edit) {
	$exists = mysqlNum(mysqlQuery("SELECT id FROM $mysqltable WHERE user_id=" . $post["user_id"] . " AND id!=$edit"));
	if ($exists){
		$error = "ملف المستخدم الذي اخترته معين لحساب وكيل آخر";
	} else {
		$query = "UPDATE $mysqltable SET
			user_id='{$post['user_id']}',
			company_name='{$post['company_name']}',
			company_address='{$post['company_address']}',
			fixed='{$post['fixed']}',
			percentage='{$post['percentage']}'
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

include "_header.php"; ?>

<script src="../plugins/fixed-data.js?v=<?=$system_settings["system_version"]?>"></script>

<div class=title><?=getPageTitle($base_name)?></div>
<?=$message?>

<form method=post enctype="multipart/form-data" action="<?=$action?>">
    <input type=hidden name=token value="<?=$token?>">

    <table class=data_table>
    <tr>
        <? $input = "user_id"; $value = $entry["user_id"]; $conditions = null; $mandatory = true; $removable = false;?>
        <? include "includes/select_user.php";?>
		<script>
		function onSelectProfile_user_id(data){
			showCurrency(data.entry.user_currency);
		}
		function onUnselectProfile_user_id(){
			showCurrency();
		}
		</script>
    </tr>
    <tr>
        <td class=title>إسم الشركة: <i class=requ></i></td>
        <td><input type=text name=company_name value="<?=$entry["company_name"]?>" data-validation=required></td>
        <td class=title>عنوان الشركة: <i class=requ></i></td>
        <td><input type=text name=company_address value="<?=$entry["company_address"]?>" data-validation=required></td>
    </tr>
	</table>
	
	<div class="subtitle margin-top">سياسة التسعير</div>
	<table class=data_table>
    <tr>
        <td class=title>مبلغ ثابت:</td>
        <td class=valign-middle>
            <input type=hidden name=fixed id=fixed>
			<span class=empty>لم تقم باختيار ملف المستخدم</span>
            <ul class=inline_input json-fixed-data=fixed style="display:none">
            <? $result = mysqlQuery("SELECT * FROM system_payment_currencies");
            while ($currency = mysqlFetch($result)){ ?>
                <li data-fixed-currency="<?=$currency["code"]?>" style="flex-basis:100px; display:none">
                    <div class=input-addon><input type=number data-name="<?=$currency["code"]?>" data-validation=number data-validation-optional=true data-validation-allowing="range[0;9999],float"><span after><?=$currency["ar_name"]?></span></div>
                </li>
            <? } ?>
            </ul>
            <? if ($entry["fixed"]){ ?><script>fixedDataRead("fixed", <?=$entry["fixed"]?>)</script><? } ?>
        </td>
        <td class=title>نسبة العمولة:</td>
        <td>
            <div class=input-addon><input type=number name=percentage data-validation=number data-validation-optional=true data-validation-allowing="range[0;99],float" value="<?=$entry['percentage']?>"><span after>%</span></div>
        </td>
    </tr>
    </table>

    <div class=submit_container><input type=button class=submit value="<?=$button?>"></div>
</form>

<script>
function showCurrency(currency=null){
	$("ul.inline_input li").hide();
	
	if (!currency){
		$("ul.inline_input").hide();
		$(".empty").show();
		
	} else {
		$("ul.inline_input").show();
		$(".empty").hide();		
		$("ul.inline_input li[data-fixed-currency='" + currency + "']").show();
	}
}

<? if ($edit){ ?>showCurrency("<?=getID($entry["user_id"], "users_database", "user_currency")?>");<? } ?>
</script>

<div class=crud_separator></div>

<?
$crud_data["buttons"] = array(true,true,false,true,true); //Add - Search - View - Edit - Delete
$crud_data["columns"] = array( //Filter - Search - Copy Enabled
	array("user_id","ملف المستخدم","50%","center","getCustomData('name','users_database','id','%s','_view_user')",false,true),
	array("company_name","إسم الشركة","50%","center",null,false,false),
);
require_once("crud/crud.php");
?>

<? include "_footer.php"; ?>