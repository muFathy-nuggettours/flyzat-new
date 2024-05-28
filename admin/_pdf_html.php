<? include "system/_handler.php";

//Security Measure: Allow via POST requests & for logged users only
if (!$post["token"] || !$logged_user){ brokenLink(); exit(); }

ini_set("max_execution_time", 0);
ini_set("memory_limit",-1);

//Set titles
$archive_prefix = "View";
$pdf_title = $post["title"];
$pdf_subtitle = $post["subtitle"];
$orientation = ($post["orientation"] ? $post["orientation"] : "P");

//Set default language directions
$page_direction = $language["dir"];
$page_negative_align = ($panel_language=="ar" ? "left" : "right");

//=============== Standards ===============

//PDF headers and footers
$header = "<table class=pdf_header><tr>";
$header .= "<td><img src='../uploads/_website/" . $website_information["website_logo"] . "' class=pdf_logo></td>";
$header .= "<td align=$page_negative_align>" . dateLanguage("l, d M Y h:i A",time()) . "</td>";
$header .= "</tr></table>";

//Footer
$footer .= "<table class=pdf_footer><tr>";
$footer .= "<td>" . $pdf_title . ($pdf_subtitle ? " - " . $pdf_subtitle : "") . "</td>";
$footer .= "<td align=$page_negative_align>" . readLanguage(general,page) . " {PAGENO} " . readLanguage(general,of) . " {nb}</td>";
$footer .= "</tr></table>";

//Page header
$page_header .= "<!DOCTYPE html>";
$page_header .= "<html>";
$page_header .= "<meta http-equiv='Content-type' content='text/html; charset=UTF-8'>";
$page_header .= "<head><link href='core/_publish.css' rel='stylesheet'><link href='website/publish.css' rel='stylesheet'></head>";
$page_header .= "<body style='direction:$page_direction'>";

//Header title
if ($pdf_title || $pdf_subtitle){
	$page_header .= "<div class=pdf_page_header><div class=pdf_title>" . ($pdf_subtitle ? "<div class=pdf_subtitle>" . $pdf_subtitle . "</div>" : "") .  $pdf_title . "</div></div>";
}

//Page body
$page_header .= "<div class=pdf_page_body>";

//Page footer
$page_footer .= "</div>";
$page_footer .= "</body>";
$page_footer .= "</html>";

$output_path = "archives/" . $archive_prefix . "_" . uniqid() . ".pdf";
$output_name = $pdf_title . ($pdf_subtitle ? " - " . $pdf_subtitle : "");

//=============== End Standards ===============

//Page title
$content = ($post["file"]=="true" ? file_get_contents($post["content"]) : $_POST["content"]);

//Remove links and lists
$tags = array("a", "li");
foreach($tags as $tag){
	$content = preg_replace("/<\\/?" . $tag . "(.|\\s)*?>/","",$content);
}

//Remove scripts
$content = preg_replace("#<script(.*?)>(.*?)</script>#is","",$content);

//Close MySQL link
if ($connection){ mysqlClose(); }

//===== Publish PDF =====
require_once("snippets/mpdf/mpdf.php");

//Language - Size/Orientation - Font Size - Font Family - Margin L - Margin R - Margin T - Margin B - Margin Header - Margin Footer
$mpdf = new mPDF(null,"A4-" . $orientation, 13, null, 10, 10, 30, 17, 10, 10);

//Options
$mpdf->useSubstitutions = false;
$mpdf->SetTitle($output_name);
$mpdf->SetAuthor($website_information["website_name"]);
$mpdf->SetFont("dejavusans", "R", 16);
$mpdf->SetHTMLHeader($header);
$mpdf->SetHTMLFooter($footer);
$mpdf->SetWatermarkImage("../uploads/_website/" . $website_information["website_logo"], 0.05, 30);
$mpdf->showWatermarkImage = true;
$mpdf->WriteHTML($page_header . $content . $page_footer);
$mpdf->Output($output_path, "F");

//Return file block & exit
echo fileBlock($output_path, $output_name, null, true);
exit();
?>