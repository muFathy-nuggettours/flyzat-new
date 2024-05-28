<? include "system/_handler.php";

$multiple_languages = false;
$mysqltable = "travelport_errors";
$base_name = basename($_SERVER["SCRIPT_FILENAME"], ".php");
checkPermissions($base_name);

include "_header.php"; ?>

<div class=title><?=getPageTitle($base_name)?></div>

<?
$crud_data["buttons"] = array(false,true,false,false,false); //Add - Search - View - Edit - Delete
$crud_data["columns"] = array( //Filter - Search - Copy Enabled
	array("error","الخطأ","100%","center",null,false,true),
	array("xml_request","Request","150px","center","hasVal('%s','',fileBlock('../uploads/xml/%s','Request'))",false,false),
	array("xml_response","Response","150px","center","hasVal('%s','',fileBlock('../uploads/xml/%s','Response'))",false,true),
	array("date","التاريخ","250px","center","dateLanguage('l, d M Y h:i A','%s')",false,true),
);
require_once("crud/crud.php");
?>

<? include "_footer.php"; ?>