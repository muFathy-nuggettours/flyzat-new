<? include "system/_handler.php";

$mysqltable = $suffix . "website_forms";
$base_name = basename($_SERVER["SCRIPT_FILENAME"], ".php");
checkPermissions($base_name);

//Show Records
if ($get["show"]){
	$form_data = getID($get["show"], $mysqltable);
}

//Delete Multiple Records
if ($form_data && $post["delete"]){
	mysqlQuery("DELETE FROM " . $suffix . "website_forms_records WHERE id IN (" . $post["delete"] . ")");
	mysqlQuery("UPDATE $mysqltable SET records='" . mysqlNum(mysqlQuery("SELECT id FROM " . $suffix . "website_forms_records WHERE form_id='" . $get["show"] . "'")) . "' WHERE id=" . $get["show"]);
	if (mysqlAffectedRows()){ $success = readLanguage(records,deleted); } else { $error = readLanguage(records,unavailable); }
}

if ($success){ $message = "<div class='alert alert-success'>$success</div>"; }
if ($error){ $message = "<div class='alert alert-danger'>$error</div>"; }

include "_header.php"; ?>

<div class=title><?=getPageTitle($base_name)?></div>
<?=$message?>

<!-- Records Table -->
<? if ($form_data){ ?>

<script src="../plugins/datatables.min.js?v=<?=$system_settings["system_version"]?>"></script>
<link href="../plugins/datatables.min.css?v=<?=$system_settings["system_version"]?>" rel="stylesheet">
<script src="../plugins/tabletoexcel.js?v=<?=$system_settings["system_version"]?>"></script>

<div class="alert alert-title"><?=$form_data["placeholder"]?></div>

<? $form_fields = json_decode($form_data["form"], true);
$excluded_types = array("heading", "plain"); ?>
<table id=records_table class="display cell-border">
<thead>
	<th>#</th>
	<th><input type=checkbox name=select_records></th>
	<? foreach ($form_fields AS $key=>$value){
		if (!in_array($value["type"], $excluded_types)){
			print "<th style='min-width:200px'>" . $value["properties"]["label"] . "</th>";
		}
	} ?>
	<th style="min-width:250px"><?=readLanguage(users,user)?></th>
	<th style="min-width:150px"><?=readLanguage(forms,ip)?></th>
	<th style="min-width:250px"><?=readLanguage(inputs,date)?></th>
</thead>
<? $result = mysqlQuery("SELECT * FROM " . $suffix . "website_forms_records WHERE form_id='" . $form_data["id"] . "'");
while ($entry = mysqlFetch($result)){ $serial++; ?>
<tr>
	<td><?=$serial?></td>
	<td><input type=checkbox name=records[] record-checkbox value="<?=$entry["id"]?>"></td>
	<? $user_input = json_decode($entry["content"], true);
	foreach ($form_fields AS $key=>$value){
		if (!in_array($value["type"], $excluded_types)){
			$input = $user_input[$value["id"]];
			switch ($value["type"]){
				case "textarea":
					$content = naRes($input, maxLength($input, 40) . " <a modal-content='" . base64_encode($input) . "'>". readLanguage(forms,read_more) ."</a>");
				break;
				
				case "file":
					$content = naRes($input, fileBlock("../uploads/forms/" . $input, ($value["label"] ? $value["label"] : $input)));
				break;
				
				case "checkbox": case "multiple_select":
					$content = naRes($input, implode(", ", $input));
				break;
				
				case "date":
					$content = naRes($input, dateLanguage("l, d F Y", $input));
				break;
				
				default:
					$content = naRes($input);
			}
			print "<td>$content</td>";
		}
	} ?>
	<td><?=naRes($entry["user_id"], getCustomData("name","users_database","id","%s","_view_user"))?></td>
	<td><?=$entry["ip"]?></td>
	<td><?=dateLanguage("l, d F Y h:i A", $entry["date"])?></td>
</tr>
<? } ?>
</table>

<script>
$(document).ready(function(){
	//Initialize DataTable
	var table = $("#records_table").DataTable({
		paging: true,
		pageLength: 10,
		searching: true,
		ordering: true,		
		bLengthChange: true,
		responsive: false,
		scrollX: true,
		fixedColumns: {
            left: 2
        },
		dom: "<'toolbar dataTables_toolbar margin-bottom-10 justify-content-end'l<'search'f>B>t<ip>",
		buttons: [
			{
				text: readLanguage.crud.button_excel,
				className: "btn btn-success btn-sm",
				action: function (e, dt, node, config){
					$("#records_table").tblToExcel("<?=$form_data["placeholder"]?>");
				}
			},
			{
				text: readLanguage.forms.remove_selections,
				className: "btn btn-danger btn-sm",
				action: function (e, dt, node, config){
					removeSelections();
				}
			},
		],
		columnDefs: [
			{ orderable: true, searchable: true, className: "dt-center", targets: "_all" },
			{ orderable: false, searchable: false, className: "dt-center", targets: [0] }
		],
		initComplete: function(settings, json){
			//Remove data table class from buttons
			$(".dt-button").removeClass("dt-button");
	
			//Drag Scroll
			var x, y, top, left, down;
			$(".dataTables_scrollBody").css("cursor","grab");
			$(".dataTables_scrollBody").mousedown(function(e){
				e.preventDefault();
				down = true;
				x = e.pageX;
				y = e.pageY;
				top = $(this).scrollTop();
				left = $(this).scrollLeft();
				$(this).css("cursor","grabbing");
			});
			$("body").mousemove(function(e){
				if (down){
					var newX = e.pageX;
					var newY = e.pageY;
					$(".dataTables_scrollBody").scrollTop(top - newY + y);
					$(".dataTables_scrollBody").scrollLeft(left - newX + x);
				}
			});
			$("body").mouseup(function(e){
				down = false;
				$(".dataTables_scrollBody").css("cursor","grab");
			});
		}
	});

	//Append sorting icon in span
	table.columns().iterator("column", function (ctx, idx){
		var header = $(table.column(idx).header());
		if (!header.hasClass("dtfc-fixed-left")){
			header.prepend("<span class=sort-icon>");
		}
    });
});

//Read more in modal
$("[modal-content]").on("click", function(){
	bootbox.alert(atob($(this).attr("modal-content")));
});

//Selection
$("[name=select_records]").on("change", function(){
	$("[record-checkbox]").prop("checked", $(this).prop("checked"));
});

//Remove selection
function removeSelections(){
	if (!$("[record-checkbox]:checked").length){
		quickNotify("<?=readLanguage(crud,operations_selection)?>", null, "danger", "fas fa-times fa-3x");
	} else {
		var checked_values = [];
		$("[record-checkbox]:checked").each(function(){
			checked_values.push($(this).val());
		});
		$.confirm({
			title: "<?=readLanguage(crud,operations_delete)?>",
			content: readLanguage.crud.operations_delete_message.replace("{{1}}", $("[record-checkbox]:checked").length),
			buttons: {
				confirm: {
					text: readLanguage.plugins.message_yes,
					btnClass: "btn-red",
					action: function (){
						postForm({
							delete: checked_values.join(",")
						});
					}
				},
				cancel: {
					text: readLanguage.plugins.message_cancel
				},
			}
		});
	}
}
</script>

<!-- Selection Table -->
<? } else { ?>
<?
$crud_data["buttons"] = array(false, true, false, false, false); //Add - Search - View - Edit - Delete
$crud_data["columns"] = array( //Filter - Search - Copy Enabled
    array("placeholder", readLanguage(builder,placeholder), "100%", "center", null, false, true),
	array("records", readLanguage(forms,records), "120px", "center", null, false, true),
	array("id", readLanguage(forms,records_show), "120px", "center", "'<a class=\"btn btn-primary btn-sm btn-block\" href=\"$base_name.php?show=%s\">".readLanguage(forms,records_show)."</a>'", false, false),
);
require_once("crud/crud.php");
?>
<? } ?>

<? include "_footer.php"; ?>