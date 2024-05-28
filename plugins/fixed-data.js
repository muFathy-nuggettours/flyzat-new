function fixedDataBuild(){
	$("[json-fixed-data]").each(function(){
		var dataPlaceholder = $(this).attr("json-fixed-data");
		var clearEmpty = $(this)[0].hasAttribute("clear-empty");
		var infoArray = {};
		$(this).find("[data-name]").each(function(){
			var objectKey = $(this).attr("data-name");
			var objectValue = $(this).val();
			var insert = (clearEmpty ? (objectValue != "") : true);
			if (insert){
				infoArray[objectKey] = objectValue;
			}
		});
		var jsonData = JSON.stringify(infoArray);
		$("#" + dataPlaceholder).val(jsonData);
    });	
}

function fixedDataRead(placeholder, data){
	var jsonArray = jQuery.parseJSON(JSON.stringify(data));
	$.each(jsonArray,function(index, value){
		$("[json-fixed-data=" + placeholder + "] [data-name='" + index + "']").val(decodeHTML(value));
	});
}