<div class=overlay_panel id=settings data-title="<?=readLanguage(mobile,settings)?>" data-header-buttons="back"  data-footer-buttons="exit">
	<form id=settings_form>
	<div class=settings_form>
		<div class=settings_item>
			<span><?=readLanguage(mobile,settings_language)?></span>
			<div>
				<select name=language id=language>
				<? foreach ($supported_languages as $value){
					print "<option value='$value'>" . languageOptions($value)["name"] . "</option>";
				} ?>
				</select>	
			</div>
		</div>
		<div class=settings_item>
			<span><?=readLanguage(mobile,settings_notifications)?></span>
			<div>
				<div class=switch><label><?=readLanguage(mobile,settings_notifications_off)?><input type=checkbox name=notifications id=notifications value=1 <?=($entry["switch"] ? "checked" : "")?>><span class=lever></span><?=readLanguage(mobile,settings_notifications_on)?></label></div>	
			</div>
		</div>
	</div>
	<div class=submit_container><button type=button class=submit onclick="saveSettings()"><?=readLanguage(mobile,settings_save)?></button></div>
	</form>
</div>

<script>
//Save Settings
function saveSettings(){
	settings = $("#settings_form").serializeObject();
	
	sendApplicationMessage("Save-Data", {
		key: "settings",
		value: JSON.stringify(settings)
	});
	
	var current_language = "<?=$website_language?>";
	if (current_language != settings.language){
		sendApplicationMessage("Restart-Application");
	} else {
		hideOverlayPanel("settings");
	}
}

//Load Settings
function onDataReceived_settings(settings){
	settings = JSON.parse(settings);
	
	//Language
	if (settings.hasOwnProperty("language")){
		setSelectValue("#language", settings.language);
	}
	
	//Notifications
	if (settings.hasOwnProperty("notifications")){
		$("#notifications").prop("checked", true);
	}
}

$(document).ready(function(){
	sendApplicationMessage("Get-Data", "settings");
});
</script>