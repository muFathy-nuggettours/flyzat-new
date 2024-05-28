<? include "system/_handler.php";

$multiple_languages = false;
$base_name = basename($_SERVER["SCRIPT_FILENAME"], ".php");
checkPermissions($base_name);

//==== SEND ====
if ($post["token"]){
	$mobile_numbers = explode("\r\n", $_POST["mobile"]);
	$result = sendSMS($mobile_numbers,$post["message"]);
	$error = $result[0];
	$success = $result[1];
}

//Read and Set Operation
if ($success){ $message = "<div class='alert alert-success'>$success</div>"; }
if ($error){ $message .= "<div class='alert alert-danger'>$error</div>"; }

include "_header.php"; ?>

<div class=title><?=getPageTitle($base_name)?></div>
<?=$message?>

<form method=post enctype="multipart/form-data" action="<?=$action?>">
<input type=hidden name=token value="<?=$token?>">

<table class=data_table>
<tr>
	<td class=title><?=readLanguage(channels,mobiles)?>: <i class=requ></i></td>
	<td>
		<textarea name=mobile data-validation=required></textarea>
		<div class=input_description><?=readLanguage(inputs,instructions_newline)?></div>
	</td>
</tr>
<tr>
	<td class=title><?=readLanguage(channels,message)?>: <i class=requ></i></td>
	<td><input type=text name=message data-validation=required maxlength=140></td>
</tr>
</table>

<div class=submit_container><input type=button class=submit value="<?=readLanguage(channels,send)?>"></div>
</form>

<? include "_footer.php"; ?>