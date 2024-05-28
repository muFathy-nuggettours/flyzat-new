<? include "system/_handler.php";

//Security Measure: Allow via POST requests & for logged users only
if (!$post["token"] || !$logged_user){ brokenLink(); exit(); }

//========= Core Requests =========

include "system/_requests.php";

//========== Project Requests ==========

//Get Country Regions
if ($post["action"]=="get_regions"){
	$result = mysqlQuery("SELECT * FROM system_database_regions WHERE country='" . $post["country"] . "'");
	while ($entry = mysqlFetch($result)){
		$regions[intval($entry["id"])] = $entry["ar_name"];
	}
	echo json_encode($regions, JSON_UNESCAPED_UNICODE);
}

//Search Airports
if ($post["action"]=="search_regions"){
	$normalized =  normalizeString($post["search"]);
	$result = mysqlQuery("SELECT * FROM system_database_regions WHERE
		code='" . $post["search"] . "' OR
		keywords LIKE '%" . $post["search"] . "%' OR
		ar_name LIKE '%" . $post["search"] . "%' OR
		code='$normalized' OR
		keywords LIKE '%$normalized%' OR
		ar_name LIKE '%$normalized%'
	ORDER BY CASE WHEN code='" . $post["search"] . "' THEN 1 ELSE 2 END, popularity DESC, priority DESC LIMIT 0,10");
	$return_data = array();
	$keyword = $post["search"];
	while ($entry = mysqlFetch($result)){
		$block_html = "<div class=search_box><b>" . highlightKeyword($entry["code"] . " - " . $entry["ar_name"], $keyword) . "</b><div>" .
			"<p><i class='fas fa-globe-africa'></i><span>" . getData("system_database_countries", "code", $entry["country"], "ar_name") . "</span></p>" .
		"</div></div>";
		$data_array = array(
			"id" => $entry["id"],
			"code" => $entry["code"],
			"html" => $block_html,
			"text" => $entry["code"] . " - " . $entry["ar_name"]
		);
		array_push($return_data,$data_array);
	}
	$data["results"] = $return_data;
	echo json_encode($data);
}

//Search Airports
if ($post["action"]=="search_airports"){
	$normalized =  normalizeString($post["search"]);
	$result = mysqlQuery("SELECT * FROM system_database_airports WHERE
		iata='" . $post["search"] . "' OR
		keywords LIKE '%" . $post["search"] . "%' OR
		ar_name LIKE '%" . $post["search"] . "%' OR
		iata='$normalized' OR
		keywords LIKE '%$normalized%' OR
		ar_name LIKE '%$normalized%'
	ORDER BY CASE WHEN iata='" . $post["search"] . "' THEN 1 ELSE 2 END, popularity DESC, priority DESC LIMIT 0,10");
	$return_data = array();
	$keyword = $post["search"];
	while ($entry = mysqlFetch($result)){
		$block_html = "<div class=search_box><b>" . highlightKeyword($entry["iata"] . " - " . $entry["ar_name"], $keyword) . "</b><div>" .
			"<p><i class='fas fa-map-marker-alt'></i><span>" . highlightKeyword($entry["ar_short_name"], $keyword) . "</span></p>" .
			"<p><i class='fas fa-globe-africa'></i><span>" . getData("system_database_countries", "code", $entry["country"], "ar_name") . "</span></p>" .
		"</div></div>";
		$data_array = array(
			"id" => $entry["id"],
			"iata" => $entry["iata"],
			"html" => $block_html,
			"text" => $entry["iata"] . " - " . $entry["ar_name"]
		);
		array_push($return_data,$data_array);
	}
	$data["results"] = $return_data;
	echo json_encode($data);
}

//Search Airlines
if ($post["action"]=="search_airlines"){
	$normalized =  normalizeString($post["search"]);
	$result = mysqlQuery("SELECT * FROM system_database_airlines WHERE
		iata='" . $post["search"] . "' OR
		keywords LIKE '%" . $post["search"] . "%' OR
		ar_name LIKE '%" . $post["search"] . "%' OR
		iata='$normalized' OR
		keywords LIKE '%$normalized%' OR
		ar_name LIKE '%$normalized%'
	ORDER BY CASE WHEN iata='" . $post["search"] . "' THEN 1 ELSE 2 END, popularity DESC, priority DESC LIMIT 0,10");
	$return_data = array();
	$keyword = $post["search"];
	while ($entry = mysqlFetch($result)){
		$block_html = "<div class=search_box><b>" . highlightKeyword($entry["iata"] . " - " . $entry["ar_name"], $keyword) . "</b><div>" .
			"<p><i class='fas fa-globe-africa'></i><span>" . getData("system_database_countries", "code", $entry["country"], "ar_name") . "</span></p>" .
		"</div></div>";
		$data_array = array(
			"id" => $entry["id"],
			"iata" => $entry["iata"],
			"html" => $block_html,
			"text" => $entry["iata"] . " - " . $entry["ar_name"]
		);
		array_push($return_data,$data_array);
	}
	$data["results"] = $return_data;
	echo json_encode($data);
}

//Search Website Users
if ($post["action"]=="search_users"){
	$normalized =  normalizeString($post["search"]);
	$result = mysqlQuery("SELECT * FROM users_database WHERE banned=0 AND (
			user_id LIKE '%" . $post["search"] . "%' OR
			email LIKE '%" . $post["search"] . "%' OR
			mobile LIKE '%" . $post["search"] . "%' OR
			name LIKE '%" . $post["search"] . "%' OR
			user_id LIKE '%$normalized%' OR
			email LIKE '%$normalized%' OR
			mobile LIKE '%$normalized%' OR
			name LIKE '%$normalized%'
		) LIMIT 0,10");
	$return_data = array();
	$keyword = $post["search"];
	while ($entry = mysqlFetch($result)){
		$block_html = "<div class=search_box><b>" . highlightKeyword($entry["name"], $keyword) . "</b><div>" .
			"<p><i class='fas fa-user'></i><span>" . highlightKeyword($entry["user_id"], $keyword) . "</span></p>" .
			"<p><i class='fas fa-envelope'></i><span>" . ($entry["email"] ? highlightKeyword($entry["email"], $keyword) : readLanguage(general,na)) . "</span></p>" .
			"<p><i class='fas fa-mobile-alt'></i><span class='d-inline-block force-ltr'>" . ($entry["mobile"] ? highlightKeyword($entry["mobile"], $keyword) : readLanguage(general,na)) . "</span></p>" .
		"</div></div>";
		$data_array = array(
			"id" => $entry["id"],
			"user_id" => $entry["user_id"],
			"html" => $block_html,
			"text" => $entry["name"],
			"entry" => $entry
		);
		array_push($return_data,$data_array);
	}
	$data["results"] = $return_data;
	echo json_encode($data);
}
?>