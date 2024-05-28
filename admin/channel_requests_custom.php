<? include "system/_handler.php";

$multiple_languages = false;
$mysqltable = "channel_requests";
$base_name = basename($_SERVER["SCRIPT_FILENAME"], ".php");
checkPermissions($base_name);

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

//==== UPDATE Status ====
if ($post["update"]){
	mysqlQuery("UPDATE $mysqltable SET status=" . $post["status"] . " WHERE id IN (" . $post["update"] . ")");
	$success = readLanguage(records,updated);
}

//==== Reply ====
if ($post["reply"]){
	$message_data = getID($post["reply"],$mysqltable);
	if ($message_data){
		$email = array($message_data["email"]);
		$subject = "Re: " . $message_data["subject"];
		$message = nl2br($_POST["message"]);		
		$result = sendMail($email,$subject,$message);
		$error = $result[0];
		$success = $result[1];
		if ($success){
			$success = readLanguage(records,updated);
			mysqlQuery("UPDATE $mysqltable SET status=1 WHERE id='" . $post["reply"] . "'");
		}
	}
}

//Read and Set Operation
if ($success){ $message = "<div class='alert alert-success'>$success</div>"; }
if ($error){ $message = "<div class='alert alert-danger'>$error</div>"; }

include "_header.php"; ?>

<div class=title><?=getPageTitle($base_name)?></div>
<?=$message?>

<?
$crud_data["multiple_operations"] = array(
	array("multipleDelete",readLanguage(crud,operations_delete),"fas fa-trash"),
	array("multipleUpdate",readLanguage(crud,operations_update),"fas fa-check-circle")
);
$crud_data["delete_record_message"] = "title";
$crud_data["order_by"] = "status ASC, id DESC";
$crud_data["buttons"] = array(false,true,false,false,true); //Add - Search - View - Edit - Delete
$crud_data["columns"] = array(
	array("status",readLanguage(inputs,status),"120px","center","returnStatusLabel('data_new_closed',%s)",true,false),
	array("message",readLanguage(channels,message),"120px","center","readRecordData(%d,'channel_requests_custom')",false,false),
	array("reason","الغرض","150px","center","getVariable('data_contact_reasons')[%s]",true,false),
	array("name",readLanguage(users,name),"250px","center",null,false,true),
	array("email",readLanguage(users,email),"250px","center",null,false,true),
	array("mobile",readLanguage(users,mobile),"150px","center",null,false,true),
	array("ticket","التذكرة / رقم الحجز","150px","center",null,false,true),
	array("subject",readLanguage(channels,subject),"250px","center",null,false,true),
	array("date",readLanguage(inputs,date),"220px","center","dateLanguage('l, d M Y h:i A',%s)",false,false),
	array("id",readLanguage(operations,manage),"130px","fixed-right","crudDropdown(%d,'channel_requests')",false,false),
);
require_once("crud/crud.php");
?>

<div class="modal fade" id="message_modal" role=dialog>
	<div class=modal-dialog role=document>
		<div class=modal-content>
			<div class=modal-header>
				<button type=button class=close data-dismiss=modal><span aria-hidden=true>&times;</span></button>
				<h4 class=modal-title><span id=message_name></span><div style="color:#909090; font-size:12px"><span id=message_email></span></div></h4>
			</div>
			<div class=modal-body><input type=hidden id=message_id><textarea id=message_reply></textarea></div>
			<div class=modal-footer>
				<button type=button class="btn btn-default btn-sm" data-dismiss=modal><?=readLanguage(plugins,message_cancel)?></button>
				<button type=button class="btn btn-primary btn-sm" onclick="comSendReply()"><?=readLanguage(channels,send)?></button>
			</div>
		</div>
	</div>
</div>

<script>
//Update Status
function comUpdateStatus(id, status){
	postForm({ update: id, status: status });
}

//Show Reply Modal
function comReplyModal(id,name,email){
	$("#message_modal").find("#message_id").val(id);
	$("#message_modal").find("#message_name").text(name);
	$("#message_modal").find("#message_email").text(email);
	$("#message_modal").find("#message_reply").val("");
	$("#message_modal").modal("show");
}

//Send Reply
function comSendReply(){
	postForm({ reply:$("#message_id").val(), message: $("#message_reply").val() });
}

//Multiple Delete
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
			},
		}
	});
}

//Multiple Update
function multipleUpdate(ids){
	$.confirm({
		theme: "light-noborder",
		title: "<?=readLanguage(crud,operations_update)?>",
		content: "<select id=update_status style='border-radius:3px'><option value=0><?=$data_new_closed[0]?></option><option value=1><?=$data_new_closed[1]?></option></select>",
		buttons: {
			confirm: {
				text: readLanguage.plugins.message_update,
				btnClass: "btn-primary",
				action: function (){
					postForm({ update:ids, status:this.$content.find("#update_status").val() });
				}
			},
			cancel: {
				text: readLanguage.plugins.message_cancel
			},
		}
	});
}
</script>

<? include "_footer.php"; ?>