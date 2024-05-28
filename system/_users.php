<?
//If cookie is set, set session to cookie value
if (isset($_COOKIE[$user_cookie])){
    $_SESSION[$user_session] = cleanString($_COOKIE[$user_cookie]);
}

//Fetch logged user data
$user_hash = ($_SESSION[$user_session] ? cleanString($_SESSION[$user_session]) : null);
if ($user_hash){ $logged_user = mysqlFetch(mysqlQuery("SELECT * FROM users_database WHERE hash='$user_hash' AND banned=0")); }

//Check if administrator is logged in
if ($_SESSION[$panel_session]){
	$panel_hash = ($_SESSION[$panel_session] ? cleanString($_SESSION[$panel_session]) : null);
	if ($panel_hash){ $logged_administrator = mysqlFetch(mysqlQuery("SELECT id FROM system_administrators WHERE hash='$panel_hash'"))["id"]; }
}

//If user is not present or banned, remove credentials
if (!$logged_user){
	$user_hash = null;
	$_SESSION[$user_session] = null;
	unsetCookie($user_cookie);
}

//User Login
function userLogin($user_hash, $write_cookie){
	global $user_session;
	global $user_cookie;
	$_SESSION[$user_session] = $user_hash;
	if ($write_cookie){
		writeCookie($user_cookie, $user_hash, time() + (86400 * 30));
	}
	return $user_hash;
}

//Pages that require login
function requireLogin($val){
	global $user_hash;
	global $base_url;
	if ($val){
		if (!$user_hash){
			header("Location:" . $base_url . ($get["language"] ? $get["language"] . "/" : "") . "login/");
			exit();
		}	
	} else {
		if ($user_hash){
			header("Location:" . $base_url . ($get["language"] ? $get["language"] . "/" : "") . "user/");
			exit();
		}	
	}
}
?>