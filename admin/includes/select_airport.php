<? $search_data = ($value ? getData("system_database_airports", "iata", $value) : null); ?>

<input type=hidden id=<?=$input?>-input name=<?=$input?> value="<?=$value?>" <? if ($mandatory){ ?>data-validation=requiredParentVisible data-validation-error-msg="لم تقم بتحديد المطار"<? } ?>>
<? if (($value && $removable) || !$value){ ?>
	<select id=<?=$input?>-select><? if ($value){ ?><option value="<?=$value?>"><?=$search_data["iata"] . " - " . $search_data["ar_name"]?></option><? } ?></select>
<? } else {
	echo $search_data["iata"] . " - " . $search_data["ar_name"];
} ?>

<script>
//Initialize Select2
$("#<?=$input?>-select").select2({
	width: "100%",
	allowClear: <?=($removable ? "true" : "false")?>,
	placeholder: "ابحث عن مطار بالكود او الإسم",
	minimumInputLength: 3,
	escapeMarkup: function(markup){ return markup },
	templateResult: function(data) { return data.html },
	templateSelection: function(data) { return data.text },
	ajax: {
		url: "__requests.php",
		type: "POST",
		dataType: "json",
		delay: 50,
		processResults: function (data){
			return { results: data.results }
		},
		data: function (params){
			return { token:"<?=$token?>", action:"search_airports", search:params.term, conditions:"<?=$conditions?>" };
		}
	}
});

//On Selecting
$("#<?=$input?>-select").on("select2:select", function (e){
	var data = e.params.data;
	if (data.iata){
		$("#<?=$input?>-input").val(data.iata);
		if (typeof onSelectAirport_<?=$input?> === "function"){
			onSelectAirport_<?=$input?>(data);
		}
	}
});

//On Unselecting
$("#<?=$input?>-select").on("select2:unselecting", function (e){
	$(this).data("unselecting", true);
	$("#<?=$input?>-input").val("");
	if (typeof onUnselectAirport_<?=$input?> === "function"){
		onUnselectAirport_<?=$input?>();
	}
});

//On Opening
$("#<?=$input?>-select").on("select2:opening", function (e){
	if ($(this).data("unselecting")){
		$(this).removeData("unselecting");
		e.preventDefault();
	}
});

//Set Default Values
<? if ($value){ ?>
$("#<?=$input?>-input").val(<?=$value?>);
<? } ?>
</script>