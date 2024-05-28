<? include "system/_handler.php";

$multiple_languages = false;
$mysqltable = "system_settings";
$base_name = basename($_SERVER["SCRIPT_FILENAME"], ".php");
checkPermissions($base_name);

//==== EDIT Record ====
if ($post["token"]){
	mysqlQuery("UPDATE $mysqltable SET content = CASE
		WHEN title='google_maps_key' THEN '" . $post["google_maps_key"] . "'
		WHEN title='google_map_id' THEN '" . $post["google_map_id"] . "'
		WHEN title='google_analytics_id' THEN '" . $post["google_analytics_id"] . "'
		WHEN title='google_cse' THEN '" . $post["google_cse"] . "'
		WHEN title='youtube_data_key' THEN '" . $post["youtube_data_key"] . "'
		WHEN title='recaptcha_site_key' THEN '" . $post["recaptcha_site_key"] . "'
		WHEN title='recaptcha_secret_key' THEN '" . $post["recaptcha_secret_key"] . "'	
		
		WHEN title='google_login' THEN '" . $post["google_login"] . "'
		WHEN title='google_client_id' THEN '" . ($post["google_login"] ? $post["google_client_id"] : "") . "'
		WHEN title='google_client_secret' THEN '" . ($post["google_login"] ? $post["google_client_secret"] : "") . "'

		WHEN title='facebook_pixel' THEN '" . $post["facebook_pixel"] . "'
		WHEN title='hover_signal' THEN '" . $post["hover_signal"] . "'
		
		WHEN title='facebook_login' THEN '" . $post["facebook_login"] . "'
		WHEN title='facebook_app_id' THEN '" . ($post["facebook_login"] ? $post["facebook_app_id"] : "") . "'
		WHEN title='facebook_app_secret' THEN '" . ($post["facebook_login"] ? $post["facebook_app_secret"] : "") . "'
		
		WHEN title='firebase_project_id' THEN '" . $post["firebase_project_id"] . "'
		WHEN title='firebase_project_number' THEN '" . $post["firebase_project_number"] . "'
		WHEN title='firebase_app_id' THEN '" . $post["firebase_app_id"] . "'
		WHEN title='firebase_app_api_key' THEN '" . $post["firebase_app_api_key"] . "'
		WHEN title='firebase_server_key' THEN '" . $post["firebase_server_key"] . "'		
		
		WHEN title='mail_server' THEN '" . $post["mail_server"] . "'
		WHEN title='mail_username' THEN '" . $post["mail_username"] . "'
		WHEN title='mail_password' THEN '" . $post["mail_password"] . "'
		WHEN title='mail_port' THEN '" . $post["mail_port"] . "'
		WHEN title='mail_from' THEN '" . $post["mail_from"] . "'
		WHEN title='mail_from_name' THEN '" . $post["mail_from_name"] . "'
		
		WHEN title='application_build' THEN '" . $post["application_build"] . "'
		WHEN title='application_android_bundle' THEN '" . $post["application_android_bundle"] . "'
		WHEN title='application_ios_bundle' THEN '" . $post["application_ios_bundle"] . "'
		
		WHEN title='sms_username' THEN '" . $post["sms_username"] . "'
		WHEN title='sms_password' THEN '" . $post["sms_password"] . "'
		WHEN title='sms_sender_id' THEN '" . $post["sms_sender_id"] . "'
		
		WHEN title='custom_css' THEN '" . $post["custom_css"] . "'
		WHEN title='custom_javascript' THEN '" . $post["custom_javascript"] . "'

		ELSE content
	END");
	$success = readLanguage(records,updated);
}

//Read and Set Operation
if ($success){ $message = "<div class='alert alert-success'>$success</div>"; }

//Reload Data
$system_settings = fetchData($mysqltable);

include "_header.php"; ?>

<style>
textarea.code {
	direction: ltr;
	text-align: left;
}
</style>

<script src="../plugins/wizard.min.js?v=<?=$system_settings["system_version"]?>"></script>
<link href="../plugins/wizard.css?v=<?=$system_settings["system_version"]?>" rel="stylesheet">

<div class=title><?=getPageTitle($base_name)?></div>
<?=$message?>

<form method=post enctype="multipart/form-data" action="<?=$action?>">
<input type=hidden name=token value="<?=$token?>">

<!--Navigation-->
<div class="wizard margin-top"><div class=wizard-inner><div class=connecting-line style="width:85%"></div><ul class="nav nav-tabs" role="tablist">
	<li role=presentation class=active>
		<a href="#settings_general" data-toggle=tab>
			<span class=round-tab><i class="fas fa-cogs"></i></span>
			<div class="tab-title hidden-sm hidden-xs"><?=readLanguage(pages,settings_general)?></div>
		</a>
	</li>
	<li role=presentation>
		<a href="#settings_email" data-toggle=tab>
			<span class=round-tab><i class="fas fa-envelope"></i></span>
			<div class="tab-title hidden-sm hidden-xs"><?=readLanguage(pages,settings_email)?></div>
		</a>
	</li>
	<li role=presentation>
		<a href="#settings_push" data-toggle=tab>
			<span class=round-tab><i class="fas fa-bell"></i></span>
			<div class="tab-title hidden-sm hidden-xs"><?=readLanguage(pages,settings_push)?></div>
		</a>
	</li>
	<li role=presentation>
		<a href="#settings_sms" data-toggle=tab>
			<span class=round-tab><i class="fas fa-sms"></i></span>
			<div class="tab-title hidden-sm hidden-xs"><?=readLanguage(pages,settings_sms)?></div>
		</a>
	</li>
	<li role=presentation>
		<a href="#settings_application" data-toggle=tab>
			<span class=round-tab><i class="fas fa-mobile-alt"></i></span>
			<div class="tab-title hidden-sm hidden-xs"><?=readLanguage(pages,settings_application)?></div>
		</a>
	</li>
	<li role=presentation>
		<a href="#settings_code" data-toggle=tab>
			<span class=round-tab><i class="fas fa-code"></i></span>
			<div class="tab-title hidden-sm hidden-xs"><?=readLanguage(pages,settings_code)?></div>
		</a>
	</li>
</ul></div></div>

<div class=tab-title-container>
	<span><?=readLanguage(pages,settings_general)?></span>
	<div class=tab-title-buttons>
		<button type=button class="btn btn-default btn-sm prev-step"><i class="glyphicon glyphicon-chevron-left"></i></button>&nbsp;
		<button type=button class="btn btn-default btn-sm next-step"><i class="glyphicon glyphicon-chevron-right"></i></button>
	</div>
	<div style="clear:both"></div>
</div>

<div class=tab-content>

<!-- General Settings -->
<div class="tab-pane active" id=settings_general>
<div class=subtitle><?=readLanguage(pages,settings_general_google)?></div>
<div class=data_table_container><table class=data_table>
<tr>
	<td class=title>Maps Javascript Key:</td>
	<td><input type=text name=google_maps_key value="<?=$system_settings["google_maps_key"]?>"></td>
</tr>
<tr>
	<td class=title>Google Map ID:</td>
	<td><input type=text name=google_map_id value="<?=$system_settings["google_map_id"]?>"></td>
</tr>
<tr>
	<td class=title>Google Analytics ID:</td>
	<td><input type=text name=google_analytics_id value="<?=$system_settings["google_analytics_id"]?>"></td>
</tr>
<tr>
	<td class=title>Google Search ID:</td>
	<td><input type=text name=google_cse value="<?=$system_settings["google_cse"]?>"></td>
</tr>
<tr>
	<td class=title>Youtube Data Key:</td>
	<td><input type=text name=youtube_data_key value="<?=$system_settings["youtube_data_key"]?>"></td>
</tr>
<tr>
	<td class=title>reCAPTCHA Site Key:</td>
	<td><input type=text name=recaptcha_site_key value="<?=$system_settings["recaptcha_site_key"]?>"></td>
</tr>
<tr>
	<td class=title>reCAPTCHA Secret Key:</td>
	<td><input type=text name=recaptcha_secret_key value="<?=$system_settings["recaptcha_secret_key"]?>"></td>
</tr>
<tr>
	<td class=title>Google Login:</td>
	<td>
		<div class=switch><label><?=$data_disabled_enabled[0]?><input type=checkbox name=google_login id=google_login onchange="toggleVisibility(this)" value=1 <?=($system_settings["google_login"] ? "checked" : "")?>><span class=lever></span><?=$data_disabled_enabled[1]?></label></div>
		<script>
		$(document).ready(function(){
			toggleVisibility($("#google_login")[0])
		});
		</script>
	</td>
</tr>
<tr visibility-control=google_login visibility-value=1>
	<td class=title>Google Client ID: <i class=requ></i></td>
	<td><input type=text name=google_client_id value="<?=$system_settings["google_client_id"]?>" data-validation=requiredVisible></td>
</tr>
<tr visibility-control=google_login visibility-value=1>
	<td class=title>Google Client Secret: <i class=requ></i></td>
	<td><input type=text name=google_client_secret value="<?=$system_settings["google_client_secret"]?>" data-validation=requiredVisible></td>
</tr>
</table></div>

<div class=subtitle><?=readLanguage(pages,settings_general_social)?></div>
<table class=data_table>
<tr>
	<td class=title>Facebook Login:</td>
	<td>
		<div class=switch><label><?=$data_disabled_enabled[0]?><input type=checkbox name=facebook_login id=facebook_login onchange="toggleVisibility(this)" value=1 <?=($system_settings["facebook_login"] ? "checked" : "")?>><span class=lever></span><?=$data_disabled_enabled[1]?></label></div>
		<script>
		$(document).ready(function(){
			toggleVisibility($("#facebook_login")[0])
		});
		</script>
	</td>
</tr>
<tr visibility-control=facebook_login visibility-value=1>
	<td class=title>Facebook App ID: <i class=requ></i></td>
	<td><input type=text name=facebook_app_id value="<?=$system_settings["facebook_app_id"]?>" data-validation=requiredVisible></td>
</tr>
<tr visibility-control=facebook_login visibility-value=1>
	<td class=title>Facebook App Secret: <i class=requ></i></td>
	<td><input type=text name=facebook_app_secret value="<?=$system_settings["facebook_app_secret"]?>" data-validation=requiredVisible></td>
</tr>
<tr>
	<td class=title>Facebook Pixel:</td>
	<td><input type=text name=facebook_pixel value="<?=$system_settings["facebook_pixel"]?>"></td>
</tr>
<tr>
	<td class=title>Hover Signal:</td>
	<td>
		<input type=text name=hover_signal value="<?=$system_settings["hover_signal"]?>">
		<div class=input_description><a href="https://www.hoversignal.com/" target=_blank>www.hoversignal.com</a></div>
	</td>
</tr>
</table>
</div>

<!-- E-Mail -->
<div class=tab-pane id=settings_email>
<div class=subtitle><?=readLanguage(pages,settings_email_connection)?></div>
<table class=data_table>
<tr>
	<td class=title><?=readLanguage(pages,settings_email_server)?>:</td>
	<td><input type=text name=mail_server value="<?=$system_settings["mail_server"]?>"></td>
</tr>
<tr>
	<td class=title><?=readLanguage(pages,user_username)?>:</td>
	<td><input type=text name=mail_username value="<?=$system_settings["mail_username"]?>"></td>
</tr>
	<tr><td class=title><?=readLanguage(pages,user_password)?>:</td>
	<td><input type=text name=mail_password value="<?=$system_settings["mail_password"]?>"></td>
</tr>
<tr>
	<td class=title><?=readLanguage(pages,settings_email_port)?>:</td>
	<td><input type=text name=mail_port value="<?=$system_settings["mail_port"]?>"></td>
</tr>
</table>

<div class=subtitle><?=readLanguage(pages,settings_email)?></div>
<table class=data_table>
<tr>
	<td class=title><?=readLanguage(pages,settings_email_sender_name)?>:</td>
	<td>
		<input type=text name=mail_from_name value="<?=$system_settings["mail_from_name"]?>">
	</td>
</tr>
<tr>
	<td class=title><?=readLanguage(pages,settings_email_sender_email)?>:</td>
	<td>
		<input type=text name=mail_from value="<?=$system_settings["mail_from"]?>">
	</td>
</tr>
</table>
</div>

<!-- Push Notifications -->
<div class=tab-pane id=settings_push>
<div class=subtitle><?=readLanguage(pages,settings_firebase)?></div>
<table class=data_table>
<tr>
	<td class=title>Project ID:</td>
	<td><input type=text name=firebase_project_id value="<?=$system_settings["firebase_project_id"]?>"></td>
</tr>
<tr>
	<td class=title>Project Number:</td>
	<td><input type=text name=firebase_project_number value="<?=$system_settings["firebase_project_number"]?>"></td>
</tr>
<tr>
	<td class=title>Application ID:</td>
	<td><input type=text name=firebase_app_id value="<?=$system_settings["firebase_app_id"]?>"></td>
</tr>
<tr>
	<td class=title>Application API Key:</td>
	<td><input type=text name=firebase_app_api_key value="<?=$system_settings["firebase_app_api_key"]?>"></td>
</tr>
<tr>
	<td class=title>Server Key:</td>
	<td><input type=text name=firebase_server_key value="<?=$system_settings["firebase_server_key"]?>"></td>
</tr>
</table>
</div>

<!-- SMS Settings -->
<div class=tab-pane id=settings_sms>
<table class=data_table>
<tr>
	<td class=title><?=readLanguage(inputs,url)?>:</td>
	<td><?=$system_settings["sms_post_url"]?></td>
</tr>
<tr>
	<td class=title><?=readLanguage(pages,user_username)?>:</td>
	<td><input type=text name=sms_username value="<?=$system_settings["sms_username"]?>"></td>
</tr>
<tr>
	<td class=title><?=readLanguage(pages,user_password)?>:</td>
	<td><input type=text name=sms_password value="<?=$system_settings["sms_password"]?>"></td>
</tr>
<tr>
	<td class=title><?=readLanguage(pages,settings_sender_id)?>:</td>
	<td>
		<input type=text name=sms_sender_id value="<?=$system_settings["sms_sender_id"]?>">
	</td>
</tr>
</table>
</div>

<!-- Application Settings -->
<div class=tab-pane id=settings_application>
<table class=data_table>
<tr>
	<td class=title>Build Number:</td>
	<td><input type=text name=application_build value="<?=$system_settings["application_build"]?>"></td>
</tr>
<tr>
	<td class=title>Android Bundle ID:</td>
	<td><input type=text name=application_android_bundle value="<?=$system_settings["application_android_bundle"]?>"></td>
</tr>
<tr>
	<td class=title>iOS Bundle ID:</td>
	<td><input type=text name=application_ios_bundle value="<?=$system_settings["application_ios_bundle"]?>"></td>
</tr>
</table>
</div>

<!-- Custom Code -->
<div class=tab-pane id=settings_code>
<table class=data_table>
<tr>
	<td class=title>CSS:</td>
	<td><textarea class=code name=custom_javascript><?=$system_settings["custom_css"]?></textarea></td>
</tr>
<tr>
	<td class=title>Javascript:</td>
	<td><textarea class=code name=custom_javascript><?=$system_settings["custom_javascript"]?></textarea></td>
</tr>
</table>
<script>
$(".code").keydown(function(e){
	if (e.keyCode === 9){
		var start = this.selectionStart;
		var end = this.selectionEnd;
		var value = $(this).val();
		$(this).val(value.substring(0, start) + "\t" + value.substring(end));
		this.selectionStart = this.selectionEnd = start + 1;
		e.preventDefault();
	}
});
</script>
</div>

</div><div class=submit_container><input type=button class=submit value="<?=readLanguage(records,update)?>"></div>
</form>

<? include "_footer.php"; ?>