<? include "system/_handler.php";

$mysqltable = $suffix . "website_modules";
$base_name = basename($_SERVER["SCRIPT_FILENAME"], ".php");
checkPermissions($base_name);

//==== EDIT Record ====
if ($post["token"]){
	mysqlQuery("UPDATE $mysqltable SET content = CASE
		WHEN title='about_mission' THEN '" . $post["about_mission"] . "'
		WHEN title='about_vision' THEN '" . $post["about_vision"] . "'
		WHEN title='about_objectives' THEN '" . $post["about_objectives"] . "'
		WHEN title='about_goals' THEN '" . $post["about_goals"] . "'
		ELSE content
	END");	
	$success = readLanguage(records,updated);
}

//Read and Set Operation
if ($success){ $message = "<div class='alert alert-success'>$success</div>"; }

//Reload Modules Data
$entry = fetchData($mysqltable);

include "_header.php"; ?>

<div class=title><?=getPageTitle($base_name)?></div>
<?=$message?>

<form method=post enctype="multipart/form-data" action="<?=$action?>">
<input type=hidden name=token value="<?=$token?>">

<!-- About Us Module -->
<div class=subtitle>About Us Module</div>
<table class=data_table>
<tr>
	<td class=title>Mission: <i class=requ></i></td>
	<td><input type=text name=about_mission value="<?=$entry["about_mission"]?>" data-validation=required></td>
	<td class=title>Vision: <i class=requ></i></td>
	<td><input type=text name=about_vision value="<?=$entry["about_vision"]?>" data-validation=required></td>
</tr>
<tr>
	<td class=title>Objectives: <i class=requ></i></td>
	<td><input type=text name=about_objectives value="<?=$entry["about_objectives"]?>" data-validation=required></td>
	<td class=title>Goals: <i class=requ></i></td>
	<td><input type=text name=about_goals value="<?=$entry["about_goals"]?>" data-validation=required></td>
</tr>
</table>

<div class=submit_container><input type=button class=submit value="<?=readLanguage(records,update)?>"></div>
</form>

<? include "_footer.php"; ?>