<? include "system/_handler.php";

//Security Measures
if (!$logged_user){ header("Location: ."); exit(); }

$mysqltable = "system_administrators";
$base_name = basename($_SERVER["SCRIPT_FILENAME"], ".php");

//==== EDIT Record ====
if ($post["token"]){
	$query = "UPDATE $mysqltable SET
		name='" . $post["name"] . "',
		email='" . $post["email"] . "',
		password='" . ($post["user_password"] ? password_hash($post["user_password"], PASSWORD_DEFAULT) : $logged_user["password"]) . "'
	WHERE id='" . $logged_user["id"] . "'";
	mysqlQuery($query);
	
	$success = readLanguage(records,updated);
	$logged_user = getID($logged_user["id"],"system_administrators");
}

//Read and Set Operation
if ($success){ $message = "<div class='alert alert-success'>$success</div>"; }

include "_header.php"; ?>

<div class=title><a href="_page_profile.php"><?=readLanguage(pages,user_update)?></a></div>
<?=$message?>

<form method=post enctype="multipart/form-data" action="<?=$action?>">
<input type=hidden name=token value="<?=$token?>">

<table class=data_table>
<tr>
	<td class=title><?=readLanguage(pages,user_username)?>:</td>
	<td><?=$logged_user["username"]?></td>
	<td class=title><?=readLanguage(pages,user_role)?>:</td>
	<td><?=getID($logged_user["permission"],"system_permissions")["title"]?></td>
</tr>
<tr>
	<td class=title><?=readLanguage(pages,user_name)?>: <i class=requ></i></td>
	<td>
		<input type=text name=name value="<?=$logged_user["name"]?>" data-validation=required>
	</td>
	<td class=title><?=readLanguage(pages,user_email)?>:</td>
	<td>
		<input type=text name=email value="<?=$logged_user["email"]?>" data-validation=email data-validation-optional=true>
	</td>
</tr>
<tr>
	<td class=title><?=readLanguage(pages,user_password)?>:</td>
	<td colspan=3>
		<input type=password name=user_password autocomplete=new-password data-validation-optional=true data-validation=custom data-validation-regexp="^(.{8,})$" data-validation-error-msg="<?=readLanguage(pages,user_password_requirements)?>">
		<div class=input_description><?=readLanguage(pages,user_password_tip)?></div>
	</td>
</tr>
</table>

<div class=submit_container><input type=button class=submit value="<?=readLanguage(records,update)?>"></div>
</form>

<? include "_footer.php"; ?>