<? include "../system/_handler.php";

//Security Measure: Allow for post requests and logged users only
if (!$post["token"]){ brokenLink(); }

//Clean table data
$print_data = file_get_contents("../archives/" . $post["table"] . ".txt");
$print_data = preg_replace("/<img[^>]+\>/i", readLanguage(crud,image), $print_data);
$print_data = str_replace("<br>","<br style='mso-data-placement:same-cell'>", $print_data);

//Set page headers
header("Content-type: application/vnd.ms-excel; charset=UTF-8");
header("Content-Disposition: attachment; filename=" . mysqlEscape($_POST["filename"]) . ".xls");
?>
<html xmlns:x="urn:schemas-microsoft-com:office:excel">
<head>
<meta charset="utf-8">
<xml>
<x:ExcelWorkbook>
	<x:ExcelWorksheets>
		<x:ExcelWorksheet>
			<x:Name>Sheet 1</x:Name>
			<x:WorksheetOptions>
				<x:Print>
					<x:ValidPrinterInfo/>
				</x:Print>
			</x:WorksheetOptions>
		</x:ExcelWorksheet>
	</x:ExcelWorksheets>
</x:ExcelWorkbook>
</xml>
<style>
table {
	border-collapse: collapse;
}

table th {
	font-size: 16px;
	font-weight: bold;
	vertical-align: middle;
	text-align: center;
	height: 30px;
	background: #c8c8c8;
}

table td {
	vertical-align: middle;
	font-size: 16px;
	text-align: center;
}
</style>
</head>
<body><?=$print_data?></body>
</html>