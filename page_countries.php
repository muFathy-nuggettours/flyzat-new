<? include "system/_handler.php";

setCurrentPageRedirect();
$mysqltable = "system_database_countries";
$canonical = $get["id"];
$publish = 1;

//Section Information
$section_information = mysqlFetch(mysqlQuery("SELECT * FROM " . $suffix . "website_pages WHERE page='" . basename($_SERVER["SCRIPT_FILENAME"], ".php") . "'"));
if ($section_information["hidden"]){ brokenLink(); }
$section_header = $section_information["section_header"];
$section_footer = $section_information["module_footer"];
$section_layout = $section_information["layout"];
$section_header_image = ($section_information["header_image"] ? "uploads/database/" . $section_information["header_image"] : null);
$section_cover_image = ($section_information["cover_image"] ? "uploads/database/" . $section_information["cover_image"] : null);
	
//========= Home Page =========
if (!$canonical){
	$section_title = $section_information["title"];
	$section_description = $section_information["description"];

	$breadcrumbs = array();
	array_push($breadcrumbs,"<li><a href='.'>" . readLanguage(general,home) . "</a></li>");
	array_push($breadcrumbs,"<li>" . $section_title . "</li>");
	
//========= Content =========
} else {
	$result = mysqlQuery("SELECT * FROM $mysqltable WHERE FIND_IN_SET('$canonical', slugs) ORDER BY priority DESC LIMIT 0,1");
	if (!mysqlNum($result)){ brokenLink(); }
	$page_data = mysqlFetch($result);
	
	$section_prefix = $section_information["title"];
	$section_title = $page_data[$suffix . "name"];
	$section_header_image = ($page_data["header_image"] ? "uploads/database/" . $page_data["header_image"] : $section_header_image);
	$section_cover_image = ($page_data["cover_image"] ? "uploads/database/" . $page_data["cover_image"] : $section_cover_image);
	
	$breadcrumbs = array();
	array_push($breadcrumbs,"<li><a href='.'>" . readLanguage(general,home) . "</a></li>");
	array_push($breadcrumbs,"<li><a href='" . $section_information["canonical"] . "/'>" . $section_information["title"] . "</a></li>");
	array_push($breadcrumbs,"<li>" . $section_title . "</li>");
}

include "system/header.php";
include "website/section_header.php"; ?>

<!-- Home Page -->
<? if (!$canonical){ ?>
<? $total_records = mysqlNum(mysqlQuery("SELECT * FROM $mysqltable WHERE publish=$publish ORDER BY priority DESC"));
$pagination = paginateRecords(15, $total_records, $section_information["canonical"] . "/");
$result = mysqlQuery("SELECT * FROM $mysqltable WHERE publish=$publish ORDER BY priority DESC LIMIT " . $pagination["min"] . "," . $pagination["max"]);
if (!mysqlNum($result)){
	print noContent();
} else { ?>
	<div class="row grid-container-15">
	<? while ($entry=mysqlFetch($result)){ ?>
		<div class="col-md-4 col-sm-three col-xs-10 grid-item">
			<? $block["title"] = $entry[$suffix . "name"];
			$block["cover"] = ($entry["cover_image"] ? "uploads/database/" . $entry["cover_image"] : "uploads/_website/" . $website_information["cover_image"]);
			$block["url"] = $section_information["canonical"] . "/" . $entry[$suffix . "slug"] . "/";
			include "blocks/basic-1.php"; ?>
		</div>
	<? } ?>
	</div>
<? } ?>
<?=$pagination["object"]?>

<!-- Content Page -->
<? } else { ?>
<div class=seo_content>
	<!-- Content -->
	<? $content = $page_data[$suffix . "content"]; ?>
	<div class="margin-bottom-20 margin-bottom-progressive">
		<div class=page_container>
			<? if ($content){ ?>
				<div class=margin-bottom><?=htmlContent($content)?></div>
			<? } ?>
			<div class=custom_info_card>
				<div><small><?=readLanguage(pages,country_code)?></small><br><?=strtoupper($page_data["code"])?></div>
				<div><small><?=readLanguage(pages,continent)?></small><br><?=$page_data["continent"]?></div>
				<div><small><?=readLanguage(pages,phone_code)?></small><br>+<?=$page_data["phone_code"]?></div>
				<div><small><?=readLanguage(currencies,currency)?></small><br><?=$page_data[$suffix . "currency_name"]?></div>
				<div><small><?=readLanguage(currencies,currency_code)?></small><br><?=$page_data["currency_code"]?></div>
				<div><small><?=readLanguage(currencies,currency_symbol)?></small><br><?=$page_data["currency_symbol"]?></div>
			</div>
		</div>
	</div>
	
	<!-- Booking -->
	<div class="margin-bottom-20 margin-bottom-progressive">
		<h2 class="page_subtitle large margin-bottom"><?=readLanguage(booking,book_now_to)?> <?=$section_title?></h2>
		<div class=inline_search>
		<? $search["to"] = mysqlFetch(mysqlQuery("SELECT iata FROM system_database_airports WHERE country='" . $page_data["code"] . "' ORDER BY popularity DESC, priority DESC LIMIT 0,1"))["iata"];
		include "modules/search.php"; ?>
		</div>
	</div>
	
	<!-- Regions -->
	<? $result = mysqlQuery("SELECT * FROM system_database_regions WHERE publish=$publish AND country='" . $page_data["code"] . "'"); ?>
	<? if (mysqlNum($result)){ ?>
	<div class="margin-bottom-20 margin-bottom-progressive">
		<h2 class=page_subtitle><?=readLanguage(filter,popular_cities_at)?> <?=$section_title?></h2>
		<div class="row grid-container">
			<? while ($entry = mysqlFetch($result)){ ?>
			<div class="col-md-4 col-sm-three col-xs-10 grid-item">
				<? $block["title"] = $entry[$suffix . "name"];
				$block["cover"] = ($entry["cover_image"] ? "uploads/database/" . $entry["cover_image"] : "uploads/_website/" . $website_information["cover_image"]);
				$block["url"] = "regions/" . $entry[$suffix . "slug"] . "/";
				include "blocks/basic-1.php"; ?>
			</div>
			<? } ?>
		</div>
	</div>
	<? } ?>
	
	<!-- Airports -->
	<? $result = mysqlQuery("SELECT * FROM system_database_airports WHERE publish=$publish AND country='" . $page_data["code"] . "'"); ?>
	<? if (mysqlNum($result)){ ?>
	<div class="margin-bottom-20 margin-bottom-progressive">
		<h2 class=page_subtitle><?=readLanguage(filter,popular_airports_at)?> <?=$section_title?></h2>
		<div class="row grid-container">
			<? while ($entry = mysqlFetch($result)){ ?>
			<div class="col-md-4 col-sm-three  col-xs-10 grid-item">
				<? $block["title"] = $entry[$suffix . "name"];
				$block["cover"] = ($entry["cover_image"] ? "uploads/database/" . $entry["cover_image"] : "uploads/_website/" . $website_information["cover_image"]);
				$block["url"] = "airports/" . $entry[$suffix . "slug"] . "/";
				include "blocks/basic-1.php"; ?>
			</div>
			<? } ?>
		</div>
	</div>
	<? } ?>
	
	<!-- Airlines -->
	<? $result = mysqlQuery("SELECT * FROM system_database_airlines WHERE publish=$publish AND country='" . $page_data["code"] . "'"); ?>
	<? if (mysqlNum($result)){ ?>
	<div class="margin-bottom-20 margin-bottom-progressive">
		<h2 class=page_subtitle><?=readLanguage(filter,airlines_at)?> <?=$section_title?></h2>
		<div class="row grid-container">
			<? while ($entry = mysqlFetch($result)){ ?>
			<div class="col-md-4 col-sm-three col-xs-10 grid-item">
				<? $block["title"] = $entry[$suffix . "name"];
				$block["cover"] = ($entry["cover_image"] ? "uploads/database/" . $entry["cover_image"] : (file_exists("uploads/airlines/" . $entry["iata"] . ".png") ? "uploads/airlines/" . $entry["iata"] . ".png" : "uploads/_website/" . $website_information["cover_image"]));
				$block["url"] = "airlines/" . $entry[$suffix . "slug"] . "/";
				include "blocks/logo-2.php"; ?>
			</div>
			<? } ?>
		</div>
	</div>
	<? } ?>
	
	<!-- Expressions -->
	<div class="margin-bottom-20 margin-bottom-progressive">
		<h2 class=page_subtitle><?=readLanguage(booking,special_trips_from)?> <?=$section_title?></h2>
		<? $target="countries"; $origin=$page_data["code"];
		include "modules/expressions.php"; ?>
	</div>
</div>

<!-- End Condition -->
<? } ?>

<? include "website/section_footer.php";
include "system/footer.php"; ?>