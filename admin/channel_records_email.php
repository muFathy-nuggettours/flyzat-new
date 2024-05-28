<? include "system/_handler.php";

$multiple_languages = false;
$mysqltable = "channel_records";
$base_name = basename($_SERVER["SCRIPT_FILENAME"], ".php");
checkPermissions($base_name);

/* Common Pages
	- channel_records_email.php
	- channel_records_sms.php
	- channel_records_push.php
*/

switch ($base_name){
	case "channel_records_email":
		$delete_record_message = "title";
		$type = 1;
	break;
	
	case "channel_records_sms":
		$delete_record_message = "message";
		$type = 2;
	break;
	
	case "channel_records_push":
		$delete_record_message = "title";
		$type = 3;
	break;
}

//==== DELETE Record ====
if ($delete){
	mysqlQuery("DELETE FROM $mysqltable WHERE id=$delete");
	if (mysqlAffectedRows()){ $success = readLanguage(records,deleted); } else { $error = readLanguage(records,unavailable); }
}

//Delete Multiple Records
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
$crud_data["where_statement"] = "type=$type";
$crud_data["delete_record_message"] = $delete_record_message;
$crud_data["buttons"] = array(false,true,false,false,true); //Add - Search - View - Edit - Delete
$crud_data["columns"] = array(
	array("success",readLanguage(channels,sent),"100px","center","returnStatusLabel('data_no_yes',%s)",true,false),
	array("response",readLanguage(channels,response),"300px","center",null,false,true),
	array("date",readLanguage(inputs,date),"240px","center","dateLanguage('l, d M Y h:i A',%s)",false,false),
);

switch ($type){
	case 1:
		array_unshift($crud_data["columns"],
			array("email",readLanguage(users,email),"300px","center","nl2br('%s')",false,true),
			array("title",readLanguage(channels,subject),"300px","center",null,false,true),
			array("message",readLanguage(channels,message),"120px","center","readRecordData(%d,$base_name)",false,true)
		);
	break;
	
	case 2:
		array_unshift($crud_data["columns"],
			array("mobile",readLanguage(users,mobile),"300px","center","nl2br('%s')",false,true),
			array("message",readLanguage(channels,message),"300px","center",null,false,true)
		);
	break;
	
	case 3:
		array_unshift($crud_data["columns"],
			array("title",readLanguage(channels,subject),"300px","center",null,false,true),
			array("message",readLanguage(channels,message),"120px","center","readRecordData(%d,$base_name)",false,true)
		);	 
	break;
}

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