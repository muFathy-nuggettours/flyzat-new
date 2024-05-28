<? include "system/_handler.php";

//Security Measure: Validate post token
if (!$post["token"]){ brokenLink(); exit(); }

//========== [Air Search Request Operations] ==========

//Travelport Search
if ($post["action"]=="travelport_search"){
	$parameters = $post["parameters"];
	$response = travelportSearch($parameters);
	if ($response[0]){
		exit($response[1]);
	} else {
		header("HTTP/1.1 400 Bad Request");
		exit($response[1]);		
	}
}

//Travelport Search
if ($post["action"]=="custom_search"){
	$parameters = $post["parameters"];
	$response = customSearch($parameters);
	if ($response[0]){
		exit($response[1]);
	} else {
		header("HTTP/1.1 400 Bad Request");
		exit($response[1]);		
	}
}

//========== [Travelport Operations] ==========

//Travelport PNR
if ($post["action"]=="issue_pnr"){
	$valid_reservation = getID($post["reservation"], "flights_reservations");
	if ($valid_reservation){
		switch ($valid_reservation["so_platform"]){
			case 0:
				$response = customBook($post["reservation"]);
			break;
			
			case 1:
				$response = travelportBook($post["reservation"]);
			break;
		}
	}
	if ($valid_reservation && $response[0]){
		exit($response[1]);
	} else {
		header("HTTP/1.1 400 Bad Request");
		exit(($response[1] ? $response[1] : "Invalid Reservation"));		
	}
}

//Return flight warning
if ($post["action"]=="render_warning"){
	$explode = explode(",", $post["origin_destination"]);
	$warning = mysqlFetch(mysqlQuery("SELECT * FROM flights_warnings WHERE origin='" . $explode[0] . "' AND destination='" . $explode[1] . "' LIMIT 0,1"));
	if ($warning){
		$warning = htmlContent($warning["message"]);
		exit($warning);
	}
}

//Render flight details
if ($post["action"]=="flight_details"){
	exit(renderFlightDetails($post["flight"], $post["penalties"]));
}

//========== [Air Booking Request Operations] ==========

//Flight search booking request
if ($post["action"]=="flight_request"){
	$uniqid = md5($user_session_id . generateHash(6,2,2,2,0));
	mysqlQuery("DELETE FROM flights_reqeusts WHERE session='$uniqid'");
	$query = "INSERT INTO flights_reqeusts (
		session,
		search_object,
		selections,
		notes,
		date
	) VALUES (
		'" . $uniqid . "',
		'" . $post["search_object"] . "',
		'" . $post["selections"] . "',
		'" . $post["notes"] . "',
		'" . time() . "'
	)";
	mysqlQuery($query);
	exit($uniqid);
}

//Upload and parse passport image
if ($post["action"]=="upload_passport"){
	if (isset($_FILES["passport"]) && !empty($_FILES["passport"])){
		header("Access-Control-Allow-Origin: " . $_SERVER["HTTP_ORIGIN"]);
		
		if (!validateFileName($_FILES["passport"]["name"]) || !isImage($_FILES["passport"]["name"])){
			header("HTTP/1.1 400 Bad Request");
			exit(readLanguage(plugins,upload_error_extension));
			
		} else if (!$_FILES["passport"]["size"] || $_FILES["passport"]["size"] > parseSize(ini_get("upload_max_filesize"))){
			header("HTTP/1.1 400 Bad Request");
			exit(readLanguage(plugins,upload_error_size) . ini_get("upload_max_filesize"));			

		} else {
			$original_name = pathinfo($_FILES["passport"]["name"], PATHINFO_FILENAME);
			$storage_name = uniqid() . "." . strtolower(pathinfo($_FILES["passport"]["name"], PATHINFO_EXTENSION));
			$upload_path = "uploads/passports/" . $storage_name;
			$upload_file = move_uploaded_file($_FILES["passport"]["tmp_name"], $upload_path);
		}
	}
	
	if ($storage_name){
		$return["passport"] = $storage_name;

		//OCR
		$image_path = $base_url . "uploads/passports/" . $storage_name;
		$ocr_url = "https://api.ocr.space/parse/imageurl?apikey=" . $system_settings["ocr_space"] . "&ocrengine=2&url=$image_path";
		$ocr_result = json_decode(file_get_contents($ocr_url), true);
		$ocr_text = $ocr_result["ParsedResults"][0]["ParsedText"];
		$splits = explode("\n", $ocr_text);
		$splits_length = count($splits) - 1;
		$mrz = $splits[$splits_length - 1] . $splits[$splits_length];
		$mrz_clean = str_replace("«", "<<", $mrz);
		$mrz_clean = str_replace(" ", "", $mrz_clean);

		//Parse MRZ
		include("admin/snippets/mrz.php");
		$MRZ = new SolidusMRZ;
		$data = $MRZ->parseMRZ($mrz_clean);

		if ($data["mrzisvalid"]=="true"){
			$nationality = getData("system_database_countries", "iso", $data["nationality"]["abbr"]);
			$return["name_prefix"] = ($data["sex"]["abbr"]=="M" ? 1 : 2);
			$return["first_name"] = ucwords(strtolower($data["names"]["firstName"]));
			$return["last_name"] = ucwords(strtolower($data["names"]["lastName"]));
			$return["birth_date"] = date("j/n/Y", getTimeStamp($data["dob"], "d/m/Y"));
			$return["nationality"] = ($nationality ? $nationality["code"] : "us");
			$return["ssn"] = $data["documentNumber"];
			$return["ssn_end"] = date("j/n/Y", getTimeStamp($data["expiry"], "d/m/Y"));
		}

		exit(json_encode($return));
	} else {
		header("HTTP/1.1 400 Bad Request");
		exit(readLanguage(plugins,upload_error));
	}
}

//========== [Project Requests] ==========

//Search destinations
if ($post["action"]=="search_destinations"){
	$keyword_original = $post["search"];
	$keyword = normalizeString($post["search"]);
	if (!$keyword && count($search_history)){
		$array = array();
		foreach ($search_history AS $key=>$value){
			if (!in_array($value["from"])){
				array_push($array, strtoupper($value["from"]));
			}
			if (!in_array($value["to"])){
				array_push($array, strtoupper($value["to"]));
			}
		}
		$condition = "iata IN ('" . implode("','", $array) . "')";
		$order = "popularity DESC, priority DESC";
	} else {
		$condition = "keywords LIKE '%$keyword%' OR ar_name LIKE '%" . $post["search"] . "%'";
		$order = "LOCATE('$keyword', iata) DESC, popularity DESC, priority DESC";
	}
	$result = mysqlQuery("SELECT * FROM system_database_airports WHERE publish=1 AND active=1 AND ($condition) ORDER BY $order LIMIT 0,10");
	$return_data = array();
	while ($airport = mysqlFetch($result)){
		$country = getCountry($airport["country"]);
		$region = getID($airport["region"], "system_database_regions");
		$block_text = $airport[$suffix . "name"] . "، " . $country[$suffix . "name"];
		$block_html = "<div class=search_destinations>
			<i class='fas fa-plane fa-fw'></i>&nbsp;&nbsp;
			<img src='images/countries/" . $airport["country"] . ".gif'>&nbsp;&nbsp;
			<div class=title>
				<b>" . highlightKeyword($block_text, $keyword_original) . "</b>
				<small>" . $region[$suffix . "name"] . "</small>
			</div>
			<b class=code>" . $airport["iata"] . "</b>
		</div>";
		$data_array = array(
			"id" => $airport["iata"],
			"html" => $block_html,
			"text" => $block_text
		);
		array_push($return_data,$data_array);
	}
	$data["results"] = $return_data;
	echo json_encode($data);
}

//Get destination selection text
if ($post["action"]=="get_destination"){
	$airport = mysqlFetch(mysqlQuery("SELECT * FROM system_database_airports WHERE iata='" . $post["iata"] . "'"));
	$country = getCountry($airport["country"]);
	$block_text = $airport[$suffix . "name"] . "، " . $country[$suffix . "name"];
	echo ($airport ? $block_text : null);
}

//Get expressions for SEO
if ($post["action"]=="get_expressions"){
	$start = intval($post['start'] ? $post['start'] : 1);

	$valid_origins = array("country", "airport", "region");
	$post["origin_type"] = ($post["origin_type"] && in_array($post["origin_type"], $valid_origins) ? $post["origin_type"] : "country");
	$post["origin"] = ($post["origin"] ? $post["origin"] : $user_countryCode);
	if ($post["origin_type"]=="country"){
		$origin = mysqlFetch(mysqlQuery("SELECT *, " . $suffix . "name AS name FROM system_database_countries WHERE code='" . $post["origin"] . "'"));
	} elseif ($post["origin_type"]=="airport"){
		$origin = mysqlFetch(mysqlQuery("SELECT *, " . $suffix . "short_name AS name FROM system_database_airports WHERE iata='" . $post["origin"] . "'"));
	} elseif ($post["origin_type"]=="region"){
		$origin = mysqlFetch(mysqlQuery("SELECT *, " . $suffix . "name AS name FROM system_database_regions WHERE code='" . $post["origin"] . "'"));
	}
	
	$valid_destinations = array("country", "airport");
	$post["destination_type"] = ($post["destination_type"] && in_array($post["destination_type"], $valid_destinations) ? $post["destination_type"] : "country");
	if ($post["destination_type"]=="country"){
		$slugs = mysqlFetchAll(mysqlQuery("SELECT {$suffix}name AS name FROM system_database_countries WHERE code!='{$origin['code']}' AND publish=1 LIMIT 100 OFFSET $start"));
		$total = round(mysqlNum(mysqlQuery("SELECT id FROM system_database_countries WHERE code!='{$origin['code']}' AND publish=1")) / 100);
	} elseif ($post["destination_type"]=="airport"){
		$slugs = mysqlFetchAll(mysqlQuery("SELECT {$suffix}short_name AS name FROM system_database_airports WHERE iata!='{$origin['iata']}' AND publish=1 LIMIT 100 OFFSET $start"));
		$total = round(mysqlNum(mysqlQuery("SELECT id FROM system_database_airports WHERE iata!='{$origin['iata']}' AND publish=1")) / 100);
	}

	if ($start > $total) die(json_encode(["error" => true]));
	
	$expressions_count = mysqlNum(mysqlQuery("SELECT id FROM " . $suffix . "website_seo"));
	$expression = getData($suffix . 'website_seo', 'id', ($post['expression'] ? $post['expression'] : intval(rand(1, $expressions_count))), 'route');
	foreach($slugs as $destination){
        $res[] = [
			"name" => str_replace(["{1}", "{2}"], [$origin["name"], $destination["name"]], $expression),
			"url" => "s/" . createCanonical(str_replace(["{1}", "{2}"], [$origin["name"], $destination["name"]], $expression)) . "/"
		];
    }

    echo json_encode(["result" => $res, "total" => $total], JSON_UNESCAPED_UNICODE);
}

//User balance request
if ($post["action"]=="balance_request"){
	$valid = (is_numeric($post["amount"]) && $post["amount"] >=10 && $post["amount"] <= 99999);
	if ($valid){
		$uniqid = md5($user_session_id . generateHash(6,2,2,2,0));
		mysqlQuery("DELETE FROM users_balance_requests WHERE session='$uniqid'");
		$query = "INSERT INTO users_balance_requests (
			session,
			user_id,
			amount,
			date
		) VALUES (
			'" . $uniqid . "',
			'" . $logged_user["id"] . "',
			'" . $post["amount"] . "',
			'" . time() . "'
		)";
		mysqlQuery($query);
		exit($uniqid);
	} else {
		header("HTTP/1.1 400 Bad Request");
		exit("خطأ في القيمة المدخلة");
	}
}

//Mobile verification request
if ($post["action"]=="mobile_request_verification"){
	if (!$logged_user){
		header("HTTP/1.1 400 Bad Request");
		exit();		
	}
	$code = rand(100000, 999999);
	$sms_sent = sendSMS($logged_user["mobile"], $code)[1];
	if ($sms_sent){
		mysqlQuery("UPDATE users_database SET mobile_verification='$code' WHERE id=" . $logged_user["id"]);
	} else {
		header("HTTP/1.1 400 Bad Request");	
	}
	exit();
}

//========== [Core Requests] ==========

include "system/requests.php";
?>