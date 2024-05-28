<? include "system/_handler.php";

$mysqltable = $suffix . "website_information";
$base_name = basename($_SERVER["SCRIPT_FILENAME"], ".php");
checkPermissions($base_name);

//==== EDIT Record ====
if ($post["token"]){
	$entry = fetchData($mysqltable);
	
	mysqlQuery("UPDATE $mysqltable SET content = CASE
		WHEN title='website_name' THEN '" . $post["website_name"] . "'
		WHEN title='short_description' THEN '" . $post["short_description"] . "'
		WHEN title='landline' THEN '" . $post["landline"] . "'
		WHEN title='mobile' THEN '" . $post["mobile"] . "'
		WHEN title='address' THEN '" . $post["address"] . "'
		WHEN title='email' THEN '" . $post["email"] . "'
		WHEN title='google_map_x' THEN '" . $post["google_map_x"] . "'
		WHEN title='google_map_y' THEN '" . $post["google_map_y"] . "'
		WHEN title='primary_email' THEN '" . $post["primary_email"] . "'
		WHEN title='primary_number' THEN '" . $post["primary_number"] . "'
		WHEN title='whatsapp' THEN '" . $post["whatsapp"] . "'
		WHEN title='social_media' THEN '" . $post["social_media"] . "'
		WHEN title='website_logo' THEN '" . imgUpload($_FILES[website_logo], "../uploads/_website/", $entry["website_logo"], "logo_") . "'
		WHEN title='website_logo_negative' THEN '" . imgUpload($_FILES[website_logo_negative], "../uploads/_website/", $entry["website_logo_negative"], "logo_negative_") . "'
		WHEN title='website_icon' THEN '" .imgUpload($_FILES[website_icon], "../uploads/_website/", $entry["website_icon"], "icon_") . "'
		WHEN title='header_image' THEN '" . imgUpload($_FILES[header_image], "../uploads/_website/", $entry["header_image"], "header_") . "'
		WHEN title='cover_image' THEN '" . imgUpload($_FILES[cover_image], "../uploads/_website/", $entry["cover_image"], "cover_") . "'
		ELSE content
	END");	
	$success = readLanguage(records,updated);
}

//Read and Set Operation
if ($success){ $message = "<div class='alert alert-success'>$success</div>"; }

//Reload Website Data
$entry = fetchData($mysqltable);

include "_header.php"; ?>

<script src="../plugins/wizard.min.js?v=<?=$system_settings["system_version"]?>"></script>
<link href="../plugins/wizard.css?v=<?=$system_settings["system_version"]?>" rel="stylesheet">

<script src="../plugins/tagify.js?v=<?=$system_settings["system_version"]?>"></script>
<link href="../plugins/tagify.css?v=<?=$system_settings["system_version"]?>" rel="stylesheet">

<script src="https://maps.googleapis.com/maps/api/js?sensor=false<?=($system_settings["google_maps_key"] ? "&key=" . $system_settings["google_maps_key"] : "")?>"></script>
<script src="../plugins/location-picker.min.js?v=<?=$system_settings["system_version"]?>"></script>

<div class=title><?=getPageTitle($base_name)?></div>
<?=$message?>

<form method=post enctype="multipart/form-data" action="<?=$action?>">
<input type=hidden name=token value="<?=$token?>">

<!-- Basic Information -->
<div class=subtitle><?=readLanguage(pages,info_basic)?></div>
<table class=data_table>
<tr>
	<td class=title><?=readLanguage(pages,info_basic_name)?>: <i class=requ></i></td>
	<td colspan=3>
		<input type=text name=website_name value="<?=$entry["website_name"]?>" data-validation=required>
	</td>
</tr>
<tr>
	<td class=title><?=readLanguage(pages,info_basic_description)?>: <i class=requ></i></td>
	<td colspan=3>
		<textarea name=short_description data-validation=required><?=$entry["short_description"]?></textarea>
	</td>
</tr>
<tr>
	<td class=title><?=readLanguage(pages,info_basic_logo)?>: <i class=requ></i></td>
	<td>
		<table class=attachment><tr>
		<td>
			<input type=file name=website_logo id=website_logo accept="image/*" data-validation=mime data-validation-allowing="image/bmp,image/jpeg,image/png,image/gif">
			<div class=input_description><?=readLanguage(inputs,instructions_design)?></div>
		</td>
		<td width=100>
			<? $path = ($entry["website_logo"] ? "../uploads/_website/" . $entry["website_logo"] : "images/placeholder.png") ?>
			<a data-fancybox=images href="<?=$path?>"><img class=sample_img image-placeholder=website_logo src="<?=$path?>"></a>
		</td>
		</tr></table>
		<!-- Used Only For Upload -->
		<script>$(document).ready(function(){ bindImage("website_logo") })</script>
	</td>
	<td class=title><?=readLanguage(pages,info_basic_logo_negative)?>: <i class=requ></i></td>
	<td>
		<table class=attachment><tr>
		<td>
			<input type=file name=website_logo_negative id=website_logo_negative accept="image/*" data-validation=mime data-validation-allowing="image/bmp,image/jpeg,image/png,image/gif">
			<div class=input_description><?=readLanguage(inputs,instructions_design)?></div>
		</td>
		<td width=100>
			<? $path = ($entry["website_logo_negative"] ? "../uploads/_website/" . $entry["website_logo_negative"] : "images/placeholder.png") ?>
			<a data-fancybox=images href="<?=$path?>"><img class=sample_img image-placeholder=website_logo_negative src="<?=$path?>"></a>
		</td>
		</tr></table>
		<!-- Used Only For Upload -->
		<script>$(document).ready(function(){ bindImage("website_logo_negative") })</script>
	</td>
</tr>
<tr>
	<td class=title><?=readLanguage(pages,info_basic_cover)?>: <i class=requ></i></td>
	<td>
		<table class=attachment><tr>
		<td>
			<input type=file name=cover_image id=cover_image accept="image/*" data-validation=mime data-validation-allowing="image/bmp,image/jpeg,image/png,image/gif">
			<div class=input_description><?=readLanguage(inputs,instructions_design)?></div>
		</td>
		<td width=100>
			<? $path = ($entry["cover_image"] ? "../uploads/_website/" . $entry["cover_image"] : "images/placeholder.png") ?>
			<a data-fancybox=images href="<?=$path?>"><img class=sample_img image-placeholder=cover_image src="<?=$path?>"></a>
		</td>
		</tr></table>
		<!-- Used Only For Upload -->
		<script>$(document).ready(function(){ bindImage("cover_image") })</script>
	</td>
	<td class=title><?=readLanguage(pages,info_basic_header)?>: <i class=requ></i></td>
	<td>
		<table class=attachment><tr>
		<td>
			<input type=file name=header_image id=header_image accept="image/*" data-validation=mime data-validation-allowing="image/bmp,image/jpeg,image/png,image/gif">
			<div class=input_description><?=readLanguage(inputs,instructions_design)?></div>
		</td>
		<td width=100>
			<? $path = ($entry["header_image"] ? "../uploads/_website/" . $entry["header_image"] : "images/placeholder.png") ?>
			<a data-fancybox=images href="<?=$path?>"><img class=sample_img image-placeholder=header_image src="<?=$path?>"></a>
		</td>
		</tr></table>
		<!-- Used Only For Upload -->
		<script>$(document).ready(function(){ bindImage("header_image") })</script>
	</td>
</tr>
<tr>
	<td class=title><?=readLanguage(pages,info_basic_icon)?>: <i class=requ></i></td>
	<td colspan=3>
		<table class=attachment><tr>
		<td>
			<input type=file name=website_icon id=website_icon accept="image/*" data-validation=mime data-validation-allowing="image/bmp,image/jpeg,image/png,image/gif">
			<div class=input_description><?=readLanguage(inputs,instructions_design)?></div>
		</td>
		<td width=100>
			<? $path = ($entry["website_icon"] ? "../uploads/_website/" . $entry["website_icon"] : "images/placeholder.png") ?>
			<a data-fancybox=images href="<?=$path?>"><img class=sample_img image-placeholder=website_icon src="<?=$path?>"></a>
		</td>
		</tr></table>
		<!-- Used Only For Upload -->
		<script>$(document).ready(function(){ bindImage("website_icon") })</script>
	</td>
</tr>
</table>

<!-- Contact Information -->
<div class=subtitle><?=readLanguage(pages,info_contact)?></div>
<table class=data_table>
<tr>
	<td class=title><?=readLanguage(pages,info_contact_email)?>:</td>
	<td colspan=3>
		<input type=text name=email value="<?=$entry["email"]?>">
	</td>
</tr>
<tr>
	<td class=title><?=readLanguage(pages,info_contact_landline)?>:</td>
	<td>
		<textarea class=tagarea data-tags=landline data-separator="{NewLine}" data-class=tag-box-block name=landline placeholder="<?=readLanguage(plugins,tags_enter)?>"><?=$entry["landline"]?></textarea>
	</td>
	<td class=title><?=readLanguage(pages,info_contact_mobile)?>:</td>
	<td>
		<textarea class=tagarea data-tags=mobile data-separator="{NewLine}" data-class=tag-box-block name=mobile placeholder="<?=readLanguage(plugins,tags_enter)?>"><?=$entry["mobile"]?></textarea>
	</td>
</tr>
<tr>
	<td class=title><?=readLanguage(pages,info_contact_primary)?>:</td>
	<td>
		<input type=text name=primary_number value="<?=$entry["primary_number"]?>">
	</td>
	<td class=title><?=readLanguage(pages,info_contact_whatsapp)?>:</td>
	<td>
		<input type=text name=whatsapp value="<?=$entry["whatsapp"]?>">
	</td>
</tr>
<tr>
	<td class=title><?=readLanguage(pages,info_contact_address)?>:</td>
	<td colspan=3>
		<textarea name=address><?=$entry["address"]?></textarea>
	</td>
</tr>
<tr>
	<td class=title><?=readLanguage(pages,info_contact_coordinates)?>:</td>
	<td colspan=3>
		<div class=d-flex>
			<input type=text name=google_map_x value="<?=$entry["google_map_x"]?>" placeholder="<?=readLanguage(pages,info_contact_long)?>">&nbsp;&nbsp;
			<input type=text name=google_map_y value="<?=$entry["google_map_y"]?>" placeholder="<?=readLanguage(pages,info_contact_lat)?>">&nbsp;&nbsp;
			<button type=button class="btn btn-primary btn-sm" onclick="selectLocation()"><?=readLanguage(pages,info_contact_map)?></button>
		</div>
		
		<!-- Location Selection Template -->
		<div id=map-container class=d-none>
		<fieldset class=gllpLatlonPicker>
			<div class=gllpMap><?=readLanguage(pages,info_contact_coordinates)?></div>
			<ul class="inline_input compact">
				<li><span><p><?=readLanguage(pages,info_contact_long)?></p><input type=text class=gllpLatitude name=google_x data-default="<?=$entry["google_map_x"]?>"></span></li>
				<li><span><p><?=readLanguage(pages,info_contact_lat)?></p><input type=text class=gllpLongitude name=google_y data-default="<?=$entry["google_map_y"]?>"></span></li>
				<li class=d-none><input type=text class=gllpZoom value=15></li>
				<button type=button class="gllpUpdateButton btn btn-default"><i class="fas fa-search fa-fw"></i></button>
			</ul>
		</fieldset>
		</div>
	</td>
</tr>
<tr>
	<td class=title><?=readLanguage(pages,info_contact_social)?>:</td>
	<td colspan=3 data-multiple=social_media>
		<button type=button class="btn btn-primary btn-sm" onclick="multipleDataCreate('social_media')"><?=readLanguage(operations,insert)?></button>
		<input type=hidden name=social_media>
		<ul multiple-sortable>
			<li data-template>
				<div class=d-flex>
					<div class="grabbable grabbable_icon"><i class="fas fa-bars"></i></div>&nbsp;&nbsp;
					<select data-name=platform style="width:25%">
					<? foreach ($data_social_media AS $key=>$value){
						print "<option class=force-ltr value='$key'>" . $value[0] . "</option>";
					} ?>
					</select>&nbsp;&nbsp;
					<input type=text class=ltr-input data-name=url data-validation=required disabled>&nbsp;&nbsp;
					<a class="btn btn-danger btn-sm remove"><i class="fas fa-times"></i></a>
				</div>
			</li>
		</ul>
		<? if ($entry["social_media"]){ ?>
		<script>
		var jsonArray = <?=$entry["social_media"]?>;
		jsonArray.forEach(function(entry){ multipleDataCreate("social_media", entry); });
		</script>
		<? } ?>
	</td>
</tr>
</table>

<div class=submit_container><input type=button class=submit value="<?=readLanguage(records,update)?>"></div>
</form>

<script>
function selectLocation(){
	var targetX = $("[name=google_map_x]");
	var targetY = $("[name=google_map_y]");
	var dialog = bootbox.dialog({
		title: "<?=readLanguage(pages,info_contact_coordinates)?>",
		message: $("#map-container").html(),
		closeButton: true,
		buttons: {
				confirm: {
					label: "<?=readLanguage(plugins,message_confirm)?>",
					className: "btn-primary",
					callback: function(){
						targetX.val(dialog.find(".bootbox-body input[name=google_x]").val());
						targetY.val(dialog.find(".bootbox-body input[name=google_y]").val());
					}
				},
				cancel: {
					label: "<?=readLanguage(plugins,message_cancel)?>",
					className: "btn-default"
				}
			}
	});
	dialog.init(function(){
		var defaultX = (targetX.val() ? targetX.val() : dialog.find(".bootbox-body input[name=google_x]").attr("data-default"));
		var defaultY = (targetY.val() ? targetY.val() : dialog.find(".bootbox-body input[name=google_y]").attr("data-default"));
		dialog.find(".bootbox-body input[name=google_x]").val(defaultX);
		dialog.find(".bootbox-body input[name=google_y]").val(defaultY);
		$.gMapsLatLonPickerNoAutoInit = 1;
		dialog.find(".bootbox-body .gllpLatlonPicker").each(function(){
			$obj = $(document).gMapsLatLonPicker();
			$obj.params.defLat = defaultX;
			$obj.params.defLng = defaultY;
			$obj.init($(this));
		});
	});
}
</script>

<? include "_footer.php"; ?>