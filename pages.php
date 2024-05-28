<? include "system/_handler.php";

//Similar page content variables
$inline_page = (isset($get["inline"]) ? true : false);
$uploads_path = "pages";

$mysqltable = $suffix . "website_pages_custom";
$canonical = end(array_filter(explode("/", $get["canonical"])));

//Page Data
$result = mysqlQuery("SELECT * FROM $mysqltable WHERE canonical='$canonical' AND hidden=0");
if (!mysqlNum($result)){ brokenLink(); }
$page_data = mysqlFetch($result);

setCurrentPageRedirect();

//Section Information
if ($page_data["parent"] && $page_data["type"]==0){
	$parent = getID($page_data["parent"], $mysqltable);
}
$section_prefix = ($parent ? $parent["title"] : "");
$section_title = $page_data["title"];
$section_description = $page_data["description"];
$section_header_image = ($page_data["header_image"] ? "uploads/$uploads_path/" . $page_data["header_image"] : null);
$section_cover_image = ($page_data["cover_image"] ? "uploads/$uploads_path/" . $page_data["cover_image"] : null);
$section_header = ($page_data["page_header"] ? $page_data["page_header"] : $parent["child_header"]);
$section_footer = ($page_data["page_footer"] ? $page_data["page_footer"] : $parent["child_footer"]);
$section_layout = $page_data["page_layout"];

//Breadcrumbs
$breadcrumbs = array();
array_push($breadcrumbs,"<li><a href='.'>" . readLanguage(general,home) . "</a></li>");
if ($page_data["parent"]){
	$page_path = explode(",", customPagePath($page_data["id"]));
	array_pop($page_path); //Remove the displayed page from path
	foreach ($page_path AS $value){
		$page_parent = getID($value, $mysqltable);
		array_push($breadcrumbs, "<li><a href='" . ($page_parent["parent"] ? customPagePathRender($page_parent["parent"], null, "/", "canonical") . "/" : "") . $page_parent["canonical"] . "/'>" . $page_parent["title"] . "</a></li>");
	}
}
array_push($breadcrumbs, "<li class=active>" . $section_title . "</a></li>");

//Update Views
mysqlQuery("UPDATE $mysqltable SET views=views+1 WHERE id='" . $page_data["id"] . "'");

//Page Content Displays for (Gallery, Vidoes & Attachments)
$page_content_displays = json_decode($page_data["page_content_displays"], true);

$exclude_inner_container = ($page_data["type"] ? $page_data["page_content_module"]=="none" : $parent["child_content_module"]=="none");
include "system/header.php";
include "website/section_header.php"; ?>

<!-- ===== Variable Cards ===== -->
<? if ($page_data["blocks_show"]){ ?>
<? ob_start(); ?>
<? $query_statement = "SELECT * FROM $mysqltable WHERE parent='" . $page_data["id"] . "' ORDER BY priority DESC";
$pagination = paginateRecords($page_data["blocks_per_page"], mysqlNum(mysqlQuery($query_statement)), $page_data["canonical"] . "/");
$result = mysqlQuery("$query_statement LIMIT " . $pagination["min"] . "," . $pagination["max"]);
if (mysqlNum($result)){ ?>
	<div class="row <?=gridSpacingToClass($page_data["blocks_spacing"])?>" style="justify-content: <?=$page_data["blocks_grid_justify"]?>; align-items: <?=$page_data["blocks_grid_align"]?>">
	<? while ($entry=mysqlFetch($result)){ ?>
		<div class="grid-item <?=gridCountToClass($page_data["blocks_per_row"]) . " " . $page_data["blocks_class"]?>" <?=createAOSTags($page_data["blocks_animation"])?>>
			<? $block = customPageBlock($entry);
			include "blocks/" . createCanonical($page_data["blocks_template"]) . ".php"; ?>
		</div>
	<? } ?>
	</div>
	<?=$pagination["object"]?>
<? } else {
	print noContent();
} ?>
<? $page_variables["variable-cards"] = ob_get_clean(); ?>
<? } ?>

<!-- ===== Variable Date ===== -->
<? ob_start(); ?>
<span><?=dateLanguage("l, d M Y", $page_data["date"])?></span>
<? $page_variables["variable-date"] = ob_get_clean(); ?>

<!-- ===== Variable Views ===== -->
<? ob_start(); ?>
<span><?=$page_data["views"]?></span>
<? $page_variables["variable-views"] = ob_get_clean(); ?>

<!-- ===== Variable Content ===== -->
<? if ($page_data && $page_data["content"]){ ?>
<? ob_start(); ?>
<div class=html-content><?=htmlContent($page_data["content"])?></div>
<? $page_variables["variable-content"] = ob_get_clean(); ?>
<? } ?>

<!-- ===== Variable Gallery ===== -->
<? $gallery = json_decode($page_data["gallery"],true);
if (count($gallery) && $page_content_displays["gallery"]!="none"){ ?>
<? ob_start(); ?>
<? if (!$page_content_displays["gallery"]){ ?>
	<div class="row grid-container">
	<? foreach ($gallery AS $entry){ ?>
		<div class="col-md-4 col-sm-5 col-xs-10 grid-item">
			<? $block = builtPageBlock("gallery", $entry);
			include "blocks/image.php"; ?>
		</div>
	<? } ?>
	</div>
<? } else {
	print customDisplayRender($page_content_displays["gallery"], "gallery", $gallery);
} ?>
<? $page_variables["variable-gallery"] = ob_get_clean(); ?>
<? } ?>

<!-- ===== Variable Videos ===== -->
<? $videos = json_decode($page_data["videos"],true);
if (count($videos) && $page_content_displays["videos"]!="none"){ ?>
<? ob_start(); ?>
<? if (!$page_content_displays["videos"]){ ?>
	<div class="row grid-container">
	<? foreach ($videos AS $entry){ ?>
		<div class="col-md-4 col-sm-5 col-xs-10 grid-item">
			<? $block = builtPageBlock("videos", $entry);
			include "blocks/video.php"; ?>
		</div>
	<? } ?>	
	</div>
<? } else {
	print customDisplayRender($page_content_displays["videos"], "videos", $videos);
} ?>
<? $page_variables["variable-videos"] = ob_get_clean(); ?>
<? } ?>

<!-- ===== Variable Attachments ===== -->
<? $attachments = json_decode($page_data["attachments"],true);
if (count($attachments) && $page_content_displays["attachments"]!="none"){ ?>
<? ob_start(); ?>
<? if (!$page_content_displays["attachments"]){ ?>
	<ul class=list_grid>
		<? foreach ($attachments AS $entry){
			print "<li>" . fileBlock("uploads/$uploads_path/" . $entry["url"], $entry["title"]) . "</li>";
		} ?>
	</ul>
<? } else {
	print customDisplayRender($page_content_displays["attachments"], "attachments", $attachments);
} ?>
<? $page_variables["variable-attachments"] = ob_get_clean(); ?>
<? } ?>

<!-- ===== Variable Cover Image ===== -->
<? ob_start(); ?>
<a href="<?=$cover_image?>" data-fancybox=images><img preload=true src="<?=$cover_image?>" alt="<?=$section_title?>"></a>
<? $page_variables["variable-cover"] = ob_get_clean(); ?>

<!-- ===== Variable Navigation ===== -->
<? ob_start(); ?>
<? print "<div class=recursive_navigation>" . customPageNavigation(($page_data["parent"]==0 ? $page_data["id"] : $page_data["parent"]), 1, 2, $page_data["id"]) . "</div>"; ?>
<? $page_variables["variable-navigation"] = ob_get_clean(); ?>

<!-- ===== (PAGE RENDER) ===== -->
<? $page_content_module = ($page_data["page_content_module"] ? $page_data["page_content_module"] : $parent["child_content_module"]);
print customModuleRender($page_content_module, $page_variables); ?>

<? include "website/section_footer.php";
include "system/footer.php"; ?>