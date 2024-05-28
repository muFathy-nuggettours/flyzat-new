<? include "system/_handler.php";

$base_name = basename($_SERVER["SCRIPT_FILENAME"], ".php");
checkPermissions($base_name);

//Generate sitemap
if ($post["token"] && $post["generate"]=="links"){
	ob_end_flush();
	
	include "snippets/simple_html_dom.php";
	$skip_hrefs = array(null, "#", ".");
	$valid_hrefs = array();

	function getLinks($page){
		global $base_url;
		$return = array();
		if ($page){
			$html = file_get_html($page);
			if ($html){
				foreach($html->find("a") as $element){
					$href = str_replace($base_url, "", $element->href);
					if ($href && validateLinks($href)){
						array_push($return, $href);
					}
				}
			}
		}
		return $return;
	}

	function validateLinks($href){
		global $skip_hrefs;
		return (array_search($href, $skip_hrefs)==false && substr($href,0,4)!="http" && substr($href,0,1)!="#" && !explode(".",$href)[1] && !explode(":",$href)[1]);
	}
	
	$home = getLinks($base_url . ($database_language["code"]==$supported_languages[0] ? "" : $database_language["code"] . "/"));
	foreach ($home AS $key=>$value){
		if (array_search($value, $valid_hrefs)===false){
			array_push($valid_hrefs, $value);
		}
	}

	foreach ($valid_hrefs AS $key=>$value){
		$page = getLinks($base_url . ($database_language["code"]==$supported_languages[0] ? "" : $database_language["code"] . "/") . $value);
		foreach ($page AS $page_key=>$page_value){
			if (array_search($page_value, $valid_hrefs)===false){
				array_push($valid_hrefs, $page_value);
			}
		}
	}
	
	foreach ($valid_hrefs AS $key=>$value){
		$valid_hrefs[$key] = $base_url . ($database_language["code"]==$supported_languages[0] ? "" : $database_language["code"] . "/") . $value;
	}
	
	echo implode($valid_hrefs,"\r\n");
	
	exit();
}

//Update sitemap
if ($post["token"] && $post["generate"]=="sitemap"){
	$explode = explode("\r\n", unescapeString($post["sitemap"]));
	$explode = array_filter($explode);
	$xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\r\n<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\r\n";
	foreach ($explode AS $key=>$value){
		$xml .= "\t<url><loc>$value</loc></url>\r\n";
	}
	$xml .= "</urlset>";
	file_put_contents("../sitemap_" . $database_language["code"] . ".xml", $xml);
}

//Read and Set Operation
if ($success){ $message = "<div class='alert alert-success'>$success</div>"; }

//Load current sitemap
$xml = json_decode(json_encode((array)simplexml_load_string(file_get_contents("../sitemap_" . $database_language["code"] . ".xml"))),true);
$sitemap = array();
foreach ($xml["url"] AS $key=>$value){
	if (gettype($value)=="array"){
		array_push($sitemap,$value["loc"]);
	} else {
		array_push($sitemap,$value);
	}
}
$sitemap = implode("\r\n",$sitemap);

include "_header.php"; ?>

<div class=title><?=getPageTitle($base_name)?></div>
<?=$message?>

<form method=post enctype="multipart/form-data" action="<?=$action?>">
<input type=hidden name=token value="<?=$token?>">
<input type=hidden name=generate value="sitemap">

<table class=data_table>
<tr>
	<td class=title><?=readLanguage(pages,sitemap_autofill)?>:</td>
	<td>
		<input type=button class="btn btn-primary btn-sm" value="<?=readLanguage(pages,sitemap_autofill)?>" onclick="generateSitemap()">
		<div class=input_description><?=readLanguage(pages,sitemap_autofill_description)?></div>
	</td>
</tr><tr>
	<td class=title><?=readLanguage(pages,sitemap_urls)?>:</td>
	<td>
		<textarea name=sitemap id=sitemap class=force-ltr style="height:300px"><?=$sitemap?></textarea>
		<div class=input_description><?=readLanguage(inputs,instructions_newline)?></div>
	</td>
</tr>
</table>

<div class=submit_container><input type=button class=submit value="<?=readLanguage(records,update)?>"></div>
</form>

<script>
function generateSitemap(){
	$.confirm({
		content: function(){
			var self = this;
			return $.ajax({
				method: "POST",
				url: "<?=$base_name?>.php",
				data: {token:user_token, generate:"links"}
			}).done(function(response){
				$("#sitemap").val(response);
			}).always(function(){
				self.close();
			});
		}
	});
}
</script>

<? include "_footer.php"; ?>