<? include "../system/_handler.php";

//Security Measure: Allow for post requests and logged users only
if (!$post["token"]){ brokenLink(); }

//Clean Table Data
$print_data = file_get_contents("../archives/" . $post["table"] . ".txt");
?>
<html>
<head>
<title><?=$post["filename"]?></title>
<style>
@page {
	size: A4 landscape;
}

body {
	font-family: tahoma;
	font-size: 14px;
	padding: 10px;
	margin: 0px;
	min-width: -webkit-min-content;
	min-width: -moz-min-content;
	min-width: min-content;
}

table {
	min-width: 100%;
	border-collapse: collapse;
}

table tr {
	page-break-inside: avoid;
}

table th {
	padding: 8px;
	background: #f1f1f1;
	border: 1px solid #c8c8c8;
	font-weight: bold;
	font-size: 13px;
}

table td {
	padding: 8px;
	border: 1px solid #c8c8c8;
	font-size: 12px;
	text-align: center;
}

img {
	max-width: 100%;
}
</style>
</head>
<?=$print_data?>
</html>