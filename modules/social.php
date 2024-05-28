<? $social_media = json_decode($website_information["social_media"], true);
if (count($social_media)){ ?>
	<div class=module_social>
	<? foreach ($social_media AS $key=>$value){
		print "<a href='" . $value["url"] . "' style='background: #" . $data_social_media[$value["platform"]][2] . "' target=_blank><span class='" . $data_social_media[$value["platform"]][1] . "'></span></a>";
	} ?>
	</div>
<? } ?>
