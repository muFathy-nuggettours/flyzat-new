<? include "system/_handler.php";

$multiple_languages = false;
$base_name = basename($_SERVER["SCRIPT_FILENAME"], ".php");
checkPermissions($base_name);

//==== Login ====
if ($post["token"]){
	$user_data = getData("users_database","id",$post["user_id"]);
	if ($user_data){
		writeCookie($user_session, null, time() - (86400 * 30), "/");
		$_SESSION[$user_session] = $user_data["hash"];
		$success = readLanguage(users,login_success) . "<b>" . $user_data["name"] . "</b>";
		$success .= "<script>window.open('../');</script>";
	} else {
		$error = readLanguage(general,error);
	}
}

//Read and Set Operation
if ($success){ $message = "<div class='alert alert-success'>$success</div>"; }
if ($error){ $message = "<div class='alert alert-danger'>$error</div>"; }

include "_header.php"; ?>

<div class=title><?=getPageTitle($base_name)?></div>
<?=$message?>

<form method=post enctype="multipart/form-data" action="<?=$action?>">
<input type=hidden name=token value="<?=$token?>">

<table class=data_table>
<? $input = "user_id"; $value = null; $conditions = null; $mandatory = true; $removable = true; ?>
<script>
function onSelectProfile_<?=$input?>(data){
	//On selecting profile function
}
function onUnselectProfile_<?=$input?>(){
	//On unselecting profile function
}
</script>
<tr>
	<? include "includes/select_user.php"; ?>
</tr>
</table>

<div class=submit_container><input type=button class=submit value="<?=readLanguage(users,login)?>"></div>
</form>

<? include "_footer.php"; ?>