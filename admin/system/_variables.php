<?
//Original CMS Colors
$original_colors = array(
	"#0c1a26", "#0e2133", "#10263b", "#112b44", "#143350",
	"#112334", "#132d46", "#163451", "#173b5d", "#1b466e",
	"#173047", "#1a3e60", "#1e476f", "#20517f", "#256096",
);
	
//Social media websites
$data_social_media = array(
	"facebook" => array("Facebook", "fab fa-facebook-f", "3b579c"),
	"twitter" => array("Twitter", "fab fa-twitter", "21a1f0"),
	"linked-in" => array("Linked-In", "fab fa-linkedin-in", "0677b4"),
	"youtube" => array("YouTube", "fab fa-youtube", "fe0303"),
	"google-plus" => array("Google+", "fab fa-google", "da4437"),
	"snapchat" => array("Snapchat", "fab fa-snapchat-ghost", "e5be36"),
	"instagram" => array("Instagram", "fab fa-instagram", "336699"),
	"pinterest" => array("Pinterest", "fab fa-pinterest-p", "bc091b"),
	"telegram" => array("Telegram", "fab fa-telegram", "1e96c8"),
	"whatsapp" => array("WhatsApp", "fab fa-whatsapp", "1bbe43"),
);

//Allowed file extensions
$allowed_extensions = array(
	"jpg","jpeg","png","bmp","gif","tif", //Images
	"mp3","mp4","avi","mov","mpg","mpeg","wmv", //Videos
	"pdf","xls","xlsx","doc","docx", //Documents
	"zip","rar","7z" //Archives
);

//File types icons
$data_file_icons = array(
	"png" => "fas fa-image",
	"jpg" => "fas fa-image",
	"jpeg" => "fas fa-image",
	"bmp" => "fas fa-image",
	"gif" => "fas fa-image",
	"zip" => "fas fa-file-archive",
	"rar" => "fas fa-file-archive",
	"xls" => "fas fa-file-excel",
	"xlsx" => "fas fa-file-excel",
	"doc" => "fas fa-file-word",
	"docx" => "fas fa-file-word",
	"pdf" => "fas fa-file-pdf",
);

//Device types [For push notifications]
$data_device_types = array(
	0 => "iOS",
	1 => "ANDROID",
	2 => "AMAZON",
	3 => "WINDOWSPHONE (MPNS)",
	4 => "CHROME APPS / EXTENSIONS",
	5 => "CHROME WEB PUSH",
	6 => "WINDOWS (WNS)",
	7 => "SAFARI",
	8 => "FIREFOX",
	9 => "MACOS",
	10 => "ALEXA",
	11 => "EMAIL"
);

//Module widths matrix [For custom modules]
$data_module_widths = array(
	"100" => "20",
	"95" => "19",
	"90" => "18",
	"85" => "17",
	"83.33" => "six-comp",
	"80" => "16",
	"75" => "15",
	"70" => "14",
	"66.66" => "three-comp",
	"65" => "13",
	"60" => "12",
	"55" => "11",
	"50" => "10",
	"45" => "9",
	"40" => "8",
	"35" => "7",
	"33.33" => "three",
	"30" => "6",
	"25" => "5",
	"20" => "4",
	"16.66" => "six",
	"15" => "3",
	"10" => "2",
	"5" => "1",
);

//Screens sizes
$data_screen_sizes = array(
	"md" => "fal fa-desktop",
	"sm" => "fal fa-tablet",
	"xs" => "fal fa-mobile",
);

//Columns count to grid class [For grid displays and variable cards]
$data_columns_count = array(
	1 => "20",
	2 => "10",
	3 => "three",
	4 => "5",
	5 => "4",
	6 => "six",
);

//Read system variables
$variables_result = mysqlQuery("SELECT * FROM system_variables");
while ($variable_entry = mysqlFetch($variables_result)){
	$variable_name = $variable_entry["variable"];
	$variable_array = json_decode(unescapeJson($variable_entry["variables"]),true);
	foreach ($variable_array AS $key=>$value){
		$variable_key = $value["key"];
		$variable_value = ($variable_entry["multi_language"] ? $value[$language["suffix"] . "value"] : $value["value"]);
		${$variable_name}[$variable_key] = $variable_value;
	}
}

//Read variables file [For multiple languages]
$root = (strpos(strtolower($_SERVER["PHP_SELF"]),"crud") !== false ? "../" : (strpos(strtolower($_SERVER["PHP_SELF"]),$panel_folder) !== false ? "" : $panel_folder . "/"));
$variables_json = file_get_contents($root . "system/variables_" . $language["code"] . ".json");
$variables_array = json_decode($variables_json,true) ?? [];
foreach ($variables_array AS $key=>$values){
	$$key = $values;
}

//Predefined variables
if ($language["code"]=="ar"){
	//Arabic
	$data_no_yes = array("لا", "نعم");
	$data_yes_no = array("نعم", "لا");
	$data_disabled_enabled = array("معطل", "مفعل");
	$data_enabled_disabled = array("مفعل", "معطل");
	$data_active_inactive = array("نشط", "خامل");
	$data_inactive_active = array("خامل", "نشط");
	$data_new_closed = array("جديد", "مغلق");
	$data_week_days = array( 1 => "السبت", 2 => "الأحد", 3 => "الإثنين", 4 => "الثلاثاء", 5 => "الأربعاء", 6 => "الخميس", 7 => "الجمعة" );
	$data_months = array( 1 => "يناير", 2 => "فبراير", 3 => "مارس", 4 => "إبريل", 5 => "مايو", 6 => "يونيو", 7 => "يوليو", 8 => "اغسطس", 9 => "سبتمبر", 10 => "اكتوبر", 11 => "نوفمبر", 12 => "ديسمبر" );
	$data_module_types = array("تنسيق", "شريحة", "محتوي");
	$data_pages_types = array("صفحة ثانوية", "صفحة اساسية", "صفحة قسم");
	$data_displays_types = array("شبكة", "سلايدر");
	$data_displays_sources = array("صفحات ثانوية", "صفحات مختارة", "قوالب مخصصة", "استعلام مخصص", "نموذج عرض");
	$data_displays_slides_effect = array("إنزلاق", "مخصص", "مجسم", "تلاشي");	
} else {
	//English or any other language
	$data_no_yes = array("No", "Yes");
	$data_yes_no = array("Yes", "No");
	$data_disabled_enabled = array("Disabled", "Enabled");
	$data_enabled_disabled = array("Enabled", "Disabled");
	$data_active_inactive = array("Active", "Inactive");
	$data_inactive_active = array("Inactive", "Active");
	$data_new_closed = array("New", "Closed");
	$data_week_days = array( 1 => "Saturday", 2 => "Sunday", 3 => "Monday", 4 => "Tuesday", 5 => "Wednesday", 6 => "Thursday", 7 => "Friday" );
	$data_months = array( 1 => "January", 2 => "February", 3 => "March", 4 => "April", 5 => "May", 6 => "June", 7 => "July", 8 => "August", 9 => "September", 10 => "October", 11 => "November", 12 => "December" );
	$data_module_types = array("Layout", "Section", "Content");
	$data_pages_types = array("Child Page", "Standard Page", "Contents Page");
	$data_displays_types = array("Grid", "Slider");
	$data_displays_sources = array("Child Pages", "Selected Pages", "Custom Modules", "Custom Query", "Display Template");
	$data_displays_slides_effect = array("Standard Slide", "Custom", "3D", "Fade");	
}
?>