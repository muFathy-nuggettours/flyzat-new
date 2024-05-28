<? include "system/_handler.php";

header("Content-type: application/xml");
echo file_get_contents("sitemap_" . $language["code"] . ".xml");

if ($connection){ mysqlClose(); } ob_end_flush(); ?>