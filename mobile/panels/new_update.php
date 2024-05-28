<div class=overlay_panel id=new_update data-title="<?=readLanguage(mobile,new_update)?>" data-mandatory=true data-header-buttons=""  data-footer-buttons="exit">
	<div class="content overlay">
		<img src="uploads/_website/<?=$website_information["website_logo"]?>">
		<span class="fas fa-rocket"></span>
		<h3><?=readLanguage(mobile,new_update)?></h3>
		<div><?=readLanguage(mobile,new_update_message)?></div>
		<input type=button class="btn btn-primary btn-shadow" onclick="sendApplicationMessage('Download-Update')" value="<?=readLanguage(mobile,download_update)?>">
		<a onclick="exitApplication()"><?=readLanguage(mobile,exit_application)?></a>
	</div>
</div>