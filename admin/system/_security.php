<?
//Clean string
function cleanString($value){
	$sanitized = str_replace(chr(0), "", $value); //Remove NULL bytes
	$sanitized = mysqlEscape($sanitized); //Escape string
	$sanitized = filter_var($sanitized, FILTER_SANITIZE_SPECIAL_CHARS); //Sanitize special characters
	return $sanitized;
}

//Sanitize string
function sanitizeString($value){
	$sanitized = str_replace(chr(0), "", $value); //Remove NULL bytes
	$sanitized = filter_var($sanitized, FILTER_SANITIZE_SPECIAL_CHARS); //Sanitize special characters
	return $sanitized;
}

//Validate file names [plugins/tinymce/upload.php]
function validateFileName($filename){
	global $allowed_extensions;
	global $upload_allow;
	$extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
	if ($upload_allow){
		$accepted_extensions = array_merge($upload_allow, $allowed_extensions);
	} else {
		$accepted_extensions = $allowed_extensions;
	}
	$accepted_extensions = in_array($extension, $accepted_extensions);
	$disallowed = preg_match("^[\p{L}\p{N}_\-.~]+$", $filename);
	$deny_pattern = preg_match("\.(php[3-6]?|phpsh|phtml)(\..*)?$|^\.htaccess$", $filename);
	return ($accepted_extensions && !$disallowed && !$deny_pattern);
}

//Encryption function
$cipher = "AES-128-CBC";
function encryptText($text, $key=null){
	global $private_key;
	global $cipher;
	$key = ($key ? $key : $private_key);
	$ivlen = openssl_cipher_iv_length($cipher);
	$iv = openssl_random_pseudo_bytes($ivlen);
	$ciphertext_raw = openssl_encrypt($text, $cipher, $key, $options=OPENSSL_RAW_DATA, $iv);
	$hmac = hash_hmac("sha256", $ciphertext_raw, $key, $as_binary=true);
	$ciphertext = base64_encode($iv.$hmac.$ciphertext_raw);
	return $ciphertext;
}

//Decryption function
function decryptText($text, $key=null){
	global $private_key;
	global $cipher;
	$key = ($key ? $key : $private_key);
	$c = base64_decode($text);
	$ivlen = openssl_cipher_iv_length($cipher);
	$iv = substr($c, 0, $ivlen);
	$hmac = substr($c, $ivlen, $sha2len=32);
	$ciphertext_raw = substr($c, $ivlen + $sha2len);
	$original_plaintext = openssl_decrypt($ciphertext_raw, $cipher, $key, $options=OPENSSL_RAW_DATA, $iv);
	$calcmac = hash_hmac("sha256", $ciphertext_raw, $key, $as_binary=true);
    return (hash_equals($hmac,$calcmac) ? $original_plaintext : null);
}

//Escap JSON
function escapeJson($string){
	$array = json_decode($string, true);
	array_walk_recursive($array, function(&$value, $key){
		$value = sanitizeString($value);
		$value = str_replace("\\","&#92;",$value); //Unicode Decimal Code \
	});
	return ($array ? json_encode($array, JSON_UNESCAPED_UNICODE) : "");
}

//Unescape JSON
function unescapeJson($string){ //For reading php json in a javascript code
	$array = json_decode($string,true);
	array_walk_recursive($array, function(&$value, $key){
		$value = html_entity_decode($value);
		$value = addslashes($value);
		$value = str_replace("&#39;", "'", $value); //Parse Unicode Decimal Code '
	});
	return ($array ? json_encode($array, JSON_UNESCAPED_UNICODE | JSON_HEX_APOS) : ""); //JSON_HEX_APOS avoid escaping single quotes
}

//Generate CSRF token
if (!$_SESSION["token"]){
	$token = substr(base_convert(sha1(uniqid(mt_rand())),16,36),0,32); 
	$_SESSION["token"] = $token;
} else {
	$token = $_SESSION["token"];
}

//Clean post requests on valid token
if (($_POST["token"] && $_POST["token"]===$_SESSION["token"]) || $skip_csrf_token==true){
	foreach ($_POST as $key => $value){
		if (is_array($value)){
			$json = json_encode($value, JSON_UNESCAPED_UNICODE);
			$clean_json = escapeJson($json);
			$post[$key] = json_decode($clean_json, true);
		} else if (isJson($value)){
			$post[$key] = escapeJson($value);
		} else {
			$post[$key] = cleanString($value);
			$post_sanitized[$key] = sanitizeString($value);
		}
	}
}

//Clean GET requests
foreach ($_GET as $key => $value){
	$get[$key] = cleanString($value);
	$get_sanitized[$key] = sanitizeString($value);
}
$edit = intval($get["edit"]);

//Set get request delete variable
if ($get["delete"] && $get["token"]===$_SESSION["token"]){
	$delete = intval($get["delete"]);
}
?>