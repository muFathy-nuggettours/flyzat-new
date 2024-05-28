<?
if ($post["token"] && $post["action"]=="update-profile"){
	//Input handling
	$mobile_phone_code = getData("system_database_countries", "code", $post["country"], "phone_code");
	$mobile_prefix = "+" . $mobile_phone_code;
	$mobile = $mobile_prefix . cltrim($post["mobile"], "0");
	$mobile_conventional = "0" . cltrim($post["mobile"], "0");
	$email = strtolower($post["email"]);
	
	//Server Side Validation
	$errors = array();
	if (mysqlNum(mysqlQuery("SELECT id FROM users_database WHERE email='$email' AND id!='" . $logged_user["id"] . "'"))){
		array_push($errors, readLanguage(user,email_registered));
	}
	if (mysqlNum(mysqlQuery("SELECT * FROM users_database WHERE (mobile='$mobile' OR mobile_conventional='$mobile_conventional') AND id!='" . $logged_user["id"] . "'"))){
		array_push($errors, readLanguage(user,mobile_registered));
	}
	$rules["name"] = array("required","max_length(100)");
	$rules["email"] = array("required","max_length(100)","email");
	$rules["mobile"] = array("required","min_length(6)");
	$validation_result = SimpleValidator\Validator::validate($post,$rules);

	//Validate user data
	if ($validation_result->isSuccess()==false || !$mobile_phone_code){
		$error = readLanguage(general,error);
	} else if ($errors){
		$error = "<ul><li>" . implode("</li><li>", $errors) . "</li></ul>";
	} else {
		//Update & Re-Login
		mysqlQuery("UPDATE users_database SET
			name='" . $post["name"] . "',
			email='" . $email . "',
			mobile_prefix='" . $mobile_prefix . "',
			mobile='" . $mobile . "',
			mobile_conventional='" . $mobile_conventional . "',
			country='" . $post["country"] . "',
			password='" . ($post["password"] ? password_hash($post["password"], PASSWORD_DEFAULT) : $logged_user["password"]) . "'
		WHERE id='" . $logged_user["id"] . "'");
		$logged_user = mysqlFetch(mysqlQuery("SELECT * FROM users_database WHERE id='" . $logged_user["id"] . "'"));
		$success = readLanguage(user,edit_success);
		
		//Newsletter
		if ($post["newsletter"]){
			if (!mysqlNum(mysqlQuery("SELECT email FROM channel_newsletter WHERE email='$email'"))){
				$query = "INSERT INTO channel_newsletter (
					email,
					date
				) VALUES (
					'" . $email . "',
					'" . time() . "'
				)";
				mysqlQuery($query);
			}
		} else {
			mysqlQuery("DELETE FROM channel_newsletter WHERE email='$email'");
		}
	}
}

if ($success){ $message = "<div class='alert alert-success'>$success</div>"; }
if ($error){ $message = "<div class='alert alert-danger'>$error</div>"; }
?>

<div class=page_container>
<?=$message?>

<form method=post>
	<input type=hidden name=token value="<?=$token?>">
	<input type=hidden name=action value="update-profile">

	<!-- Name & E-Mail -->
	<table class=form_table>
	<tr>
		<td>
			<div class=title><?=readLanguage(accounts,name)?> <i class=requ></i></div>
			<div class=input data-icon="&#xf007;"><input type=text name=name value="<?=$logged_user["name"]?>" maxlength=255 placeholder="<?=readLanguage(accounts,name_placeholder)?>" data-validation=required></div>
		</td>
		<td>
			<div class=title><?=readLanguage(accounts,email)?> <i class=requ></i></div>
			<div class=input data-icon="&#xf1fa;"><input type=email name=email value="<?=$logged_user["email"]?>" maxlength=100 placeholder="<?=readLanguage(accounts,email_placeholder)?>" data-validation=email autocomplete=email></div>
		</td>
	</tr>

	<!-- Mobile -->
	<tr>
		<td>
			<div class=title><?=readLanguage(accounts,mobile)?> <i class=requ></i></div>
			<div class="input force-ltr" data-icon="&#xf3cd;">
				<select name=country id=country>
				<? $country_result = mysqlQuery("SELECT code, phone_code, en_name, ar_name FROM system_database_countries ORDER BY phone_code ASC");
				while ($country_entry = mysqlFetch($country_result)){
					print "<option value='" . $country_entry["code"] . "' data-phone-code='+" . $country_entry["phone_code"] . "' data-name='" . $country_entry[$website_language . "_name"] . "'>+" . $country_entry["phone_code"] . " " . $country_entry[$website_language . "_name"] . "</option>";
				} ?>
				</select>
				&nbsp;&nbsp;<input type=number name=mobile value="<?=str_replace($logged_user["mobile_prefix"], null, $logged_user["mobile"])?>" maxlength=11 placeholder="<?=readLanguage(accounts,mobile_placeholder)?>" data-validation=validateMobile>
			</div>
			<script>
				//Set Default Value
				setSelectValue("#country", "<?=$logged_user["country"]?>");
				
				//Initialize Select2
				$("#country").select2({
					dir: "ltr",
					width: "40%",
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
	
		<!-- Password -->
		<td>
			<script src="plugins/password-requirements.js?v=<?=$system_settings["system_version"]?>"></script>
			<div class=title><?=readLanguage(accounts,password)?></div>
			<div class=input data-icon="&#xf023;"><input type=password id=password name=password placeholder="<?=readLanguage(user,edit_password)?>" data-validation-optional=true data-validation=passwordStrength autocomplete=new-password></div>
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
			</script>
		</td>
	</tr>			
	</table>
	
	<!-- Newsletter -->
	<div class="check_container margin-top margin-bottom">
		<? $subscribed = mysqlNum(mysqlQuery("SELECT email FROM channel_newsletter WHERE email='" . $logged_user["email"] . "'")); ?>
		<label><input type=checkbox class=filled-in name=newsletter value=newsletter <?=($subscribed ? "checked" : "")?>><span><?=readLanguage(accounts,newsletter)?></span></label>
	</div>

	<div class=submit_container>
		<button type=button class=submit><?=readLanguage(user,update_profile)?></button>
	</div>
</form>
</div>