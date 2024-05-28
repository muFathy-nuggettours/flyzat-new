<link href="modules/welcome.css?v=<?=$system_settings["system_version"]?>" rel="stylesheet">

<div class=welcome_module><div class="row grid-container">
	<div class="col-md-10 col-sm-10 grid-item">
		<div class=user_welcome>
		<img src="../uploads/_website/<?=$website_information["website_logo_negative"]?>">
		<div><?=readLanguage(general,welcome)?><br><b><?=$logged_user["name"]?></b></div>
		</div>
	</div>
	<div class="col-md-10 col-sm-10 grid-item">
		<div class=date_container>
			<b><?=readLanguage(general,current_date)?></b>
			<div class=date><?=dateLanguage("l, d M Y",time())?></div>
			<b><?=readLanguage(general,current_time)?></b>
			<div class=time id=current_time style="direction:ltr"></div>
			<script>
			$("#current_time").html(new Date().toLocaleTimeString());
			$(document).ready(function(){
				setInterval(function(){
					$("#current_time").html(new Date().toLocaleTimeString());
				}, 1000);
			});</script>
		</div>
	</div>
</div></div>