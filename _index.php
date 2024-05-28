<? $index_page = getData($suffix . "website_pages", "page", "index");
$index_modules = explode(",", $index_page["page_layout"]);
foreach ($index_modules AS $module){
	echo customModuleRender($module);
} ?>