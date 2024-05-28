<div class=overlay_panel id=no_connection data-title="<?=readLanguage(mobile,connection_error)?>" data-mandatory=true data-header-buttons=""  data-footer-buttons="exit">
	<div class="content overlay">
		<img src="uploads/_website/<?=$website_information["website_logo"]?>">
		<span class="fas fa-wifi"></span>
		<h3><?=readLanguage(mobile,connection_error)?></h3>
		<div><?=readLanguage(mobile,connection_error_message)?></div>
		<input type=button class="btn btn-primary btn-shadow" onclick="retryConnection()" value="<?=readLanguage(mobile,retry)?>">
		<a onclick="exitApplication()"><?=readLanguage(mobile,exit_application)?></a>
	</div>
</div>