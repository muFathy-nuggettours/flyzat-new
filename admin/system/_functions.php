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
	return "<div class='crud-dropdown-container'><button type=button class='dropdown-toggle $button_class' data-toggle=dropdown>$button_label&nbsp;&nbsp;<i class='fas fa-angle-down'></i></button><ul class='dropdown-menu animate'>" . $list . "</ul></div>";
}

/**
 * Upload file
 * 
 * @param string $file
 * @param string $path
 * @param string $default
 * @param string $prefix
 * @return string
 */
function fileUpload($file, $path, $default = "", $prefix = ""){
	if ($file['name'] && validateFileName($file['name'])){
		$file_name = strtolower(uniqid($prefix) . "." . pathinfo($file['name'], PATHINFO_EXTENSION));
		$file_path = $path . $file_name;
		$file = move_uploaded_file($file['tmp_name'], $file_path);
		return $file ? $file_name : false;
	}

	return $default;
}


/**
 * Parse size to bytes
 * 
 * @param string $size
 * @return int|float
 */
function parseSize($size){
	$unit = preg_replace('/[^bkmgtpezy]/i', '', $size);
	$size = preg_replace('/[^0-9\.]/', '', $size);
	if ($unit){
		return round($size * pow(1024, stripos('bkmgtpezy', $unit[0])));
	}

	return round($size);
}


/**
 * Copy folder
 * 
 * @param string $source
 * @param string $destination
 * @param string $exception
 * @return void
 */
function copyFolder($source, $destination, $exception = ''){
	if (is_dir($source)){
		@mkdir($destination);
		$directory = dir($source);
		while (FALSE !== ($read_directory = $directory->read())){
			if ($read_directory == '.' || $read_directory == '..' || $read_directory == $exception){
				continue;
			}
			$path_dir = $source . '/' . $read_directory;
			if (is_dir($path_dir)){
				copyFolder($path_dir, $destination . '/' . $read_directory);
				continue;
			}
			copy($path_dir, $destination . '/' . $read_directory);
		}
		$directory->close();
	} else {
		copy($source, $destination);
	}
}


/**
 * Create folder
 * 
 * @param string $path
 * @param int $mode
 */
function createDirectory($path, $mode = 0777){
    return is_dir($path) || mkdir($path, $mode, true);
}


/**
 * Remove folder
 * 
 * @param string $dirname
 */
function removeDirectory($dirname){
	if (is_dir($dirname)) $dir_handle = opendir($dirname);
	if (!$dir_handle) return false;
	while ($file = readdir($dir_handle)){
		if ($file != "." && $file != ".."){
			if (!is_dir($dirname . "/" . $file)) unlink($dirname . "/" . $file);
			else removeDirectory($dirname . '/' . $file);
		}
	}
	closedir($dir_handle);
	rmdir($dirname);
	return true;
}


/**
 * Retrieve directory files
 * 
 * @param string $path
 * @param string $extension
 * @param string $filter
 */
function retrieveDirectoryFiles($path, $extension = "*", $filter = "*"){
	$files = glob("$path/$filter.$extension", GLOB_BRACE);
	natsort($files);
	$files = array_map(function($item){
		return basename($item);
	}, $files);
	return $files;
}


/**
 * Create ZIP file
 * 
 * @param string $target
 * @param string $folder
 * @param bool $include_parent
 * @return void
 */
function createZipFile($target, $folder, $include_parent=true, $password=null){
	class FlxZipArchive extends ZipArchive {
		public function addDir($location, $name){
			$this->addDirDo($location, $name);
		}
		
		private function addDirDo($location, $name){
			if ($name){
				$name .= '/';
			}
			$location .= '/';
			$dir = opendir($location);
			while ($file = readdir($dir)){
				if ($file == '.' || $file == '..') continue;
				$do = (filetype($location . $file) == 'dir') ? 'addDir' : 'addFile';
				$this->$do($location . $file, $name . $file);
				$this->setEncryptionName($name . $file, ZipArchive::EM_AES_256);
			}
		}
	}

	$zip_archive = new FlxZipArchive;
	$zip_archive->open($target, ZipArchive::CREATE);
	if ($password){
		$zip_archive->setPassword($password);
	}
	$zip_archive->addDir($folder, ($include_parent ? basename($folder) : ""));
	$zip_archive->close();
}


/**
 * Extract ZIP file
 * 
 * @param string $file
 * @param string $destination
 * @return bool
 */
function extractZipFile($file, $destination, $password=null){
	$zip = new ZipArchive();
	if (!$zip->open($file)) return false;
	if ($password){
		$zip->setPassword($password);
	}
	$result = $zip->extractTo($destination);
	$zip->close();
	return $result;
}

//==================================================
//========== Image Related Functions ==========
//==================================================

/**
 * Get center text
 * 
 * @param string $text
 * @param float $fsize
 * @param string $fname
 * @return int|float
 */
function getCenter($text, $fsize, $fname){
	$w = imagettfbbox($fsize, 0, $fname, $text);
	$image_width = abs($w[4] - $w[0]);
	return (500 / 2) - ($image_width / 2);
}


/**
 * Get text width
 * 
 * @param string $text
 * @param float $fsize
 * @param string $fname
 * @return int|float
 */
function getWidth($text, $fsize, $fname){
	$w = imagettfbbox($fsize, 0, $fname, $text);
	$image_width = abs($w[4] - $w[0]);
	return $image_width;
}


/**
 * Get text height
 * 
 * @param string $text
 * @param float $size
 * @param string $font
 * @return int|float
 */
function getHeight($text, $size, $font){
	$w = imagettfbbox($size, 0, $font, $text);
	$image_height = abs($w[3] - $w[5]);
	return $image_height;
}


/**
 * Darken color
 * 
 * @param string $rgb
 * @param int $darker
 */
function darkenColor($rgb, $darker = 2){
	$hash = (strpos($rgb, '#') !== false) ? '#' : '';
	$rgb = (strlen($rgb) == 7) ? str_replace('#', '', $rgb) : ((strlen($rgb) == 6) ? $rgb : false);
	if (strlen($rgb) != 6) return $hash . '000000';
	$darker = ($darker > 1) ? $darker : 1;
	list($R16, $G16, $B16) = str_split($rgb, 2);
	$R = sprintf("%02X", floor(hexdec($R16) / $darker));
	$G = sprintf("%02X", floor(hexdec($G16) / $darker));
	$B = sprintf("%02X", floor(hexdec($B16) / $darker));
	return $hash . $R . $G . $B;
}


/**
 * Create PHP image from URL
 * 
 * @param string $filename
 */
function imageCreateFromURL($filename){
    switch (strtolower(pathinfo($filename, PATHINFO_EXTENSION))){
        case "jpeg":
        case "jpg":
            return imagecreatefromjpeg($filename);
        case "png":
            return imagecreatefrompng($filename);
        case "gif":
            return imagecreatefromgif($filename);
        default:
            return null;
    }
}


/**
 * Create image thumbnail
 * 
 * @param string $filepath
 * @param string $thumbpath
 * @param $thumbnail_size
 * @return bool
 */
function createThumbnail($filepath, $thumbpath, $thumbnail_size){
    [$width, $height, $index] = getimagesize($filepath);
    $types = ["ImageGIF", "ImageJPEG", "ImagePNG"];
    $creators = ["ImageCreateFromGIF", "ImageCreateFromJPEG", "ImageCreateFromPNG"];

	if (!$types[$index - 1]) return false;

	$type = $types[$index - 1];
	$imgcreatefrom = $creators[$index - 1];

	$old = call_user_func($imgcreatefrom, $filepath);
	$factor = $thumbnail_size / min($width, $height);

	$pwidth = $width * $factor;
	$pheight = $height * $factor;
	$new = imagecreatetruecolor($pwidth, $pheight);

	// Keep transparency
	imagealphablending($new, false);
	imagesavealpha($new, true);
	$transparentindex = imagecolorallocatealpha($new, 255, 255, 255, 127);
	imagefill($new, 0, 0, $transparentindex);
	imagecopyresampled($new, $old, 0, 0, 0, 0, $pwidth, $pheight, $width, $height);
	call_user_func($type, $new, $thumbpath, ($type == "ImageJPEG" ? 85 : null));

    return file_exists($thumbpath);
}


/**
 * Check if file is image, same used in [plugins/tinymce/upload.php]
 * 
 * @param string $filename
 * @return bool
 */
function isImage($filename){
	$filename = strtolower($filename);
	$image = array("bmp", "jpg", "jpeg", "png", "gif");
	$extension = pathinfo($filename, PATHINFO_EXTENSION);
	return (in_array($extension, $image));
}


/**
 * Get image thumbnail
 * 
 * @param string $path
 * @param string $url
 * @return string
 */
function imgThumb($path, $url = '', $class = "crud_image"){
	$original_path = $path;
	if (!file_exists($path)){ $path = "../" . $path; } //Search above folder [When called from CRUD]

	//Return null if image doesn't exists
	if (!is_file($path) || !file_exists($path)) return null;

	$thumbnail = pathinfo($path, PATHINFO_DIRNAME) . "/thumbnails/" . basename($path);
	$thumbnail = (file_exists($thumbnail) ? pathinfo($original_path, PATHINFO_DIRNAME) . "/thumbnails/" . basename($original_path) : $original_path);
	$original_path = ($url ? $url : $original_path);
	return "<a data-fancybox=images href='$original_path'><img class='$class' src='$thumbnail'></a>";
}


/**
 * Upload image
 * 
 * @param string $file
 * @param string $path
 * @param string $default
 * @param string $prefix
 * @param ?Closure $callback
 * @param bool $resize
 * @return string
 */
function imgUpload($file, $path, $default = "", $prefix = "", $callback = null, $resize = true){
	if ($file['name'] && validateFileName($file['name']) && isImage($file['name'])){
		$image = fileUpload($file, $path, $default, $prefix);
		$file_path = $path . $image;

		if ($image){
			// Resize if necessary
			if ($resize){
				[$original_width, $original_height] = getimagesize($file_path);
				if ($original_width > 1200 || $original_height > 1200){
					createThumbnail($file_path, $file_path, 1200);
				}
			}

			// Create thumbnail if available
			if (file_exists($path . "/thumbnails/")){
				createThumbnail($file_path, $path . "/thumbnails/" . $image, 400);
			}

			// Call user function if set
			if ($callback){
				call_user_func($callback, $file_path);
			}

			return $image;
		}
	}

	return $default;
}


/**
 * Upload image in base64
 * 
 * @param string $file
 * @param string $path
 * @param string $default
 * @return string
 */
function imgUploadBase64($file, $path, $default = "", $prefix = ""){
	if ($file){
		$image = uniqid($prefix) . ".png";
		file_put_contents($path . $image, base64_decode(preg_replace("#^data:image/\w+;base64,#i", "", $file)));
		return $image;
	}

	return $default;
}


/**
 * Get average color of image
 * 
 * @param string $image
 * @param array $pallet_size
 * @return array
 */
function getAverageColor($image, $pallet_size = [16, 8]){
	if (!$image) return false;
	$img = imagecreatefromjpeg($image);
	$img_sizes = getimagesize($image);
	$resized_img = imagecreatetruecolor($pallet_size[0], $pallet_size[1]);
	imagecopyresized($resized_img, $img, 0, 0, 0, 0, $pallet_size[0], $pallet_size[1], $img_sizes[0], $img_sizes[1]);
	imagedestroy($img);
	for ($i = 0; $i < $pallet_size[1]; $i++)
	for ($j = 0; $j < $pallet_size[0]; $j++) $colors[] = dechex(imagecolorat($resized_img, $j, $i));
	imagedestroy($resized_img);
	return $colors ? array_unique($colors) : [];
}

//==================================================
//========== Communication Channels Functions ==========
//==================================================

/**
 * Send E-Mail
 * 
 * @param array|string $to
 * @param string $subject
 * @param string $message
 * @param string $language
 * @param array $cc
 * @param array $bcc
 * @param array $attachments
 * @param string $reply_to
 * @return array
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
	
function sendMail($to, $subject, $message, $language = null, $cc = [], $bcc = [], $attachments = null, $reply_to = null){
	// If a custom function exists, use it instead
	if (function_exists("customMail")){
		$result = customMail($to, $subject, $message, $language, $cc, $bcc, $attachments, $reply_to);
		$error = $result[0];
		$success = $result[1];
		$to = $result[2];
		$save_attempt = $result[3];
		
	//Otherwise use buit-in function (SMTP)
	} else {
		global $system_settings;

		//Convert string to array
		if (!is_array($to)) $to = [$to];

		//Sanitize emails
		$to = array_map("trim", $to); //Remove empty spaces
		$to = array_filter($to); //Remove empty lines
		foreach ($to as $email){
			if (filter_var($email, FILTER_VALIDATE_EMAIL)){
				$emails[] = $email;
			}
		}

		//Validate and send
		if (!$emails){
			$error = "No valid recipients were provided";
		} else if (count($emails) > 500){
			$error = "Maximum recipients per request is 500";
		} else {
			global $panel_path;
			require_once($panel_path . "/snippets/mailer/Exception.php");
			require_once($panel_path . "/snippets/mailer/PHPMailer.php");
			require_once($panel_path . "/snippets/mailer/SMTP.php");
			$mail = new PHPMailer(true);
			try {
				$save_attempt = true;
				$mail->CharSet = 'UTF-8';
				$mail->IsSMTP();
				$mail->Host = $system_settings["mail_server"];
				$mail->Port = $system_settings["mail_port"]; //465 (ssl) or 587 (non-ssl)
				$mail->SMTPAuth = true;
				$mail->SMTPSecure = ($system_settings["mail_port"]=="587" ? PHPMailer::ENCRYPTION_STARTTLS : PHPMailer::ENCRYPTION_SMTPS);
				$mail->Username = $system_settings["mail_username"];
				$mail->Password = $system_settings["mail_password"];
				$mail->setFrom($system_settings["mail_from"], $system_settings["mail_from_name"]);
				$mail->isHTML(true);
				$mail->Subject = html_entity_decode(stripcslashes($subject), ENT_QUOTES);
				$mail->Body = mailFormat(unescapeString($message), $language);
				$mail->AltBody = mailFormat(unescapeString($message), $language);
				foreach ($to as $value) $mail->addAddress($value);
				if ($cc){
					foreach ($cc as $value) $mail->addCC($value);
				}
				if ($bcc){
					foreach ($bcc as $value) $mail->addBCC($value);
				}
				if ($attachments){
					foreach ($attachments as $attachment){
						$extension = pathinfo($attachment[0], PATHINFO_EXTENSION);
						$mail->addAttachment($attachment[0], $attachment[1] . "." . $extension);
					}
				}
				if ($reply_to){
					$mail->AddReplyTo($reply_to);
				}
				$mail->send();
				$invalid = count($to) - count($emails);
				$error = ($invalid ? $invalid . " invalid recipients were found" : null);
				$success = ($error ? "Sent successfully to " . count($emails) . " recipients of " . count($to) : "Sent successfully");
			
			} catch (Exception $e){
				$error = "Sending failed " . $mail->ErrorInfo;
			}
		}
	}

	//Save
	if (function_exists("saveMail") && $save_attempt==true){
		$response_message = ($success ? $success : $error);
		$response_status = ($success ? 1 : 0);
		saveMail(array($to, $subject, $message, $response_status, $response_message));
	}

	return [$error, $success];	
}


/**
 * Send SMS
 * 
 * @param array|string $to
 * @param string $message
 */
function sendSMS($to, $message){
	// If a custom function exists, use it instead
	if (function_exists("customSMS")){
		$result = customSMS($to, $message);
		$error = $result[0];
		$success = $result[1];
		$mobile_numbers = $result[2];
		$save_attempt = $result[3];
		
	// Otherwise use buit-in function (SMSMisr)
	} else {
		global $system_settings;

		// Convert string to array
		if (!is_array($to)) $to = [$to];

		// Validate mobile numbers
		$to = array_map("trim", $to); //Remove empty spaces
		$to = array_filter($to); //Remove empty lines
		foreach ($to AS $number){
			$clean_number = str_replace(array("+2","+","-"," "), "", $number);
			$country_code = substr($clean_number,0,3);
			if (strlen($clean_number) == 11 && in_array($country_code, ['010', '011', '012', '015']) && is_numeric($clean_number)){
				$mobile_numbers[] = $clean_number;
			}
		}

		// Validate and send
		if (!$mobile_numbers){
			$error = "No valid recipients were provided";
		} else if (count($mobile_numbers) > 500){
			$error = "Maximum recipients per request is 500";
		} else if (strlen($message) > 140){
			$error = "The message provided is too long";
		} else {
			$save_attempt = true;
			$fields = [
				"username" => $system_settings["sms_username"],
				"password" => $system_settings["sms_password"],
				"language" => (isArabic($message) ? 2 : 1),
				"sender" => $system_settings["sms_sender_id"],
				"mobile" => implode(",", $mobile_numbers),
				"message" => $message,
			];

			$curl = curl_init();
			curl_setopt($curl, CURLOPT_URL, $system_settings["sms_post_url"] . http_build_query($fields));
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt($curl, CURLOPT_HEADER, FALSE);
			curl_setopt($curl, CURLOPT_POST, TRUE);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
			$response = json_decode(curl_exec($curl),true);
			curl_close($curl);

			// Response
			if ($response){
				if ($response["code"] == "1901"){	
					$invalid = count($to) - count($mobile_numbers);
					$error = ($invalid ? $invalid . " invalid recipients were found" : null);
					$success = ($error ? "Sent successfully to " . count($mobile_numbers) . " recipients of " . count($to) : "Sent successfully");
				} else {
					$error = "Sending failed" . ($response["code"] ? " error code " . $response["code"] : " service provider not responding");
				}
			} else {
				$error = "Failed to communicate with sms server";
			}
		}
	}

	//Save
	if (function_exists("saveSMS") && $save_attempt==true){
		$response_message = ($success ? $success : $error);
		$response_status = ($success ? 1 : 0);
		saveSMS(array($mobile_numbers, $message, $response_status, $response_message));
	}

	return [$error, $success];
}


/**
 * Send Push Notification
 * 
 * @param
 */
function sendNotification($to, $title = null, $message, $icon = null, $url = null, $image = null){
	//If a custom function exists, use it instead
	if (function_exists("customNotification")){
		$result = customNotification($to, $title, $message, $icon, $url, $image);
		$error = $result[0];
		$success = $result[1];
		$save_attempt = $result[2];
		
		
	// Otherwise use buit-in function (Firebase)
	} else {
		global $base_url;
		global $website_information;
		global $system_settings;

		// Set notification message
		if ($title) $notification["title"] = $title;

		$notification["body"] = $message;
		$notification["icon"] = ($icon ? $icon : $base_url . "uploads/_website/" . $website_information["cover_image"]);

		if ($image) $notification["image"] = $image;
		if ($url) $notification["click_action"] = $base_url . $url;
		$fields["notification"] = $notification;

		// Initialize recipients
		if (is_array($to)){
			if (count($to) > 1){
				$fields["registration_ids"] = $to;
			} else {
				$fields["to"] = $to[0];
			}
		} else {
			$fields["to"] = $to;
		}

		// Validate and send
		if (!$fields["to"] && !$fields["registration_ids"]){
			$error = "No valid recipients were provided";
		} else if (is_array($to) && count($to) > 100){
			$error = "Maximum recipients per request is 100";
		} else if (strlen($message) > 1024){
			$error = "The message provided is too long";
		} else {
			$save_attempt = true;
			$headers = [
				"Authorization: key=" . $system_settings["firebase_server_key"],
				"Content-Type: application/json"
			];
			$curl = curl_init();
			curl_setopt($curl, CURLOPT_URL, "https://fcm.googleapis.com/fcm/send");
			curl_setopt($curl, CURLOPT_POST, TRUE);
			curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($fields));
			$response = curl_exec($curl);
			$curl_error = curl_error($curl);
			curl_close($curl);

			// Response
			if ($response){
				if (isJson($response)){
					$response = json_decode($response, true);
					$error = ($response["failure"] ? $response["failure"] . " invalid recipients were found" : null);
					$success = ($response["success"] ? "Sent successfully to " . $response["success"] . " recipients of " . count($to) : "Sent successfully");
				} else {
					$error = "Sending failed" . ($response ? ": $response" : "");
				}
			} else {
				$error = "Failed to communicate with server" . ($curl_error ? ": $curl_error" : "");
			}
		}
	}

	//Save
	if (function_exists("saveNotification") && $save_attempt==true){
		$response_message = ($success ? $success : $error);
		$response_status = ($success ? 1 : 0);
		saveNotification(array($to, $title, $message, $response_status, $response_message));
	}

	return [$error, $success];
}

//==================================================
//========== Generic Functions ==========
//==================================================

/**
 * Generate random hash
 * 
 * @param int $length
 * @param int $uppercase
 * @param int $lowercase
 * @param int $numbers
 * @param int $symbols
 * @return string
 */
function generateHash($length = 8, $uppercase = 1, $lowercase = 1, $numbers = 1, $symbols = 1){
	$uppercase_list = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
	$lowercase_list = "abcdefghijklmnopqrstuvwxyz";
	$numbers_list = "0123456789";
	$symbols_list = "!@#$%^&*()_-[]";

	//Uppercase count
	if (is_numeric($uppercase) && $uppercase > 0){
		for ($x = 1; $x <= $uppercase; $x += 1){
			$hash .= $uppercase_list[rand(0, strlen($uppercase_list)-1)];
		}
	}

	//Lowercase count
	if (is_numeric($lowercase) && $lowercase > 0){
		for ($x = 1; $x <= $lowercase; $x += 1){
			$hash .= $lowercase_list[rand(0, strlen($lowercase_list)-1)];
		}
	}

	//Numbers count
	if (is_numeric($numbers) && $numbers > 0){
		for ($x = 1; $x <= $numbers; $x += 1){
			$hash .= $numbers_list[rand(0, strlen($numbers_list)-1)];
		}
	}

	//Symbols count
	if (is_numeric($symbols) && $symbols > 0){
		for ($x = 1; $x <= $symbols; $x += 1){
			$hash .= $symbols_list[rand(0, strlen($symbols_list)-1)];
		}
	}

	//If the length is still smaller than required, fill with random values
	if (strlen($hash) < $length){
		//Generate a list to fill random values
		$use_list = uniqid();
		for ($x = strlen($hash); $x < $length; $x += 1){
			$hash .= $use_list[rand(0, strlen($use_list)-1)];
		}
	}

	return str_shuffle($hash);
}


/**
 * Return clickable & copyable url
 * 
 * @param string $url
 * @param int $max_length
 * @return string
 */
function pageURL($url, $max_length=35){
	return $url ? "<div class=flex-center>
		<i class='fas fa-copy fa-lg' style='cursor:pointer; color:#909090' onclick=\"copyText($(this).parent().find('a').attr('href'))\"></i>&nbsp;&nbsp;
		<a href='$url' style='display:block; direction:ltr; word-break: break-all' target=_blank>" . (is_numeric($max_length) ? maxLength($url, $max_length) : $url) . "</a>
	</div>" : null;
}


/**
 * Implode comma separated values from database
 * 
 * @param string $values
 * @param string $table
 * @param string $render_column
 * @param string $condition_column
 * @param string $separator
 * @return string
 */
function implodeDatabase($values, $table, $render_column, $condition_column="id", $separator=", "){
	$values = explode(",", $values);
	foreach ($values as $value){
    $entry = getID($value, $table, $render_column);
    if (!$entry) { continue; }
		$result[] = $entry;
	}
	return implode($separator, $result ?? []);
}


/**
 * Implode IDs from variable
 * 
 * @param string $values
 * @param string $variable
 * @param string $separator
 * @return string
 */
function implodeVariable($values, $variable=null, $separator=", "){	 
	$result = $explode = explode(",", $values);
	if ($variable){
		$result = [];
		global $$variable;
		$local = (is_string($variable) ? $$variable : $variable);
		foreach ($explode as $value) $result[] = $local[$value];
	}
	return implode($separator, $result);
}


/**
 * Left trim specific characters
 * 
 * @param string $value
 * @param string $characters
 * @return string
 */
function cltrim($value, $characters){
	$char_length = mb_strlen($characters);
	return (substr($value, 0, $char_length) == $characters) ? substr($value, $char_length) : $value;
}


/**
 * Right trim specific characters
 * 
 * @param string $value
 * @param string $characters
 * @return string
 */
function crtrim($value, $characters){
	$char_length = mb_strlen($characters) * -1;
	return (substr($value, $char_length) == $characters) ? substr($value, 0, $char_length) : $value;
}


/**
 * Maximum length
 * 
 * @param string $text
 * @param int $max
 * @param string $suffix
 * @return string
 */
function maxLength($text, $max, $suffix = ".."){
	return (mb_strlen($text, "UTF-8") > $max ? mb_substr($text, 0, $max,"UTF-8") . $suffix : $text);
}


/**
 * Highlight keyword
 * 
 * @param string $string
 * @param string $search
 * @param string $highlightcolor
 * @return string
 */
function highlightKeyword($string, $search, $highlightcolor = "red"){
    $occurrences = substr_count(strtolower($string), strtolower($search));
    $newstring = $string;
    $match = [];
    for ($i = 0; $i < $occurrences; $i++){
        $match[$i] = stripos($string, $search, $i);
        $match[$i] = substr($string, $match[$i], strlen($search));
        $newstring = str_replace($match[$i], '[#]' . $match[$i] . '[@]', strip_tags($newstring));
    }
    $newstring = str_replace('[#]', "<i class=search_higlight style='color:$highlightcolor'>", $newstring);
    $newstring = str_replace('[@]', '</i>', $newstring);
    return $newstring;
}


/**
 * Populate select options
 * 
 * @param array $data
 * @param string $data_type
 * @param array $execlude
 * @param array $include 
 * @return string
 */
function populateOptions($data, $data_type = "key", $execlude = [], $include = []){
	$data = array_filter($data, function ($key) use ($execlude, $include){
		return (in_array($key, $include) || !in_array($key, $execlude));
	}, ARRAY_FILTER_USE_KEY);

	foreach ($data as $key => $value){
		if ($data_type == "key") $return .= "<option value='$key'>$value</option>";
		else if ($data_type == "value") $return .= "<option value='$value'>$value</option>";
		else $return .= "<option>$value</option>";
	}

	return $return ?? '';
}


/**
 * Populate database
 * 
 * @param string $query
 * @param string $key
 * @param string $value
 * @return string
 */
function populateData($query, $key, $value){
	return implode('', mysqlFetchAll(mysqlQuery($query), null, function ($entry) use ($key, $value){
		return "<option value='$entry[$key]'>$entry[$value]</option>";
	}));
}


/**
 * Get YouTube ID from URL
 * 
 * @param string $url
 * @return string
 */
function getYoutubeID($url){
    preg_match('/(http(s|):|)\/\/(www\.|)yout(.*?)\/(embed\/|watch.*?v=|)([a-z_A-Z0-9\-]{11})/i', $url, $results);
	return $results[6];
}


/**
 * Get Vimeo ID from URL
 * 
 * @param string $url
 * @return string
 */
function getVimeoID($url){
	preg_match('/(https?:\/\/)?(www\.)?(player\.)?vimeo\.com\/([a-z]*\/)*([0-9]{6,11})[?]?.*/', $url, $results);
	return $results[5];
}


/**
 * Create canonical title
 * 
 * @param string $text
 * @return string
 */
function createCanonical($text){
	$text = html_entity_decode(unescapeString($text));
    $letters = array('–','—','"','\'','\\','/','&','÷','×','+','*','<','>','(',')','[',']','«','»','?','؟','!','@','#','$','%','^','&','.');
    $text = str_replace($letters, "", $text);
	return mb_strtolower(str_replace([" ", "--", "--"], "-", $text));
}


/**
 * Validate canonical title
 * 
 * @param string $val
 * @return bool
 */
function validateCanonical($val){
	return createCanonical($val) == $val;
}


/**
 * Round up to any number
 * 
 * @param int|float $n
 * @param int|float $x
 * @return float
 */
function roundUpToAny($n, $x = 5){
    return (round($n) % $x === 0) ? round($n) : round(($n + $x / 2) / $x) * $x;
}


/**
 * Make links clickable
 * 
 * @param string $text
 * @param string $target
 * @return string
 */
function makeClickable($text, $target = "_blank"){
    $regex = "#\bhttps?://[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/))#";
    return preg_replace_callback($regex, function($matches) use($target){
        return "<a target=$target href='$matches[0]'>$matches[0]</a>";
    }, $text);
}


/**
 * Check if text contains arabic
 * 
 * @param string $string
 * @return bool
 */
function isArabic($string){
	return preg_match('/\p{Arabic}/u', $string) !== false;
}


/**
 * Get client IP
 * 
 * @return string
 */
function getClientIP(){
	if (!empty($_SERVER['HTTP_CLIENT_IP'])) return $_SERVER['HTTP_CLIENT_IP'];
	if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) return $_SERVER['HTTP_X_FORWARDED_FOR'];
	return $_SERVER['REMOTE_ADDR'];
}


/**
 * Force file download
 * 
 * @param string $file
 * @return void
 */
function forceDownload($file){
    if (isset($file) && file_exists($file)){
		header("Content-length: ". filesize($file));
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename="' . $file . '"');
		readfile("$file");
    }
}


/**
 * Get current page name
 * 
 * @return string
 */
function getPageName(){
	$explode = explode("/", $_SERVER['SCRIPT_NAME']);
	return $explode[(count($explode) - 1)];
}


/**
 * Validate JSON
 * 
 * @param string $json
 * @return bool
 */
function isJson($json){
	json_decode($json);
	return (json_last_error() == JSON_ERROR_NONE);
}


/**
 * Return value if exists or na
 * 
 * @param $value
 * @param $response
 * @param $unavailable
 */
function naRes($value, $response = null, $unavailable = null){
	if ($value) return ($response ?: $value);
	return ($unavailable ?: "<i class=na>" . readLanguage('general', 'na') . "</i>");
}


/**
 * Get country data from json
 * 
 * @param string $code
 * @return array
 */
function getCountryData($code){
	global $base_url;
	$countries = json_decode(file_get_contents($base_url . "plugins/countries/countries.json"), true);
	foreach ($countries AS $country){
		if ($country["code"] == $code) return $country;
	}
	return $return;	
}


/**
 * Check visitor's platform
 * 
 * @return string
 */
function checkPlatform(){
	$iPhoneBrowser = stripos($_SERVER["HTTP_USER_AGENT"], "iPhone");
	$iPadBrowser = stripos($_SERVER["HTTP_USER_AGENT"], "iPad");
	$AndroidBrowser = stripos($_SERVER["HTTP_USER_AGENT"], "Android");
	$AndroidApplication = ($_SERVER["HTTP_X_REQUESTED_WITH"] == fetchData("system_settings")["application_android_bundle"]);
	$iOSApplication = (strpos($_SERVER["HTTP_USER_AGENT"], "Mobile/") !== false) && (strpos($_SERVER["HTTP_USER_AGENT"], "Safari/") == false);

	if ($_SERVER["HTTP_X_REQUESTED_WITH"] && $AndroidApplication) return "Android_Application";
	if ($AndroidBrowser) return "Android_Browser";
	if ($iOSApplication) return "iOS_Application";
	if ($iPhoneBrowser || $iPadBrowser) return "iOS_Browser";

	return "Browser";
}


/**
 * Create searchable (normalized) string
 * 
 * @param string $string
 * @return string
 */
function normalizeString($string){
	// Remove Arabic Diacritic
	$string = preg_replace("~[\x{064B}-\x{065B}]~u", "", $string);

	// Replace Special Letters
	$table = [
		// Latin Characters
		'Š'=>'S', 'š'=>'s', 'Ð'=>'D', 'Ž'=>'Z', 'ž'=>'z', 'Ľ'=>'L', 'ľ'=>'l', 'Č'=>'C', 'č'=>'c',
		'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
		'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O',
		'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U', 'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss',
		'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c', 'è'=>'e', 'é'=>'e',
		'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o',
		'ô'=>'o', 'õ'=>'o', 'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'ý'=>'y', 'þ'=>'b',
		'ÿ'=>'y', 'Ť'=>'T', 'ť'=>'t',

		// Arabic Characters
		'أ'=>'ا', 'إ'=>'ا', 'آ'=>'ا', 'ى'=>'ي', 'ة'=>'ه', 'ؤ'=>'و', 'پ'=>'ب', 'ڤ'=>'ف', 'چ'=>'ج',
		'ژ'=>'ز', 'گ'=>'ك', 'ـ'=>'', '،'=>''
	];
	$string = strtr($string, $table);

	// Remove special characters
	$string = preg_replace('/[^\x{0600}-\x{06FF}A-Za-z]/u', ' ', $string);

	// Remove double or multiple spaces
	$string = preg_replace('/\s+/', ' ', $string);

	return mb_strtolower($string, "UTF-8");
}

//==================================================
//========== Panel Specific Functions ==========
//==================================================

/**
 * Rebuild query parameters
 * 
 * @param array $unset
 * @param array $set
 * @return string
 */
function rebuildQueryParameters($unset = [], $set = []){
	$parameters = parse_url($_SERVER["REQUEST_URI"])["query"];
	parse_str($parameters, $parameters);

	foreach ($unset AS $value) unset($parameters[$value]);
	foreach ($set AS $key => $value) $parameters[$key] = $value;

	return ($parameters ? "?" . http_build_query($parameters) : "");
}


/**
 * Get page title
 * 
 * @param string $page_link
 * @param bool $full_navigation
 * @return string
 */
function getPageTitle($page_link, $full_navigation = true){
	global $panel_section;
	global $panel_categories;

	foreach ($panel_section as $section => $pages){
		if ($page_title = $pages[$page_link]){

			if (!$full_navigation) return $page_title;

			foreach ($panel_categories as $category => $sections){
				if (in_array($section, $sections)){
					$image = "<div class=image><img src='images/icons/" . (file_exists("images/icons/$page_link.png") ? $page_link : "_default") . ".png'></div>";
					$breadcrumb = "<div class=title_breadcrumb><i class='fas fa-home'></i> <a href='.'>" . readLanguage('general', 'control_panel') . "</a> <i class='fas fa-angle-double-right'></i> $section <i class='fas fa-angle-double-right'></i> $category </div>";
					$title = "<h1><a href='$page_link.php'>$page_title</a></h1>";
					return "$image <div>$breadcrumb $title</div>";
				}
			}
		}
	}

	return $page_link;
}


/**
 * Check admin authorization
 * 
 * @param string $page_title
 * @param int $mode
 * @return bool
 */
function checkPermissions($page_title = "", $mode = 0){
	global $logged_user;
	global $user_permissions;

	//Prismatecs account exception
	if ($logged_user["id"] ==1 ){
		$has_permission = true;
	} else {
		$has_permission = $logged_user && (($page_title && in_array($page_title, $user_permissions)) || !$page_title);
	}
	switch ($mode){
		case 0: // Include permissions page
			if (!$has_permission){
				extract($GLOBALS);
				include "401.php";
				exit();
			} else {
				return true;
			}
		case 1: // Redirect to permissions page
			if (!$has_permission){
				extract($GLOBALS) ;
				include "404.php";
				exit();
			} else {
				return true;
			}
		case 2: // Boolean check
			return $has_permission;
		default:
			return false;
	}
}


/**
 * Get administrators with specific page permission
 * 
 * @param string $page
 * @param string $field
 * @return array
 */
function getAdminsWithPermission($page, $field = ''){
	$permissions = mysqlFetchAll(mysqlQuery("SELECT id FROM system_permissions WHERE FIND_IN_SET('$page', permissions)"), 'id');
	$administrators = mysqlFetchAll(mysqlQuery("SELECT * FROM system_administrators WHERE permission IN (" . implode(",", $permissions) . ")"), null, function ($entry) use ($field){
		return ($field ? $entry[$field]: $entry);
	});

	return array_filter($administrators);
}

//==================================================
//========== Website Functions ==========
//==================================================

/**
 * Print no content
 * 
 * @param bool $small
 * @param string $title
 * @param string $description
 */
function noContent($small = false, $title = null, $description = null){
	if ($small) return "<div class=no_content_sm>" . ($title ? $title : readLanguage('general', 'no_content')) . "</div>";
	return "<div class=no_content><h2>" . ($title ? $title : readLanguage('general', 'no_content_title')) . "</h2>" . ($description ? $description : readLanguage('general', 'no_content_description')) . "</div>";
}


/**
 * Return broken link
 * 
 * @return void
 */
function brokenLink(){
	extract($GLOBALS);
	include "404.php";
	exit();
}


/**
 * Sanitize WYSIWYG content
 * 
 * @param string $content
 * @param string $container
 */
function htmlContent($content, $append_container="html_content", $purify=true){
	global $panel_folder;
	$in_panel = (strpos(strtolower($_SERVER["PHP_SELF"]), $panel_folder . "/") !== false);

	// Clean content and fix image paths
	if (!$in_panel){
		$content = str_replace("../../uploads", "uploads", $content);
		$content = str_replace("../uploads", "uploads", $content);
		$content = str_replace("href=&#34;../", "href=&#34;", $content);
	}

	$content = html_entity_decode($content);

	// Initialize HTMLPurifier_Config
	if ($purify){
		global $panel_path;
		require_once($panel_path . "/snippets/html_purifier/HTMLPurifier.standalone.php");

		// Clean content
		$config = HTMLPurifier_Config::createDefault();
		$config->set("Attr.AllowedFrameTargets", ["_blank"]);
		$config->set("HTML.SafeIframe", true);
		$config->set("URI.SafeIframeRegexp", "/(^uploads)|(^http[s]?:\/{2})|(^www)|(^\/{1,2})/im");
		$config->set("CSS.AllowTricky", true);

		// Initialize
		$purifier = new HTMLPurifier($config);
		$content = $purifier->purify($content);
		$content = str_replace("@nbsp;", "&nbsp;", $content);
	}

	return ($container ? "<div class='$container'>$content</div>" : $content);
}


/**
 * Set current page redirect
 * 
 * @return void
 */
function setCurrentPageRedirect(){
	global $redirect_session;
	$_SESSION[$redirect_session] = (isset($_SERVER["HTTPS"]) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
}

//Append pagination
function appendPagination($page, $url){
	$query = parse_url($url)["query"];
	parse_str($query, $parameters);
	unset($parameters["page"]);
	return $url . ($parameters ? "&" : "?") . "page=" . $page;
}

/**
 * Build pagination
 * 
 * @param int $item_per_page
 * @param int $total_records
 * @param string $url
 * @param string $click
 * @param int $current_page
 */
function paginateRecords($item_per_page, $total_records, $url = '', $click = '', $current_page = 1){
	global $get;
	$item_per_page = ($item_per_page ? $item_per_page : 1);
	$total_records = ($total_records ? $total_records : 1);

	// Rebuild query string
	if ($url){
		$appended_parameters = parse_url($_SERVER["REQUEST_URI"])["query"];
		parse_str($appended_parameters, $appended_parameters);
		unset($appended_parameters["page"]);
		$url_parameters = parse_url($url)["query"];
		parse_str($url_parameters, $url_parameters);
		unset($url_parameters["page"]);
		$parameters = array_unique(array_merge($url_parameters, $appended_parameters));
		$url = parse_url($url, PHP_URL_PATH) . ($parameters ? "?" . http_build_query($parameters,"","&") : "");
	}

	$current_page = (!$get["page"] ? $current_page : $get["page"]);
	$limit_min = ($current_page - 1) * $item_per_page;
	$limit_max = $item_per_page;
	$pages = ceil($total_records / $item_per_page);

	if ($pages > 1){
		$delta = 2;
		$left = $current_page - $delta;
		$right = $current_page + $delta + 1;
		$range = [];
		$range_truncated = [];
		$temp = -1;

		for ($i = 1; $i <= $pages; $i++){
			if ($i == 1 || $i == $pages || $i >= $left && $i < $right){
				$range[] = $i;
			}
		}

		for ($i = 0; $i < count($range); $i++){
			if ($temp != -1){
				if ($range[$i] - $temp === 2){
					$range_truncated[] = $temp + 1;
				} else if ($range[$i] - $temp !== 1){
					$range_truncated[] = "...";
				}
			}
			array_push($range_truncated, $range[$i]);
			$temp = $range[$i];
		}

		// Build pagination DOM object
		$pagination = "<div class=pagination_div><ul class=pagination>";
		foreach ($range_truncated AS $page){
			$pagination_class = ($page == $current_page ? "active" : "regular");
			if (is_numeric($page)){
				$pagination .= "<li class='$pagination_class'><a" . ($click ? " onclick='$click(" . $page . ")'" : "") . ($url ? " href='" . ($page==1 ? $url : appendPagination($page,$url)) . "'" : "") . ">" . $page . "</a></li>";
			} else {
				$pagination .= "<li class=dots><a>" . $page . "</a></li>";
			}
		}
		$pagination .= "</ul></div>";

		return [
			"min" => $limit_min,
			"max" => $limit_max,
			"object" => $pagination
		];
	}

	return [
		"min" => $limit_min,
		"max" => $limit_max,
	];
}

//==================================================
//========== Recursive Pages ==========
//==================================================

/**
 * Return custom page tree
 * 
 * @param int $id
 * @param string $table
 */
function customPagePath($id, $table = ''){
	global $suffix;
	$table = ($table ?: $suffix . "website_pages_custom");
	$result = mysqlQuery("SELECT id,parent FROM $table WHERE id=$id");
	while ($entry = mysqlFetch($result)){
		if ($entry["parent"] == 0) return $entry["id"];
		return customPagePath($entry["parent"], $table) . "," . $entry["id"];
	}
}


/**
 * Return custom page path
 * 
 * @param string $ids
 * @param string $table
 * @param string $separator
 * @param string $selector
 * @param bool $array
 * @return array|string
 */
function customPagePathRender($ids, $table = '', $separator = null, $selector = null, $array = false){
	global $suffix;
	$table = ($table ?: $suffix . "website_pages_custom");
	$separator = ($separator ? $separator : " » ");
	$selector = ($selector ? $selector : "title");
	$explode = explode(",", $ids);
	foreach ($explode AS $value){
		$page_data = getID($value, $table);
		$pages_data[] = ($array ? $page_data : $page_data[$selector]);
	}
	return ($array ? $pages_data : implode($separator, $pages_data));
}


/**
 * Return custom page children
 * 
 * @param int $id
 * @param string $table
 */
function customPageChildren($id, $table = ''){
	global $suffix;
	$table = ($table ?: $suffix . "website_pages_custom");
	$return = [$id];
	$result = mysqlQuery("SELECT id FROM $table WHERE parent='$id'");
	while ($entry = mysqlFetch($result)){
		if (mysqlNum(mysqlQuery("SELECT id FROM $table WHERE parent=" . $entry["id"]))){
			$return = array_merge($return, customPageChildren($entry["id"], $table));
		} else {
			$return = array_merge($return, array($entry["id"]));
		}
	}
	return $return;
}


/**
 * Custom page URL
 * 
 * @param array $entry
 * @param string $table
 * @param string $prefix
 * @return string
 */
function customPageURL($entry, $table='', $prefix=''){
	global $suffix;
	global $base_url;
	global $supported_languages;
	global $database_language;
	global $website_language;
	$table = ($table ?: $suffix . "website_pages_custom");
	$target_language = ($database_language ? $database_language["code"] : $website_language);
	$url_language = ($target_language==$supported_languages[0] ? "" : $target_language . "/");
	
	//Reload entry as not full page data are passed from CRUD
	$entry = getID($entry["id"], $table);
	
	if (!$entry["canonical"]){
		return null;
	}
	
	switch ($entry["url_target"]){
		case 0:
			return $base_url . $url_language . $prefix . ($entry["parent"] ? customPagePathRender(customPagePath($entry["parent"], $table), $table, "/", "canonical") . "/" : "") . $entry["canonical"] . "/";
		break;
		
		case 1:
			return $base_url . $url_language . $entry["canonical"];
		break;
		
		case 2:
			return $entry["canonical"];
		break;
		
		default:
			return null;
	}
}


/**
 * Custom page block
 * 
 * @param array $entry
 * @return array
 */
function customPageBlock($entry){
	global $website_information;
	
	$block["title"] = $entry["title"];
	$block["description"] = $entry["description"];
	$block["cover"] = ($entry["cover_image"] ? "uploads/pages/" . $entry["cover_image"] : "uploads/_website/" . $website_information["cover_image"]);
	$block["header"] = ($entry["header_image"] ? "uploads/pages/" . $entry["header_image"] : "uploads/_website/" . $website_information["header_image"]);
	
	//Cover alternative (First image in gallery)
	$gallery = json_decode($page_data["gallery"], true)[0];
	$block["image"] = ($gallery["url"] ? $gallery["url"] = "uploads/pages/" . $gallery["url"] : "uploads/_website/" . $website_information["cover_image"]);
	
	$block["url"] = customPageURL($entry);
	if ($block["url"]){
		$block["url_attributes"] = createCustomAttributes($entry["url_attributes"]);
	}
	$block["content"] = $entry["content"];
	$block["date"] = $entry["date"];
	$block["views"] = $entry["views"];
	$block["subtitle"] = $entry["child_subtitle"];
	$block["color"] = $entry["child_color"];
	$block["icon"] = $entry["child_icon"];
	$block["extras"] = json_decode($entry["child_extras"], true);
	$block["entry"] = $entry;
	return $block;
}


/**
 * Return custom page navigation
 * 
 * @param int $parent
 * @param int $current
 * @param int $max
 * @param id $active
 */
function customPageNavigation($parent = 0, $current = 0, $max = 2, $active = null, $table = '', $prefix = ''){
	global $suffix;
	$table = ($table ?: $suffix . "website_pages_custom");
	if ($current > $max) return;

	$result = mysqlQuery("SELECT * FROM $table WHERE parent=$parent ORDER BY priority DESC");
	if (mysqlNum($result)){
		while ($entry = mysqlFetch($result)){
			$children = null;
			if (mysqlNum(mysqlQuery("SELECT id FROM $table WHERE parent=$entry[id]"))){
				$children = customPageNavigation($entry["id"], $current + 1, $max, null, $table, $prefix);
			}
			$url = customPageURL($entry, $table, $prefix);
			$class = ($entry["id"] == $active || $children && strpos($children, 'class=active') !== false ? "active" : "standard");
			$pages[] = "<li class=$class><a href='$url'>$entry[title]</a></li>";
			if ($children) $pages[] = $children;
		}
	}

	return "<ul>" . implode('', $pages ?? []) . "</ul>";
}

//==================================================
//========== Website Builder Functions ==========
//==================================================

//Load custom module
function customModuleRender($uniqid, $variables=null){
	if (!customModuleCheck($uniqid)){
		return "<div class='alert alert-danger clear-margin'>Error rendering module <b>$uniqid</b></div>";
	}
	
	extract($GLOBALS);

	$module_data = getData($suffix . "website_modules_custom", "uniqid", $uniqid);
	
	$separators = json_decode($module_data["custom_separator"], true);
	$spacings = json_decode($module_data["custom_spacing"], true);
	$separators["md"] = ($separators["md"]!="" ? "separator-" . $separators["md"] : "separator-" . ($spacings["md"] * 2));
	$separators["sm"] = ($separators["sm"]!="" ? "separator-sm-" . $separators["sm"] : "separator-sm-" . ($spacings["sm"] * 2));
	$separators["xs"] = ($separators["xs"]!="" ? "separator-xs-" . $separators["xs"] : "separator-xs-" . ($spacings["xs"] * 2));
	$module_separator = "<div class='module_custom_separator " . implode(" ", $separators) . "'></div>";

	//Start module division
	$module_class = "module_custom" . ($module_data["module_class"] ? " " . $module_data["module_class"] : "") . ($module_data["type"]==0 ? " module_layout" : "");
	$custom_attributes = createCustomAttributes($module_data["custom_attributes"]);
	$content = "<div id='$uniqid' class='$module_class $uniqid' $custom_attributes>";
	
	//Module custom background
	if ($module_data["background_custom"] && $module_data["background_file"]){
		$background_attributes = createCustomAttributes($module_data["background_attributes"]);
		
		//Image
		if (isImage($module_data["background_file"])){
			$content .= "<div class=module_background><img $background_attributes src='uploads/classes/" . $module_data["background_file"] . "'></div>";
			
		//Video
		} else {
			$content .= "<div class=module_background><video $background_attributes autoplay loop muted><source src='uploads/classes/" . $module_data["background_file"] . "' type=video/mp4></video></div>";
		}
	}
	
	//Start container
	if ($module_data["container"]){
		$container_class = "container container-basic" . ($module_data["container_class"] ? " " . $module_data["container_class"] : "");
		$content .= "<div class='$container_class'>";
	}
	
	//Top separator
	$content .= $module_separator;
	
	//Title
	if ($module_data["title"] || $module_data["subtitle"]){
		$title_container_class = "module_title" . ($module_data["title_container_class"] ? " " . $module_data["title_container_class"] : "");
		$content .= "<div class='$title_container_class'>";
			if ($module_data["title"]){
				$content .= "<h1 class='" . $module_data["title_class"] . "' " . createAOSTags($module_data["title_animation"]) . ">" . $module_data["title"] . "</h1>";
			}
			if ($module_data["subtitle"]){
				$content .= "<h3 class='" . $module_data["subtitle_class"] . "' " . createAOSTags($module_data["subtitle_animation"]) . ">" . nl2br($module_data["subtitle"]) . "</h3>";
			}
		$content .= "</div>";
		$content .= $module_separator;
	}
	
	//Array to hold available components for removal
	$available_components = array();
	
	//Variable to replace if no content is available
	$no_content = noContent();
	
	//Content
	$content .= "<div class='module_content" . ($module_data["content_container_class"] ? " " . $module_data["content_container_class"] : "") . "'>";
		$content .= "<div class='row " . gridSpacingToClass($module_data["custom_spacing"]) . "' style='justify-content:{$module_data["custom_justify"]}; align-items:{$module_data["custom_align"]}; flex-wrap:{$module_data["custom_wrap"]}'>";
			$sub_modules = mysqlQuery("SELECT * FROM " . $suffix . "website_modules_components WHERE module_uniqid='" . $module_data["uniqid"] . "' ORDER BY arrangement ASC");
			while ($sub_module = mysqlFetch($sub_modules)){
				$custom_attributes = createCustomAttributes($sub_module["attributes"]);
				$content .= "<div class='grid-item " . gridWidthToClass($sub_module["width"]) . " " . $sub_module["class"] . "' " . createAOSTags($sub_module["animation"]) . " $custom_attributes>";
					switch ($sub_module["type"]){
						//Mixed content
						case 0:
							$modules_contents = array();
							preg_match_all("/{{(.*?)}}/", $sub_module["content"], $matches);
							
							foreach ($matches[1] AS $replace){
								//Remove special characters
								$replace = createCanonical($replace);
								
								//Custom module
								if (substr($replace, 0, 6)=="module"){
									$result = customModuleRender($replace);
									if ($result){
										array_push($available_components, $replace);
									}
									$sub_content = ($result ? $result : $no_content);

								//Page variable (cards, date, views, content, gallery, videos, attachments, cover, navigation)
								} else if (substr($replace, 0, 8)=="variable"){
									$result = $variables[$replace];
									if ($result){
										array_push($available_components, $replace);
									}
									$sub_content = ($result ? $result : $no_content);
									
								//Custom display
								} else if (substr($replace, 0, 7)=="display"){
									$result = customDisplayRender($replace);
									if ($result){
										array_push($available_components, $replace);
									}
									$sub_content = ($result ? $result : $no_content);

								//Custom form
								} else if (substr($replace, 0, 4)=="form"){
									$result = customFormRender($replace);
									if ($result){
										array_push($available_components, $replace);
									}
									$sub_content = ($result ? $result : $no_content);
									
								//Built-In module
								} else if (file_exists("modules/$replace.php")){
									ob_start();
									include "modules/$replace.php";
									$result = ob_get_clean();
									if ($result){
										array_push($available_components, $replace);
									}
									$sub_content = ($result ? $result : $no_content);
								
								//Invalid content
								} else {
									$sub_content = null;
								}
								
								//Push to replacement array
								if ($sub_content){
									$modules_contents["{{" . $replace . "}}"] = $sub_content;
								}
							}
							
							//Generic content
							$mixed_content = htmlContent($sub_module["content"], false, false);
							$content .= str_replace(array_keys($modules_contents), array_values($modules_contents), $mixed_content);
						break;
						
						//Built-In module
						case 1:
							$replace = $sub_module["content"];
							ob_start();
							include "modules/" . $sub_module["content"] . ".php";
							$result = ob_get_clean();
							if ($result){
								array_push($available_components, $replace);
							}
							$content .= ($result ? $result : $no_content);
						break;
						
						//Page variable (cards, date, views, content, gallery, videos, attachments, cover, navigation)
						case 2:
							$replace = $sub_module["content"];
							$result = $variables[$replace];
							if ($result){
								array_push($available_components, $replace);
							}
							$content .= ($result ? $result : $no_content);
						break;						

						//Custom module
						case 3:
							$replace = $sub_module["content"];
							$result = customModuleRender($sub_module["content"]);
							if ($result){
								array_push($available_components, $replace);
							}
							$content .= ($result ? $result : $no_content);
						break;

						//Custom display
						case 4:
							$replace = $sub_module["content"];
							$result = customDisplayRender($replace);
							if ($result){
								array_push($available_components, $replace);
							}
							$content .= ($result ? $result : $no_content);
						break;
						
						//Custom form
						case 5:
							$replace = $sub_module["content"];
							$result = customFormRender($sub_module["content"]);
							if ($result){
								array_push($available_components, $replace);
							}
							$content .= ($result ? $result : $no_content);
						break;
					}			
				$content .= "</div>";
			}
		$content .= "</div>";
	$content .= "</div>";
	
	//Buttons
	if ($module_data["buttons"]){
		$content .= $module_separator;
		$buttons = json_decode($module_data["buttons"],true);
		$buttons_container_class = "module_buttons" . ($module_data["buttons_container_class"] ? " " . $module_data["buttons_container_class"] : "");
		$content .= "<div class='$buttons_container_class'>";
			foreach ($buttons AS $key=>$value){
				$button_class = ($value["class"] ? $value["class"] : "btn btn-primary btn-sm");
				$content .= "<a class='$button_class' href='" . $value["url"] . "' " . createAOSTags(html_entity_decode($value["animation"])) . ">" . $value["title"] . "</a>";
			}			
		$content .= "</div>";
	}

	//Bottom separator
	$content .= $module_separator;
	
	//Close container
	if ($module_data["container"]){
		$content .= "</div>";
	}
	
	//Close module division
	$content .= "</div>";
	
	//Replace global parameters
	$content = replaceGlobalParameters($content);
	
	//Remove conditional DOM objects
	global $panel_path;
	require_once($panel_path . "/snippets/simple_html_dom.php");
	$html = str_get_html($content);
	foreach($html->find("div[data-condition]") AS $conditioned){
		$attribute_value = $conditioned->attr["data-condition"];
		if (substr($attribute_value, 0, 9)=="parameter"){
			$explode = explode("-", $attribute_value);
			$remove = !$global_parameters[$explode[1]][$explode[2]];
		} else {
			$remove = !in_array($attribute_value, $available_components);
		}
		if ($remove){
			$conditioned->outertext = "";
		}
	}
	
	return $html;
}

//Check for duplicate modules resulting in an infinite loop
function customModuleCheck($uniqid, $array=array()){
	global $suffix;
	$used_modules = array($uniqid);
	$used_modules = array_merge($used_modules, $array);
	$sub_modules = mysqlQuery("SELECT * FROM " . $suffix . "website_modules_components WHERE type IN (0,3,4) AND module_uniqid='$uniqid'");
	while ($sub_module = mysqlFetch($sub_modules)){
		//Mixed content
		if ($sub_module["type"]==0){
			$modules_contents = array();
			preg_match_all("/{{(.*?)}}/", $sub_module["content"], $matches);
			foreach ($matches[1] AS $module){
				//Custom module
				if (substr($module, 0, 6)=="module"){
					if (!in_array($sub_module["content"], $used_modules)){
						$sub_results = customModuleCheck($module, $used_modules);
						if ($sub_results !== false){
							$used_modules = array_merge($used_modules, $sub_results);
						} else {
							return false;
						}
					} else {
						return false;
					}
				
				//Custom display
				} else if (substr($module, 0, 7)=="display"){
					$display_modules = getData($suffix . "website_custom_displays", "uniqid", $module, "modules_layout");
					if ($display_modules){
						$display_modules = array_unique(explode(",", $display_modules));
						foreach ($display_modules AS $display_module){
							if (!in_array($display_module, $used_modules)){
								$sub_results = customModuleCheck($display_module, $used_modules);
								if ($sub_results !== false){
									$used_modules = array_merge($used_modules, $sub_results);
								} else {
									return false;
								}
							} else {
								return false;
							}						
						}
					}
				}
			}

		//Custom module
		} else if ($sub_module["type"]==3){
			if (!in_array($sub_module["content"], $used_modules)){
				$sub_results = customModuleCheck($sub_module["content"], $used_modules);
				if ($sub_results!==false){
					$used_modules = array_merge($used_modules, $sub_results);
				} else {
					return false;
				}
			} else {
				return false;
			}
			
		//Custom display
		} else if ($sub_module["type"]==4){
			$display_modules = mysqlFetch(mysqlQuery("SELECT modules_layout FROM " . $suffix . "website_custom_displays WHERE uniqid='" . $sub_module["content"] . "' AND source=1"))["modules_layout"];
			if ($display_modules){
				$display_modules = array_unique(explode(",", $display_modules));
				foreach ($display_modules AS $display_module){
					if (!in_array($display_module, $used_modules)){
						$sub_results = customModuleCheck($display_module, $used_modules);
						if ($sub_results!==false){
							$used_modules = array_merge($used_modules, $sub_results);
						} else {
							return false;
						}
					} else {
						return false;
					}
				}
			}
		}
	}
	return $used_modules;
}

//Render custom display
function customDisplayRender($uniqid, $template_block=null, $template_data=null){
	extract($GLOBALS);
	
	$display_data = getData($suffix . "website_custom_displays", "uniqid", $uniqid);
	$display_identifier = $display_data["uniqid"] . rand(1000,9999);
	$display_blocks = null;
	$blocks_contents = array();
	$max = ($display_data["type"]==0 ? $display_data["grid_blocks_count"] : $display_data["slides_count"]);
	
	//Display blocks content source
	switch ($display_data["source"]){
		//Child pages
		case 0:
			if (!$display_data["source_content"]){ break; }
			$result = mysqlQuery("SELECT * FROM " . $suffix . "website_pages_custom WHERE parent='" . $display_data["source_content"] . "' ORDER BY priority DESC LIMIT 0,$max");
			while ($entry = mysqlFetch($result)){
				ob_start();
				$block = customPageBlock($entry);
				include "blocks/" . createCanonical($display_data["blocks_template"]) . ".php";
				array_push($blocks_contents, ob_get_clean());
			}
		break;
		
		//Selected pages
		case 1:
			if (!$display_data["source_content"]){ break; }
			$result = mysqlQuery("SELECT * FROM " . $suffix . "website_pages_custom WHERE id IN (" . $display_data["source_content"] . ") ORDER BY priority DESC LIMIT 0,$max");
			while ($entry = mysqlFetch($result)){
				ob_start();
				$block = customPageBlock($entry);
				include "blocks/" . createCanonical($display_data["blocks_template"]) . ".php";
				array_push($blocks_contents, ob_get_clean());
			}
		break;
		
		//Custom modules
		case 2:
			if (!$display_data["source_content"]){ break; }
			$modules = explode(",", $display_data["source_content"]);
			foreach ($modules AS $key=>$module){
				array_push($blocks_contents, customModuleRender($module));
				if ($key + 1 >= $max){
					break;
				}
			}
		break;
		
		//Custom query
		case 3:
			if (!$display_data["source_content"]){ break; }
			$target = getID($display_data["source_content"], "system_queries");
			$conditions = ($target["conditions"] ? "WHERE " . $target["conditions"] : "");
			$statement = "SELECT * FROM " . $target["target"] . " $conditions ORDER BY " . $target["sort_column"] . " " . $target["sort_method"] . " LIMIT 0,$max";
			
			//Replace query statement global parameters
			$statement = str_replace(["'{", "}'"], ["{", "}"], $statement); //Remove single quote around global parameter
			$statement = replaceGlobalParameters($statement);
			
			$result = mysqlQuery($statement);
			while ($entry = mysqlFetch($result)){
				ob_start();
				$block = (substr($target["target"], 3)=="website_pages_custom" ? customPageBlock($entry) : builtPageBlock($target["target"], $entry));
				include "blocks/" . createCanonical($display_data["blocks_template"]) . ".php";
				array_push($blocks_contents, ob_get_clean());
			}
		break;
		
		//Display template
		case 4:
			foreach ($template_data AS $key=>$entry){
				ob_start();
				$block = builtPageBlock($template_block, $entry);
				include "blocks/" . createCanonical($display_data["blocks_template"]) . ".php";
				array_push($blocks_contents, ob_get_clean());
				if ($key + 1 == $max){
					break;
				}
			}
		break;		
	}
	
	//====== Grid =====
	if ($display_data["type"]==0){

	//Render display blocks
	foreach ($blocks_contents AS $block_content){
		$display_blocks .= "<div class='grid-item " . gridCountToClass($display_data["grid_blocks_per_row"]) . " " . $display_data["grid_blocks_class"] . "' " . createAOSTags($display_data["grid_blocks_animation"]) . ">" . $block_content . "</div>";
	}

	//Grid component
	$display = "<div class='row " . gridSpacingToClass($display_data["grid_blocks_spacing"]) . "' style='justify-content: " . $page_data["grid_justify"] . "; align-items: " . $page_data["grid_align"] . "'>
		$display_blocks
	</div>";
	
	//====== Slider =====
	} else {

	//Stretch Height
	if ($display_data["slide_stretch_height"]){
		$slide_style = "style='height: auto !important'";
		$wrapper_style = "style='align-items: stretch !important'";
		$animation_attributes = "block-animation-container";
	}
	
	//Render display blocks
	if ($display_data["slide_animation"]){
		$slide_animation = createAOSTags($display_data["slide_animation"]);
	}
	foreach ($blocks_contents AS $block_content){
		$block_content = ($slide_animation ? "<div $animation_attributes $slide_animation>$block_content</div>" : $block_content);
		$display_blocks .= "<div class='swiper-slide " . $display_data["slide_class"] . "' $slide_style>" . $block_content . "</div>";
	}		

	//Cover flow
	if ($display_data["slides_effect"]==1){
		$slides_effect = json_decode($display_data["slides_creative"], true);
		$translate_prev["x"] = ($slides_effect["prev_translate_x"] ? (is_numeric($slides_effect["prev_translate_x"]) ? $slides_effect["prev_translate_x"] : "'" . $slides_effect["prev_translate_x"] . "'") : 0);
		$translate_prev["y"] = ($slides_effect["prev_translate_y"] ? (is_numeric($slides_effect["prev_translate_y"]) ? $slides_effect["prev_translate_y"] : "'" . $slides_effect["prev_translate_y"] . "'") : 0);
		$translate_prev["z"] = ($slides_effect["prev_translate_z"] ? (is_numeric($slides_effect["prev_translate_z"]) ? $slides_effect["prev_translate_z"] : "'" . $slides_effect["prev_translate_z"] . "'") : 0);
		$translate_next["x"] = ($slides_effect["next_translate_x"] ? (is_numeric($slides_effect["next_translate_x"]) ? $slides_effect["next_translate_x"] : "'" . $slides_effect["next_translate_x"] . "'") : 0);
		$translate_next["y"] = ($slides_effect["next_translate_y"] ? (is_numeric($slides_effect["next_translate_y"]) ? $slides_effect["next_translate_y"] : "'" . $slides_effect["next_translate_y"] . "'") : 0);
		$translate_next["z"] = ($slides_effect["next_translate_z"] ? (is_numeric($slides_effect["next_translate_z"]) ? $slides_effect["next_translate_z"] : "'" . $slides_effect["next_translate_z"] . "'") : 0);
		$slides_effect = "
			effect: 'creative',
			creativeEffect: {
				limitProgress: " . ($slides_effect["limit"] ?: 1) . ",
				perspective: " . ($slides_effect["perspective"] ? "true" : "false") . ",
				shadowPerProgress: " . ($slides_effect["shadow"] ? "true" : "false") . ",
				prev: {
					translate: [" . $translate_prev["x"] . "," . $translate_prev["y"] . "," . $translate_prev["z"] . "],
					rotate: [" . ($slides_effect["prev_rotate_x"] ?: 0) . "," . ($slides_effect["prev_rotate_y"] ?: 0) . "," . ($slides_effect["prev_rotate_z"] ?: 0) . "],
					opacity: " . ($slides_effect["prev_opacity"] ?: 1) . ",
					scale: " . ($slides_effect["prev_scale"] ?: 1) . ",
					shadow: " . ($slides_effect["prev_shadow"] ? "true" : "false") . ",
					origin: '" . ($slides_effect["prev_origin"] ?: "center center") . "'
				},
				next: {
					translate: [" . $translate_next["x"] . "," . $translate_next["y"] . "," . $translate_next["z"] . "],
					rotate: [" . ($slides_effect["next_rotate_x"] ?: 0) . "," . ($slides_effect["next_rotate_y"] ?: 0) . "," . ($slides_effect["next_rotate_z"] ?: 0) . "],
					opacity: " . ($slides_effect["next_opacity"] ?: 1) . ",
					scale: " . ($slides_effect["next_scale"] ?: 1) . ",
					shadow: " . ($slides_effect["next_shadow"] ? "true" : "false") . ",
					origin: '" . ($slides_effect["next_origin"] ?: "center center") . "'
				}
			},
		";
	
	//Creative
	} else if ($display_data["slides_effect"]==2){
		$slides_effect = json_decode($display_data["slides_cover_flow"], true);
		$slides_effect = "
			effect: 'coverflow',
			coverflowEffect: {
				rotate: " . ($slides_effect["rotate"] ?: 0) . ",
				stretch: " . ($slides_effect["stretch"] ?: 0) . ",
				depth: " . ($slides_effect["depth"] ?: 0) . ",
				slideShadows: " . ($slides_effect["shadows"] ? "true" : "false") . "
			},
		";
	
	//Fade
	} else if ($display_data["slides_effect"]==3){
		$slides_effect = "
			effect: 'fade',
			fadeEffect: {
				crossFade: true
			},
		";
	}
	
	//Auto play
	if ($display_data["slides_auto_play"]){
		$slides_auto_play = json_decode($display_data["slides_auto_play"], true);
		$slides_auto_play = "
			autoplay: {
				delay: " . ($slides_auto_play["delay"] ?: 1000) . ",
				disableOnInteraction: " . ($slides_auto_play["disable_interaction"] ? "true" : "false") . ",
				pauseOnMouseEnter: " . ($slides_auto_play["pause_mouse"] ? "true" : "false") . ",
				reverseDirection: " . ($slides_auto_play["reverse_direction"] ? "true" : "false") . "
			},
		";
	}
	
	//Arrows
	if ($display_data["arrows_enable"]){
		$arrows_next = "<div class='swiper-button-next " . $display_data["arrows_class"] . "'></div>";
		$arrows_previous = "<div class='swiper-button-prev " . $display_data["arrows_class"] . "'></div>";
		$arrows_settings = "
		navigation: {
			nextEl: '.$display_identifier .swiper-button-next',
			prevEl: '.$display_identifier .swiper-button-prev',
		},";
	}
	
	//Bullets (Pagination)
	if ($display_data["bullets_enable"]){
		$bullets_dom = "<div class='swiper-pagination " . $display_data["bullets_container_class"] . "'></div>";
		$bullets_settings = "
		pagination: {
			el: '.$display_identifier .swiper-pagination',
			bulletClass: '" . ($display_data["bullets_class"] ? $display_data["bullets_class"] : "swiper-pagination-bullet") . "',
			bulletActiveClass: '" . ($display_data["bullets_class"] ? $display_data["bullets_class"] . "-active" : "swiper-pagination-bullet-active") . "',
			clickable: true,
		},";
	}
	
	//Speed
	$speed = (intval($display_data["slides_speed"]) ? intval($display_data["slides_speed"]) : 300);
	
	//Slider component
	$display = "
	<div class='slider_custom $uniqid $display_identifier'>
		<div class='slider_custom_components'>
		$arrows_previous
			<div id=$display_identifier class='swiper " . $display_data["slides_container_class"] . "' style='margin:0 " . $display_data["arrows_spacing"] . "px 0 " . $display_data["arrows_spacing"] . "px'>
				<div class=swiper-wrapper $wrapper_style>
					$display_blocks
				</div>
			</div>
		$arrows_next
		</div>
		$bullets_dom
	</div>
	<script>
	if (typeof swiperInstances === 'undefined'){
		var swiperInstances = [];
	}
	swiperInstances['$display_identifier'] = new Swiper('#$display_identifier', {
		slidesPerView: " . ($display_data["slides_per_view"] ? $display_data["slides_per_view"] : 'auto') . ",
		grid: {
			rows: " . $display_data["slides_per_column"] . ",
			fill: 'row'
		},
		spaceBetween: " . $display_data["slides_space_between"] . ",
		centeredSlides: " . ($display_data["slides_center"] ? "true" : "false") . ",
		autoHeight: " . ($display_data["slide_auto_height"] ? "true" : "false") . ",
		loop: " . ($display_data["slides_loop"] ? "true" : "false") . ",
		speed: $speed,
		$slides_effect
		$slides_auto_play
		$bullets_settings
		$arrows_settings
		observer: true,
		observeParents: true,
		watchSlidesProgress: true,
		on: {
			init: function(){
				$('.$display_identifier').find('.swiper-slide').removeClass('active');
				$('.$display_identifier').find('.swiper-slide-active').addClass('active');
				if (typeof timerInitiated == 'undefined'){
					window['timerInitiated'] = true;
					setInterval(() => {
						$('.swiper-slide:not(.swiper-slide-visible):not(.swiper-slide-duplicate-active) [data-aos]').removeClass('aos-animate').attr('style','');
					}, 1000);
				}
			},
			slideChangeTransitionStart: () => {
				$('.$display_identifier .swiper-slide-duplicate-active [data-aos], .$display_identifier .swiper-slide.active [data-aos]').addClass('aos-animate');
			},
			slideChange: function(){
				setTimeout(function(){
					$('.$display_identifier').find('.swiper-slide').removeClass('active');
					$('.$display_identifier').find('.swiper-slide-active').addClass('active');
					$('.$display_identifier .swiper-slide.swiper-slide-visible [data-aos]').addClass('aos-animate');
				}, " . round($speed / 2) . ");
			}
		},
		breakpoints: {
			0: { slidesPerView: 1 },
			768: { slidesPerView: " . round($display_data["slides_per_view"] / 2) . " },
			992: { slidesPerView: " . $display_data["slides_per_view"] . " }
		}
	});
	</script>";
	}

	return ($blocks_contents ? $display : null);
}

//Custom form render
function customFormRender($uniqid){
	global $suffix;
	global $logged_user;
	
	$form_data = getData($suffix . "website_forms", "uniqid", $uniqid);
	if (!$form_data){
		return null;
	}
	
	$input_data = json_decode($form_data["form"], true);
	$can_submit = customFormCheck($form_data);
	
	if ($form_data["closed"]){
		$return = htmlContent($form_data["closed_message"]);
	
	} else if ($form_data["require_login"] && !$logged_user){
		$return = htmlContent($form_data["login_message"]);
		
	} else if (!$can_submit){
		$return = htmlContent($form_data["success_message"]);
		
	} else {
		$return = "<form id='$uniqid' class='" . $form_data["form_class"] . " " . $form_data["form_template"] . "' method=post enctype='multipart/form-data'>";
		$return .= "<div class='row grid-container-" . $form_data["form_spacing"] . " input_container' " . createCustomAttributes($form_data["form_attributes"]) . ">";
		
		//Loop throug input data
		foreach ($input_data AS $key=>$data){
			//Get form input rendering parameters
			$input = customFormInput($data);
			
			//Start buffer and include input template
			ob_start();
			include "website/templates/" . $form_data["form_template"] . ".php";
			$return .= ob_get_clean();
		}
		$return .= "</div>";
		
		$return .= "<div class='" . ($form_data["btn_container_class"] ? $form_data["btn_container_class"] : "submit_container") . "'>
			<button type=button class='" . ($form_data["btn_class"] ? $form_data["btn_class"] : "btn btn-primary btn-insert") . "' onclick='customFormSubmit(this)'>" . $form_data["btn_text"] . "</button>
		</div>";
		$return .= "</form>";
	}
	
	return $return;
}

//Render custom form inputs
function customFormInput($element){
	//Set element parameters
	$name = $element["id"];
	$type = $element["type"];
	$props = $element["properties"];
	
	//Default input template parameters
	$return = array(
		"label" => $props["label"],
		"icon" => $props["icon"],
		"description" => $props["description"]
	);
	
	//Custom attributes (attributes)
	$attributes = createCustomAttributes(json_encode($props["attributes"]));
	
	//Input validation (mandatory, error_msg, regex, min_number, max_number, allow_float)
	$validation = array();
	
	if (!$props["mandatory"]){
		$validation["data-validation-optional"] = "true";
	}
	
	if ($props["error_msg"]){
		$validation["data-validation-error-msg"] = $props["error_msg"];
	}
	
	if ($props["regex"]){
		$validation["data-validation"] = "custom";
		$validation["data-validation-regexp"] = $props["regex"];
	
	} else if ($type=="number"){
		if ($props["allow_float"]) $allowing[] = "float";
		$min = ($props["min_number"] ? $props["min_number"] : 0);
		$max = ($props["max_number"] ? $props["max_number"] : 999999999);
		$allowing[] = "range[$min;$max]";
		if ($props["min_number"] < 0){
			$allowing[] = "negative";
		}
		$validation["data-validation"] = "number";
		$validation["data-validation-allowing"] = implode(",", $allowing);
	
	} else if ($type=="email"){
		$validation["data-validation"] = "email";
	
	} else if ($type=="checkbox"){
		if ($props["min_selections"] && $props["max_selections"]) $quantity = "$props[min_selections]-$props[max_selections]";
		elseif ($props["min_selections"]) $quantity = "min$props[min_selections]";
		elseif ($props["min_selections"]) $quantity = "max$props[max_selections]";
		if ($quantity){
			$validation["data-validation"] = "checkbox_group";
			$validation["data-validation-qty"] = $quantity;
		}
		
	} else if ($type=="multiple_select"){
		if ($props["min_selections"] && $props["max_selections"]) $quantity = "$props[min_selections]-$props[max_selections]";
		elseif ($props["min_selections"]) $quantity = "min$props[min_selections]";
		elseif ($props["min_selections"]) $quantity = "max$props[max_selections]";
		if ($quantity){
			$validation["data-validation"] = "length";
			$validation["data-validation-length"] = $quantity;
		}
	}
	
	if (!$validation["data-validation-optional"] && !$validation["data-validation"]){
		$validation["data-validation"] = "required";
	}
	
	$validation = array_map(function($k, $v){
		return "$k='$v'";
	}, array_keys($validation), array_values($validation));
	$validation = implode(" ", $validation);

	//Element built-in attributes (step, maxlength, verbose)
	$parameters = array();
	if ($props["step"]){
		array_push($parameters, "step='{$props["step"]}'");
	}
	if ($props["maxlength"]){
		array_push($parameters, "maxlength='{$props["maxlength"]}'");
	}
	if ($props["verbose"]){
		array_push($parameters, "verbose");
	}
	$parameters = implode(" ", $parameters);

	//Placeholder
	if ($props["placeholder"]){
		$placeholder = "placeholder='" . $props["placeholder"] . "'";
	}

	//Input types
	switch ($type){
		//Heading
		case "heading":
			unset($return["label"]);
			$return["dom"] = "<h1 $attributes>" . $props["label"] . "</h1>";
		break;
		
		//Paragraph
		case "plain":
			$return["dom"] = "<p $attributes>" . nl2br(html_entity_decode($props["content"])) . "</p>";
		break;	

		//Text
		case "text":
			$return["dom"] = "<input type=text input-name='$name' input-type='$type' value='{$props["default"]}' $validation $placeholder $parameters $attributes>";
		break;
		
		//Textarea
		case "textarea":
			$return["dom"] = "<textarea input-name='$name' input-type='$type' $validation $placeholder $parameters $attributes>{$props["default"]}</textarea>";
		break;
		
		//Number
		case "number":
			$return["dom"] = "<input type=number input-name='$name' input-type='$type' value='{$props["default"]}' $validation $placeholder $parameters $attributes>";
		break;
		
		//EMail
		case "email":
			$return["dom"] = "<input type=email input-name='$name' input-type='$type' value='{$props["default"]}' $validation $placeholder $parameters $attributes>";
		break;
		
		//Mobile
		case "mobile":
			$return["dom"] = "<input type=number input-name='$name' input-type='$type' value='{$props["default"]}' $validation $placeholder $parameters $attributes>";
		break;
		
		//File
		case "file":
			$return["dom"] = "<input type=file name='$name' input-name='$name' input-type='$type' $validation $placeholder $parameters $attributes>";
		break;	
		
		//Radio
		case "radio":
			$target_options = ($props["value_source"] ? customFormOptions($props["value_source"], $props["source_target"]) : $props["options"]);
			foreach ($target_options AS $option){
				$value = $option["value"];
				$checked = ($option["default"] ? "checked" : "");
				$options .= "<label><input type=radio name='{$name}' value='$value' $validation $parameters $attributes $checked><span>$value</span></label>";
			}
			$return["dom"] = "<div class=radio_container input-name='$name' input-type='$type'>$options</div>";
		break;
		
		//Checkbox
		case "checkbox":
			$target_options = ($props["value_source"] ? customFormOptions($props["value_source"], $props["source_target"]) : $props["options"]);
			foreach ($target_options AS $option){
				$value = $option["value"];
				$checked = ($option["default"] ? "checked" : "");
				$options .= "<label><input type=checkbox name='{$name}[]' value='$value' class=filled-in  $validation $parameters $attributes $checked><span>$value</span></label>";
			}
			$return["dom"] = "<div class=check_container input-name='$name' input-type='$type'>$options</div>";
		break;
		
		//Single Select
		case "single_select":
			$target_options = ($props["value_source"] ? customFormOptions($props["value_source"], $props["source_target"]) : $props["options"]);
			foreach ($target_options AS $option){
				$value = $option["value"];
				$selected = ($option["default"] ? $option["value"] : $selected);
				$options .= "<option value='$value'>$value</option>";
			}

			$return["dom"] = "<select input-name='$name' input-type='$type' $validation $parameters $attributes>$options</select>";
			$return["dom"] .= "<script>
				$('[input-name=$name]').select2({
					tags: " . ($props["allow_other"] ? "true" : "false") . ",
					allowClear: true,
					minimumResultsForSearch: " . ($props["allow_search"] ? 1 : "Infinity") . ",
					placeholder: '" . ($props["placeholder"] ? $props["placeholder"] : "Select") . "'
				}).val('$selected').trigger('change');
			</script>";
		break;
		
		//Multiple Select
		case "multiple_select":
			$target_options = ($props["value_source"] ? customFormOptions($props["value_source"], $props["source_target"]) : $props["options"]);
			foreach ($target_options AS $option){
				$value = $option["value"];
				$selected = ($option["default"] ? "selected" : "");
				$options .= "<option value='$value' $selected>$value</option>";
			}

			$return["dom"] = "<select input-name='{$name}[]' input-type='$type' multiple $validation $parameters $attributes>$options</select>";
			$return["dom"] .= "<script>
				$('[input-name^=$name]').select2({
					tags: " . ($props["allow_other"] ? "true" : "false") . ",
					minimumResultsForSearch: " . ($props["allow_search"] ? 1 : "Infinity") . ",
					placeholder: '" . ($props["placeholder"] ? $props["placeholder"] : "Select") . "'
				}).val('$selected').trigger('change');
			</script>";
		break;
		
		//Date
		case "date":
			if ($props["min_date"]=="fixed" && $props["min_date_fixed"]){
				$minDate = "new Date(" . getTimestamp($props["min_date_fixed"]) . "000)";
			} else if ($props["min_date"] && $props["min_date_custom"]){
				$minDate = "new Date(" . strtotime($props["min_date_custom"]) . "000)";
			} else {
				$minDate = "null";
			}
			if ($props["max_date"]=="fixed" && $props["max_date_fixed"]){
				$maxDate = "new Date(" . getTimestamp($props["max_date_fixed"]) . "000)";
			} else if ($props["max_date"]=="custom" && $props["max_date_custom"]){
				$maxDate = "new Date(" . strtotime($props["max_date_custom"]) . "000)";
			} else {
				$maxDate = "null";
			}
			$return["dom"] = "<input type=text readonly input-name='$name' input-type='$type' $validation $parameters $attributes>";
			$return["dom"] .= "<script>
				createCalendar($('[input-name=$name]'), new Date(0), $minDate, $maxDate, null, null, false, true)
			</script>";
		break;
	}

	//Width
	$return["width"] = gridWidthToClass($props["width"]);
	$return["required"] = $props["mandatory"];
	
	return $return;
}

//Render custom form options
function customFormOptions($source, $column="id"){
	$options = array();

	//System query
	if (is_numeric($source)){
		$target = getID($source, "system_queries");
		$table = $target["target"];
		$conditions = ($target["conditions"] ? "WHERE " . $target["conditions"] : "");
		$result = mysqlQuery("SELECT $column FROM $table $conditions ORDER BY " . $target["sort_column"] . " " . $target["sort_method"]);
		while ($entry = mysqlFetch($result)){
			array_push($options, array("value"=>$entry[$column]));
		}
		
	//Variable
	} else {
		global $$source;
		foreach ($$source AS $value){
			array_push($options, array("value"=>$value));
		}
	}
	
	return $options;
}

//Check if form is eligible for submission
function customFormCheck($form){
	global $suffix;
	if ($form["once_device"]){
		global $form_cookie;
		$disabled_device = $_COOKIE[$form["uniqid"]];
	}
	if ($form["once_user"] && $form["require_login"]){
		global $logged_user;
		$disabled_user = $logged_user["id"] && mysqlNum(mysqlQuery("SELECT ip FROM " . $suffix . "website_forms_records WHERE form_id='" . $form["id"] . "' AND user_id='" . $logged_user["id"] . "'"));
	}
	if ($form["once_ip"]){
		$disabled_ip = mysqlNum(mysqlQuery("SELECT ip FROM " . $suffix . "website_forms_records WHERE form_id='" . $form["id"] . "' AND ip='" . getClientIP() . "'"));
	}
	return !$form["closed"] && !$disabled_device && !$disabled_user && !$disabled_ip;
}

//Replace global parameters in content
function replaceGlobalParameters($content){
	global $global_parameters;
	preg_match_all("/{{parameter-(.*?)}}/", $content, $matches);
	foreach ($matches[1] AS $replace){
		$explode = explode("-", $replace);
		$variable_key = $explode[0];
		$variable_value = $explode[1];
		$result = $global_parameters[$variable_key][$variable_value];
		$parameters_replacements["{{parameter-" . $replace . "}}"] = $result;
	}
	$content = ($parameters_replacements ? str_replace(array_keys($parameters_replacements), array_values($parameters_replacements), $content) : $content);
	return $content;
}

//Create AOS animation tags & attributes from json
function createAOSTags($json){
	if (!$json){
		return null;
	}
	$attributes = array();
	$animation = json_decode($json, true);
	foreach ($animation AS $attribute=>$value){
		if ($value){
			array_push($attributes, "data-$attribute='$value'");
		}
	}
	return implode(" ", $attributes);
}

//Create custom attributes
function createCustomAttributes($json){
	if (!$json){
		return null;
	}
	$attributes = array();
	$custom_attributes = json_decode($json, true);
	foreach ($custom_attributes AS $key=>$value){
		array_push($attributes, ($value["value"] ? $value["attribute"] . "='" . $value["value"] . "'" : $value["attribute"]));
	}
	return implode(" ", $attributes);		
}

//Parse grid spacings to class
function gridSpacingToClass($spacing){
	if (!$spacing){
		return null;
		
	} else if (is_numeric($spacing)){
		return "grid-container-" . $spacing;
		
	} else {
		if (!is_array($spacing)){
			$spacing = json_decode($spacing, true);
		}
		$classes = array(
			"grid-container-" . $spacing["md"],
			"grid-container-sm-" . $spacing["sm"],
			"grid-container-xs-" . $spacing["xs"]
		);
		return implode(" ", $classes);
	}
}

//Parse grid spacings to class
function gridCountToClass($count){
	global $data_columns_count;
	
	if (!$count){
		return null;
		
	} else if (is_numeric($count)){
		return "col-md-" . $data_columns_count[$count];
		
	} else {
		if (!is_array($count)){
			$count = json_decode($count, true);
		}
		$classes = array(
			"col-md-" . $data_columns_count[$count["md"]],
			"col-sm-" . $data_columns_count[$count["sm"]],
			"col-xs-" . $data_columns_count[$count["xs"]]
		);
		return implode(" ", $classes);
	}
}

//Parse widths objects to bootstrap classes
function gridWidthToClass($widths){
	global $data_module_widths;
	
	if (!$widths){
		return;
		
	} else if (is_numeric($widths)){
		return "col-md-${data_module_widths[$widths]}";
	
	} else {
		if (!is_array($widths)){
			$widths = json_decode($widths, true);
		}
		$classes = array();
		foreach ($widths AS $screen=>$value){
			$class = "col-$screen-${data_module_widths[$value]}";
			array_push($classes, $class);
		}
		return implode(" ", $classes);
	}
}

//Create built-in schemas
function builtInSchemas(){
	global $data_pages_types;
	global $data_no_yes;
	
	//Read labels by language
	$label["id"] = readLanguage(general,id);
	$label["type"] = readLanguage(builder,type);
	$label["parent"] = readLanguage(builder,page_parent);
	$label["title"] = readLanguage(inputs,title);
	$label["date"] = readLanguage(inputs,date);
	$label["hidden"] = readLanguage(inputs,hidden);
	$label["priority"] = readLanguage(general,priority);
	
	//en_website_pages_custom
	$website_pages_custom = array(
		"English" => "en_website_pages_custom",
		"عربي" => "ar_website_pages_custom"
	);
	foreach ($website_pages_custom AS $language=>$table){
		if (mysqlNum(mysqlQuery("SHOW TABLES LIKE '$table'"))){
			$schema[$table] = [
				'label' => readLanguage(builder,pages) . " ($language)",
				'fields' => [
					'id' => ['label' => $label["id"], 'type' => 'number'],
					'type' => ['label' => $label["type"], 'type' => 'list', 'source' => $data_pages_types],		
					'parent' => ['label' => $label["parent"], 'type' => 'number'],		
					'title' => ['label' => $label["title"], 'type' => 'string'],
					'date' => ['label' => $label["date"], 'type' => 'date'],
					'hidden' => ['label' => $label["hidden"], 'type' => 'list', 'source' => $data_no_yes],
					'priority' => ['label' => $label["priority"], 'type' => 'number'],
				],
			];
		}	
	}
	
	return $schema;
}

//Minify CSS
function minifyCSS($input){
    if(trim($input) === "") return $input;
    return preg_replace(
        array(
            // Remove comment(s)
            '#("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\')|\/\*(?!\!)(?>.*?\*\/)|^\s*|\s*$#s',
            // Remove unused white-space(s)
            '#("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\'|\/\*(?>.*?\*\/))|\s*+;\s*+(})\s*+|\s*+([*$~^|]?+=|[{};,>~]|\s(?![0-9\.])|!important\b)\s*+|([[(:])\s++|\s++([])])|\s++(:)\s*+(?!(?>[^{}"\']++|"(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\')*+{)|^\s++|\s++\z|(\s)\s+#si',
            // Replace `0(cm|em|ex|in|mm|pc|pt|px|vh|vw|%)` with `0`
            '#(?<=[\s:])(0)(cm|em|ex|in|mm|pc|pt|px|vh|vw|%)#si',
            // Replace `:0 0 0 0` with `:0`
            '#:(0\s+0|0\s+0\s+0\s+0)(?=[;\}]|\!important)#i',
            // Replace `background-position:0` with `background-position:0 0`
            '#(background-position):0(?=[;\}])#si',
            // Replace `0.6` with `.6`, but only when preceded by `:`, `,`, `-` or a white-space
            '#(?<=[\s:,\-])0+\.(\d+)#s',
            // Minify string value
            '#(\/\*(?>.*?\*\/))|(?<!content\:)([\'"])([a-z_][a-z0-9\-_]*?)\2(?=[\s\{\}\];,])#si',
            '#(\/\*(?>.*?\*\/))|(\burl\()([\'"])([^\s]+?)\3(\))#si',
            // Replace `(border|outline):none` with `(border|outline):0`
            '#(?<=[\{;])(border|outline):none(?=[;\}\!])#',
            // Remove empty selector(s)
            '#(\/\*(?>.*?\*\/))|(^|[\{\}])(?:[^\s\{\}]+)\{\}#s'
        ),
        array(
            '$1',
            '$1$2$3$4$5$6$7',
            '$1',
            ':0',
            '$1:0 0',
            '.$1',
            '$1$3',
            '$1$2$4$5',
            '$1:0',
            '$1$2'
        ),
    $input);
}

//Build CSS classes file
function buildCSSClasses(){
	$css = "../website/_classes.css";
	
	$result = mysqlQuery("SELECT * FROM website_classes");
	while ($entry = mysqlFetch($result)){
		$classes .= $entry["css"] . "\r\n\r\n";
		if ($entry["custom_css"]){
			$classes .= $entry["custom_css"] . "\r\n\r\n";
		}		
	}
	
	$classes = minifyCSS(html_entity_decode($classes,ENT_QUOTES));
	
	unlink($css);
	file_put_contents($css, $classes, LOCK_EX);
	mysqlQuery("UPDATE website_theme SET content=content+1 WHERE title='version'");
}

//Build modules custom CSS
function buildCSSModules(){
	global $supported_languages;
	$css = "../website/_modules.css";
	
	foreach ($supported_languages AS $language){
		$result = mysqlQuery("SELECT * FROM " . $language . "_website_modules_custom");
		while ($entry = mysqlFetch($result)){
			if ($entry["custom_css"]){
				$classes .= $entry["custom_css"] . "\r\n\r\n";
			}
		}
	}
	
	$classes = minifyCSS(html_entity_decode($classes, ENT_QUOTES));
	
	unlink($css);
	file_put_contents($css, $classes, LOCK_EX);
	mysqlQuery("UPDATE website_theme SET content=content+1 WHERE title='version'");
}

//Update website theme design
function buildWebsiteTheme($debug=false){
	global $original_colors;
	$template = fetchData("website_theme");
	
	//Template colors
	$template_colors = explode(",", $template["colors"]);

	//----- Website Components -----

	//Original Theme
	$theme_website .= "\r\n\r\n/* Theme */\r\n\r\n";
	$theme_website .= str_replace($original_colors, $template_colors, file_get_contents("../website/_theme.css"));

	//Custom Classes
	$theme_website .= "\r\n\r\n/* Custom Classes */\r\n\r\n";
	$theme_website .= str_replace($original_colors, $template_colors, file_get_contents("../website/_classes.css"));
	
	//Custom Modules
	$theme_website .= "\r\n\r\n/* Custom Modules */\r\n\r\n";
	$theme_website .= str_replace($original_colors, $template_colors, file_get_contents("../website/_modules.css"));

	//Loading
	$theme_website .= "\r\n\r\n/* Loading */\r\n\r\n";
	$theme_website .= str_replace($original_colors, $template_colors, file_get_contents("../website/loading.css"));
	
	//Header
	$theme_website .= "\r\n\r\n/* Header */\r\n\r\n";
	$theme_website .= str_replace($original_colors, $template_colors, file_get_contents("../website/header.css"));
	
	//Section Header
	$theme_website .= "\r\n\r\n/* Section Header */\r\n\r\n";
	$theme_website .= str_replace($original_colors, $template_colors, file_get_contents("../website/section_header.css"));
	
	//Footer
	$theme_website .= "\r\n\r\n/* Footer */\r\n\r\n";
	$theme_website .= str_replace($original_colors, $template_colors, file_get_contents("../website/footer.css"));

	//Blocks
	$blocks = retrieveDirectoryFiles("../blocks/", "css");
	foreach ($blocks AS $key=>$value){
		$theme_website .= "\r\n\r\n/* Block $value */\r\n\r\n";
		$theme_website .= str_replace($original_colors, $template_colors, file_get_contents("../blocks/" . $value));
	}
	
	//Modules
	$modules = retrieveDirectoryFiles("../modules/", "css");
	foreach ($modules AS $key=>$value){
		$theme_website .= "\r\n\r\n/* Module $value */\r\n\r\n";
		$theme_website .= str_replace($original_colors, $template_colors, file_get_contents("../modules/" . $value));
	}
	
	//Templates
	$templates = retrieveDirectoryFiles("../website/templates/", "css");
	foreach ($templates AS $key=>$value){
		$theme_website .= "\r\n\r\n/* Template $value */\r\n\r\n";
		$theme_website .= str_replace($original_colors, $template_colors, file_get_contents("../website/templates/" . $value));
	}

	//Build final theme
	if (!$debug){
		$theme_website = minifyCSS($theme_website);
	}
	file_put_contents("../website/website.min.css", $theme_website, LOCK_EX);	
	
	//----- Panel Components -----
	
	//Original Theme
	$theme_panel = str_replace($original_colors, $template_colors, file_get_contents("website/_theme.css"));
	
	//Build final theme
	if (!$debug){
		$theme_panel = minifyCSS($theme_panel);
	}
	file_put_contents("website/website.min.css", $theme_panel, LOCK_EX);	
	
	//----- Mobile Application Components -----
	
	if (file_exists("../mobile/")){
		//Original Theme
		$theme_mobile = str_replace($original_colors, $template_colors, file_get_contents("../mobile/_webview/_theme.css"));
		
		//Build final theme
		if (!$debug){
			$theme_mobile = minifyCSS($theme_mobile);
		}
		file_put_contents("../mobile/website/webview.min.css", $theme_mobile, LOCK_EX);
	}
}

/**
 * Build QueryBuilder filters
 * 
 * @param array $schema
 * @param bool|array $build_keys
 * @return array
 */
function buildQueryFilters($schema, $build_keys=false){
	$cached_refs = [];
	
	foreach ($schema as $table => $table_schema){
		foreach ($table_schema['fields'] as $key => $value){
			unset($query);

			$query['id'] = '@' . $key;
			$query['label'] = $value['label'];
			$query['countable'] = $value['countable'] ?? false;

			//Build custom keys if $build_keys == true || $key in $build_key array
			if (($ref = $value['ref']) && ((is_array($build_keys) && in_array($key, $build_keys)) || $build_keys === true)){
				$ref['pk'] = $ref['pk'] ?: 'id';
				$ref['table'] = $ref['table'] ?: $table;

				$cache_name = implode('|', [$ref['table'], $ref['pk'], $ref['column']]);
				if (!$cached_refs[$cache_name]){
					$q = "SELECT $ref[pk], $ref[column] FROM $ref[table]";
					$cached_refs[$cache_name] = array_column(mysqlFetchAll(mysqlQuery($q)), $ref['column'], $ref['pk']);
				}

				$query['ref'] = true;
				$value['type'] = 'list';
				$value['source'] = $cached_refs[$cache_name];
			}

			switch ($value['type']){
				case 'number':
					$query['type'] = 'string';
					$query['operators'] = ['equal', 'not_equal', 'less', 'less_or_equal', 'greater', 'greater_or_equal', 'between', 'not_between', 'in', 'not_in'];
					break;

				case 'query':
					$query['type'] = 'integer';
					$query['operators'] = ['equal', 'not_equal', 'less', 'less_or_equal', 'greater', 'greater_or_equal', 'between', 'not_between', 'in', 'not_in'];
					$query['data'] = [base64_encode($value['source'])];
					break;

				case 'list':
					$query['type'] = 'integer';
					$query['operators'] = ['equal', 'not_equal', 'contains', 'not_contains'];
					$query['input'] = 'select';
					$query['values'] = json_decode(json_encode($value['source'], JSON_FORCE_OBJECT));
					break;

				case 'date':
					$query['type'] = 'string';
					$query['operators'] = ['less', 'less_or_equal', 'greater', 'greater_or_equal'];
					$query['plugin'] = 'datePicker';
					break;

				default:
					$query['type'] = 'string';
					$query['operators'] = ['equal', 'not_equal', 'begins_with', 'not_begins_with', 'contains', 'not_contains', 'ends_with', 'not_ends_with'];
			}

			$query_array[$table][] = $query;
		}
	}

	return $query_array;
}

//==================================================
//========== Export & Import Functions ==========
//==================================================

/**
 * Create export folder
 * 
 * @return string path for the created folder
 */
function createExportFolder(){
	$path = "archives/export-" . uniqid();
	mkdir($path, 0777, true);
	mkdir($path . "/classes", 0777, true);
	mkdir($path . "/modules-custom", 0777, true);
	mkdir($path . "/modules-components", 0777, true);
	mkdir($path . "/displays", 0777, true);
	mkdir($path . "/forms", 0777, true);
	mkdir($path . "/editor", 0777, true);
	mkdir($path . "/blocks", 0777, true);
	mkdir($path . "/modules", 0777, true);
	return $path;
}

/**
 * Compress export folder to zip file
 * 
 * @param string $path
 * @return string path of zip file
 */
function compressExportFolder($path){
	global $cms_version;
	$password = "prismatecs@" . str_replace(".", "", $cms_version);
	$zip_name = basename($path);
	$zip_path = "archives/$zip_name.zip";
	$file = createZipFile($zip_path, $path, false, $password);
	return $zip_path;
}

/**
 * Check there's no existing components when importing & extracts zip file for processing
 * 
 * @param string $zip
 * @param bool $replce_custom (remove databse records if already exists)
 * @param bool $replace_built (remove built-in modules & blocks if already exists)
 * @return array of errors | true
 */
function checkCanImport($zip, $replce_custom=false, $replace_built=false, $password=null){
	global $suffix;
	global $cms_version;
	$errors = array();
	
	//Extract the zip file
	$password = ($password ? $password : "prismatecs@" . str_replace(".", "", $cms_version));
	$folder = "archives/" . basename($zip, ".zip");
	$result = extractZipFile("archives/$zip", $folder . "/", $password);
	if (!$result){
		array_push($errors, "Failed to extract components");
		return $errors;
	}
	
	//Check components
	$components = [
		"classes" => [
			"table" => "website_classes",
			"column" => "class"
		],
		"modules-custom" => [
			"table" => $suffix . "website_modules_custom",
			"column" => "uniqid"
		],
		"modules-components" => [
			"table" => $suffix . "website_modules_components",
			"column" => "module_uniqid"
		],		
		"displays" => [
			"table" => $suffix . "website_custom_displays",
			"column" => "uniqid"
		],
		"forms" => [
			"table" => $suffix . "website_forms",
			"column" => "uniqid"
		]
	];
	foreach ($components AS $target=>$params){
		$files = glob("$folder/$target/*.json", GLOB_BRACE);
		foreach ($files AS $file){
			$filename = basename($file, ".json");
			
			//Exception for custom modules components filename
			if ($target=="modules-components"){
				$explode = explode("-", $filename);
				array_pop($explode);
				$filename = implode("-", $explode);
			}
			
			if ($replce_custom){
				mysqlQuery("DELETE FROM " . $params["table"] . " WHERE " . $params["column"] . "='" . $filename . "'");
			} else {
				if (mysqlNum(mysqlQuery("SELECT id FROM " . $params["table"] . " WHERE " . $params["column"] . "='" . $filename . "'"))){
					array_push($errors, $filename . " already exists");
				}
			}
		}
	}
	
	//Check blocks and modules
	$built = ["blocks", "modules"];
	foreach ($built AS $target){
		$files = glob("$folder/$target/*.php", GLOB_BRACE);
		foreach ($files AS $file){
			$filename = basename($file, ".php");
			$exists = file_exists("../$target/$filename.php");
			if (!$replace_built && $exists){
				array_push($errors, $filename . " already exists");
			}
		}		
	}

	return ($errors ? $errors : true);
}

/**
 * Import components from a folder
 * 
 * @param string $folder
 * @return null
 */
function importComponents($folder){
	global $suffix;
	
	$components = [
		"classes" => "website_classes",
		"modules-custom" => $suffix . "website_modules_custom",
		"modules-components" => $suffix . "website_modules_components",
		"displays" => $suffix . "website_custom_displays",
		"forms" => $suffix . "website_forms"
	];
	
	//Import data
	foreach ($components AS $target=>$table){
		$files = glob("archives/$folder/$target/*.json", GLOB_BRACE);
		foreach ($files AS $file){
			$file_contents = file_get_contents($file);
			
			//Replace original colors with website colors
			global $original_colors;
			$template_colors = explode(",", fetchData("website_theme")["colors"]);
			$file_contents = str_replace($original_colors, $template_colors, $file_contents);
			
			//Parse json
			$json = json_decode($file_contents, true);
			
			//Set priority column if available
			$has_priority_column = mysqlNum(mysqlQuery("SHOW COLUMNS FROM $table LIKE 'priority'"));
			if ($has_priority_column){
				$json["priority"] = newRecordID($table);
			}
			
			//Fix to keep escaped backslash (Mainly CSS content attribute)
			$values = array_values($json);
			foreach ($values AS $key=>$value){
				$values[$key] = str_replace("\\", "\\\\", $value);
			}
			
			mysqlQuery("INSERT INTO $table (" . implode(",", array_keys($json)) . ") VALUES ('" . implode("','", $values) . "')");
			unlink($file);
		}
	}
	
	//Copy images
	$image_folders = ["classes", "editor"];
	foreach ($image_folders AS $image_folder){
		$files = glob("archives/$folder/$image_folder/*.*", GLOB_BRACE);
		foreach ($files AS $file){
			copy($file, "../uploads/$image_folder/" . basename($file));
		}
	}
	
	//Copy built-in modules
	$files = glob("archives/$folder/modules/*.*", GLOB_BRACE);
	foreach ($files AS $file){
		copy($file, "../modules/" . basename($file));
	}
	
	//Copy blocks
	$files = glob("archives/$folder/blocks/*.*", GLOB_BRACE);
	foreach ($files AS $file){
		copy($file, "../blocks/" . basename($file));
	}
}

/**
 * Export CSS classes
 * 
 * @param string $uniqid
 * @param bool $archive (path for the target folder, if set to null a new folder is created)
 * @return string path of zip file if created
 */
function exportCSS($uniqid, $archive=null){
	$data = getData("website_classes", "class", $uniqid);
	if (!$data){ return; }
	
	//Create export folder if not set
	$folder = ($archive ? $archive : createExportFolder());

	//Extract images
	preg_match_all('/uploads\/classes\/([a-z0-9\.]+)/', $data["json"], $matches);
	$images = $matches[1];

	//Export images
	foreach ($images AS $image){
		copy("../uploads/classes/$image", "$folder/classes/$image");
	}
	
	//Create data file
	$remove = ['id', 'priority'];
	$data = array_diff_key($data, array_flip($remove));
	file_put_contents("$folder/classes/" . $data["class"] . ".json", json_encode($data, JSON_UNESCAPED_UNICODE));
	
	//Return if archive is not set (end of process)
	if (!$archive){
		$zip = compressExportFolder($folder);
		return $zip;
	}
}

/**
 * Export custom module
 * 
 * @param string $uniqid
 * @param bool $archive (path for the target folder, if set to null a new folder is created)
 * @return string path of zip file if created
 */
function exportModule($uniqid, $archive=null){
	global $suffix;
	$data = getData($suffix . "website_modules_custom", "uniqid", $uniqid);
	if (!$data){ return; }
	$json_data = json_encode($data, JSON_UNESCAPED_UNICODE);
	
	//Create export folder if not set
	$folder = ($archive ? $archive : createExportFolder());

	//Extract used classes
	preg_match_all('/class-([a-z0-9]+)/', $json_data, $matches);
	$classes = $matches[1];
	
	//Export custom background file
	if ($data["background_file"]){
		$image = $data["background_file"];
		copy("../uploads/classes/$image", "$folder/classes/$image");
	}
	
	//Extract module contents
	$modules = [];
	$displays = [];
	$forms = [];
	$editor_images = [];
	$modules_built = [];
	$result = mysqlQuery("SELECT * FROM " . $suffix . "website_modules_components WHERE module_uniqid='$uniqid'");
	while ($entry = mysqlFetch($result)){
		$json_entry = json_encode($entry, JSON_UNESCAPED_UNICODE);
		
		//Extract used classes
		preg_match_all('/class-([a-z0-9]+)/', $json_entry, $class_matches);
		$classes = array_merge($classes, $class_matches[1]);
		
		//Extract used modules
		preg_match_all('/module-([a-z0-9]+)/', $json_entry, $module_matches);
		$modules = array_merge($modules, $module_matches[1]);
		
		//Extract used displays
		preg_match_all('/display-([a-z0-9]+)/', $json_entry, $display_matches);
		$displays = array_merge($displays, $display_matches[1]);

		//Extract used forms
		preg_match_all('/form-([a-z0-9]+)/', $json_entry, $form_matches);
		$forms = array_merge($forms, $form_matches[1]);

		//Extract editor images
		preg_match_all('/uploads\/editor\/([a-z0-9_\.]+)/', $entry["content"], $editor_matches);
		$editor_images = array_merge($editor_images, $editor_matches[1]);
		
		//Extract built-in modules
		if (!$entry["type"]){
			preg_match_all("/{{(.*?)}}/", $entry["content"], $modules_built_matches);
			foreach ($modules_built_matches[1] AS $possible_module){
				if (file_exists("../modules/$possible_module.php")){
					array_push($modules_built, $possible_module);
				}
			}			
		} else if ($entry["type"]==1){
			array_push($modules_built, $entry["content"]);
		}
	
		//Create data file
		$component_suffix = $entry["id"];
		$remove = ['id', 'priority'];
		$entry = array_diff_key($entry, array_flip($remove));
		file_put_contents("$folder/modules-components/" . $data["uniqid"] . "-$component_suffix.json", json_encode($entry, JSON_UNESCAPED_UNICODE));		
	}

	//Export classes
	$classes = array_unique($classes);
	foreach ($classes AS $class){
		exportCSS("class-$class", $folder);
	}

	//Export modules
	$modules = array_unique($modules);
	array_splice($modules, array_search(str_replace("module-", "", $uniqid), $modules), 1);
	foreach ($modules AS $module){
		exportModule("module-$module", $folder);
	}
	
	//Export displays
	$displays = array_unique($displays);
	foreach ($displays AS $display){
		exportDisplay("display-$display", $folder);
	}
	
	//Export forms
	$forms = array_unique($forms);
	foreach ($forms AS $form){
		exportForm("form-$form", $folder);
	}

	//Export editor images
	$editor_images = array_unique($editor_images);
	foreach ($editor_images AS $image){
		copy("../uploads/editor/$image", "$folder/editor/$image");
	}
	
	//Export built-in modules
	foreach ($modules_built AS $module_built){
		$files = glob("../modules/$module_built.*", GLOB_BRACE);
		foreach ($files AS $file){
			copy($file, "$folder/modules/" . basename($file));
		}
	}
	
	//Create data file
	$remove = ['id', 'priority'];
	$data = array_diff_key($data, array_flip($remove));
	file_put_contents("$folder/modules-custom/" . $data["uniqid"] . ".json", json_encode($data, JSON_UNESCAPED_UNICODE));
	
	//Return if archive is not set (end of process)
	if (!$archive){
		$zip = compressExportFolder($folder);
		return $zip;
	}
}

/**
 * Export custom display
 * 
 * @param string $uniqid
 * @param bool $archive (path for the target folder, if set to null a new folder is created)
 * @return string path of zip file if created
 */
function exportDisplay($uniqid, $archive=null){
	global $suffix;
	$data = getData($suffix . "website_custom_displays", "uniqid", $uniqid);
	if (!$data){ return; }
	$json_data = json_encode($data, JSON_UNESCAPED_UNICODE);
	
	//Create export folder if not set
	$folder = ($archive ? $archive : createExportFolder());

	//Extract used classes
	preg_match_all('/class-([a-z0-9]+)/', $json_data, $class_matches);
	$classes = $class_matches[1];
	
	//Extract used modules
	preg_match_all('/module-([a-z0-9]+)/', $json_data, $module_matches);
	$modules = $module_matches[1];

	//Export classes
	$classes = array_unique($classes);
	foreach ($classes AS $class){
		exportCSS("class-$class", $folder);
	}
	
	//Export modules
	$modules = array_unique($modules);
	foreach ($modules AS $module){
		exportModule("module-$module", $folder);
	}
	
	//Export blocks
	$files = glob("../blocks/" . $data["blocks_template"] . ".*", GLOB_BRACE);
	foreach ($files AS $file){
		copy($file, "$folder/blocks/" . basename($file));
	}

	//Create data file
	$remove = ['id', 'priority'];
	if ($data["source"] != 2){
		//Remove source content if it's not a custom module as pages and queries are not exported
		array_push($remove, 'source_content');
	}
	$data = array_diff_key($data, array_flip($remove));
	file_put_contents("$folder/displays/" . $data["uniqid"] . ".json", json_encode($data, JSON_UNESCAPED_UNICODE));
	
	//Return if archive is not set (end of process)
	if (!$archive){
		$zip = compressExportFolder($folder);
		return $zip;
	}
}

/**
 * Export website form
 * 
 * @param string $uniqid
 * @param bool $archive (path for the target folder, if set to null a new folder is created)
 * @return string path of zip file if created
 */
function exportForm($uniqid, $archive=null){
	global $suffix;
	$data = getData($suffix . "website_forms", "uniqid", $uniqid);
	if (!$data){ return; }
	$json_data = json_encode($data, JSON_UNESCAPED_UNICODE);
	
	//Create export folder if not set
	$folder = ($archive ? $archive : createExportFolder());

	//Extract used classes
	preg_match_all('/class-([a-z0-9]+)/', $json_data, $class_matches);
	$classes = $class_matches[1];

	//Export classes
	$classes = array_unique($classes);
	foreach ($classes AS $class){
		exportCSS("class-$class", $folder);
	}

	//Create data file
	$remove = ['id', 'priority', 'records'];
	$data = array_diff_key($data, array_flip($remove));
	file_put_contents("$folder/forms/" . $data["uniqid"] . ".json", json_encode($data, JSON_UNESCAPED_UNICODE));
	
	//Return if archive is not set (end of process)
	if (!$archive){
		$zip = compressExportFolder($folder);
		return $zip;
	}
}

//Render website menu
function renderWebsiteMenu($type=0){
	global $suffix;
	global $base_url;
	
	$result = mysqlQuery("SELECT * FROM " . $suffix . "website_menu WHERE type=$type ORDER BY priority DESC");
	while ($entry = mysqlFetch($result)){
		$multiple_children = false;
		
		//Custom child menus
		if ($entry["sub_menus_type"]==1){
			$sub_menus = json_decode($entry["sub_menus"], true);
		
		//Child menus from custom pages
		} else if ($entry["sub_menus_type"]==2){
			$sub_menus = array();
			$pages_result = mysqlQuery("SELECT * FROM " . $suffix . "website_pages_custom WHERE parent=" . intval($entry["sub_menus"]) . " ORDER BY priority DESC");
			while ($page = mysqlFetch($pages_result)){
				$sub_menu = array();
				$sub_menu["content"] = array(
					"title" => $page["title"],
					"icon" => ($entry["sub_menus_side"]=="icon" && $page["child_icon"] ? $page["child_icon"] : ($entry["sub_menus_side"]=="cover" && $page["cover_image"] ? "img:uploads/pages/" . $page["cover_image"] : null)),
					"url" => ($page["canonical"] ? customPageURL($page) : null)
				);
				if ($entry["sub_menus_children"]){
					$children_result = mysqlQuery("SELECT * FROM " . $suffix . "website_pages_custom WHERE parent=" . $page["id"] . " ORDER BY priority DESC");
					if (mysqlNum($children_result)){
						$sub_menu["children"] = array();
						while ($child = mysqlFetch($children_result)){
							$children_content["content"] = array(
								"title" => $child["title"],
								"icon" => ($entry["sub_menus_side"]=="icon" && $child["child_icon"] ? $child["child_icon"] : ($entry["sub_menus_side"]=="cover" && $child["cover_image"] ? "img:uploads/pages/" . $child["cover_image"] : null)),
								"url" => ($child["canonical"] ? customPageURL($child) : null)
							);
							array_push($sub_menu["children"], $children_content);
						}
					}
				}
				array_push($sub_menus, $sub_menu);
			}
		
		//Child menus from built-in pages
		} else if ($entry["sub_menus_type"]==3){
			$sub_menus = array();
			$explode = explode(",", $entry["sub_menus"]);
			$target_table = $explode[0];
			$parent_exists = mysqlNum(mysqlQuery("SHOW COLUMNS FROM $target_table LIKE 'parent'"));
			$pages_result = mysqlQuery("SELECT * FROM $target_table " . ($parent_exists ? "WHERE parent=" . $explode[1] : "") . " ORDER BY priority DESC");
			while ($page = mysqlFetch($pages_result)){
				$block_components = builtPageBlock($target_table, $page);
				$sub_menu = array();
				$sub_menu["content"] = array(
					"title" => $block_components["title"],
					"icon" => ($entry["sub_menus_side"]=="icon" && $block_components["icon"] ? $block_components["icon"] : ($block_components[$entry["sub_menus_side"]] ? "img:" . $block_components[$entry["sub_menus_side"]] : null)),
					"url" => $block_components["url"]
				);
				if ($parent_exists && $entry["sub_menus_children"]){
					$children_result = mysqlQuery("SELECT * FROM $target_table WHERE parent=" . $page["id"] . " ORDER BY priority DESC");
					if (mysqlNum($children_result)){
						$sub_menu["children"] = array();
						while ($child = mysqlFetch($children_result)){
							$child_block_components = builtPageBlock($target_table, $child);
							$children_content["content"] = array(
								"title" => $child_block_components["title"],
								"icon" => ($entry["sub_menus_side"]=="icon" && $block_components["icon"] ? $block_components["icon"] : ($block_components[$entry["sub_menus_side"]] ? "img:" . $block_components[$entry["sub_menus_side"]] : null)),
								"url" => $child_block_components["url"]
							);
							array_push($sub_menu["children"], $children_content);
						}
					}
				}
				array_push($sub_menus, $sub_menu);
			}
		} else {
			$sub_menus = null;
		}

		//Dropdown menu
		if (count($sub_menus)){
			$menu_links = "";
			foreach ($sub_menus AS $sub_menu){
				$image = explode(":", $sub_menu[content][icon])[1];
				$icon = ($image ? null : $sub_menu[content][icon]);
				$side = ($icon ? "<i class='" . $sub_menu[content][icon] . " fa-fw'></i>" : ($image ? "<img src='$image'>" : null));
				$menu_links .= "<li class='" . ($sub_menu[children] ? "multiple" : "single") . "'><a href='" . $sub_menu[content][url] . "'>" . ($side ? $side . "&nbsp;&nbsp;" : "") . "<span>" . $sub_menu[content][title] . "</span></a>";
				if ($sub_menu[children]){
					$multiple_children = true;
					$menu_links .= "<ul class=children>";
					foreach ($sub_menu[children] AS $sub_menu_item){
						$image = explode(":", $sub_menu_item[content][icon])[1];
						$icon = ($image ? null : $sub_menu_item[content][icon]);
						$side = ($icon ? "<i class='" . $sub_menu_item[content][icon] . " fa-fw'></i>" : ($image ? "<img src='$image'>" : null));
						$menu_links .= "<li><a href='" . $sub_menu_item[content][url] . "'>" . ($side ? $side . "&nbsp;&nbsp;" : "") . "<span>" . $sub_menu_item[content][title] . "</span></a></li>";
					}
					$menu_links .= "</ul>";
				}
				$menu_links .= "</li>";
			}
			$dropdown_menu = "<ul class='nav-dropdown " . ($multiple_children ? "has-grand-children" : "has-children") . " " . (count($sub_menus) > 6 ? "multiple-columns" : "") . "'>
				" . $menu_links . "
			</ul>";
			$nav_item = "<li class='nav-item nav-dropdown-item'><a class='" . $entry["class"] . "' toggle href='" . $entry["url"] . "'>" . ($entry["icon"] ? "<i class='" . $entry["icon"] . " fa-fw'></i>&nbsp;&nbsp;" : ($entry["image"] ? "<img src='uploads/menu/" . $entry["image"] . "'>&nbsp;&nbsp;" : "")) . $entry["title"] . "</a>" . $dropdown_menu . "</li>";
		
		//Single URL
		} else {
			$menu_url = str_replace(".", "", crtrim($entry["url"], "/"));
			$current_url = (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] === "on" ? "https" : "http") . "://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];
			$current_url = crtrim(str_replace($base_url, "", $current_url), "/");
			$nav_item_class = ($menu_url && urldecode($menu_url)==urldecode($current_url) ? "active" : "inactive");
			$nav_item = "<li class='nav-item $nav_item_class'><a class='" . $entry["class"] . "' href='" . $entry["url"] . "'>" . ($entry["icon"] ? "<i class='" . $entry["icon"] . " fa-fw'></i>&nbsp;&nbsp;" : ($entry["image"] ? "<img src='uploads/menu/" . $entry["image"] . "'>&nbsp;&nbsp;" : "")) . $entry["title"] . "</a></li>";
		}
		print $nav_item;
	}
}
?>
