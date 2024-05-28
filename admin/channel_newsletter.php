<? include "system/_handler.php";

$multiple_languages = false;
$mysqltable = "channel_newsletter";
$base_name = basename($_SERVER["SCRIPT_FILENAME"], ".php");
checkPermissions($base_name);

//==== DELETE Record ====
if ($delete){
	mysqlQuery("DELETE FROM $mysqltable WHERE id=$delete");
	if (mysqlAffectedRows()){ $success = readLanguage(records,deleted); } else { $error = readLanguage(records,unavailable); }
}

if ($post["delete"]){
	mysqlQuery("DELETE FROM $mysqltable WHERE id IN (" . $post["delete"] . ")");
	if (mysqlAffectedRows()){ $success = readLanguage(records,deleted); } else { $error = readLanguage(records,unavailable); }
}

//Read and Set Operation
if ($success){ $message = "<div class='alert alert-success'>$success</div>"; }
if ($error){ $message = "<div class='alert alert-danger'>$error</div>"; }

include "_header.php"; ?>

<div class=title><?=getPageTitle($base_name)?></div>
<?=$message?>

<?
$crud_data["multiple_operations"] = array(
	array("multipleDelete",readLanguage(crud,operations_delete),"fas fa-times-circle")
);
$crud_data["delete_record_message"] = "email";
$crud_data["buttons"] = array(false,true,false,false,true); //Add - Search - View - Edit - Delete
$crud_data["columns"] = array(
	array("email",readLanguage(users,profile),"50%","center","getCustomData('name','users_database','email','%s','_view_user')",false,true),
	array("email",readLanguage(channels,email),"50%","center",null,false,true,true),
	array("date",readLanguage(inputs,date),"220px","center","dateLanguage('l, d M Y h:i A',%s)",false,false),
);
require_once("crud/crud.php");
?>

<script>
function multipleDelete(ids){
	$.confirm({
		title: "<?=readLanguage(crud,operations_delete)?>",
		content: readLanguage.crud.operations_delete_message.replace("{{1}}", ids.split(",").length),
		buttons: {
			confirm: {
				text: readLanguage.plugins.message_yes,
				btnClass: "btn-red",
				action: function (){
					postForm({
						delete: ids
					});
				}
			},
			cancel: {
				text: readLanguage.plugins.message_cancel
			}
		}
	});
}
</script>

<? include "_footer.php"; ?>