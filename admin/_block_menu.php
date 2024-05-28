<?
//Loop through panel categories
foreach($panel_categories AS $category => $sections){
	$category_count++;
	$panel_pages = null;
	$expanded = null;
	$panel_description = array();
	
	//Loop through category sections
	foreach ($sections AS $key => $section){
		$links = null;
		//Loop through section pages
		foreach($panel_section[$section] AS $page_link => $page_name){
			if (checkPermissions($page_link, 2)){
				$is_active = (basename($_SERVER["SCRIPT_NAME"]) == "$page_link.php");
				$image = (file_exists("images/icons/$page_link.png") ? $page_link : "_default");
				$notifications_badge = ($notifications[$page_link][1] ? "<span class=icon_notification>" . $notifications[$page_link][1] . "</span>" : "");
				$class = ($is_active ? "active" : "standard");
				$links .= "<li class='$class'><a href='$page_link.php'><div class=index_button search-category='$category' search-section='$section' search-normalized='" . normalizeString($page_name) . "'>$notifications_badge<img src='images/icons/$image.png'><b>$page_name</b></div></a></li>";
				if (basename($_SERVER["SCRIPT_NAME"]) == "$page_link.php"){
					$expanded = "in";
				}
			}
		}
		if ($links){
			if (count($sections) > 1){
				array_push($panel_description, $section);
				$panel_pages .= "<div class=separator>$section</div>";
			}
			$panel_pages .= "<ul class=icons_container>$links</ul>";
		}
	}
	
	//Final Menu
	if ($panel_pages){
		$image = (file_exists("images/sections/$category_count.png") ? $category_count : "_default");
		$menu .= "<div class='panel panel-default'>
			<div class=panel-heading><a class='" . ($expanded ? '' : 'collapsed') . "' data-toggle=collapse data-parent='#{{menu_parent}}' href='#{{menu_parent}}_$category_count'>
				<img src='images/sections/$image.png'>
				<div>$category<small>" . implode(", ", $panel_description) . "</small></div>
				<i class='fas fa-angle-down'></i>
			</a></div>
			<div id='{{menu_parent}}_$category_count' class='panel-collapse collapse $expanded'>
				<div class=panel-body>$panel_pages</div>
			</div>
		</div>";
	}
}
?>