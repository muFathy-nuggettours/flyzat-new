<? include "system/_handler.php";

//Index doesn't support multiple languages
$multiple_languages = false;

//Login rate limit
$rate_limit = 5;
	
//Handle login post request
if ($post["token"]){
	$rules["login_username"] = array("required", "max_length(255)");
	$rules["login_password"] = array("required", "max_length(255)", "min_length(8)");
	$validation_result = SimpleValidator\Validator::validate($post,$rules);	
	if ($validation_result->isSuccess()==false){
		$message = "<div class='alert alert-danger'>" . readLanguage(login,invalid) . "</div>";
	} else {
		$valid_attempt = false;
		if ($system_settings["recaptcha_secret_key"] && $_SESSION[$recaptcha_session] >= $rate_limit){
			if (isset($post["g-recaptcha-response"]) && !empty($post["g-recaptcha-response"])){
				$recaptcha_response = json_decode(file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=". $system_settings["recaptcha_secret_key"] . "&response=" . $post["g-recaptcha-response"]));
				$valid_attempt = ($recaptcha_response->success ? true : false);
			}
		} else {
			$valid_attempt = true;
		}
		if ($valid_attempt){
			$user_hashed_password = mysqlFetch(mysqlQuery("SELECT password FROM system_administrators WHERE username='" . $post["login_username"] . "'"))["password"];
			if ($user_hashed_password && password_verify($post["login_password"], $user_hashed_password)){
				$logged_user = mysqlFetch(mysqlQuery("SELECT * FROM system_administrators WHERE username='" . $post["login_username"] . "'"));
				userLogin($logged_user["hash"], $post["remember"]);
				$_SESSION[$recaptcha_session] = 0;
				header("Location: .");
			} else {
				$message = "<div class='alert alert-danger'>" . readLanguage(login,invalid) . "</div>";
				$_SESSION[$recaptcha_session]++;
			}
		} else {
			$message = "<div class='alert alert-danger'>" . readLanguage(general,recaptcha_error) . "</div>";
		}
	}
}

include "_header.php"; ?>

<? if (!$logged_user){ ?>
	<? if ($system_settings["recaptcha_secret_key"]){ ?><script src="https://www.google.com/recaptcha/api.js" async defer></script><? } ?>
	<div class="row login">
		<div class="col-md-10 login_form">
			<h1><?=$website_information["website_name"]?><br><small><?=readLanguage(general,control_panel)?></small></h1>
			<div class=login_form>
				<img src="../uploads/_website/<?=$website_information["website_logo"]?>">
				<?=$message?>
				<form method=post>
					<input type=hidden name=token value="<?=$token?>">
					<center><?=readLanguage(login,description)?></center>
					<table class="form_table margin-top-5">
						<tr><td><div class=input data-icon="&#xf1fa;"><input type=text name=login_username maxlength=100 placeholder="<?=readLanguage(login,username)?>" data-validation=required autocomplete=new-password></div></td></tr>
						<tr><td><div class=input data-icon="&#xf023;"><input type=password name=login_password placeholder="<?=readLanguage(login,password)?>" data-validation=required autocomplete=new-password></div></td>			</tr>
					</table>
					<div class="check_container margin-top-5">
						<label><input type=checkbox class=filled-in name=remember value=remember checked><span><?=readLanguage(login,remember)?></span></label>
					</div>
					<? if ($_SESSION[$recaptcha_session] >= $rate_limit){ ?>
					<div class=recaptcha_box>
						<small><?=readLanguage(general,recaptcha_required)?></small>
						<center><div class=g-recaptcha data-sitekey="<?=$system_settings["recaptcha_site_key"]?>"></div></center>
					</div>
					<? } ?>
					<div class=submit_container><button type=button class=submit><?=readLanguage(login,login)?></button></div>
				</form>
			</div>
		</div>
		<div class="col-md-10 login_side">
			<!-- Version -->
			<div class="version align-center">
				<?=readLanguage(general,control_panel)?> <b><?=$website_information["website_name"]?></b>
				<?=$powered_by?>
				<small>Version <?=$system_settings["system_version"]?></small>
				<br><small><?=(!$white_label ? "Prismatecs " : "")?>CMS Version <?=$cms_version?></small>
			</div>
		</div>
	</div>

<? } else { ?>
	<!-- Modules -->
	<? include "modules/welcome.php"; ?>
	<? include "modules/statistics.php"; ?>
	
	<!-- Icons -->
	<div class="panel-group menu-panel" id=index-menu>
		<?=str_replace("{{menu_parent}}", "index-menu", $menu)?>
	</div>
	
<? } ?>

<? include "_footer.php"; ?>