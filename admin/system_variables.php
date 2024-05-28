<? include "system/_handler.php";

$mysqltable = "system_variables";
$base_name = basename($_SERVER["SCRIPT_FILENAME"], ".php");
checkPermissions($base_name);

//==== EDIT Record ====
if ($post["token"] && $edit) {
	$query = "UPDATE $mysqltable SET
		variables='" . $post["variables"] . "'
	WHERE id=$edit";
	mysqlQuery($query);
	$success = readLanguage(records, updated);
}

if ($edit){
	$entry = getID($edit,$mysqltable);
	if (!$entry){ $error = readLanguage(records,unavailable); $edit = null; }
}
if ($edit){
	$button = readLanguage(records,update);
	$action = "$base_name.php" . rebuildQueryParameters(array("delete","token"));
}

if ($success){ $message = "<div class='alert alert-success'>$success</div>"; }
if ($error){ $message = "<div class='alert alert-danger'>$error</div>"; }

include "_header.php"; ?>

<div class=title><?=getPageTitle($base_name)?></div>
<?=$message?>

<? if ($edit){ ?>
<div class="alert alert-title"><?=$entry["placeholder"]?></div>

<form method=post enctype="multipart/form-data" action="<?=$action?>">
<input type=hidden name=token value="<?=$token?>">

<table class=data_table>
<tr>
	<td class=title><?=readLanguage(inputs,content)?>: <i class=requ></i></td>
	<td data-multiple=variables>
		<button type=button class="btn btn-primary btn-sm" onclick="multipleDataCreate('variables')"><?=readLanguage(operations,insert)?></button>
		<input type=hidden name=variables>
		<ul multiple-sortable>
			<li data-template>
				<input type=hidden data-name=key>
				<div class=d-flex>
					<div class="grabbable grabbable_icon">
						<i class="fas fa-bars"></i>
					</div>&nbsp;&nbsp;
					<? if (!$entry["multi_language"]){
						print "<input type=text data-name=value data-validation=required disabled>&nbsp;&nbsp;";
					} else {
						foreach ($supported_languages AS $language){
							$language_options = languageOptions($language);
							print "<input type=text data-name=" . $language . "_value data-validation=required disabled placeholder='" . $language_options["name"] . "'>&nbsp;&nbsp;";
						}
					} ?>
					<div class=check_container id=checkboxes>
						<label>
							<input type=checkbox data-type=checkbox data-name=hidden class=filled-in value=1>
							<span><?=readLanguage(inputs,hidden)?></span>
						</label>
					</div>
					<a class="btn btn-danger btn-sm remove d-none"><i class="fas fa-times"></i></a>
				</div>
			</li>
		</ul>
		
		<script>
		<? if ($entry["variables"]){ ?>
			let jsonArray = <?=$entry["variables"]?>;
			jsonArray.forEach(function(entry){ multipleDataCreate("variables", entry); });
		<? } ?>
		
		//Assign variable key when newly created
		function onMultipleDataCreate_variables(object, data){
			if (!data){
				$(object).attr("new", true);
				$(object).find(".remove").removeClass("d-none");
				$(object).find("[data-name='key']").val(newVariableID());
			}
		}
		
		//Get new variable ID
		function newVariableID(){
			var existing_keys = [];
			$("[data-multiple=variables] [multiple-sortable] li:not('[data-template]')").each(function(){
				var key = $(this).find("[data-name=key]").val();
				if (key){
					existing_keys.push(parseInt(key));
				}
			});
			if (existing_keys.length){
				var sorted_keys = existing_keys.sort(function(a, b){return b - a});
				return sorted_keys[0] + 1;
			} else {
				return 1;
			}
		}
		
		//Re-arrange keys for newly created variables
		function onMultipleDataRemove_variables(object){
			$("[data-multiple=variables] [multiple-sortable] li[new] [data-name=key]").val("").each(function(){
				$(this).val(newVariableID());
			});
		}
		</script>
	</td>
</tr>
</table>

<div class=submit_container><input type=button class=submit value="<?=$button?>"></div>
</form>
<? } ?>

<div class=crud_separator></div>

<?
$crud_data["buttons"] = array(false, true, false, true, false); //Add - Search - View - Edit - Delete
$crud_data["columns"] = array( //Filter - Search - Copy Enabled
    array("placeholder", readLanguage(general,variable), "100%", "center", null, false, true),
);
require_once("crud/crud.php");
?>

<? include "_footer.php"; ?>