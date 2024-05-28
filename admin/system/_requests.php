<?
//Export Website Builder
if ($post["action"]=="export_builder"){
	switch ($post["target"]){
		case "css": $result = exportCSS($post["uniqid"]); break;
		case "module": $result = exportModule($post["uniqid"]); break;
		case "display": $result = exportDisplay($post["uniqid"]); break;
		case "form": $result = exportForm($post["uniqid"]); break;
	}
	echo $result;
}
?>