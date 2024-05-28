<? include "system/_handler.php";

$mysqltable = $suffix . "website_seo";
$base_name = basename($_SERVER["SCRIPT_FILENAME"], ".php");
checkPermissions($base_name);

if ($get["token"]){
	switch ($get["origin_type"]){
		case 1:
			$origin_code = getData("system_database_countries", "code", $get["origin_country"], "code");
			$origin_slugs = getData("system_database_countries", "code", $get["origin_country"], "slugs");
		break;
		case 2:
			$origin_code = getData("system_database_regions", "code", $get["origin_region"], "code");
			$origin_slugs = getData("system_database_regions", "code", $get["origin_region"], "slugs");
		break;
		case 3:
			$origin_code = getData("system_database_airports", "iata", $get["origin_airport"], "iata");
			$origin_slugs = getData("system_database_airports", "iata", $get["origin_airport"], "slugs");
		break;
	}
	$origin_slugs = explode(",", $origin_slugs);
	
	switch ($get["destination_type"]){
		case 1:
			$destination_code = getData("system_database_countries", "code", $get["destination_country"], "code");
			$destination_slugs = getData("system_database_countries", "code", $get["destination_country"], "slugs");
		break;
		case 2:
			$destination_code = getData("system_database_regions", "code", $get["destination_region"], "code");
			$destination_slugs = getData("system_database_regions", "code", $get["destination_region"], "slugs");
		break;
		case 3:
			$destination_code = getData("system_database_airports", "iata", $get["destination_airport"], "iata");
			$destination_slugs = getData("system_database_airports", "iata", $get["destination_airport"], "slugs");
		break;
	}
	$destination_slugs = explode(",", $destination_slugs);
	
	$routes = array();
	$route_result = mysqlQuery("SELECT route FROM " . $suffix . "website_seo");
	while ($route = mysqlFetch($route_result)){
		array_push($routes, $route["route"]);
	}

	$urls = array();
	foreach ($routes AS $route){
		foreach ($origin_slugs AS $origin){
			foreach ($destination_slugs AS $destination){
				$url = createCanonical(str_replace(["{1}", "{2}"], [$origin, $destination], $route));
				array_push($urls, $url);
			}
		}
	}

	if (count($urls) > 1){
		$success = "تم عرض روابط محركات البحث بنجاح";
		if ($post["token"]){
			$exists = mysqlFetch(mysqlQuery("SELECT id FROM " . $suffix . "website_seo_pages WHERE origin_type=" . $get["origin_type"] . " AND origin_code='" . $origin_code . "' AND destination_type=" . $get["destination_type"] . " AND destination_code='" . $destination_code . "'"));
			if (!$exists){
				$query = "INSERT INTO " . $suffix . "website_seo_pages (
					origin_type,
					origin_code,
					destination_type,
					destination_code,
					content,
					gallery
				) VALUES (
					'" . $get["origin_type"] . "',
					'" . $origin_code . "',
					'" . $get["destination_type"] . "',
					'" . $destination_code . "',
					'" . $post["content"] . "',
					'" . $post["gallery"] . "'
				)";
				mysqlQuery($query);
				$success = "تم إضافة بيانات السجل بنجاح";
			} else {
				$query = "UPDATE " . $suffix . "website_seo_pages SET
					content='" . $post["content"] . "',
					gallery='" . $post["gallery"] . "'
				WHERE id=" . $exists["id"];
				mysqlQuery($query);
				$success = "تم تحديث بيانات السجل بنجاح";
			}
		}
		$entry = mysqlFetch(mysqlQuery("SELECT * FROM " . $suffix . "website_seo_pages WHERE origin_type=" . $get["origin_type"] . " AND origin_code='" . $origin_code . "' AND destination_type=" . $get["origin_type"] . " AND destination_code='" . $destination_code . "'"));
	} else {
		$error = "لا يوجد روابط صالحة للعرض ";
	}
}

if ($success){ $message = "<div class='alert alert-success'>" . $success . "</div>"; }
if ($error){ $message = "<div class='alert alert-danger'>" . $error . "</div>"; }

include "_header.php" ?>

<style>
.urls a {
	display: block;
	font-size: 12px;
	line-height: 2;
}
</style>

<div class=title><?=getPageTitle($base_name)?></div>
<?=$message?>

<? if ($urls){ ?>
<div class=subtitle>الروابط المتاحة</div>
<div class="page_container urls margin-bottom">
	<? foreach ($urls AS $url){ ?>
	<a href="<?=$base_url?>s/<?=$url?>/" target=_blank><?=$url?></a>
	<? } ?>
</div>

<div class=subtitle>إدارة المحتوي</div>
<form method=post enctype="multipart/form-data" action="<?=$action?>">
<input type=hidden name=token value="<?=$token?>">

<table class=data_table>
<tr>
	<td class=title><?=readLanguage(inputs,content)?>:</td>
	<td colspan=3><textarea class=contentEditor style="height:400px" name=content id=content><?=$entry["content"]?></textarea></td>
</tr>
<tr>
	<td class=title><?=readLanguage(inputs,gallery)?>:</td>
	<td colspan=3 data-token="<?=$token?>" data-attachments=gallery data-upload-path="../uploads/destinations/">
		<div class=attachment-button>
			<input type=hidden name=gallery value="<?=$entry["gallery"]?>">
			<label class="btn btn-primary btn-lrg btn-upload"><?=readLanguage(inputs,gallery_insert)?><input type=file id=gallery accept="image/*" multiple></label>
			<div><i class="fas fa-spinner fa-spin"></i><?=readLanguage(inputs,uploading)?></div>
		</div>
		<ul sortable class=attachments-list></ul><div style="clear:both"></div>
		<? if ($entry["gallery"]){ ?>
		<script>
		var jsonArray = jQuery.parseJSON(JSON.stringify(<?=$entry["gallery"]?>));
		jsonArray.forEach(function(entry){ attachmentsLoadFile(entry,"gallery"); });	
		</script>
		<? } ?>
	</td>
</tr>
</table>
<div class=submit_container><input type=button class=submit value="تحديث"></div>
</form>

<? } else { ?>
<form method=get enctype="multipart/form-data" action="<?=$action?>">
<input type=hidden name=token value="<?=$token?>">

<div class=subtitle>محطة الإقلاع</div>
<div class=data_table_container><table class=data_table>
<tr>
	<td class=title>مكان الإقلاع:</td>
	<td>
		<div class=radio_container id=origin_type>
			<? foreach ($data_locations_types AS $key=>$value){ ?>
			<label><input name=origin_type type=radio value="<?=$key?>"><span><?=$value?></span></label>
			<? } ?>
		</div>
		<script>
		$("#origin_type input[type=radio]").on("change", function(){
			toggleVisibility($(this));
		});
		$(document).ready(function(){
			$("#origin_type").find("[value='1']").prop("checked", true).trigger("change");
		});
		</script>
	</td>
</tr>
<tr visibility-control=origin_type visibility-value="1">
	<td class=title>الدولة: <i class=requ></i></td>
	<td>
		<select name=origin_country id=origin_country>
		<? $country_result = mysqlQuery("SELECT code, phone_code, en_name, ar_name FROM system_database_countries ORDER BY code ASC");
		while ($country_entry = mysqlFetch($country_result)){
			print "<option value='" . $country_entry["code"] . "' data-name='" . $country_entry[$panel_language . "_name"] . "'>" . $country_entry[$panel_language . "_name"] . "</option>";
		} ?>
		</select>
		<script>
		//Set default value
		$(document).ready(function(){
			setSelectValue("#origin_country", "eg");
			$("#origin_country").trigger("change");
		});
		
		//Initialize Select2
		$("#origin_country").select2({
			dropdownAutoWidth: true,
			templateResult: function(state){
				var element = $(state.element);
				return $("<div class='d-flex align-items-center'><img src='../images/countries/" + $(element).val() + ".gif'>&nbsp;" + $(element).attr("data-name") + "</div>");
			},
			templateSelection: function(state){
				var element = $(state.element);
				return $("<div class='d-flex align-items-center'><img src='../images/countries/" + $(element).val() + ".gif'>&nbsp;" + $(element).attr("data-name") + "</div>");
			}
		});
		</script>
	</td>
</tr>
<tr visibility-control=origin_type visibility-value="2">
	<td class=title>المدينة: <i class=requ></i></td>
	<td>
		<? $input = "origin_region"; $value = null; $conditions = null; $mandatory = true; $removable = true; ?>
		<? include "includes/select_region.php"; ?>
	</td>
</tr>
<tr visibility-control=origin_type visibility-value="3">
	<td class=title>المطار: <i class=requ></i></td>
	<td>
		<? $input = "origin_airport"; $value = null; $conditions = null; $mandatory = true; $removable = true; ?>
		<? include "includes/select_airport.php"; ?>
	</td>
</tr>
</table></div>

<div class=subtitle>محطة الوجهة</div>
<div class=data_table_container><table class=data_table>
<tr>
	<td class=title>مكان الوجهة:</td>
	<td>
		<div class=radio_container id=destination_type>
			<? foreach ($data_locations_types AS $key=>$value){ ?>
			<label><input name=destination_type type=radio value="<?=$key?>"><span><?=$value?></span></label>
			<? } ?>
		</div>
		<script>
		$("#destination_type input[type=radio]").on("change", function(){
			toggleVisibility($(this));
		});
		$(document).ready(function(){
			$("#destination_type").find("[value='1']").prop("checked", true).trigger("change");
		});
		</script>
	</td>
</tr>
<tr visibility-control=destination_type visibility-value="1">
	<td class=title>الدولة: <i class=requ></i></td>
	<td>
		<select name=destination_country id=destination_country>
		<? $country_result = mysqlQuery("SELECT code, phone_code, en_name, ar_name FROM system_database_countries ORDER BY code ASC");
		while ($country_entry = mysqlFetch($country_result)){
			print "<option value='" . $country_entry["code"] . "' data-name='" . $country_entry[$panel_language . "_name"] . "'>" . $country_entry[$panel_language . "_name"] . "</option>";
		} ?>
		</select>
		<script>
		//Set default value
		$(document).ready(function(){
			setSelectValue("#destination_country", "eg");
			$("#destination_country").trigger("change");
		});
		
		//Initialize Select2
		$("#destination_country").select2({
			dropdownAutoWidth: true,
			templateResult: function(state){
				var element = $(state.element);
				return $("<div class='d-flex align-items-center'><img src='../images/countries/" + $(element).val() + ".gif'>&nbsp;" + $(element).attr("data-name") + "</div>");
			},
			templateSelection: function(state){
				var element = $(state.element);
				return $("<div class='d-flex align-items-center'><img src='../images/countries/" + $(element).val() + ".gif'>&nbsp;" + $(element).attr("data-name") + "</div>");
			}
		});
		</script>
	</td>
</tr>
<tr visibility-control=destination_type visibility-value="2">
	<td class=title>المدينة: <i class=requ></i></td>
	<td>
		<? $input = "destination_region"; $value = null; $conditions = null; $mandatory = true; $removable = true; ?>
		<? include "includes/select_region.php"; ?>
	</td>
</tr>
<tr visibility-control=destination_type visibility-value="3">
	<td class=title>المطار: <i class=requ></i></td>
	<td>
		<? $input = "destination_airport"; $value = null; $conditions = null; $mandatory = true; $removable = true; ?>
		<? include "includes/select_airport.php"; ?>
	</td>
</tr>
</table></div>

<div class=submit_container><input type=button class=submit value="عرض الروابط"></div>
</form>
<? } ?>

<? include "_footer.php" ?>