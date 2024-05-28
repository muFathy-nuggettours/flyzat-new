<? $result = mysqlQuery("SELECT * FROM " . $suffix . "website_destinations ORDER BY priority DESC LIMIT 0,8");
if (!mysqlNum($result)){
	print noContent();
} else { ?>
	<div class="row grid-container-15">
	<? while ($entry=mysqlFetch($result)){ ?>
		<div class="col-md-5 col-sm-10 grid-item">
			<? $block["title"] = $entry["title"];
			$block["subtitle"] = getData("system_database_countries", "code", getData("system_database_airports", "iata", $entry["airport"], "country"), $suffix . "name");
			$block["cover"] = ($entry["cover_image"] ? "uploads/destinations/" . $entry["cover_image"] : "uploads/_website/" . $website_information["cover_image"]);
			$block["url"] = "destinations/" . $entry["canonical"] . "/";
			include "blocks/generic-10.php"; ?>
		</div>
	<? } ?>
	</div>
<? } ?>