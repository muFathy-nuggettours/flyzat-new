<?
//Additional page variables from database
$global_parameters["website_information"] = $website_information;
$global_parameters["logged_user"] = $logged_user;

//========== Custom user sessions ==========

$geo_session = $session_prefix . "USER_GEO_DATA";
$search_cookie = $session_prefix . "USER_SEARCH_HISTORY";

//========== User country and currency ==========

$user_ip = getClientIP();
/*
$user_ip = "196.128.5.106"; //EG - For Debugging Purposes
$user_ip = "1.179.127.255"; //Australia - For Debugging
$user_ip = "104.115.31.255"; //KSA - For Debugging
$user_ip = "196.128.5.106"; //EG - For Debugging Purposes
*/

//If user is logged in, get country and currency from account
if ($logged_user){
	$user_countryCode = $logged_user["user_country"];
	$user_currencyCode = $logged_user["user_currency"];

//Otherwise check geoplugin
} else {
	//Read existing session
	if ($_SESSION[$geo_session]){
		$session_value = explode(",", $_SESSION[$geo_session]);
		$geo_data["geoplugin_countryCode"] = $session_value[0];
		$geo_data["geoplugin_currencyCode"] = $session_value[1];
		$geo_data["geoplugin_currencyConverter"] = floatval($session_value[2]);
	}

	//Reload geoplugin data if not set
	if (!$geo_data["geoplugin_countryCode"] || !$geo_data["geoplugin_currencyCode"] || is_numeric($geo_data["geoplugin_currencyConverter"])==0){
		$geo_data = json_decode(file_get_contents("http://www.geoplugin.net/json.gp?ip=$user_ip"),true);
		$session_value =  $geo_data["geoplugin_countryCode"] . "," . $geo_data["geoplugin_currencyCode"] . "," . $geo_data["geoplugin_currencyConverter"];
		$_SESSION[$geo_session] = $session_value;
	}

	//Set user geoplugin data
	$user_countryCode = strtolower($geo_data["geoplugin_countryCode"]);
	$user_currencyCode = strtoupper($geo_data["geoplugin_currencyCode"]);

	//Fallback in case geoplugin fails
	if (!$user_countryCode || !$user_currencyCode){
		$user_countryCode = "sa";
		$user_currencyCode = "SAR";
	}
}

//Set payment currency
$user_paymentCurrency = getData("system_payment_currencies", "code", $user_currencyCode);
if (!$user_paymentCurrency){
	$user_paymentCurrency = getData("system_payment_currencies", "code", "SAR");	
}

//========== Contact info by country ==========

$website_contact = mysqlFetch(mysqlQuery("SELECT * FROM website_contact WHERE country='$user_countryCode'"));
if ($website_contact){
	foreach ($website_contact AS $key=>$value){
		$website_information[$key] = $value;
	}
}

//========== User session ID ==========

$user_session_id = session_id();

//========== User search history ==========

$search_history = array();
if (isset($_COOKIE[$search_cookie])){
	$search_history = json_decode(base64_decode($_COOKIE[$search_cookie]), true);
}
?>