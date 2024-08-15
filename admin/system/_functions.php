<?
//==================================================
//========== Database Functions ==========
//==================================================

/**
 * Performs a query on the database.
 * 
 * @param string $query
 * @return mysqli_result|bool
 */
function mysqlQuery($query){
	global $connection;
	$GLOBALS['queryCount'] = $GLOBALS['queryCount'] + 1;
	return mysqli_query($connection, $query);
}


/**
 * Fetch a result row as an associative array.
 * 
 * @param mysqli_result $query
 * @return array|null|false
 */
function mysqlFetch($query){
	return mysqli_fetch_assoc($query);
}


/**
 * Gets the number of rows in a result.
 * 
 * @param mysqli_result $query
 * @return int|string
 */
function mysqlNum($query){
	return mysqli_num_rows($query);
}


/**
 * Escapes special characters in a string for use in an SQL statement,
 * taking into account the current charset of the connection.
 * 
 * @param string $query
 * @return string
 */
function mysqlEscape($query){
	global $connection;
	return mysqli_real_escape_string($connection, $query);
}


/**
 * Selects the default database for database queries.
 * 
 * @param string $database
 */
function mysqlSelectDatabase($database){
	global $connection;
	return mysqli_select_db($connection, $database);
}


/**
 * Closes a previously opened database connection.
 * 
 * @return bool
 */
function mysqlClose(){
	global $connection;
	return mysqli_close($connection);
}


/**
 * Gets the number of affected rows in a previous MySQL operation.
 * 
 * @return int|string
 */
function mysqlAffectedRows(){
	global $connection;
	return mysqli_affected_rows($connection);
}


/**
 * Fetches all result rows as an associative array,
 * a numeric array, or both.
 * 
 * @param mysqli_result $query
 * @param ?string $column
 * @return array
 */
function mysqlFetchAll($query, $column = null, $callaback = null){
    while ($row = mysqlFetch($query)){
		$value = ($row[$column] ?? $row);
        $result[] = !is_null($callaback) ? call_user_func($callaback, $value) : $value;
    }

    return ($result ?? []);
}


/**
 * Get total number of queries
 * 
 * @return int
 */
function queriesCount(){
	return $GLOBALS['queryCount'] ?? 0;
}


/**
 * Get MySQLi error if any
 * 
 * @return string
 */
function mysqlError(){
	global $connection;
	return mysqli_error($connection);
}


/**
 * Get record from table when column = value
 * 
 * @param string $table
 * @param string $key
 * @param $value
 * @param ?string $columns
 */
function getData($table, $key, $value, $columns = null){
	$columns = ($columns ?: '*');
	$record = mysqlFetch(mysqlQuery("SELECT $columns FROM $table WHERE $key = '$value'"));
	return ($record[$columns] ?? $record);
}


/**
 * Get record from table by specific ID
 * 
 * @param int $id
 * @param string $table
 * @param ?string $columns;
 */
function getID($id, $table, $columns = null){
	return getData($table, 'id', $id, $columns);
}


/**
 * Get custom data representation
 * 
 * @param string $column
 * @param string $table
 * @param string $key
 * @param $value
 * @param ?string $page
 * @param ?string $url
 * @return string|void
 */
function getCustomData($column, $table, $key, $value, $page = null, $url = null){
	$record = getData($table, $key, $value, "id, $column");
	if (!$record) return;

	$text = "<div>$record[$column]</div>";
	$button = "<div class=hide_pdf><a href='" . ($url ?: "$page.php?id={$record["id"]}") . "' class='btn btn-primary btn-sm hide_pdf' data-fancybox data-type=iframe><i class='fas fa-search'></i></a>&nbsp;&nbsp;</div>";
	return "<div class='flex-center justify-content-start'>$button $text</div>";;
}


/**
 * Fetch data from a settings table
 * 
 * @param string $table
 */
function fetchData($table){
	$result = mysqlQuery("SELECT * FROM $table ORDER BY id DESC");
	while ($entry = mysqlFetch($result)){
		$data[$entry["title"]] = $entry["content"];
	}

	return $data;
}


/**
 * Reverse mysqli_real_escape_string
 * 
 * @param string $string
 * @return string
 */
function unescapeString($string){
    $characters = array('x00','n','r','\\','\'','"','x1a');
    $o_chars = array("\x00","\n","\r","\\","'","\"","\x1a");
    for ($i = 0; $i < strlen($string); $i++){
        if (substr($string, $i, 1) == '\\'){
            foreach ($characters as $index => $char){
                if ($i <= strlen($string) - strlen($char) && substr($string, $i + 1, strlen($char)) == $char){
                    $string = substr_replace($string, $o_chars[$index], $i, strlen($char) + 1);
                    break;
                }
            }
        }
    }

    return $string;
}


/**
 * Return new record ID from AUTO_INCREMENT
 * 
 * @param string $table
 * @return int
 */
function newRecordID($table){
	global $mysqldatabase;
	mysqlQuery("SET PERSIST information_schema_stats_expiry = 0");
	$increment = mysqlFetch(mysqlQuery("SELECT AUTO_INCREMENT FROM information_schema.TABLES WHERE (TABLE_SCHEMA='$mysqldatabase' AND TABLE_NAME='$table')"))["AUTO_INCREMENT"];
	return ($increment ? $increment : 1);
}


/**
 * Reset table IDs
 * 
 * @param string $table
 */
function resetTableIDs($table){
	$increment = mysqlFetch(mysqlQuery("SELECT id FROM $table ORDER BY id DESC LIMIT 0,1"))["id"] + 10;
	mysqlQuery("UPDATE system_settings SET id=id+$increment");
	$result = mysqlQuery("SELECT id FROM $table ORDER BY id ASC");
	while ($entry = mysqlFetch($result)){
		$id++;
		mysqlQuery("UPDATE $table SET id=$id WHERE id=" . $entry["id"]);
	}
	mysqlQuery("ALTER TABLE $table AUTO_INCREMENT=1");
}


/**
 * Generate user ID
 * 
 * @param string $table
 */
function generateUserID($table){
	$new_record = newRecordID($table);
	$user_id = rand(10, 99) . str_pad($new_record, 5, "0", STR_PAD_LEFT) . rand(1, 9);
	return $user_id;
}

function writeCookie($name, $value, $expires, $path="/", $domain=null, $secure=false, $httponly=false, $samesite=null){
	global $on_mobile;
	
	//Mobile overrides
	if ($on_mobile){
		$domain = str_replace("www.", "", strtolower($_SERVER['SERVER_NAME']));
		$secure = true;
		$httponly = false;
		$samesite= "None";
	}
	
	//Force secure when samesite is none
	if (strtolower($samesite)=="none" && !$secure){
		$secure = true;
	}
	
	//Hack for older php versions
    if (PHP_VERSION_ID < 70300){
        setcookie($name, $value, $expires, $path . ($samesite ? "; samesite=$samesite" : ""), $domain, $secure, $httponly);
        return true;
    
	//Standard options method
	} else {
		$options = [
			"expires" => $expires,
			"secure" => $secure,
			"httponly" => $httponly,
			"path" => $path,
			"domain" => $domain
		];
		
		if ($samesite){
			$options["samesite"] = $samesite;
		}
		
		$result = setcookie($name, $value, $options);
		return $result;
	}
}

function unsetCookie($name){
	unset($_COOKIE[$name]);
	writeCookie($name, "", time() - 3600);
}


//==================================================
//========== Language Functions ==========
//==================================================

/**
 * Read language properties
 * 
 * @param string $code
 * @return array
 */
function languageOptions($code){
	if (function_exists("customLanguageOptions")){
		return customLanguageOptions($code);
	}

	switch ($code){
		case "en":
			$options["code"] = "en";
			$options["name"] = "English";
			$options["dir"] = "ltr";
			$options["suffix"] = "en_";
		break;

		case "ar":
			$options["code"] = "ar";
			$options["name"] = "العربية";
			$options["dir"] = "rtl";
			$options["suffix"] = "ar_";
		break;
	}

	return $options;
}

//==================================================
//========== CRUD Functions ==========
//==================================================

/**
 * Render view button
 * 
 * @param string $url
 * @param string $text
 * @param string $class
 * @param string $icon
 * @param string $additional_tags
 * @return string
 */
function viewButton($url, $text, $class, $icon, $attributes = ''){
	return "<a class='btn $class btn-block btn-sm' href='$url' data-fancybox data-type=iframe $attributes>" . ($icon ? "<i class='$icon'></i>&nbsp;&nbsp;" : "") . "$text</a>";
}


/**
 * Render status label
 * 
 * @param string $text
 * @param string $class
 * @return string
 */
function statusLabel($text, $class){
	return "<span class='label $class d-block' style='padding:10px'>$text</span>";
}


/**
 * Get value from variable
 * 
 * @param string $name
 * @param bool $return_null
 */
function getVariable($name, $return_null = false){
	global $$name;
	$variable = $$name;
	if ($return_null) $variable[0] = null;
	return $variable;
}


/**
 * Check value
 * 
 * @param $value
 * @param $empty
 * @param $assigned
 */
function hasVal($value, $empty, $assigned){
	return ($value ? $assigned : $empty);
}

//==================================================
//========== Date Functions ==========
//==================================================

/**
 * Return elapsed time
 * 
 * @param int $time
 * @return string
 */
function timeElapsed($time){
	global $website_language;
    $current_time= time();
    $time_elapsed = $current_time - $time;
    $seconds = $time_elapsed ;
    $minutes = round($time_elapsed / 60);
    $hours = round($time_elapsed / 3600);
    $days = round($time_elapsed / 86400);
    $weeks = round($time_elapsed / 604800);
    $months = round($time_elapsed / 2600640);
    $years = round($time_elapsed / 31207680);

	//Arabic
	$elapsed["ar"]["now"] = "اقل من دقيقة";
	$elapsed["ar"]["minute"] = "منذ دقيقة";
	$elapsed["ar"]["hour"] = "منذ ساعة";
	$elapsed["ar"]["yesterday"] = "امس";
	$elapsed["ar"]["week"] = "منذ اسبوع";
	$elapsed["ar"]["month"] = "منذ شهر";
	$elapsed["ar"]["year"] = "منذ عام";
	$elapsed["ar"]["xminute"] = "منذ {x} دقيقة";
	$elapsed["ar"]["xhour"] = "منذ {x} ساعة";
	$elapsed["ar"]["xday"] = "منذ {x} يوم";
	$elapsed["ar"]["xweek"] = "منذ {x} اسبوع";
	$elapsed["ar"]["xmonth"] = "منذ {x} اشهر";
	$elapsed["ar"]["xyear"] = "منذ {x} عام";

	//English
	$elapsed["en"]["now"] = "Less than a Minute ago";
	$elapsed["en"]["minute"] = "About a Minute ago";
	$elapsed["en"]["hour"] = "About an Hour ago";
	$elapsed["en"]["yesterday"] = "Yesterday";
	$elapsed["en"]["week"] = "About a Week ago";
	$elapsed["en"]["month"] = "About a Month ago";
	$elapsed["en"]["year"] = "About a Year ago";
	$elapsed["en"]["xminute"] = "About {x} Minutes ago";
	$elapsed["en"]["xhour"] = "About {x} Hours ago";
	$elapsed["en"]["xday"] = "About {x} Days ago";
	$elapsed["en"]["xweek"] = "About {x} Weeks ago";
	$elapsed["en"]["xmonth"] = "About {x} Months ago";
	$elapsed["en"]["xyear"] = "About {x} Years ago";

	$messages = $elapsed[$website_language];

	if ($seconds <= 60) return $messages["now"];
	if ($minutes <= 60) return str_replace("{x}", $minutes, ($minutes == 1 ? $messages["minute"] : $messages["xminute"]));
	if ($hours <= 24) return str_replace("{x}", $hours, ($hours == 1 ? $messages["hour"] : $messages["xhour"]));
	if ($days <= 7) return str_replace("{x}", $days, ($days == 1 ? $messages["yesterday"] : $messages["xday"]));
	if ($weeks <= 4.3) return str_replace("{x}", $weeks, ($weeks == 1 ? $messages["week"] : $messages["xweek"]));
	if ($months <= 12) return str_replace("{x}", $months, ($months == 1 ? $messages["month"] : $messages["xmonth"]));
	return str_replace("{x}", $years, ($years == 1 ? $messages["year"] : $messages["xyear"]));
}


/**
 * Get timestamp of date
 * 
 * @param int $date
 * @param string $format
 * @return int
 */
function getTimestamp($date, $format = "j/n/Y"){
    $stamp = DateTime::createFromFormat($format, $date);
    $valid = $stamp && $stamp->format($format) == $date;
	return ($valid ? $stamp->getTimestamp() : 0);
}


/**
 * Check if two dates in same day
 * 
 * @param int $first_date
 * @param int $second_date
 * @return bool
 */
function inSameDay($first_date, $second_date){
	return (date('d-m-Y', $first_date) === (date('d-m-Y', $second_date)));
}


/**
 * Get date by language
 * 
 * @param string $format
 * @param ?int $date
 * @return string
 */
function dateLanguage($format, $date){
	if (!$date){
		return null;
	}
	global $language_array;
	$translations = $language_array["dates"];
	return str_ireplace(array_keys($translations), array_values($translations), date($format, $date));
}

//==================================================
//========== Files, Folders & ZIP Management ==========
//==================================================

/**
 * Render file block
 * 
 * @param string $path
 * @param string $title
 * @param string $class
 * @param bool $download
 * @return string
 */
function fileBlock($path, $title, $class = '', $download = false){
	global $data_file_icons;

	$original_path = $path;
	if (!file_exists($path)){ $path = "../" . $path; } // Search above folder [When called from CRUD]

	$extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
	$icon = $data_file_icons[$extension];
	$icon = ($icon ? $icon : "fas fa-file");
	$icon = "<i class='$icon'></i>";

	switch($extension){
		case "png": case "jpg": case "jpeg": case "bmp": case "gif":
			$fancybox = "data-fancybox";
		break;

		case "pdf":
			$fancybox = "data-fancybox data-type=iframe";
		break;

		default:
			$download = true;
	}

	$filesize = filesize($path) / 1024; //In KB
	$size = ($filesize > 1024 ? round($filesize / 1024, 2) . " MB" : round($filesize,0) . " KB");
	$button = "<a data-file=$extension class='btn btn-default btn-sm download_button $class' href='$original_path' " . ($download ? "download='$title.$extension'" : $fancybox) . ">$icon<div><span>$title</span><small>$extension - $size</small></div></a>";

	return $button;
}


/**
 * Attachments Dropdown (fileBlock)
 * 
 * @param string $data
 * @param string $path
 * @param string $title
 * @param string $class
 * @param string $icon
 * @param string $reverse
 * @return string
 */
function attachmentsDropdown($data, $path, $title = "Attachments", $class = "btn-default", $icon = "far fa-paperclip", $reverse = ""){
	if (isJson($data)){
		$json = json_decode($data, true);
		foreach ($json AS $attachment){
			$attachments[] = [
				"path" => $path . $attachment["url"],
				"title" => $attachment["title"]
			];
		}
	}

	if (count($attachments ?? [])){
		$return = "<div class=crud-dropdown-container>
		<button type=button class='btn $class btn-sm btn-block flex-center' data-toggle=dropdown>
			<i class='$icon'></i>&nbsp;$title&nbsp;<small>(" . count($attachments) . ")</small>&nbsp&nbsp<i class='fas fa-angle-down'></i>
		</button>";

		$return .= "<ul class='dropdown-menu download_dropdown $reverse'>";
		foreach ($attachments AS $attachment){
			$return .= fileBlock($attachment["path"], $attachment["title"]);
		}

		$return .= "</ul></div>";
	}

	return $return ?? '';
}

/**
 * Custom dropdown
 * 
 * @param string $items
 * @param string $button_label
 * @param string $button_class
 * @return string
 */
function customDropdown($items, $button_label, $button_class="btn btn-primary btn-sm btn-block"){
	$items = json_decode(html_entity_decode($items), true);
	foreach ($items AS $item){
		switch ($item["type"]){
			case "divider":
				$list .= "<li class=divider></li>";
			break;
			
			case "title":
				$list .= "<li class=title>" . $item["label"] . "</li>";
			break;

			default:
				$icon = ($item["icon"] ? "<i class='" . $item["icon"] . "'></i> " : "");
				$click = ($item["click"] ? "onclick=\"" . $item["click"] . "\"" : "");
				$href = ($item["href"] ? "href=\"" . $item["href"] . "\"" : "");
				$list .= "<li><a $href $click>$icon" . $item["label"] . "</a></li>";
		}
	}
	return "<div class='crud-dropdown-container'><button type=button class='