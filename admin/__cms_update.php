<? include "system/_handler.php";

$cms_versions = array("2.6.3", "2.6.4", "2.6.5", "2.6.6", "2.7.0", "2.7.1", "2.7.2");

//==================================================
//v.2.7.2
//==================================================

/*
- Replace "setcookie" with "writeCookie"
- Replace "website_pages" with "website_pages_custom"
- Replace "website_sections" with "website_pages"
- Replace "website_modules_built" with "website_modules"
- Replace "website_theme_classes" with "website_classes"
- Replace "website_custom_sections" with "website_custom_contents"
- Replace "Built-In Sections" with "Built-In Pages"
- Replace "Custom Pages Sections" with "Custom Pages Contents"
- Replace "Theme Class Builder" with "Class Builder"
- Replace "<? include "website/_menu.php"; ?>" with "<? renderWebsiteMenu() ?>"
- Optional, replace setcookie with negative dates to "unsetCookie"
- Manually remove website menu css from header and update existing accordingly
*/

$update_instructions["2.7.2"] = "UPDATE en_website_modules_custom SET custom_spacing=REPLACE('{\"md\":\"w\",\"sm\":\"w\",\"xs\":\"w\"}','w',custom_spacing) WHERE custom_spacing!='';
UPDATE ar_website_modules_custom SET custom_spacing=REPLACE('{\"md\":\"w\",\"sm\":\"w\",\"xs\":\"w\"}','w',custom_spacing) WHERE custom_spacing!='';
ALTER TABLE en_website_modules_custom DROP custom_spacing_bottom;
ALTER TABLE ar_website_modules_custom DROP custom_spacing_bottom;
ALTER TABLE en_website_custom_displays CHANGE grid_spacing grid_spacing TEXT NOT NULL;
ALTER TABLE ar_website_custom_displays CHANGE grid_spacing grid_spacing TEXT NOT NULL;
UPDATE en_website_custom_displays SET grid_spacing='' WHERE grid_spacing=0;
UPDATE ar_website_custom_displays SET grid_spacing='' WHERE grid_spacing=0;
UPDATE en_website_custom_displays SET grid_spacing=REPLACE('{\"md\":\"w\",\"sm\":\"w\",\"xs\":\"w\"}','w',grid_spacing) WHERE grid_spacing!='';
UPDATE ar_website_custom_displays SET grid_spacing=REPLACE('{\"md\":\"w\",\"sm\":\"w\",\"xs\":\"w\"}','w',grid_spacing) WHERE grid_spacing!='';
ALTER TABLE en_website_modules_custom ADD custom_separator TEXT NOT NULL AFTER custom_spacing;
ALTER TABLE ar_website_modules_custom ADD custom_separator TEXT NOT NULL AFTER custom_spacing;
UPDATE en_website_modules_custom SET custom_separator='{\"md\":\"\",\"sm\":\"\",\"xs\":\"\"}';
UPDATE ar_website_modules_custom SET custom_separator='{\"md\":\"\",\"sm\":\"\",\"xs\":\"\"}';
ALTER TABLE en_website_menu ADD type INT NOT NULL AFTER id;
ALTER TABLE ar_website_menu ADD type INT NOT NULL AFTER id;
RENAME TABLE en_website_pages TO en_website_pages_custom;
RENAME TABLE en_website_sections TO en_website_pages;
RENAME TABLE ar_website_pages TO ar_website_pages_custom;
RENAME TABLE ar_website_sections TO ar_website_pages;
RENAME TABLE en_website_modules_built TO en_website_modules;
RENAME TABLE ar_website_modules_built TO ar_website_modules;
RENAME TABLE website_theme_classes TO website_classes;
ALTER TABLE en_website_pages_custom CHANGE blocks_per_row blocks_per_row TEXT NOT NULL;
ALTER TABLE ar_website_pages_custom CHANGE blocks_per_row blocks_per_row TEXT NOT NULL;
UPDATE en_website_pages_custom SET blocks_per_row='' WHERE blocks_per_row=0;
UPDATE ar_website_pages_custom SET blocks_per_row='' WHERE blocks_per_row=0;
ALTER TABLE en_website_pages_custom CHANGE blocks_spacing blocks_spacing TEXT NOT NULL;
ALTER TABLE ar_website_pages_custom CHANGE blocks_spacing blocks_spacing TEXT NOT NULL;
UPDATE en_website_pages_custom SET blocks_spacing='' WHERE blocks_spacing=0;
UPDATE ar_website_pages_custom SET blocks_spacing='' WHERE blocks_spacing=0;
ALTER TABLE en_website_custom_displays DROP grid_center, DROP grid_container_class;
ALTER TABLE ar_website_custom_displays DROP grid_center, DROP grid_container_class;
ALTER TABLE en_website_custom_displays CHANGE grid_width grid_blocks_per_row TEXT NOT NULL;
ALTER TABLE en_website_custom_displays CHANGE grid_spacing grid_blocks_spacing TEXT NOT NULL;
ALTER TABLE en_website_custom_displays CHANGE grid_block_class grid_blocks_class TEXT NOT NULL;
ALTER TABLE en_website_custom_displays CHANGE grid_block_animation grid_blocks_animation TEXT NOT NULL;
ALTER TABLE en_website_custom_displays CHANGE grid_count grid_blocks_count TEXT NOT NULL;
ALTER TABLE ar_website_custom_displays CHANGE grid_width grid_blocks_per_row TEXT NOT NULL;
ALTER TABLE ar_website_custom_displays CHANGE grid_spacing grid_blocks_spacing TEXT NOT NULL;
ALTER TABLE ar_website_custom_displays CHANGE grid_block_class grid_blocks_class TEXT NOT NULL;
ALTER TABLE ar_website_custom_displays CHANGE grid_block_animation grid_blocks_animation TEXT NOT NULL;
ALTER TABLE ar_website_custom_displays CHANGE grid_count grid_blocks_count TEXT NOT NULL;
ALTER TABLE en_website_pages_custom ADD blocks_class TEXT NOT NULL AFTER blocks_per_row, ADD blocks_animation TEXT NOT NULL AFTER blocks_class;
ALTER TABLE ar_website_pages_custom ADD blocks_class TEXT NOT NULL AFTER blocks_per_row, ADD blocks_animation TEXT NOT NULL AFTER blocks_class;
ALTER TABLE en_website_custom_displays ADD grid_justify TEXT NOT NULL AFTER blocks_template, ADD grid_align TEXT NOT NULL AFTER grid_justify;
ALTER TABLE ar_website_custom_displays ADD grid_justify TEXT NOT NULL AFTER blocks_template, ADD grid_align TEXT NOT NULL AFTER grid_justify;
ALTER TABLE en_website_pages_custom ADD blocks_grid_justify TEXT NOT NULL AFTER blocks_template, ADD blocks_grid_align TEXT NOT NULL AFTER blocks_grid_justify;
ALTER TABLE ar_website_pages_custom ADD blocks_grid_justify TEXT NOT NULL AFTER blocks_template, ADD blocks_grid_align TEXT NOT NULL AFTER blocks_grid_justify;
UPDATE en_website_pages_custom SET blocks_grid_justify='flex-start', blocks_grid_align='stretch' WHERE blocks_show=1;
UPDATE ar_website_pages_custom SET blocks_grid_justify='flex-start', blocks_grid_align='stretch' WHERE blocks_show=1;
UPDATE en_website_custom_displays SET grid_justify='flex-start', grid_align='stretch' WHERE type=0;
UPDATE ar_website_custom_displays SET grid_justify='flex-start', grid_align='stretch' WHERE type=0;
ALTER TABLE en_website_pages DROP blocks_show, DROP blocks_template, DROP blocks_spacing, DROP blocks_per_page, DROP blocks_per_row, DROP page_content_module, DROP child_content_module, DROP child_header, DROP child_footer;
ALTER TABLE ar_website_pages DROP blocks_show, DROP blocks_template, DROP blocks_spacing, DROP blocks_per_page, DROP blocks_per_row, DROP page_content_module, DROP child_content_module, DROP child_header, DROP child_footer;
ALTER TABLE en_website_pages DROP layout_updatable;
ALTER TABLE ar_website_pages DROP layout_updatable;";

function copyQuickLinks($language){
	$links = json_decode(mysqlFetch(mysqlQuery("SELECT content FROM " . $language . "_website_information WHERE title='quick_links'"))["content"], true);
	$links = array_reverse($links);
	if (count($links)){
		foreach ($links AS $link){
			$sub_menus = [];
			if ($link[children]){
				foreach ($link[children] AS $child_link){
					$link_id++;
					array_push($sub_menus, [
						"id"=> $link_id,
						"content"=> [
							"title" => $child_link[content][title],
							"icon" => "",
							"url" => $child_link[content][url]
						]
					]);
				}
			}
			mysqlQuery("INSERT INTO " . $language . "_website_menu (
				type,
				title,
				url,
				sub_menus_type,
				sub_menus,
				priority
			) VALUES (
				1,
				'" . $link[content][title] . "',
				'" . $link[content][url] . "',
				'" . ($link[children] ? 1 : 0) . "',
				'" . json_encode($sub_menus, JSON_UNESCAPED_UNICODE) . "',
				'" . newRecordID($language . "_website_menu") . "'
			)");
		}
	}
}

function updateFunction272(){
	unlink("website_sections.php");
	unlink("website_theme_classes.php");
	unlink("website_custom_sections.php");
	unlink("../website/_menu.php");
	unlink("../website/header.js");
	
	rename("images/icons/website_sections.png", "images/icons/website_pages.png");
	rename("images/icons/website_theme_classes.png", "images/icons/website_classes.png");
	rename("images/icons/website_custom_sections.png", "images/icons/website_custom_contents.png");
	
	copyQuickLinks("en");
	mysqlQuery("DELETE FROM en_website_information WHERE title='quick_links'");
	resetTableIDs("en_website_information");
	
	copyQuickLinks("ar");
	mysqlQuery("DELETE FROM ar_website_information WHERE title='quick_links'");
	resetTableIDs("ar_website_information");
	
	//Update custom display grids to row count instead of width percentage
	$width_to_count = array(
		"100" => "1",
		"95" => "1",
		"90" => "1",
		"85" => "1",
		"83.33" => "1",
		"80" => "1",
		"75" => "1",
		"70" => "1",
		"66.66" => "2",
		"65" => "2",
		"60" => "2",
		"55" => "2",
		"50" => "2",
		"45" => "2",
		"40" => "2",
		"35" => "3",
		"33.33" => "3",
		"30" => "4",
		"25" => "4",
		"20" => "5",
		"16.66" => "6",
		"15" => "6",
		"10" => "6",
		"5" => "6",
		"0" => "1",
	);
	$target_tables = ["ar_website_custom_displays", "en_website_custom_displays"];
	foreach ($target_tables AS $table){
		$result = mysqlQuery("SELECT * FROM $table WHERE grid_blocks_per_row!=''");
		while ($entry = mysqlFetch($result)){
			$widths = json_decode($entry["grid_blocks_per_row"], true);
			foreach ($widths AS $key=>$value){
				$widths[$key] = $width_to_count[$value];
			}
			mysqlQuery("UPDATE $table SET grid_blocks_per_row='" . json_encode($widths, JSON_UNESCAPED_UNICODE) . "' WHERE id=" . $entry["id"]);
		}
	}
	
	//Update custom pages blocks per row for different screen sizes
	$target_tables = ["ar_website_pages_custom", "en_website_pages_custom"];
	foreach ($target_tables AS $table){
		$result = mysqlQuery("SELECT * FROM $table WHERE blocks_per_row!=''");
		while ($entry = mysqlFetch($result)){
			if ($entry["blocks_per_row"]){
				$widths = array(
					"md" => $entry["blocks_per_row"],
					"sm" => $entry["blocks_per_row"],
					"xs" => "1"
				);
				mysqlQuery("UPDATE $table SET blocks_per_row='" . json_encode($widths, JSON_UNESCAPED_UNICODE) . "' WHERE id=" . $entry["id"]);
			}
			if ($entry["blocks_spacing"]){
				$spacing = array(
					"md" => $entry["blocks_spacing"],
					"sm" => $entry["blocks_spacing"],
					"xs" => $entry["blocks_spacing"]
				);
				mysqlQuery("UPDATE $table SET blocks_spacing='" . json_encode($spacing, JSON_UNESCAPED_UNICODE) . "' WHERE id=" . $entry["id"]);
			}			
		}
	}
}


//==================================================
//v.2.7.1
//==================================================

//No instructions

//==================================================
//v.2.7.0
//==================================================

/*
- Manually add schema > website_pages_custom
- Manually replace "url_parameters" with "url_attributes"
- Manually edit (website/handler.php)
- Manually add (extraPageGlobalParameters) function in "functions.php"
- Manually move "mailFormat" function
*/

$update_instructions["2.7.0"] = "UPDATE ar_website_custom_displays SET grid_width=REPLACE('{\"md\":\"w\",\"sm\":\"w\",\"xs\":\"100\"}','w',grid_width) WHERE grid_width > 0;
UPDATE en_website_custom_displays SET grid_width=REPLACE('{\"md\":\"w\",\"sm\":\"w\",\"xs\":\"100\"}','w',grid_width) WHERE grid_width > 0;
UPDATE ar_website_modules_components SET width=REPLACE('{\"md\":\"w\",\"sm\":\"w\",\"xs\":\"100\"}','w',width) WHERE width > 0;
UPDATE en_website_modules_components SET width=REPLACE('{\"md\":\"w\",\"sm\":\"w\",\"xs\":\"100\"}','w',width) WHERE width > 0;
ALTER TABLE en_website_pages_custom ADD foreign_pages TEXT NOT NULL AFTER child_color;
ALTER TABLE ar_website_pages_custom ADD foreign_pages TEXT NOT NULL AFTER child_color;
ALTER TABLE en_website_pages_custom CHANGE child_numeric child_extras TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;
ALTER TABLE ar_website_pages_custom CHANGE child_numeric child_extras TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;
ALTER TABLE en_website_pages_custom ADD url_attributes TEXT NOT NULL AFTER url_target;
ALTER TABLE ar_website_pages_custom ADD url_attributes TEXT NOT NULL AFTER url_target;
UPDATE en_website_pages_custom SET url_target=3 WHERE canonical='';
UPDATE ar_website_pages_custom SET url_target=3 WHERE canonical='';";

unlink("snippets/mailer/class.phpmailer.php");
unlink("snippets/mailer/class.smtp.php");

//==================================================
//v.2.6.6
//==================================================

//No instructions

//==================================================
//v.2.6.5
//==================================================
$update_instructions["2.6.5"] = "ALTER TABLE ar_website_pages_custom ADD child_color TEXT NOT NULL AFTER child_numeric;
ALTER TABLE en_website_pages_custom ADD child_color TEXT NOT NULL AFTER child_numeric;
ALTER TABLE system_queries ADD sort_column TEXT NOT NULL AFTER conditions_json, ADD sort_method TEXT NOT NULL AFTER sort_column;
UPDATE system_queries SET sort_column='id', sort_method='DESC';";

function updateFunction265(){
	unlink("crud/crud.css");
	unlink("crud/plugins.js");
	unlink("../core/_jquery-ui.js");
	unlink("../plugins/confirm.min.js");
	unlink("../plugins/confirm.min.css");
	unlink("../plugins/fancybox.min.js");
	unlink("../plugins/fancybox.min.css");
	unlink("../plugins/select2.min.js");
	unlink("../plugins/select2.min.css");
	unlink("../plugins/max-length.js");
	unlink("../plugins/max-length.css");
	unlink("../plugins/sticky.min.js");
	unlink("../plugins/validator.min.js");
	unlink("../plugins/animate.min.css");
}

//==================================================
//v.2.6.4
//==================================================

$update_instructions["2.6.4"] = "ALTER TABLE ar_website_menu CHANGE sub_menus_side sub_menus_side TEXT NOT NULL;
ALTER TABLE ar_website_menu ADD sub_menus_children INT NOT NULL AFTER sub_menus_side;
UPDATE ar_website_menu SET sub_menus_side='' WHERE sub_menus_side=0;
UPDATE ar_website_menu SET sub_menus_side='icon' WHERE sub_menus_side=1;
UPDATE ar_website_menu SET sub_menus_side='cover' WHERE sub_menus_side=2;
UPDATE ar_website_menu SET sub_menus_side='' WHERE sub_menus_type=0;
ALTER TABLE en_website_menu CHANGE sub_menus_side sub_menus_side TEXT NOT NULL;
ALTER TABLE en_website_menu ADD sub_menus_children INT NOT NULL AFTER sub_menus_side;
UPDATE en_website_menu SET sub_menus_side='' WHERE sub_menus_side=0;
UPDATE en_website_menu SET sub_menus_side='icon' WHERE sub_menus_side=1;
UPDATE en_website_menu SET sub_menus_side='cover' WHERE sub_menus_side=2;
UPDATE en_website_menu SET sub_menus_side='' WHERE sub_menus_type=0;";

//==================================================

//Get current CMS version
function getCurrentCMSVersion(){
	$config = file_get_contents("system/_config.php");
	$cms_version_match = preg_match('/\$cms_version = "(.*)";/i', $config, $matches);
	return $matches[1];
}

//Update CMS version
function updateCMSVersion($version){
	$config = file_get_contents("system/_config.php");
	$current_version = getCurrentCMSVersion();
	$config = str_replace($current_version, $version, $config);
	file_put_contents("system/_config.php", $config);
}

//Get table name from query
function getTableName($query){
	$query = strtolower(trim(str_replace(PHP_EOL, ' ', $query)));
	$table = '';
	if (substr($query, 0, 12) == 'create table'){
		$start = strpos($query, 'create table') + 12;
		$end = strpos($query, '(');
		$length = $end - $start;
		$table = substr($query, $start, $length);
	} else if (substr($query, 0, 6) == 'update'){
		$parts = explode(' ', $query);
		$table = $parts[1];
	} else if (substr($query, 0, 11) == 'alter table'){
		$parts = explode(' ', $query);
		$table = $parts[2];
	} else if (substr($query, 0, 11) == 'insert into'){
		$parts = explode(' ', $query);
		$table = $parts[2];
	} else if (substr($query, 0, 12) == 'rename table'){
		$parts = explode(' ', $query);
		$table = $parts[2];
	} else if (substr($query, 0, 12) == 'create index'){
		$parts = explode(' ', $query);
		$table = $parts[4];
	} else if (substr($query, 0, 6) == 'select'){
		$parts = explode(' ', $query);
		foreach($parts as $i => $part){
			if (trim($part) == 'from'){
				$table = $parts[$i + 1];
				break;
			}
		}
	} elseif (strtolower(substr($query, 0, 29)) == 'create unique clustered index'){
		$parts = explode(' ', $query);
		$table = $parts[6];
	} else if(strtolower(substr($query, 0, 22)) == 'create clustered index'){
		$parts = explode(' ', $query);
		$table = $parts[5];
	} else if(strtolower(substr($query, 0, 15)) == 'exec sp_columns'){
		$parts = explode(' ', $query);
		$table = str_replace("'", '', $parts[2]);
	} else if(strtolower(substr($query, 0, 11)) == 'delete from'){
		$parts = explode(' ', $query);
		$table = str_replace("'", '', $parts[2]);
	}
	return trim(str_replace(['`', '[', ']'], ['', '', ''], $table));
}

//==================================================

$current_cms_version = getCurrentCMSVersion();
$latest_cms_version = $cms_versions[count($cms_versions) - 1];
$current_cms_version_index = array_search($current_cms_version, $cms_versions);

if ($current_cms_version_index===false){
	print "<font color=red>Current CMS version <b>$current_cms_version</b> not available in updatable versions</font>";

} else if ($current_cms_version==$latest_cms_version){
	print "<font color=red>CMS is already up to date to the latest version <b>$current_cms_version</b></font>";
	
} else {
	for ($x = ($current_cms_version_index + 1); $x <= (count($cms_versions) - 1); $x++){
		//Perform instructions
		if ($update_instructions[$cms_versions[$x]]){
			$instructions = explode("\r\n", $update_instructions[$cms_versions[$x]]);
			$failed_instructions = array();
			$skipped_instructions = array();
			foreach ($instructions AS $query){
				$tablename = getTableName($query);
				$table_exists = mysqlNum(mysqlQuery("SHOW TABLES LIKE '$tablename'"));
				if ($table_exists){
					mysqlQuery($query);
					if (mysqlAffectedRows()==-1){
						array_push($failed_instructions, $query);
					}
				} else {
					array_push($skipped_instructions, $tablename);
				}
			}
		}
		
		if ($failed_instructions){
			print "<font color=red>Updating to version <b>" . $cms_versions[$x] . "</b> failed for the following instructions:<br>" . implode("<br>", $failed_instructions) . "</font>";
			exit();
		}
		
		//Call update function
		$version_number = str_replace(".", "", $cms_versions[$x]);
		$update_function = "updateFunction$version_number";
		if (function_exists($update_function)){
			$update_function();
		}
		
		updateCMSVersion($cms_versions[$x]);		
		
		//Build CSS & modules classes
		buildCSSClasses();
		buildCSSModules();
		buildWebsiteTheme();		
		
		print "<font color=green>Updating to version <b>" . $cms_versions[$x] . "</b> is complete</font>" . ($skipped_instructions ? "<br>Tables " . implode(", ", $skipped_instructions) . " were skipped" : "");
		print "<br>";
	}
	
	print "Finished";
}
?>