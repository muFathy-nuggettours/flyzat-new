<?
include "system/_handler.php";

//Security Measure: Allow via POST requests & for logged users only
if (!$post["token"] || !$logged_user){
	header("HTTP/1.1 401 Unauthorized");
}

$uploaded_files = array();
if (isset($_FILES["files"]) && !empty($_FILES["files"])){
	header("Access-Control-Allow-Origin: " . $_SERVER["HTTP_ORIGIN"]);
    for ($i = 0; $i < count($_FILES["files"]["name"]); $i++){
		if (strtolower(basename(dirname($post["path"])))!="uploads"){
			header("HTTP/1.1 403 Forbidden");
			
		} else if (!validateFileName($_FILES["files"]["name"][$i])){
			header("HTTP/1.1 400 Bad Request");
			exit(readLanguage(plugins,upload_error_extension));
			
		} else if (!$_FILES["files"]["size"][$i] || $_FILES["files"]["size"][$i] > parseSize(ini_get("upload_max_filesize"))){
			header("HTTP/1.1 400 Bad Request");
			exit(readLanguage(plugins,upload_error_size) . ini_get("upload_max_filesize"));			

		} else {
			$is_image = isImage($_FILES["files"]["name"][$i]);
			$original_name = pathinfo($_FILES["files"]["name"][$i], PATHINFO_FILENAME);
			$storage_name = ($i + 1) . uniqid("_") . "." . strtolower(pathinfo($_FILES["files"]["name"][$i], PATHINFO_EXTENSION));
			$upload_path = $post["path"] . $storage_name;
			$upload_file = move_uploaded_file($_FILES["files"]["tmp_name"][$i],$upload_path);

			//Add uploaded file to array
			if ($upload_file){
				array_push($uploaded_files, array("url" => $storage_name, "title" => (!$is_image ? $original_name : "")));
				//If image resize & create thumbnail
				if ($is_image){
					//Resize if necessary
					list($original_width, $original_height, $original_type) = getimagesize($upload_path);
					if ($original_width > 1200 || $original_height > 1200){
						createThumbnail($upload_path,$upload_path,1200);
					}
					
					//Create thumbnail if available
					if (file_exists($post["path"] . "/thumbnails/")){
						createThumbnail($upload_path,$post["path"] . "/thumbnails/" . $storage_name, 400);
					}
				}
			}
		}
    }
}

if (sizeof($uploaded_files)){
	echo json_encode($uploaded_files,JSON_UNESCAPED_UNICODE);
} else {
	header("HTTP/1.1 400 Bad Request");
	exit(readLanguage(plugins,upload_error));
}
?>