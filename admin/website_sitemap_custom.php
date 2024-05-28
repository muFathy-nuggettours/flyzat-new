<? include "system/_handler.php";

$base_name = basename($_SERVER["SCRIPT_FILENAME"], ".php");
checkPermissions($base_name);

//Generate sitemap
if ($post["token"] && $post["generate"]=="links"){
	ob_end_flush();
	$valid_hrefs = array();
	
	$lang = ($suffix=="ar_" ? "" : "en/");

	$result = mysqlQuery("SELECT * FROM " . $suffix . "website_destinations");
	while ($entry = mysqlFetch($result)){
		$url = $base_url . $lang . "destinations/" . $entry["canonical"] . "/";
		array_push($valid_hrefs, $url);
	}

	$result = mysqlQuery("SELECT * FROM system_database_countries WHERE publish=1");
	while ($entry = mysqlFetch($result)){
		$url = $base_url . $lang . "countries/" . $entry[$suffix . "slug"] . "/";
		array_push($valid_hrefs, $url);
		$url = $base_url . "countries/" . strtolower($entry["code"]) . "/";
		array_push($valid_hrefs, $url);
	}
	
	$result = mysqlQuery("SELECT * FROM system_database_regions WHERE publish=1");
	while ($entry = mysqlFetch($result)){
		$url = $base_url . $lang . "regions/" . $entry[$suffix . "slug"] . "/";
		array_push($valid_hrefs, $url);
	}

	$result = mysqlQuery("SELECT * FROM system_database_airports WHERE publish=1");
	while ($entry = mysqlFetch($result)){
		$url = $base_url . $lang . "airports/" . $entry[$suffix . "slug"] . "/";
		array_push($valid_hrefs, $url);
		$url = $base_url . $lang . "airports/" . strtolower($entry["iata"]) . "/";
		array_push($valid_hrefs, $url);
	}	
	
	$result = mysqlQuery("SELECT * FROM system_database_airlines WHERE publish=1");
	while ($entry = mysqlFetch($result)){
		$url = $base_url . $lang . "airlines/" . $entry[$suffix . "slug"] . "/";
		array_push($valid_hrefs, $url);
		$url = $base_url . $lang . "airlines/" . strtolower($entry["iata"]) . "/";
		array_push($valid_hrefs, $url);
	}
	
	$result = mysqlQuery("SELECT * FROM system_database_planes WHERE publish=1");
	while ($entry = mysqlFetch($result)){
		$url = $base_url . $lang . "planes/" . $entry[$suffix . "slug"] . "/";
		array_push($valid_hrefs, $url);
		$url = $base_url . $lang . "planes/" . strtolower($entry["iata"]) . "/";
		array_push($valid_hrefs, $url);
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