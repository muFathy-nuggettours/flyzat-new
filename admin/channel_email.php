<? include "system/_handler.php";

$multiple_languages = false;
$base_name = basename($_SERVER["SCRIPT_FILENAME"], ".php");
checkPermissions($base_name);

//==== SEND ====
if ($post["token"]){
	$emails = explode("\r\n", $_POST["emails"]);
	if ($_FILES["attachments"]["name"][0]){
		$attachments = array();
		for ($i = 0; $i < count($_FILES["attachments"]["name"]); $i++){
			array_push($attachments, [$_FILES["attachments"]["tmp_name"][$i], $_FILES["attachments"]["name"][$i]]);
		}
	}
	$result = sendMail($emails, $post["subject"], mysqlEscape($_POST["message"]), null, null, null, $attachments);
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
<tr><td class=title><?=readLanguage(channels,emails)?>: <i class=requ></i></td>
<td>
	<textarea name=emails data-validation=required></textarea>
	<div class=input_description><?=readLanguage(inputs,instructions_newline)?></div>
</td></tr>
<tr>
	<td class=title><?=readLanguage(channels,subject)?>: <i class=requ></i></td>
	<td><input type=text name=subject data-validation=required></td>
</tr>
<tr>
	<td class=title><?=readLanguage(channels,message)?>: <i class=requ></i></td>
	<td><textarea class=mceEditorLimited name=message data-validation=validateEditor></textarea></td>
</tr>
<tr>
	<td class=title>Attachments:</td>
	<td>
		<input type=file name=attachments[] multiple>
	</td>
</tr>
</table>

<div class=submit_container><input type=button class=submit value="<?=readLanguage(channels,send)?>"></div>
</form>

<? include "_footer.php"; ?>