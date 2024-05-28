function handleFileSelect(event){
	var pluginID = event.target.id;
	var	pluginInput = $("[name=" + pluginID + "]");
	var selectedFiles = document.getElementById(pluginID).files.length;
	var uploadedFiles = $("[data-attachments=" + pluginID + "]").find("ul li").length;
	if (selectedFiles){
		if (pluginInput.attr("validation-max") && (selectedFiles + uploadedFiles) > pluginInput.attr("validation-max")){
			quickNotify(readLanguage.plugins.upload_error, readLanguage.general.error, "danger", "fas fa-times fa-3x");		
		} else {
			attachmentsStartUpload(pluginID);
		}
	}
}

function attachmentsStartUpload(pluginID, currentFile=0){
	var files = document.getElementById(pluginID).files;
	var filesTotal = files.length;
	var folderPath = $("td[data-attachments=" + pluginID + "]").attr("data-upload-path");
	var files = document.getElementById(pluginID).files;

	$("td[data-attachments=" + pluginID + "]").find(".attachment-button label").attr("disabled", "disabled");
	$("td[data-attachments=" + pluginID + "]").find(".attachment-button input[type=file]").attr("disabled", "disabled");
	$("td[data-attachments=" + pluginID + "]").find(".attachment-button div").css("display", "flex");

	var myFormData = new FormData();
	myFormData.append("token", user_token);
	myFormData.append("path", folderPath);
	myFormData.append("files[]", files[currentFile]);

	$.ajax({
		type: "post",
		url: (folderPath.includes("../") ? "__uploader.php" : "uploader/"),
		data: myFormData,
		dataType: "text",
		cache: false,
		contentType: false,
		processData: false,
		success: function(response){
			var jsonObject = jQuery.parseJSON(response);
			jsonObject.forEach(function(fileData){
				attachmentsLoadFile(fileData, pluginID);
			});
		},
		error: function(request, status, error){
			quickNotify(request.responseText, readLanguage.plugins.upload_error_title + files[currentFile].name, "danger", "fas fa-times fa-3x")
		},
		complete: function(request, status){
			if (currentFile == (filesTotal - 1)){
				$("td[data-attachments=" + pluginID + "]").find(".attachment-button label").removeAttr("disabled");
				$("td[data-attachments=" + pluginID + "]").find(".attachment-button input[type=file]").removeAttr("disabled");
				$("td[data-attachments=" + pluginID + "]").find(".attachment-button div").css("display", "none");				
			} else {
				attachmentsStartUpload(pluginID, currentFile + 1);
			}
		}
	});
}

function attachmentsLoadFile(jsonData, pluginID){
	var uniqueID = Date.now() + Math.floor((Math.random() * 999) + 100);
	var fileExtension = jsonData.url.split(/\#|\?/)[0].split('.').pop().trim().toLowerCase();
	var uploadPath = $("td[data-attachments=" + pluginID + "]").attr("data-upload-path");
	switch (fileExtension){
		case "png":
		case "jpg":
		case "jpeg":
		case "bmp":
		case "gif":
			var preview = "<a data-fancybox=images class=image style=\"background-image:url('" + uploadPath + jsonData.url + "')\" href='" + uploadPath + jsonData.url + "'></a>";
			break;
		case "zip":
		case "rar":
			var preview = "<i class='fas fa-file-archive'></i>";
			break;
		case "xls":
		case "xlsx":
			var preview = "<i class='fas fa-file-excel'></i>";
			break;
		case "doc":
		case "docx":
			var preview = "<i class='fas fa-file-word'></i>";
			break;
		case "pdf":
			var preview = "<i class='fas fa-file-pdf'></i>";
			break;
		default:
			var preview = "<i class='fas fa-file'></i>";
	}
	var attachmentBlock = "<li id='" + uniqueID + "' data-url='" + jsonData.url + "' data-title='" + jsonData.title + "'>" +
		"<div class=attachment-block>" +
			"<div class=attachment-preview>" + preview + "</div>" +
			"<div class=attachment-details><span>" + decodeHTML(jsonData.title) + "</span><small>" + fileExtension + "</small></div>" +
			"<div class=attachment-buttons>" +
				"<a onclick='attachmentsRemoveFile(" + uniqueID + ")'><i class='fas fa-times'></i></a>" +
				"<a href='" + uploadPath + jsonData.url + "' download='" + (!jsonData.title ? "" : decodeHTML(jsonData.title) + "." + fileExtension) + "'><i class='fas fa-download'></i></a>" +
				"<a onclick='attachmentsEditFile(" + uniqueID + ")'><i class='fas fa-edit'></i></a>" +
			"</div>" +
		"</div>" +
		"</li>";
	$("td[data-attachments=" + pluginID + "]").find("[sortable]").append(attachmentBlock);
}

function attachmentsRemoveFile(uniqueID){
	$.confirm({
		title: readLanguage.plugins.message_delete,
		content: readLanguage.plugins.attachment_delete,
		icon: "fas fa-trash",
		buttons: {
			yes: {
				text: readLanguage.plugins.message_yes,
				btnClass: "btn-red",
				action: function(){
					$("#" + uniqueID).remove();
				}
			},
			cancel: { text: readLanguage.plugins.message_cancel }
		}
	});
}

function attachmentsEditFile(uniqueID){
	var defaultTitle = decodeHTML($("#" + uniqueID).find(".attachment-details span").text());
	$.confirm({
		theme: "light-noborder",
		title: readLanguage.plugins.attachment_update,
		content: "<input type=text style='border-radius:3px' value='" + defaultTitle + "'>",
		buttons: {
			save: {
				text: readLanguage.plugins.message_save,
				btnClass: "btn-green",
				action: function (){
					var newTitle = this.$content.find("input").val();
					$("#" + uniqueID).attr("data-title", newTitle);
					$("#" + uniqueID).find(".attachment-details span").text(newTitle);
				}
			},
			cancel: { text: readLanguage.plugins.message_cancel }
		}
	});	
}

function attachmentsBuild(){
	$("[data-attachments]").each(function(){
		var filesArray = [];
		$(this).find("ul li").each(function(){
			var fileObject = {};
			fileObject.url = $(this).attr("data-url");
			fileObject.title = $(this).attr("data-title");
			filesArray.push(fileObject);
		});
		$(this).find("input[type=hidden]").val((filesArray.length ? JSON.stringify(filesArray) : ""));
	});
}

$(document).ready(function(){
	$("[data-attachments]").each(function(){
		$(this).find("[sortable]").sortable({
			containment: $(this)
		});
		$(this).find("input[type=file]")[0].addEventListener("change", handleFileSelect, false);
	});
});