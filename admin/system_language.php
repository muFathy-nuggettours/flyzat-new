<? include "system/_handler.php";

$base_name = basename($_SERVER["SCRIPT_FILENAME"], ".php");
checkPermissions($base_name);

//==== EDIT Record ====
if ($post["token"]){
	$json = array();
	unset($post["token"]);
	foreach($post AS $key=>$value){
		$explode = explode(",", $key);
		$json[$explode[0]][$explode[1]] = $_POST[$explode[0] . "," . $explode[1]];
	}
	$file = fopen("../website/languages/" . $database_language["code"] . ".json", "w");
	fwrite($file, json_encode($json, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
	fclose($file);
	$success = readLanguage(records,updated);
}

//Read and Set Operation
if ($success){ $message = "<div class='alert alert-success'>$success</div>"; }

include "_header.php"; ?>

<div class=title><?=getPageTitle($base_name)?></div>
<?=$message?>

<? $json_data = file_get_contents("../website/languages/" . $database_language["code"] . ".json"); ?>

<? if (!$json_data){ ?>
<div class="alert alert-warning align-center"><?=readLanguage(crud,records_empty)?></div>

<? } else { ?>
<form method=post enctype="multipart/form-data" action="<?=$action?>">
<input type=hidden name=token value="<?=$token?>">
<?
//Values represented as textarea
$language_text_area = array(
	"mails.body_reset_password",
	"mails.body_new_password"
);

$json_array = json_decode($json_data,true);
foreach ($json_array AS $key => $content_array){
	print "<div class=subtitle>" . strtoupper($key) . "</div>";
	print "<table class=data_table>";
	foreach ($content_array AS $content_key => $content_value){
		print "<tr>";
		print "<td class=title style='width:25%'>" . strtoupper($content_key) . "</td>";
		print "<td>";
			if (in_array("$key.$content_key" ,$language_text_area)){
				print "<textarea data-validation=required name='$key,$content_key'>" . htmlentities($content_value) . "</textarea>";
			} else {
				print "<input data-validation=required type=text name='$key,$content_key' value=\"" . htmlentities($content_value) . "\">";
			}
		print "</td>";
		print "</tr>";
	}
	print "</table>";
}
?>
<div class=submit_container><input type=button class=submit value="<?=readLanguage(records,update)?>"></div>
</form>
<? } ?>

<? include "_footer.php"; ?>