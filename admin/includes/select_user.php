<? $search_data = ($value ? getID($value,"users_database") : null); ?>

<td class=title>ملف المستخدم:<? if ($mandatory){ ?> <i class=requ></i><? } ?></td>
<td class=valign-middle>
	<input type=hidden id=<?=$input?>-input name=<?=$input?> value="<?=$value?>" <? if ($mandatory){ ?>data-validation=requiredParentVisible data-validation-error-msg="<?=readLanguage(users,search_not_selected)?>"<? } ?>>
	<? if (($value && $removable) || !$value){ ?>
		<select id=<?=$input?>-select><? if ($value){ ?><option value="<?=$value?>"><?=$search_data["name"]?></option><? } ?></select>
	<? } else {
		echo $search_data["name"];
	} ?>
</td>

<td class=title><?=readLanguage(users,profile)?>:</td>
<td data-preview=<?=$input?> class=valign-middle>
	<a class="btn btn-primary btn-sm" href="_view_user.php?id=<?=$value?>" data-fancybox data-type=iframe style="display:none"><i class="fas fa-search"></i>&nbsp;&nbsp;<?=readLanguage(operations,view)?></a>
	<span><?=readLanguage(users,search_not_selected)?></span>
	<script>
	//Initialize Select2
	$("#<?=$input?>-select").select2({
		width: "100%",
		allowClear: <?=($removable ? "true" : "false")?>,
		placeholder: "<?=readLanguage(users,search)?>",
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
				return { token:"<?=$token?>", action:"search_users", search:params.term, conditions:"<?=$conditions?>" };
			}
		}
	});
	
	//On Selecting
	$("#<?=$input?>-select").on("select2:select", function (e){
		var data = e.params.data;
		if (data.id){
			$("[data-preview=<?=$input?>]").find("span").hide();
			$("[data-preview=<?=$input?>]").find("a").attr("href","_view_user.php?id=" + data.id).show();
			$("#<?=$input?>-input").val(data.id);
			if (typeof onSelectProfile_<?=$input?> === "function"){
				onSelectProfile_<?=$input?>(data);
			}
		}
	});
	
	//On Unselecting
	$("#<?=$input?>-select").on("select2:unselecting", function (e){
		$(this).data("unselecting", true);
		$("[data-preview=<?=$input?>]").find("span").show();
		$("[data-preview=<?=$input?>]").find("a").attr("href","").hide();
		$("#<?=$input?>-input").val("");
		if (typeof onUnselectProfile_<?=$input?> === "function"){
			onUnselectProfile_<?=$input?>();
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
	$("[data-preview=<?=$input?>]").find("span").hide();
	$("[data-preview=<?=$input?>]").find("a").attr("href","_view_user.php?id=<?=$value?>").show();
	$("#<?=$input?>-input").val(<?=$value?>);
	<? } ?>
	</script>
</td>