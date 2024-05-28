<? include "system/_handler.php";

$multiple_languages = false;
$mysqltable = "system_administrators";
$base_name = basename($_SERVER["SCRIPT_FILENAME"], ".php");
checkPermissions($base_name);

//Remove variable for backdoor
if ($edit == 1 || $delete == 1){
	$edit = null; $delete = null;
}

//==== DELETE Record ====
if ($delete){
	$record_data = getID($delete,$mysqltable);
	if ($record_data["hash"] == $logged_user["hash"]){
		$error = readLanguage(pages,user_delete_error);
	} else {
		mysqlQuery("DELETE FROM $mysqltable WHERE id=$delete");
		if (mysqlAffectedRows()){ $success = readLanguage(records,deleted); } else { $error = readLanguage(records,unavailable); }
	}
	
//==== ADD Record ====
} else if ($post["token"] && !$edit){
	if (mysqlNum(mysqlQuery("SELECT * FROM $mysqltable WHERE (username='" . $post["username"] . "' OR (email!='' AND email='" . $post["email"] . "'))"))){
		$error = readLanguage(pages,user_exists);
	} else {
		$hash = md5(uniqid() . rand(1000, 9999) . newRecordID($mysqltable));
		$query = "INSERT INTO $mysqltable (
			hash,
			name,
			username,
			email,
			password,
			permission
		) VALUES (
			'" . $hash . "',
			'" . $post["name"] . "',
			'" . $post["username"] . "',
			'" . $post["email"] . "',
			'" . password_hash($post["password"], PASSWORD_DEFAULT) . "',
			'" . $post["permission"] . "'
		)";
		mysqlQuery($query);
		$success = readLanguage(records,added);
	}

//==== EDIT Record ====
} else if ($post["token"] && $edit){
	if (mysqlNum(mysqlQuery("SELECT * FROM $mysqltable WHERE (username='" . $post["username"] . "' OR (email!='' AND email='" . $post["email"] . "')) AND id!=$edit"))){
		$error = readLanguage(pages,user_exists);
	} else {
		$record_data = getID($edit,$mysqltable);
		$query = "UPDATE $mysqltable SET
			name='" . $post["name"] . "',
			username='" . $post["username"] . "',
			email='" . $post["email"] . "',
			password='" . ($post["password"] ? password_hash($post["password"], PASSWORD_DEFAULT) : $record_data["password"]) . "',
			permission='" . $post["permission"] . "'
		WHERE id=$edit";
		mysqlQuery($query);
		$success = readLanguage(records,updated);
	}
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
	<td class=title><?=readLanguage(pages,user_name)?>: <i class=requ></i></td>
	<td>
		<input type=text name=name value="<?=$entry["name"]?>" data-validation=required>
	</td>
	<td class=title><?=readLanguage(pages,user_username)?>: <i class=requ></i></td>
	<td>
		<input type=text name=username value="<?=$entry["username"]?>" autocomplete=new-password data-validation=required>
	</td>
</tr>
<tr>
	<td class=title><?=readLanguage(pages,user_email)?>:</td>
	<td>
		<input type=text name=email value="<?=$entry["email"]?>" data-validation=email data-validation-optional=true>
	</td>
	<td class=title><?=readLanguage(pages,user_password)?>: <i class=requ></i></td>
	<td>
		<input type=password name=password autocomplete=new-password data-validation-optional=<?=($edit ? "true" : "false")?> data-validation=custom data-validation-regexp="^(.{8,})$" data-validation-error-msg="<?=readLanguage(pages,user_password_requirements)?>">
		<div class=input_description><?=($edit ? readLanguage(users,password_empty) : readLanguage(pages,user_password_requirements))?></div>
	</td>
</tr>
<tr>
	<td class=title><?=readLanguage(pages,user_role)?>: <i class=requ></i></td>
	<td colspan=3>
		<select name=permission id=permission data-validation=required><?=populateData("SELECT * FROM system_permissions","id","title")?></select>
		<? if ($entry["permission"]){ ?><script>setSelectValue("#permission","<?=$entry["permission"]?>")</script><? } ?>
	</td>
</tr>
</table>

<div class=submit_container><input type=button class=submit value="<?=$button?>"></div>
</form>

<div class=crud_separator></div>
<?
$crud_data["delete_record_message"] = "name";
$crud_data["where_statement"] = "id!=1";
$crud_data["buttons"] = array(true,true,false,true,true); //Add - Search - View - Edit - Delete
$crud_data["columns"] = array(
	array("name",readLanguage(pages,user_name),"100%","center",null,false,true),
	array("username",readLanguage(pages,user_username),"150px","center",null,false,true),
	array("email",readLanguage(pages,user_email),"200px","center",null,false,true),
	array("permission",readLanguage(pages,user_role),"150px","center","getID(%s,'system_permissions')['title']",true,false)
);
require_once("crud/crud.php");
?>

<? include "_footer.php"; ?>