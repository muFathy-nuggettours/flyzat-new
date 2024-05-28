<? include "system/_handler.php";

setCurrentPageRedirect();
$mysqltable = $suffix . "website_seo";
$canonical = $get["id"];

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
	//Parse regular expression
	$result = mysqlQuery("SELECT * FROM $mysqltable ORDER BY priority DESC");
	while ($entry = mysqlFetch($result)){
		preg_match("/" . $entry["route_expression"] . "/", $canonical, $matches);
		if ($matches[1] && $matches[2]){
			$route = $entry["route"];
			$origin = $matches[1];
			$destination = $matches[2];
			break;
		}
	}
	$clean_origin = str_replace("-", " ", $origin);
	$clean_destination = str_replace("-", " ", $destination);
	
	//Get origin and destination data
	$search_databases = array("system_database_countries", "system_database_regions", "system_database_airports");
	foreach ($search_databases AS $database){
		$result = mysqlFetch(mysqlQuery("SELECT *, '$database' AS source FROM $database WHERE publish=1 AND FIND_IN_SET('$origin', slugs) ORDER BY priority DESC"));
		if ($result){
			$origin = $result;
			break;
		}
	}
	foreach ($search_databases AS $database){
		$result = mysqlFetch(mysqlQuery("SELECT *, '$database' AS source FROM $database WHERE publish=1 AND FIND_IN_SET('$destination', slugs) ORDER BY priority DESC"));
		if ($result){
			$destination = $result;
			break;
		}
	}
	
	//Broken link if no results are found
	if (!$origin || !$destination){
		brokenLink();
	}
	
	//Assign airports
	switch ($origin["source"]){
		case "system_database_countries":
			$origin_type = 1;
			$origin_code = $origin["code"];
			$origin_airport = mysqlFetch(mysqlQuery("SELECT * FROM system_database_airports WHERE country='" . $origin["code"] . "' ORDER BY popularity DESC, priority DESC LIMIT 0,1"));
		break;
		
		case "system_database_regions":
			$origin_type = 2;
			$origin_code = $origin["code"];
			$origin_airport = mysqlFetch(mysqlQuery("SELECT * FROM system_database_airports WHERE country='" . $origin["country"] . "' ORDER BY popularity DESC, priority DESC LIMIT 0,1"));
		break;
		
		default:
			$origin_type = 3;
			$origin_code = $origin["iata"];
			$origin_airport = $origin;
	}
	
	switch ($destination["source"]){
		case "system_database_countries":
			$destination_type = 1;
			$destination_code = $destination["code"];
			$destination_airport = mysqlFetch(mysqlQuery("SELECT * FROM system_database_airports WHERE country='" . $destination["code"] . "' ORDER BY popularity DESC, priority DESC LIMIT 0,1"));
		break;
		
		case "system_database_regions":
			$destination_type = 2;
			$destination_code = $destination["code"];
			$destination_airport = mysqlFetch(mysqlQuery("SELECT * FROM system_database_airports WHERE country='" . $destination["country"] . "' ORDER BY popularity DESC, priority DESC LIMIT 0,1"));
		break;

		default:
			$destination_type = 3;
			$destination_code = $destination["iata"];
			$destination_airport = $destination;
	}	
	
	$section_prefix = $section_information["title"];
	$section_title = str_replace(array("{1}", "{2}"), array($clean_origin, $clean_destination), $route);
	
	$breadcrumbs = array();
	array_push($breadcrumbs,"<li><a href='.'>" . readLanguage(general,home) . "</a></li>");
	array_push($breadcrumbs,"<li><a href='" . $section_information["canonical"] . "/'>" . $section_information["title"] . "</a></li>");
	array_push($breadcrumbs,"<li>" . $section_title . "</li>");
}

include "system/header.php";
include "website/section_header.php"; ?>

<!-- Home Page -->
<? if (!$canonical){ ?>
<div class=inline_search>
<? $search["from"] = $origin_airport["iata"];
$search["to"] = $destination_airport["iata"];
include "modules/search.php"; ?>
</div>

<!-- Content Page -->
<? } else { ?>

<!-- Booking -->
<h2 class="page_subtitle large"><?=readLanguage(booking,book_now)?></h2>
<div class=inline_search>
<? $search["from"] = $origin_airport["iata"];
$search["to"] = $destination_airport["iata"];
include "modules/search.php"; ?>
</div>

<!-- Content -->
<div class="seo_content margin-top-30">
	<!-- Origin -->
	<h2 class="page_subtitle large"><?=$origin[$suffix . "name"]?></h2>
	<? $content = $origin[$suffix . "content"]; $page_data = $origin; ?>
	<div class="margin-bottom-20 margin-bottom-progressive">
		<div class=page_container>
			<!-- Content -->
			<? if ($content){ ?>
				<div class=margin-bottom><?=htmlContent($content)?></div>
			<? } ?>
			
			<!-- Information -->
			<? if ($origin["source"]=="system_database_countries"){ ?>
				<div class=custom_info_card>
					<div><small><?=readLanguage(pages,country_code)?></small><br><?=strtoupper($page_data["code"])?></div>
					<div><small><?=readLanguage(pages,continent)?></small><br><?=$page_data["continent"]?></div>
					<div><small><?=readLanguage(pages,phone_code)?></small><br>+<?=$page_data["phone_code"]?></div>
					<div><small><?=readLanguage(currencies,currency)?></small><br><?=$page_data[$suffix . "currency_name"]?></div>
					<div><small><?=readLanguage(currencies,currency_code)?></small><br><?=$page_data["currency_code"]?></div>
					<div><small><?=readLanguage(currencies,currency_symbol)?></small><br><?=$page_data["currency_symbol"]?></div>
				</div>
			<? } ?>
			<? if ($origin["source"]=="system_database_regions"){ ?>
				<? $country = getData("system_database_countries", "code", $page_data["country"]); ?>
				<div class=custom_info_card>
					<div><small><?=readLanguage(accounts,country)?></small><br><a href="countries/<?=createCanonical($country[$suffix . "name"])?>/"><?=$country[$suffix . "name"]?></a></div>
					<div><small><?=readLanguage(pages,code)?></small><br><?=$page_data["code"]?></div>
				</div>
			<? } ?>
			<? if ($origin["source"]=="system_database_airports"){ ?>
				<? $country = getData("system_database_countries", "code", $page_data["country"]);
				$region = getID($page_data["region"], "system_database_regions"); ?>
				<div class=custom_info_card>
					<div><small><?=readLanguage(accounts,country)?></small><br><a href="countries/<?=createCanonical($country[$suffix . "name"])?>/"><?=$country[$suffix . "name"]?></a></div>
					<div><small><?=readLanguage(pages,city)?></small><br><a href="regions/<?=createCanonical($region[$suffix . "name"])?>/"><?=$region[$suffix . "name"]?></a></div>
					<div><small><?=readLanguage(pages,code)?></small><br><?=strtoupper($page_data["iata"])?></div>
					<? if ($page_data["website"]){ ?><div><small><?=readLanguage(accounts,website)?></small><br><a href="<?=$page_data["website"]?>" target=_blank><?=$page_data["website"]?></a></div><? } ?>
				</div>			
			<? } ?>
		</div>
	</div>

	<!-- Destination -->
	<h2 class="page_subtitle large"><?=$destination[$suffix . "name"]?></h2>
	<? $content = $destination[$suffix . "content"]; $page_data = $destination; ?>
	<div class="margin-bottom-20 margin-bottom-progressive">
		<div class=page_container>
			<!-- Content -->
			<? if ($content){ ?>
				<div class=margin-bottom><?=htmlContent($content)?></div>
			<? } ?>
			
			<!-- Information -->
			<? if ($destination["source"]=="system_database_countries"){ ?>
				<div class=custom_info_card>
					<div><small><?=readLanguage(pages,country_code)?></small><br><?=strtoupper($page_data["code"])?></div>
					<div><small><?=readLanguage(pages,continent)?></small><br><?=$page_data["continent"]?></div>
					<div><small><?=readLanguage(pages,phone_code)?></small><br>+<?=$page_data["phone_code"]?></div>
					<div><small><?=readLanguage(currencies,currency)?></small><br><?=$page_data[$suffix . "currency_name"]?></div>
					<div><small><?=readLanguage(currencies,currency_code)?></small><br><?=$page_data["currency_code"]?></div>
					<div><small><?=readLanguage(currencies,currency_symbol)?></small><br><?=$page_data["currency_symbol"]?></div>
				</div>
			<? } ?>
			<? if ($destination["source"]=="system_database_regions"){ ?>
				<? $country = getData("system_database_countries", "code", $page_data["country"]); ?>
				<div class=custom_info_card>
					<div><small><?=readLanguage(accounts,country)?></small><br><a href="countries/<?=createCanonical($country[$suffix . "name"])?>/"><?=$country[$suffix . "name"]?></a></div>
					<div><small><?=readLanguage(pages,code)?></small><br><?=$page_data["code"]?></div>
				</div>
			<? } ?>
			<? if ($destination["source"]=="system_database_airports"){ ?>
				<? $country = getData("system_database_countries", "code", $page_data["country"]);
				$region = getID($page_data["region"], "system_database_regions"); ?>
				<div class=custom_info_card>
					<div><small><?=readLanguage(accounts,country)?></small><br><a href="countries/<?=createCanonical($country[$suffix . "name"])?>/"><?=$country[$suffix . "name"]?></a></div>
					<div><small><?=readLanguage(pages,city)?></small><br><a href="regions/<?=createCanonical($region[$suffix . "name"])?>/"><?=$region[$suffix . "name"]?></a></div>
					<div><small><?=readLanguage(pages,code)?></small><br><?=strtoupper($page_data["iata"])?></div>
					<? if ($page_data["website"]){ ?><div><small><?=readLanguage(accounts,website)?></small><br><a href="<?=$page_data["website"]?>" target=_blank><?=$page_data["website"]?></a></div><? } ?>
				</div>			
			<? } ?>
		</div>
	</div>	
</div>

<? $page_data = mysqlFetch(mysqlQuery("SELECT * FROM " . $suffix . "website_seo_pages WHERE origin_type=" . $origin_type . " AND origin_code='" . $origin_code . "' AND destination_type=" . $destination_type . " AND destination_code='" . $destination_code . "'")); ?>
<? if ($page_data["content"]){ ?>
	<div class=margin-top>
		<!-- Content -->
		<div class="margin-bottom-20 margin-bottom-progressive">
			<div class=page_container>
				<? print htmlContent($page_data["content"]); ?>
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
				$block["image"] = "uploads/destinations/thumbnails/" . $value["url"];
				include "blocks/image.php";
				?>
				</a>
			</div>
		<? } ?>
		</div>
		<? } ?>
	</div>
<? } ?>

<!-- End Condition -->
<? } ?>

<? include "website/section_footer.php";
include "system/footer.php"; ?>