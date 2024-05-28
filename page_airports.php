<? include "system/_handler.php";

setCurrentPageRedirect();
$mysqltable = "system_database_airports";
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
	
	//Rating
	$rating = ratingCalculate(null, $page_data["iata"], null);
	if ($rating["total"]){
		$section_rating["value"] = $rating["airport"];
		$section_rating["total"] = $rating["total"];
	}
	
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
			$block["subtitle"] = getData("system_database_countries", "code", $entry["country"], $suffix . "name");
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
			<? $country = getData("system_database_countries", "code", $page_data["country"]);
			$region = getID($page_data["region"], "system_database_regions"); ?>
			<div class=custom_info_card>
				<div><small><?=readLanguage(accounts,country)?></small><br><a href="countries/<?=$country[$suffix . "slug"]?>/"><?=$country[$suffix . "name"]?></a></div>
				<div><small><?=readLanguage(pages,city)?></small><br><a href="regions/<?=$region[$suffix . "slug"]?>/"><?=$region[$suffix . "name"]?></a></div>
				<div><small><?=readLanguage(pages,code)?></small><br><?=strtoupper($page_data["iata"])?></div>
				<? if ($page_data["website"]){ ?><div><small><?=readLanguage(accounts,website)?></small><br><a href="<?=$page_data["website"]?>" target=_blank><?=$page_data["website"]?></a></div><? } ?>
			</div>
		</div>
	</div>
	
	<!-- Booking -->
	<div class="margin-bottom-20 margin-bottom-progressive">
		<h2 class="page_subtitle large margin-bottom"><?=readLanguage(booking,book_now_to)?> <?=$section_title?></h2>
		<div class=inline_search>
		<? $search["to"] = $page_data["iata"];
		include "modules/search.php"; ?>
		</div>
	</div>
	
	<!-- Expressions -->
	<div class="margin-bottom-20 margin-bottom-progressive">
		<h2 class=page_subtitle><?=readLanguage(booking,special_trips_from)?><?=$section_title?></h2>
		<? $origin_type = "airport";
		$destination_type = "country";
		$origin = $page_data["iata"];
		include "modules/expressions.php"; ?>
	</div>
</div>

<!-- End Condition -->
<? } ?>

<? include "website/section_footer.php";
include "system/footer.php"; ?>