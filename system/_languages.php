<?
$website_language = strtolower(substr($get["language"],0,2));

//Revert to default language if not supported or is default
if ($website_language){
	if (!in_array($website_language,$supported_languages) || $website_language==$supported_languages[0]){
		$website_language = $supported_languages[0];
		$language = languageOptions($website_language);
		$suffix = $language["suffix"];
		$language_json = file_get_contents($root_path . "core/languages/$website_language.json");
		$language_array = json_decode($language_json, true);
		brokenLink();
	}
} else {
	$website_language = $supported_languages[0];
}

//Set language properties
$language = languageOptions($website_language);
$suffix = $language["suffix"];

//Read core language file
$language_json = file_get_contents($root_path . "core/languages/$website_language.json");
$language_array = json_decode($language_json, true);

//Append website language file
if (file_exists($root_path . "website/languages/$website_language.json")){
	$enable_localization = true;
	$language_json = file_get_contents($root_path . "website/languages/$website_language.json");
	$website_language_array = json_decode($language_json, true);
	$language_array = array_replace_recursive($language_array, $website_language_array);
}

//Read language function
function readLanguage($category, $phrase, $array=null){
	global $language_array;
	$phrase = $language_array[$category][$phrase];
	if (!$phrase){
		$phrase = "{{" . $category . "_" . $phrase . "}}";
	} else {
		if ($array){
			foreach ($array AS $key => $value){
				$phrase = str_replace("{{" . ($key + 1 ). "}}", $value, $phrase);
			}
		}
		return stripslashes($phrase);
	}
}
?>