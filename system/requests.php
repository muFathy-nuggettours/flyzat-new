<?
//Set on mobile to true if sent via post request, since we can't know if the AJAX request is coming from the application or not
//AJAX on_mobile is automatically set from setup.js
$on_mobile = $post["on_mobile"];

//===== Push Notifications =====
if ($post["action"]=="push"){
	$user_id = $logged_user["id"];
	$platform = ($on_mobile ? 1 : 0);
	$user_agent = $_SERVER["HTTP_USER_AGENT"];
	$token_query = mysqlQuery("SELECT id FROM users_push_notifications WHERE token='" . $post["push_token"] . "'");
	if (mysqlNum($token_query)){
		$record_id = mysqlFetch($token_query)["id"];
		mysqlQuery("UPDATE users_push_notifications SET
			user_id='$user_id',
			platform='$platform',
			user_agent='$user_agent',
			date='" . time() . "'
		WHERE id=$record_id");
	
	} else {
		mysqlQuery("INSERT INTO users_push_notifications (
			user_id,
			platform,
			user_agent,
			token,
			date
		) VALUES (
			'$user_id',
			'$platform',
			'$user_agent',
			'" . $post["push_token"] . "',
			'" . time() . "'
		)");
	}
}

//===== Social Media Login =====
if ($post["action"]=="social"){
	//Set this variable to false to automatically signup user without completing profile
	$require_completion = true;
	
	//Assign target platform
	switch ($post["platform"]){
		//FACEBOOK
		case "facebook":
			$platform = "facebook";
			$token = file_get_contents("https://graph.facebook.com/debug_token?input_token=" . $post["access_token"] . "&access_token=" . $system_settings["facebook_app_id"] . "|" . $system_settings["facebook_app_secret"]);
			$token_array = json_decode($token, true);
			$valid_token = $token_array["data"]["user_id"];
			
			//Read facebook user information
			if ($valid_token){
				$user_profile = file_get_contents("https://graph.facebook.com/v3.1/me?fields=id,name,email,picture.width(400).height(400)&access_token=" . $post["access_token"]);
				$user_info = json_decode($user_profile, true);
				$user["user_id"] = $user_info["id"];
				$user["name"] = $user_info["name"];
				$user["email"] = $user_info["email"];
				$user["picture"] = $user_info["picture"]["data"]["url"];
			}
		break;
		
		//GOOGLE
		case "google":
			$platform = "google";
			$token = file_get_contents("https://oauth2.googleapis.com/tokeninfo?id_token=" . $post["access_token"]);
			$token_array = json_decode($token, true);
			$valid_token = $token_array["sub"];
			
			//Read google user information
			if ($valid_token){
				$user_info = $token_array;
				$user["user_id"] = $user_info["sub"];
				$user["name"] = $user_info["name"];
				$user["email"] = $user_info["email"];
				$user["picture"] = $user_info["picture"];				
			}
		break;
		
		default:
			header("HTTP/1.1 400 Bad Request");
			exit();		
	}

	//Validate user data
	if (!($user["user_id"] && $user["name"] && $user["email"])){
		header("HTTP/1.1 400 Bad Request");
		exit();
	
	//Proceed with signup/login
	} else {
		$target_user = mysqlFetch(mysqlQuery("SELECT * FROM users_database WHERE email='" . $user["email"] . "' OR $platform='" . $user["user_id"] . "'"));
		
		//Existing connected user (user already bound social account)
		if ($target_user){
			//Update user platform if not set
			if (!$target_user["platform"]){
				mysqlQuery("UPDATE users_database SET $platform='" . $user["user_id"] . "' WHERE id=" . $target_user["id"]);
			}
			
			//Login user
			userLogin($target_user["hash"], true);
			
			//Return login success message
			$return = "<center><div class='alert alert-success'>" . readLanguage(accounts,login_success) . "</div>
				<div class='login_message social'>
					<img src='" . ($target_user["image"] ? "uploads/users/" . $target_user["image"] : "images/user.png") . "'>
					<span>" . readLanguage(accounts,welcome) . " <b>" . $target_user["name"] . "</b></span>
				</div>" . readLanguage(accounts,redirect) . "</center>";
			
			//Website
			if (!$post["mobile"]){
				$return .= "<script>window.setTimeout(function(){
					setWindowLocation('" . ($_SESSION[$redirect_session] ? $_SESSION[$redirect_session] : ".") . "');
				}, 2000)</script>";
			
			//Mobile application
			} else {
				$return .= "<script>$('.mainWebview.active')[0].contentWindow.userLoggedIn('" . $target_user["hash"] . "');
				window.setTimeout(function(){
					setWebviewURL('" . ($_SESSION[$redirect_session] ? $_SESSION[$redirect_session] : ".") . "', true);
				}, 2000)</script>";
			}
			
			echo $return;

		//New user
		} else {
			//Website
			if (!$post["mobile"]){
				$return = "<script>postForm({
					action: '" . ($require_completion ? "" : "signup") . "',
					platform: '$platform',
					user_id: '" . $user["user_id"] . "',
					name: '" . $user["name"] . "',
					email: '" . $user["email"] . "',
					picture: '" . $user["picture"] . "'
				}, 'signup/', 'post', null, false)</script>";
			
			//Mobile application
			} else {
				$return = "<script>
					$('.mainWebview.active')[0].contentWindow.postForm({
						action: '" . ($require_completion ? "" : "signup") . "',
						platform: '$platform',
						user_id: '" . $user["user_id"] . "',
						name: '" . $user["name"] . "',
						email: '" . $user["email"] . "',
						picture: '" . $user["picture"] . "'
					}, 'signup/', 'post', null, false)</script>";
			}
			
			echo $return;
		}
	}
}

//===== Newsletter =====
if ($post["action"]=="newsletter"){
	$rules["email"] = array("required", "max_length(100)", "email");
	$validation_result = SimpleValidator\Validator::validate($post,$rules);
	if ($validation_result->isSuccess()==true){
		if (!mysqlNum(mysqlQuery("SELECT * FROM channel_newsletter WHERE email='" . strtolower($post["email"]) . "'"))){
			$query = "INSERT INTO channel_newsletter (
				email,
				user_id,
				date
			) VALUES (
				'" . strtolower($post["email"]) . "',
				'" . ($logged_user ? $logged_user["user_id"] : "") . "',
				'" . time() . "'
			)";
			mysqlQuery($query);
			exit(readLanguage(newsletter,success));
		} else {
			header("HTTP/1.1 400 Bad Request");
			exit(readLanguage(newsletter,error));
		}
	}
}

//===== Form =====
if ($post["action"]=="form"){
	$form_data = getData($suffix . "website_forms", "uniqid", $post["form"]);
	$can_submit = customFormCheck($form_data);

	if ($can_submit){
		//Parse content as array
		$content = json_decode($post["content"], true);
		
		//Upload files
		$filenames = json_decode($post["filenames"]);
		foreach ($filenames AS $filename){
			if ($_FILES[$filename]["name"]){
				if (!validateFileName($_FILES[$filename]["name"])){
					header("HTTP/1.1 400 Bad Request");
					exit(readLanguage(plugins,upload_error_extension));
				} else if (!$_FILES[$filename]["size"] || $_FILES[$filename]["size"] > parseSize(ini_get("upload_max_filesize"))){
					header("HTTP/1.1 400 Bad Request");
					exit(readLanguage(plugins,upload_error_size) . ini_get("upload_max_filesize"));
				} else {
					$content[$filename] = fileUpload($_FILES[$filename], "uploads/forms/");
				}
			}
		}
		
		//Insert submission
		$query = "INSERT INTO " . $suffix . "website_forms_records (
			form_id,
			content,
			user_id,
			ip,
			date
		) VALUES (
			'" . $form_data["id"] . "',
			'" . json_encode($content, JSON_UNESCAPED_UNICODE) . "',
			'" . $logged_user["id"] . "',
			'" . getClientIP() . "',
			'" . time() . "'
		)";
		mysqlQuery($query);
		mysqlQuery("UPDATE " . $suffix . "website_forms SET records=records+1 WHERE id=" . $form_data["id"]);
		
		writeCookie($form_data["uniqid"], md5($form_data["uniqid"] . rand(1000,9999)), time() + (86400 * 365));
		
		exit(htmlContent($form_data["success_message"]));
	} else {
		header("HTTP/1.1 400 Bad Request");
		exit("Failed to submit form, please try again later..");
	}
}

//===== Get User Data [Used for mobile application] =====
if ($post["action"]=="get-user-data"){
	$user_data = mysqlFetch(mysqlQuery("SELECT * FROM users_database WHERE hash='" . $post["hash"] . "'"));
	$required_data = ["name", "email", "image"];
	$return = array();
	foreach ($required_data as $key=>$value){
		array_push($return, $user_data[$value]);
	}
	echo json_encode($return, JSON_UNESCAPED_UNICODE);
}

//===== Write Cookie [Used for mobile application] =====
if ($post["action"]=="set-cookie"){
	writeCookie($post["name"], $post["value"] , time() + $post["expires"]);
}
?>