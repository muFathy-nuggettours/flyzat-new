<?
$contact_information = "";

if ($website_information["landline"]){
	$numbers = array();
	$explode = explode("\r\n",$website_information["landline"]);
	foreach ($explode AS $value){
		array_push($numbers, "<a href='tel:$value'>$value</a>");
	}
	$contact_information .= "<div class='info landline'><i class='fal fa-mobile-alt'></i>&nbsp;&nbsp;<div class=number>" . implode("<br>",$numbers) . "</div></div>";
}

if ($website_information["mobile"]){
	$numbers = array();
	$explode = explode("\r\n",$website_information["mobile"]);
	foreach ($explode AS $value){
		array_push($numbers, "<a href='tel:$value'>$value</a>");
	}
	$contact_information .= "<div class='info mobile'><i class='fal fa-phone'></i>&nbsp;&nbsp;<div class=number>" . implode("<br>",$numbers) . "</div></div>";
}

if ($website_information["email"]){
	$contact_information .= "<div class='info email'><i class='fal fa-envelope'></i>&nbsp;&nbsp;<a href='mailto:" . $website_information["email"] . "'>" . $website_information["email"] . "</a></div>";
}

if ($website_information["address"]){
	$contact_information .= "<div class='info address'><i class='fal fa-map-marker-alt'></i>&nbsp;&nbsp;<div>" . nl2br($website_information["address"]) . "</div></div>";
}

if ($contact_information){
	print "<div class=module_contact_info>$contact_information</div>";
}
?>