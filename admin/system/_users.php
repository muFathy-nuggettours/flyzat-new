<?
//Set session value to saved cookie value if available
if (isset($_COOKIE[$panel_cookie])){
    $_SESSION[$panel_session] = cleanString($_COOKIE[$panel_cookie]);
}

//Retrieve user data from session
$user_hash = $_SESSION[$panel_session];
if ($user_hash){
	$logged_user = mysqlFetch(mysqlQuery("SELECT * FROM system_administrators WHERE hash='$user_hash'"));
}

//If no user is found, reset session and cookie
if (!$logged_user){
	$user_hash = null;
	$_SESSION[$panel_session] = null;
	unsetCookie($panel_cookie);
}

//Logout
if ($get["action"]=="logout"){
	$_SESSION[$panel_session] = null;
	unsetCookie($panel_cookie);
	header("Location: .");
	exit();
}

//User login function
function userLogin($user_hash, $write_cookie){
	global $panel_session;
	global $panel_cookie;
	$_SESSION[$panel_session] = $user_hash;
	if ($write_cookie){
		writeCookie($panel_cookie, $user_hash, time() + (86400 * 30));
	}
	return $user_hash;
}

//Load proper permissions
$user_permissions = explode(",", getID($logged_user["permission"], "system_permissions")["permissions"]);
?>