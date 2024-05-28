<? include "system/_handler.php";

setCurrentPageRedirect();
$mysqltable = $suffix . "website_destinations";
$canonical = $get["id"];

//Section Information
$section_information = mysqlFetch(mysqlQuery("SELECT * FROM " . $suffix . "website_pages WHERE page='" . basename($_SERVER["SCRIPT_FILENAME"], ".php") . "'"));
if ($section_information["hidden"]){ brokenLink(); }
$section_header = $section_information["section_header"];
$section_footer = $section_information["module_footer"];
$section_layout = $section_information["layout"];
$section_header_image = ($section_information["header_image"] ? "uploads/destinations/" . $section_information["header_image"] : null);
$section_cover_image = ($section_information["cover_image"] ? "uploads/destinations/" . $section_information["cover_image"] : null);
	
//========= Home Page =========
if (!$canonical){
	$section_title = $section_information["title"];
	$section_description = $section_information["description"];

	$breadcrumbs = array();
	array_push($breadcrumbs,"<li><a href='.'>" . readLanguage(general,home) . "</a></li>");
	array_push($breadcrumbs,"<li>" . $section_title . "</li>");
	
//========= Content =========
} else {
	$result = mysqlQuery("SELECT * FROM $mysqltable WHERE canonical='" . $canonical . "'");
	if (!mysqlNum($result)){ brokenLink(); }
	$page_data = mysqlFetch($result);
	
	$section_prefix = $section_information["title"];
	$section_title = $page_data["title"];
	$section_description = $page_data["description"];
	$section_header_image = ($page_data["header_image"] ? "uploads/destinations/" . $page_data["header_image"] : $section_header_image);
	$section_cover_image = ($page_data["cover_image"] ? "uploads/destinations/" . $page_data["cover_image"] : $section_cover_image);
	
	$breadcrumbs = array();
	array_push($breadcrumbs,"<li><a href='.'>" . readLanguage(general,home) . "</a></li>");
	array_push($breadcrumbs,"<li><a href='" . $section_information["canonical"] . "/'>" . $section_information["title"] . "</a></li>");
	array_push($breadcrumbs,"<li>" . $section_title . "</li>");
}

include "system/header.php";
include "website/section_header.php"; ?>

<!-- Home Page -->
<? if (!$canonical){ ?>
<? $total_records = mysqlNum(mysqlQuery("SELECT * FROM $mysqltable ORDER BY priority DESC"));
$pagination = paginateRecords(12, $total_records, $section_information["canonical"] . "/");
$result = mysqlQuery("SELECT * FROM $mysqltable ORDER BY priority DESC LIMIT " . $pagination["min"] . "," . $pagination["max"]);
if (!mysqlNum($result)){
	print noContent();
} else { ?>
	<div class="row grid-container-15">
	<? while ($entry=mysqlFetch($result)){ ?>
		<div class="col-md-5 col-sm-10 grid-item">
			<? $block["title"] = $entry["title"];
			$block["subtitle"] = getData("system_database_countries", "code", getData("system_database_airports", "iata", $entry["airport"], "country"), $suffix . "name");
			$block["cover"] = ($entry["cover_image"] ? "uploads/destinations/" . $entry["cover_image"] : "uploads/_website/" . $website_information["cover_image"]);
			$block["url"] = $section_information["canonical"] . "/" . $entry["canonical"] . "/";
			include "blocks/generic-10.php"; ?>
		</div>
	<? } ?>
	</div>
<? } ?>
<?=$pagination["object"]?>

<!-- Content Page -->
<? } else { ?>
<div class=seo_content>
	<!-- Content -->
	<? $content = $page_data["content"]; ?>
	<div class="margin-bottom-20 margin-bottom-progressive">
		<div class=page_container>
			<? if ($content){
				print htmlContent($content);
			} else {
				print noContent();
			} ?>
		</div>
	</div>
</div>

<!-- Gallery -->
<? $gallery = json_decode($page_data["gallery"],true);
if (count($gallery)){ ?>
<div class=subtitle><?=readLanguage(sections,page_gallery)?></div>
<div class="row grid-container">
<? foreach ($gallery AS $key=>$value){ ?>
	<div class="col-md-4 col-sm-5 col-xs-10 grid-item">
		<a data-fancybox=gallery href="uploads/destinations/<?=$value["url"]?>">
		<?
		$block["title"] = $value["title"];
		$block["cover"] = "uploads/destinations/thumbnails/" . $value["url"];
		include "blocks/image.php";
		?>
		</a>
	</div>
<? } ?>
</div>
<? } ?>

<!-- Booking -->
<h2 class="page_subtitle large margin-top-30"><?=readLanguage(booking,book_now_to)?> <?=$section_title?></h2>
<div class=inline_search>
<? $search["to"] = $page_data["airport"];
include "modules/search.php"; ?>
</div>

<!-- End Condition -->
<? } ?>

<? include "website/section_footer.php";
include "system/footer.php"; ?>