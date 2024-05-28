<?
//========= Reserved Pages Slugs =========

//Built-in pages are to be appended to this array
$data_reserved_slugs = array(
	"webmail", "cpanel",
	$panel_folder, "blocks", "core", "fonts", "images", "mobile", "modules", "plugins", "system", "uploads", "website",
	"requests", "uploader", "broken-link", "search",
	"logout", "login", "signup", "reset-password", "user"
);

//========= Website Sections Slugs =========

//Built-In Pages
$result = mysqlQuery("SELECT * FROM " . $suffix . "website_pages ORDER BY id ASC");
if (mysqlNum($result)){
	while ($page = mysqlFetch($result)){
		$pages_built_in .= "<option value='" . ($page["canonical"] ? $page["canonical"] . "/" : ".") . "'>" . $page["title"] . "</option>";
		array_push($data_reserved_slugs, $page["canonical"]);
	}
	$data_menu_items .= "<optgroup label='" . readLanguage(pages,pages_built_in) . "'>" . $pages_built_in . "</optgroup>";
}

//Custom Pages
$result = mysqlQuery("SELECT * FROM " . $suffix . "website_pages_custom WHERE parent=0 ORDER BY priority DESC");
if (mysqlNum($result)){
	while ($page = mysqlFetch($result)){
		$custom_pages .= "<option value='" . ($page["canonical"] ? $page["canonical"] . "/" : ".") . "'>" . $page["title"] . "</option>";
	}
	$data_menu_items .= "<optgroup label='" . readLanguage(pages,pages_custom) . "'>" . $custom_pages . "</optgroup>";
}

$data_menu_items .= "<option value=''>" . readLanguage(builder,none) . "</option>";
?>