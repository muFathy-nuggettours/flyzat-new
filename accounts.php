<? include "system/_handler.php";

$mysqltable = "users_database";
$section_information = mysqlFetch(mysqlQuery("SELECT * FROM " . $suffix . "website_pages WHERE page='" . basename($_SERVER["SCRIPT_FILENAME"], ".php") . "'"));
if ($section_information["hidden"]){ brokenLink(); }
$header_image = $section_information["image"];

//Login rate limit
$rate_limit = 5;

//Valid social media login platforms
$valid_platforms = array("facebook", "google");

//Set page action
switch ($get["action"]){
	case "login":
		requireLogin(false);
		$section_title = readLanguage(accounts,login);
	break;
	
	case "reset-password":
		requireLogin(false);
		$section_title = readLanguage(accounts,reset_password);
	break;

	case "signup":
		requireLogin(false);
		$section_title = readLanguage(accounts,signup);
	break;	
	
	case "logout":
		requireLogin(true);
		$section_title = readLanguage(accounts,logout);
		$_SESSION[$user_session] = null;
		writeCookie($user_cookie, null, time() - (86400 * 30), "/");
		$user_hash = null;
		$logged_user = null;
	break;
}

//Validate social media platform
if ($post["platform"] && in_array($post["platform"], $valid_platforms)){
	$platform = $post["platform"];
}

//========== Sign-Up ==========
if ($post["action"]=="signup"){
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

	//Validate inputs when signing up via social media
	if ($platform){
		$_SESSION[$recaptcha_session]++;
		if ($_SESSION[$recaptcha_session] < $rate_limit){
			$valid_attempt = true;
		}
		$post["password"] = generateHash(10);
	}

	//Input handling
	$new_record_id = newRecordID($mysqltable);
	$email = strtolower($post["email"]);
	$hash = md5(uniqid($new_record_id,true));

	//Server side validation
	$errors = array();
	
	//Validate mobile if set
	if ($post["mobile"]){
		$mobile_phone_code = getData("system_database_countries", "code", $post["country"], "phone_code");
		$mobile_prefix = "+" . $mobile_phone_code;
		$mobile = $mobile_prefix . cltrim($post["mobile"], "0");
		$mobile_conventional = "0" . cltrim($post["mobile"], "0");
		if (mysqlNum(mysqlQuery("SELECT * FROM $mysqltable WHERE mobile='$mobile' OR mobile_conventional='$mobile_conventional'"))){
			array_push($errors, readLanguage(accounts,mobile_registered) . " <a class=alert-link href='reset-password/'>" . readLanguage(accounts,reset_password) . "</a>");
		}
	}
	
	if (mysqlNum(mysqlQuery("SELECT id FROM $mysqltable WHERE email='$email'"))){
		array_push($errors, readLanguage(accounts,email_registered) . " <a class=alert-link href='reset-password/'>" . readLanguage(accounts,reset_password) . "</a>");
	}
	$rules["name"] = array("required", "max_length(100)");
	$rules["email"] = array("required", "max_length(100)", "email");
	$rules["password"] = array("required", "min_length(8)");
	$validation_result = SimpleValidator\Validator::validate($post, $rules);

	//Validate user data
	if ($validation_result->isSuccess()==false){
		$error = readLanguage(general,error);
	} else if (!$valid_attempt){
		$error = readLanguage(general,recaptcha_error);
	} else if ($errors){
		$error = "<ul><li>" . implode("</li><li>", $errors) . "</li></ul>";
	} else {
		$user_id = generateUserID($mysqltable);
		
		//Get user image on social media login
		if ($platform && $post["picture"]){
			$image = uniqid($user_id . "_") . ".png";
			$image_jpg = file_get_contents(unescapeString(html_entity_decode($post["picture"])));
			$image_object = imagecreatefromstring($image_jpg);
			imagepng($image_object, "uploads/users/" . $image);
		}
		
		$signup_query = "INSERT INTO $mysqltable (
			user_id,
			user_country,
			user_currency,
			name,
			email,
			country,
			mobile_prefix,
			mobile,
			mobile_conventional,
			image,
			password,
			hash,
			date
		) VALUES (
			'" . $user_id . "',
			'" . $user_countryCode . "',
			'" . $user_paymentCurrency["code"] . "',
			'" . $post["name"] . "',
			'" . $email . "',
			'" . $post["country"] . "',
			'" . $mobile_prefix . "',
			'" . $mobile . "',
			'" . $mobile_conventional . "',
			'" . $image . "',
			'" . password_hash($post["password"], PASSWORD_DEFAULT) . "',
			'" . $hash . "',
			'" . time() . "'
		)";
		mysqlQuery($signup_query);
		
		//Login and fetch logged user data
		userLogin($hash, true);
		$logged_user = mysqlFetch(mysqlQuery("SELECT * FROM $mysqltable WHERE hash='$hash'"));
		
		//Update platform & send password when signing up via social media
		if ($platform){
			mysqlQuery("UPDATE $mysqltable SET $platform='" . $post["user_id"] . "' WHERE id=" . $logged_user["id"]);
			sendChannelTemplate("user_create", $logged_user["id"], array($post["password"]));
		}

		//Newsletter
		if ($post["newsletter"]){
			if (!mysqlNum(mysqlQuery("SELECT email FROM channel_newsletter WHERE email='" . $logged_user["email"] . "'"))){
				$query = "INSERT INTO channel_newsletter (
					email,
					date
				) VALUES (
					'" . $logged_user["email"] . "',
					'" . time() . "'
				)";
				mysqlQuery($query);
			}
		}
	}
}

//========== Login ==========
if ($post["action"]=="login"){
	//Validate reCAPTCHA
	$valid_attempt = false;
	if ($system_settings["recaptcha_secret_key"]){
		if ($_SESSION[$recaptcha_session] >= $rate_limit){
			if (isset($post["g-recaptcha-response"]) && !empty($post["g-recaptcha-response"])){
				$recaptcha_response = json_decode(file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=". $system_settings["recaptcha_secret_key"] . "&response=" . $post["g-recaptcha-response"]));
				$valid_attempt = ($recaptcha_response->success ? true : false);
			}
		} else {
			$valid_attempt = true;
		}
	} else {
		$valid_attempt = true;
	}
	
	//Input Handling
	$mobile = $post["mobile_prefix"] . cltrim($post["mobile"],"0");
	$mobile_conventional = "0" . cltrim($post["mobile"],"0");
	$email = strtolower($post["email"]);
	$hash = md5(uniqid($new_record_id,true));
	
	//Server Side Validation
	$rules["email"] = array("required", "max_length(100)", "email");
	$rules["password"] = array("required");
	$validation_result = SimpleValidator\Validator::validate($post, $rules);
	
	//Validate user data
	if ($validation_result->isSuccess()==false){
		$error = readLanguage(accounts,invalid);
	} else if (!$valid_attempt){
		$error = readLanguage(general,recaptcha_error);	
	} else {
		$user_hashed_password = mysqlFetch(mysqlQuery("SELECT password FROM $mysqltable WHERE email='$email'"))["password"];
		if ($user_hashed_password && password_verify($post["password"], $user_hashed_password)){
			$logged_user = mysqlFetch(mysqlQuery("SELECT * FROM $mysqltable WHERE email='$email'"));
			userLogin($logged_user["hash"], $post["remember"]);
			$_SESSION[$recaptcha_session] = 0;
		} else {
			$error = readLanguage(accounts,invalid);
			$_SESSION[$recaptcha_session]++;
		}
	}
}

//========== Reset Password ==========
if ($post["action"]=="reset-password"){
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
	$rules["email"] = array("required","max_length(100)","email");
	$validation_result = SimpleValidator\Validator::validate($post,$rules);
	
	//Validate user data
	if ($validation_result->isSuccess()==false){
		$error = readLanguage(accounts,email_not_registered);
	} else if (!$valid_attempt){
		$error = readLanguage(general,recaptcha_error);		
	} else if (!mysqlNum(mysqlQuery("SELECT id FROM $mysqltable WHERE email='" . strtolower($post["email"]) . "'"))){
		$error = readLanguage(accounts,email_not_registered);
	} else {
		$user_data = mysqlFetch(mysqlQuery("SELECT * FROM $mysqltable WHERE email='" . strtolower($post["email"]) . "'"));
		$existing_hash = mysqlFetch(mysqlQuery("SELECT * FROM users_reset_password WHERE user_hash='" . $user_data["hash"] . "'"));
		if ($existing_hash){
			mysqlQuery("UPDATE users_reset_password SET date='" . time() . "' WHERE id='" . $existing_hash["id"] . "'");
			$reset_hash = $existing_hash["reset_hash"];
		} else {
			$reset_hash = md5(uniqid(rand(1000,9999), true));
			mysqlQuery("INSERT INTO users_reset_password (user_hash,reset_hash,date) VALUES ('" . $user_data["hash"] . "','" . $reset_hash . "','" . time() . "')");
		}
		$reset_url = $base_url . "reset-password/" . $reset_hash . "/";
		$message = readLanguage(mails,body_reset_password, array("<a href='" . $reset_url . "'>" . readLanguage(accounts,reset_password) . "</a>", "<span style='color:#404040'>$reset_url</span>"));
		sendMail(array($user_data["email"]), readLanguage(mails,subject_reset_password), nl2br($message), $website_language);
		$reset_message = true;
	}
}

//========== Create New Password ==========
if ($get["hash"]){
	$reset_data = mysqlFetch(mysqlQuery("SELECT * FROM users_reset_password WHERE reset_hash='" . $get["hash"] . "' AND date < '" . (time() + 86400) . "'"));
	if (!$reset_data){
		brokenLink();
	} else {
		$user_data = mysqlFetch(mysqlQuery("SELECT * FROM $mysqltable WHERE hash='" . $reset_data["user_hash"] . "'"));
		$new_password = generateHash(10);
		mysqlQuery("UPDATE $mysqltable SET password='" . password_hash($new_password, PASSWORD_DEFAULT) . "' WHERE hash='" . $reset_data["user_hash"] . "'");
		mysqlQuery("DELETE FROM users_reset_password WHERE user_hash='" . $user_data["hash"] . "'");
		$message = readLanguage(mails,body_new_password,array($user_data["email"], $new_password));
		sendMail(array($user_data["email"]), readLanguage(mails,subject_new_password), nl2br($message), $website_language);
		$reset_success = true;
	}
}

if ($success){ $message = "<div class='alert alert-success'>$success</div>"; }
if ($error){ $message = "<div class='alert alert-danger'>$error</div>"; }

include "system/header.php";
include "website/section_header.php"; ?>

<? if ($system_settings["recaptcha_secret_key"]){ ?><script src="https://www.google.com/recaptcha/api.js" async defer></script><? } ?>

<!-- Start Tags --><div style="width:600px; max-width:100%; margin:0 auto 0 auto"><div class=page_container>

<!-- SignUp -->
<? if ($get["action"]=="signup"){ ?>
	<? if (!$logged_user){ ?>
		<?=$message?>
		
		<form method=post>
			<input type=hidden name=token value="<?=$token?>">
			<input type=hidden name=action value="signup">
			
			<!-- Social Media Block -->
			<? if ($platform && ($post["name"] || $post["email"] || $post["picture"])){ ?>
				<input type=hidden name=platform value="<?=$platform?>">
				<input type=hidden name=user_id value="<?=$post["user_id"]?>">
				
				<div class="user_card social">
					<? if ($post["picture"]){ ?>
						<img src="<?=unescapeString(html_entity_decode($post["picture"]))?>">
						<input type=hidden name=picture value="<?=unescapeString(html_entity_decode($post["picture"]))?>">
					<? } ?>
					<div>
						<? if ($post["name"]){ ?>
							<span class=single-line><b><?=$post["name"]?></b></span>
							<input type=hidden name=name value="<?=$post["name"]?>">
						<? } ?>
						<? if ($post["email"]){ ?>
							<span class=single-line><small><?=$post["email"]?></small></span>
							<input type=hidden name=email value="<?=$post["email"]?>">
						<? } ?>
					</div>	
				</div>
			<? } ?>

			<!-- Signup Form -->
			<table class=form_table>
			
			<!-- Signing up with a social media account (Show name and email fields if not received) -->
			<? if ($platform){ ?>
				<? if (!$post["name"]){ ?>
				<tr>
					<td colspan=2>
						<div class=title><?=readLanguage(accounts,name)?> <i class=requ></i></div>
						<div class=input data-icon="&#xf007;"><input type=text name=name value="<?=$post["name"]?>" maxlength=255 placeholder="<?=readLanguage(accounts,name_placeholder)?>" data-validation=required></div>
					</td>
				</tr>				
				<? } ?>
				<? if (!$post["email"]){ ?>
				<tr>
					<td colspan=2>
						<div class=title><?=readLanguage(accounts,email)?> <i class=requ></i></div>
						<div class=input data-icon="&#xf1fa;"><input type=email name=email value="<?=$post["email"]?>" maxlength=100 placeholder="<?=readLanguage(accounts,email_placeholder)?>" data-validation=email autocomplete=email></div>
					</td>
				</tr>				
				<? } ?>
			
			<!-- Not signing up with a social media account (Show name and email fields) -->
			<? } else { ?>
			<tr>
				<td>
					<div class=title><?=readLanguage(accounts,name)?> <i class=requ></i></div>
					<div class=input data-icon="&#xf007;"><input type=text name=name value="<?=$post["name"]?>" maxlength=255 placeholder="<?=readLanguage(accounts,name_placeholder)?>" data-validation=required></div>
				</td>
				<td>
					<div class=title><?=readLanguage(accounts,email)?> <i class=requ></i></div>
					<div class=input data-icon="&#xf1fa;"><input type=email name=email value="<?=$post["email"]?>" maxlength=100 placeholder="<?=readLanguage(accounts,email_placeholder)?>" data-validation=email autocomplete=email></div>
				</td>
			</tr>
			<? } ?>
			
			<!-- Mobile -->
			<tr>
				<td colspan=2>
					<div class=title><?=readLanguage(accounts,mobile)?> <i class=requ></i></div>
					<div class="input force-ltr" data-icon="&#xf3cd;">
						<select name=country id=country>
						<? $country_result = mysqlQuery("SELECT code, phone_code, en_name, ar_name FROM system_database_countries ORDER BY phone_code ASC");
						while ($country_entry = mysqlFetch($country_result)){
							print "<option value='" . $country_entry["code"] . "' data-name='" . $country_entry[$website_language . "_name"] . "' data-phone-code='+" . $country_entry["phone_code"] . "'>+" . $country_entry["phone_code"] . " " . $country_entry[$website_language . "_name"] . "</option>";
						} ?>
						</select>
						&nbsp;&nbsp;<input type=number name=mobile value="<?=$post["mobile"]?>" maxlength=11 placeholder="<?=readLanguage(accounts,mobile_placeholder)?>" data-validation=validateMobile>
					</div>
					<script>
						//Set Default Value
						setSelectValue("#country", "<?=($post["country"] ? $post["country"] : $user_countryCode)?>");
						
						//Initialize Select2
						$("#country").select2({
							dir: "ltr",
							width: "25%",
							dropdownAutoWidth: true,
							templateResult: function(state){
								var element = $(state.element);
								return $("<div class='d-flex align-items-center'><img src='images/countries/" + $(element).val() + ".gif'>&nbsp;&nbsp;<span><b>(" + $(element).data("phone-code") + ")</b>&nbsp;&nbsp;" + $(element).attr("data-name") + "</span></div>");
							},
							templateSelection: function(state){
								var element = $(state.element);
								return $("<div class='d-flex align-items-center'><img src='images/countries/" + $(element).val() + ".gif'>" + "&nbsp;<span>" + $(element).data("phone-code") + "</span></div>");
							}
						});
						
						//Validate Editor
						$.formUtils.addValidator({
							name: "validateMobile",
							validatorFunction: function(value, $el, config, language, $form){
								var valid_mobile =  false;
								switch ($("#country").val()){
									case "eg":
										valid_mobile = (value.match(/^((010|011|012|015)[0-9]{8})|((10|11|12|15)[0-9]{8})$/g)==value);
									break;
									
									case "sa":
										valid_mobile = (value.match(/^((05)[0-9]{8})|((5)[0-9]{8})$/g)==value);
									break;
									
									default:
										valid_mobile = true;
								}
								return (value ? true : false) && valid_mobile;
							},
							errorMessage: "<?=readLanguage(accounts,mobile_placeholder)?>"
						});
					</script>
				</td>
			</tr>
			
			<!-- Password -->
			<? if (!$platform){ ?>
			<tr>
				<td>
					<script src="plugins/password-requirements.js?v=<?=$system_settings["system_version"]?>"></script>
					<div class=title><?=readLanguage(accounts,password)?> <i class=requ></i></div>
					<div class=input data-icon="&#xf023;"><input match=password_retype type=password id=password name=password placeholder="<?=readLanguage(accounts,password_placeholder)?>" data-validation=passwordStrength autocomplete=new-password></div>
				</td>
				<td>
					<div class=title><?=readLanguage(accounts,password_retype)?> <i class=requ></i></div>
					<div class=input data-icon="&#xf023;"><input type=password name=password_retype placeholder="<?=readLanguage(accounts,password_placeholder)?>" data-validation=validatePasswordRetype data-validation-error-msg="Passowrds don't match" autocomplete=new-password></div>
					<script>
					//Initialize Password Requirements
					$("#password").PassRequirements({
						defaults: false,
						rules: {
							minlength: {
								text: "<?=readLanguage(accounts,password_long,array("minLength"))?>",
								minLength: 8,
							},
							containSpecialChars: {
								text: "<?=readLanguage(accounts,password_special,array("minLength"))?>",
								minLength: 1,
								regex: new RegExp("([^!,%,&,@,#,$,^,*,?,_,~])", "g")
							},
							containLowercase: {
								text: "<?=readLanguage(accounts,password_lower,array("minLength"))?>",
								minLength: 1,
								regex: new RegExp("[^a-z]", "g")
							},
							containUppercase: {
								text: "<?=readLanguage(accounts,password_upper,array("minLength"))?>",
								minLength: 1,
								regex: new RegExp("[^A-Z]", "g")
							},
							containNumbers: {
								text: "<?=readLanguage(accounts,password_number,array("minLength"))?>",
								minLength: 1,
								regex: new RegExp("[^0-9]", "g")
							}
						}
					});
					
					//Validate Password Strength
					$.formUtils.addValidator({
						name : "passwordStrength",
						validatorFunction : function(value, $el, config, language, $form){
							return $el.data("valid-password")==true;
						},
						errorMessage: "<?=readLanguage(accounts,password_weak)?>",
					});
				
					//Validate Password Re-Type
					$.formUtils.addValidator({
						name: "validatePasswordRetype",
						validatorFunction: function(value, $el, config, language, $form){
							var inputPassword = $("input[match=" + $el.attr("name") + "]").val();
							return inputPassword == $el.val();
						}
					});
					</script>
				</td>
			</tr>
			<? } ?>
			</table>
			
			<!-- Newsletter -->
			<div class="check_container margin-top">
				<label><input type=checkbox class=filled-in name=newsletter value=newsletter checked><span><?=readLanguage(accounts,newsletter)?></span></label>
			</div>
			
			<!-- ReCAPTCHA -->
			<? if ($system_settings["recaptcha_secret_key"]){ ?>
			<div class=recaptcha_box>
				<small><?=readLanguage(general,recaptcha_required)?></small>
				<center><div class=g-recaptcha data-sitekey="<?=$system_settings["recaptcha_site_key"]?>"></div></center>
			</div>
			<? } ?>
			
			<!-- Submit -->
			<div class=submit_container_blank>
				<button type=button class="submit margin-bottom"><?=readLanguage(accounts,signup)?></button>
				<?=readLanguage(accounts,has_account)?> <a href="login/"><?=readLanguage(accounts,login)?></a>
			</div>
		</form>
		
		<!-- Social Media -->
		<? include "_inl_social_login.php"; ?>
		
	<? } else { ?>
		<center>
			<div class="alert alert-success"><?=readLanguage(accounts,register_success)?></div>
			<div class=login_message>
				<img src="<?=($logged_user["image"] ? "uploads/users/" . $logged_user["image"] : "images/user.png")?>">
				<span><?=readLanguage(accounts,welcome)?> <b><?=$logged_user["name"]?></b></span>
				<?=readLanguage(accounts,redirect)?>
			</div>
			<script>
				$(document).ready(function(){
					if (typeof userLoggedIn === "function"){
						userLoggedIn("<?=$logged_user["hash"]?>");
					}
				});
				window.setTimeout(function(){
					setWindowLocation("<?=($_SESSION[$redirect_session] ? $_SESSION[$redirect_session] : ".")?>");
				}, 2000);
			</script>
		</center>
	<? } ?>

<!-- Login -->
<? } else if ($get["action"]=="login"){ ?>
	<? if (!$logged_user){ ?>
		<?=$message?>
		
		<form method=post>
			<input type=hidden name=token value="<?=$token?>">
			<input type=hidden name=action value="login">
			
			<!-- E-Mail & Password -->
			<table class=form_table>
			<tr>
				<td>
					<div class=title><?=readLanguage(accounts,email)?> <i class=requ></i></div>
					<div class=input data-icon="&#xf1fa;"><input type=email name=email value="<?=$post["email"]?>" maxlength=100 placeholder="<?=readLanguage(accounts,email_placeholder)?>" data-validation=email autocomplete=email></div>
				</td>
			</tr>
			<tr>
				<td>
					<script src="plugins/password-requirements.js?v=<?=$system_settings["system_version"]?>"></script>
					<div class=title><?=readLanguage(accounts,password)?> <i class=requ></i></div>
					<div class=input data-icon="&#xf023;"><input match=password_retype type=password id=password name=password placeholder="<?=readLanguage(accounts,password_placeholder)?>" data-validation=required autocomplete=new-password></div>
				</td>			
			</tr>
			</table>
			
			<!-- Remember Credential & Reset Password -->
			<div class=login_check>
				<div class=check_container><label><input type=checkbox class=filled-in name=remember value=remember checked><span><?=readLanguage(accounts,remember)?></span></label></div>
				<small><?=readLanguage(accounts,forgot_password)?> <a href="reset-password/"><?=readLanguage(accounts,reset_password)?></a></small>
			</div>
			
			<!-- ReCAPTCHA -->
			<? if ($system_settings["recaptcha_secret_key"] && $_SESSION[$recaptcha_session] >= $rate_limit){ ?>
			<div class=recaptcha_box>
				<small><?=readLanguage(general,recaptcha_required)?></small>
				<center><div class=g-recaptcha data-sitekey="<?=$system_settings["recaptcha_site_key"]?>"></div></center>
			</div>
			<? } ?>
			
			<!-- Submit -->
			<div class=submit_container_blank>
				<button type=button class="submit margin-bottom"><?=readLanguage(accounts,login)?></button>
				<?=readLanguage(accounts,no_account)?> <a href="signup/"><?=readLanguage(accounts,signup)?></a>
			</div>
		</form>
		
		<!-- Social Media -->
		<? include "_inl_social_login.php"; ?>
		
	<? } else { ?>
		<center>
			<div class="alert alert-success"><?=readLanguage(accounts,login_success)?></div>
			<div class=login_message>
				<img src="<?=($logged_user["image"] ? "uploads/users/" . $logged_user["image"] : "images/user.png")?>">
				<span><?=readLanguage(accounts,welcome)?> <b><?=$logged_user["name"]?></b></span>
				<?=readLanguage(accounts,redirect)?>
			</div>
			<script>
				$(document).ready(function(){
					if (typeof userLoggedIn === "function"){
						userLoggedIn("<?=$logged_user["hash"]?>");
					}
				});
				window.setTimeout(function(){
					setWindowLocation("<?=($_SESSION[$redirect_session] ? $_SESSION[$redirect_session] : ".")?>");
				}, 2000);
			</script>
		</center>
	<? } ?>	

<!-- Reset Password -->
<? } else if ($get["action"]=="reset-password"){ ?>
	<!-- Password reset success -->
	<? if ($reset_success){ ?>
		<center>
			<div class="alert alert-success"><?=readLanguage(accounts,reset_success_title)?></div>
			<div class=login_message><?=readLanguage(accounts,reset_success_description)?></div>
		</center>
	
	<!-- Password reset sent -->
	<? } else if ($reset_message){ ?>
		<center>
			<div class="alert alert-success"><?=readLanguage(accounts,reset_instructions_title)?></div>
			<div class=login_message><?=readLanguage(accounts,reset_instructions_description)?></div>
		</center>
		
	<!-- Password reset request -->
	<? } else { ?>
		<?=$message?>
		
		<form method=post>
			<input type=hidden name=token value="<?=$token?>">
			<input type=hidden name=action value="reset-password">

			<!-- E-Mail -->
			<table class=form_table>
			<tr>
				<td>
					<div class=title><?=readLanguage(accounts,email)?> <i class=requ></i></div>
					<div class=input data-icon="&#xf1fa;"><input type=email name=email value="<?=$post["email"]?>" maxlength=100 placeholder="<?=readLanguage(accounts,email_placeholder)?>" data-validation=email autocomplete=email></div>
				</td>
			</tr>
			</table>
			
			<? if ($system_settings["recaptcha_secret_key"]){ ?>
			<div class=recaptcha_box>
				<small><?=readLanguage(general,recaptcha_required)?></small>
				<center><div class=g-recaptcha data-sitekey="<?=$system_settings["recaptcha_site_key"]?>"></div></center>
			</div>
			<? } ?>
			
			<div class=submit_container_blank>
				<button type=button class=submit><?=readLanguage(accounts,reset_password)?></button>
			</div>
		</form>
	<? } ?>	
	
<!-- Logout -->
<? } else if ($get["action"]=="logout"){ ?>
	<center>
		<div class="alert alert-success"><?=readLanguage(accounts,logout_success)?></div>
		<div class=login_message><?=readLanguage(accounts,redirect)?></div>
	</center>
	<script>
		$(document).ready(function(){
			if (typeof userLoggedOut === "function"){
				userLoggedOut();
			}
		});
		window.setTimeout(function(){
			setWindowLocation("<?=($_SESSION[$redirect_session] ? $_SESSION[$redirect_session] : ".")?>");
		}, 2000);
	</script>
<? } ?>

<!-- End Tags --></div></div>

<? include "website/section_footer.php";
include "system/footer.php"; ?>