<?
//Panel language options
$language = languageOptions($panel_language);

//Read language file
$root = (strpos(strtolower($_SERVER["PHP_SELF"]),"crud") !== false ? "../" : "");
$language_json = file_get_contents($root . "core/languages/$panel_language.json");
$language_array = json_decode($language_json,true);

//Read default database language options
if ($get["language"] && in_array($get["language"], $supported_languages)){
	$_SESSION[$database_language_session] = $get["language"];
}
$database_language = languageOptions(($_SESSION[$database_language_session] && in_array($_SESSION[$database_language_session],$supported_languages) ? $_SESSION[$database_language_session] : $supported_languages[0]));

//Database suffix
$suffix = $database_language["suffix"];

//Apply localization
function readLanguage($category, $phrase, $array = []){
	global $language_array;
	$target = $language_array[$category][$phrase];
	if (!$target) return strtoupper($category . "_" . $phrase);

	foreach ($array AS $key => $value) {
		$target = str_replace("{{" . ($key + 1 ). "}}", $value, $target);
	}
	return stripslashes($target);
}
?>