<? include "../../admin/system/_handler.php";

$image_folder = "../../uploads/editor/";
reset($_FILES);
$file = current($_FILES);

if (is_uploaded_file($file["tmp_name"])){
	if (!validateFileName($file["name"]) || !isImage($file["name"])){
		header("HTTP/1.1 500 Server Error");
		return;
	}
	$filename = $image_folder . uniqid() . "_" . rand(1000,9999) . "." . strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
	move_uploaded_file($file["tmp_name"], $filename);
	echo json_encode(array("location" => $filename));
} else {
	header("HTTP/1.1 500 Server Error");
}
?>