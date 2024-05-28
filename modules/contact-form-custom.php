<div class=module_contact_form>
<?
if ($post["token"]){
	//Validate reCAPTCHA
	$valid_attempt = false;
	if ($system_settings["recaptcha_secret_key"]){
		if (isset($post["g-recaptcha-response"]) && !empty($post["g-recaptcha-response"])){
			$recaptcha_response = json_decode(file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=". $system_settings["recaptcha_secret_key"] . "&response=" . $post["g-recaptcha-response"]));
			$valid_attempt = ($recaptcha_response->success ? true : false);
		}
	} else {
		$valid_attempt = true;
	}
	
	//Server Side Validation
	$rules["name"] = array("required","max_length(100)");
	$rules["mobile"] = array("required","max_length(100)");
	$rules["email"] = array("required","max_length(100)","email");
	$rules["subject"] = array("required","max_length(100)");
	$rules["message"] = array("required","max_length(1000)");
	$validation_result = SimpleValidator\Validator::validate($post,$rules);	
	
	//Validate user data
	if ($validation_result->isSuccess()==false){
		$error = readLanguage(contact,error_description);
	} else if (!$valid_attempt){
		$error = readLanguage(general,recaptcha_error);
	} else {
		$query = "INSERT INTO channel_requests (
			reason,
			name,
			mobile,
			email,
			subject,
			message,
			ticket,
			date
		) VALUES (
			'" . $post["reason"] . "',
			'" . $post["name"] . "',
			'" . $post["mobile"] . "',
			'" . $post["email"] . "',
			'" . $post["subject"] . "',
			'" . $post["message"] . "',
			'" . $post["ticket"] . "',
			'" . time() . "'
		)";
		mysqlQuery($query);
		$success = readLanguage(contact,success);	
	}
	
	if ($success){ $message_contact_form = "<div class='alert alert-success'>" . $success . "</div>"; }
	if ($error){ $message_contact_form = "<div class='alert alert-danger'>" . $error . "</div>"; }
}
?>

<?=$message_contact_form?>
<? if (!$success){ ?>
	<? if ($system_settings["recaptcha_secret_key"]){ ?><script src="https://www.google.com/recaptcha/api.js" async defer></script><? } ?>
	<form method=post>
		<input type=hidden name=token value="<?=$token?>">
		<input type=hidden name=action value="contact-form">
		<div class=form-item><b><?=readLanguage(common,reason)?></b><div class=input><select name=reason id=reason onchange="toggleVisibility(this)"><?=populateOptions($data_contact_reasons)?></select></div></div>
		<div class=form-item><b><?=readLanguage(contact,name)?></b><div class=input><input type=text maxlength=100 name=name data-validation=required></div></div>
		<div class=form-item visibility-control='reason' visibility-value='1,2,3' style='display: none'><b><?=readLanguage(reservation,reservation_number)?></b><div class=input><input type=text maxlength=100 name=ticket data-validation=requiredVisible></div></div>
		<div class=form-item><b><?=readLanguage(contact,mobile)?></b><div class=input><input type=text maxlength=100 name=mobile data-validation=required></div></div>
		<div class=form-item><b><?=readLanguage(contact,email)?></b><div class=input><input type=email maxlength=100 name=email data-validation=email></div></div>
		<div class=form-item><b><?=readLanguage(contact,subject)?></b><div class=input><input type=text maxlength=100 name=subject data-validation=required></div></div>
		<div class=form-item><b><?=readLanguage(contact,message)?></b><div class=input><textarea name=message maxlength=1000 verbose style="height:150px" data-validation=required></textarea></div></div>
		<? if ($system_settings["recaptcha_secret_key"]){ ?>
		<div class=recaptcha_box>
			<small><?=readLanguage(general,recaptcha_required)?></small>
			<center><div class=g-recaptcha data-sitekey="<?=$system_settings["recaptcha_site_key"]?>"></div></center>
		</div>
		<? } ?>
		<div class=submit_container><button type=button class=submit><?=readLanguage(contact,send)?></button></div>
	</form>
<? } else { ?>
	<div class=message>
		<div class=success_icon></div>
		<b><?=readLanguage(contact,success_description)?></b>
	</div>
<? } ?>
</div>