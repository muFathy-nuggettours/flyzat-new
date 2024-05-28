<?
//Set passengers from dropdown for logged users
$user_passengers_result = mysqlQuery("SELECT * FROM users_passengers WHERE user_id='" . $logged_user["id"] . "' AND removed=0");
if ($logged_user && mysqlNum($user_passengers_result)){
	while ($entry = mysqlFetch($user_passengers_result)){
		$user_passengers[$entry["type"]] .= "<option passenger-data='" . json_encode($entry) . "'>" . $data_prefixes[$entry["name_prefix"]] . " " . $entry["first_name"] . " " . $entry["last_name"] . "</option>";
	}
}

//Distruct pricing array to get adt, cnn, and inf counts
['ADT' => ['count' => $adt_count], 'CNN' => ['count' => $cnn_count], 'INF' => ['count' => $inf_count]] = $itinerary['trips'][0]['pricing'];

//Countries array
$country_result = mysqlQuery("SELECT code, phone_code, en_name, ar_name FROM system_database_countries ORDER BY phone_code ASC");
$country_array = mysqlFetchAll($country_result);
?>

<form method=post>
<input type=hidden name=token value=<?=$token?>>
<input type=hidden name=travelers value=<?=intval($itinerary['travelers'])?>>

<!-- بيانات العميل -->
<div class=page_subtitle><?=readLanguage(passengers,client_data)?></div>
<div class=page_container>
<? if ($logged_user){ ?>
	<div class=user_card>
		<img src="<?=($logged_user["image"] ? "uploads/users/" . $logged_user["image"] : "images/user.png")?>">
		<div class=single-line>
			<span class=single-line><a href="user/"><b><?=$logged_user["name"]?></b></a></span>
			<span class=single-line><?=$logged_user["email"]?></span>
		</div>	
	</div>

<? } else { ?>
	<table class=form_table>
	<tr>
		<td colspan=2>
			<div class=title><?=readLanguage(contact,name)?>: <i class=requ></i></div>
			<div class=input data-icon="&#xf007;"><input type=text name=name value="<?=$post["name"]?>" maxlength=255 placeholder="<?=readLanguage(accounts,name_placeholder)?>" data-validation=required></div>
		</td>
	</tr>
	<tr>
		<td>
			<div class=title><?=readLanguage(contact,email)?>: <i class=requ></i></div>
			<div class=input data-icon="&#xf1fa;"><input type=email name=email value="<?=$post["email"]?>" maxlength=100 placeholder="<?=readLanguage(accounts,email_placeholder)?>" data-validation=email autocomplete=email></div>
		</td>
		<td>
			<div class=title><?=readLanguage(contact,mobile)?>: <i class=requ></i></div>
			<div class="input force-ltr" data-icon="&#xf3cd;">
				<select name=country id=country>
				<? foreach ($country_array as $country) {?>
					<option value="<?=$country["code"]?>" name="<?=$country[$website_language . "_name"]?>" data-phone-code="+<?=$country["phone_code"]?>">+<?=$country["phone_code"] . " " . $country[$website_language . "_name"]?></option>
				<? }?>
				</select>
				&nbsp;&nbsp;<input type=number name=mobile value="<?=$post["mobile"]?>" maxlength=11 placeholder="<?=readLanguage(accounts,mobile_placeholder)?>" data-validation=validateMobile>
			</div>
			<script>
			//Set default country selection value
			setSelectValue("#country", "<?=$user_countryCode?>");
			
			//Initialize Select2
			$("#country").select2({
				dir: "ltr",
				width: "25%",
				dropdownAutoWidth: true,
				templateResult: function(state){
					var element = $(state.element);
					return $("<div class='d-flex align-items-center'><img src='images/countries/" + $(element).val() + ".gif'>&nbsp;&nbsp;<span><b>(" + $(element).data("phone-code") + ")</b>&nbsp;&nbsp;" + $(element).attr("name") + "</span></div>");
				},
				templateSelection: function(state){
					var element = $(state.element);
					return $("<div class='d-flex align-items-center'><img src='images/countries/" + $(element).val() + ".gif'>" + "&nbsp;<span>" + $(element).data("phone-code") + "</span></div>");
				}
			});
			
			//Validate Editor
			$.formUtils.addValidator({
				name: "validateMobile",
				validatorFunction: function(value, $el, config, language, $form){
					var valid_mobile =  false;
					switch ($("#country").val()){
						case "eg":
							valid_mobile = (value.match(/^((010|011|012|015)[0-9]{8})|((10|11|12|15)[0-9]{8})$/g)==value);
						break;
						
						case "sa":
							valid_mobile = (value.match(/^((05)[0-9]{8})|((5)[0-9]{8})$/g)==value);
						break;
						
						default:
							valid_mobile = true;
					}
					return (value ? true : false) && valid_mobile;
				},
				errorMessage: "<?=readLanguage(accounts,mobile_placeholder)?>"
			});
			</script>
		</td>
	</tr>
	</table>
<? } ?>
</div>

<!-- بيانات المسافرين -->
<div class="page_subtitle margin-top-20"><?=readLanguage(passengers,passengers_data)?></div>
<div class="alert alert-warning"><?=readLanguage(passengers,passengers_data_alert)?></div>

<?
//Set passenger types count
for ($i = 0; $i < intval($itinerary['travelers']); $i++){
if ($adt_count--> 0) $type = 0;
elseif ($cnn_count--> 0) $type = 1;
elseif ($inf_count--> 0) $type = 2; ?>
<input type=hidden name=type-<?=$i?> value="<?=$type?>">
<input type=hidden name=passport-<?=$i?>>
<div class="page_container margin-top"><div class=page_subtitle><?=readLanguage(common,passenger)?> (<?=$i+1?>) - <?=$data_passenger_types[$type]?></div>
<table class=form_table>
<tr>
	<td colspan=2>
		<? if ($user_passengers[$type]){ ?>
		<select name=passengers_selection><option value=""><?=readLanguage(passengers,choosing_passengers)?></option><?=$user_passengers[$type]?></select>
		<div class=separator_or><label><?=readLanguage(accounts,separator)?></label></div>
		<? } ?>
		<label class="upload-image margin-bottom d-block">
			<input type=file name=passport_image-<?=$i?> class=d-none accept="image/*">
			<?=readLanguage(passengers,upload_passport)?>
		</label>
		<script>
		//On passport file change
		$("[name=passport_image-<?=$i?>]").on("change", function(){
			parsePassport($(this), <?=$i?>);
		});
		</script>
	</td>
</tr>
<tr>
	<td>
		<div class=title><?=readLanguage(common,first_name)?>: <i class=requ></i></div>
		<div class=d-flex>
			<div class=input style="width: 30%" data-icon="&#xf007;">
				<select name=name_prefix-<?=$i?> id=name_prefix-<?=$i?>>
					<?=populateOptions($data_passenger_names_prefix)?>
				</select>
				<script>
					setSelectValue("#name_prefix-<?=$i?>", "<?=($post["name_prefix-$i"] ?: 1)?>");
				</script>
			</div>&nbsp;&nbsp;
			<div class=input style="width: 70%" data-icon="&#xf007;"><input type=text name=first_name-<?=$i?> value="<?=$post["first_name-$i"]?>" maxlength=255 placeholder="<?=readLanguage(common,first_name_placeholder)?>" data-validation=alphanumeric data-validation-error-msg="<?=readLanguage(common,first_name_validate)?>"></div>
		</div>
	</td>
	<td>
		<div class=title>الإسم الأخير: <i class=requ></i></div>
		<div class=input data-icon="&#xf007;"><input type=text name=last_name-<?=$i?> value="<?=$post["last_name-$i"]?>" maxlength=255 placeholder="<?=readLanguage(common,last_name_validate)?>" data-validation=alphanumeric data-validation-error-msg="<?=readLanguage(common,last_name_validate)?>"></div>
	</td>
</tr>
<tr>
	<td>
		<div class=title><?=readLanguage(passengers,birth_date)?>: <i class=requ></i></div>
		<div>
			<input class=caleran name=birth_date-<?=$i?> type=text value="<?=$post["birth_date-$i"]?>">
			<script>
			$("[name=birth_date-<?=$i?>]").caleran({
				format: "D/M/YYYY",
				calendarCount: 1,
				locale: "ar",
				showHeader: false,
				showFooter: false,
				singleDate: true,
				maxDate: <?=$adt_count >= 0 ? 'moment().subtract(18, "year")' : 'moment()'?>,
				autoCloseOnSelect: true
			});
			</script>
		</div>
	</td>
	<td>
		<div class=title><?=readLanguage(common,nationality_country)?>: <i class=requ></i></div>
		<div>
			<select name=nationality-<?=$i?> id=nationality-<?=$i?>>
			<? foreach ($country_array as $country) {?>
				<option value="<?=$country["code"]?>" name="<?=$country[$website_language . "_name"]?>" data-phone-code="+<?=$country["phone_code"]?>">
					<?=$country[$website_language . "_name"]?>
				</option>
			<? }?>
			</select>
			<script>
			setSelectValue("[name=nationality-<?=$i?>]", "<?=$post["nationality-$i"] ?: $user_countryCode?>");
			//Initialize Select2
			$("[name=nationality-<?=$i?>]").select2({
				dropdownAutoWidth: true,
				templateResult: function(state) {
					var element = $(state.element);
					return $("<div class='d-flex align-items-center'><img src='images/countries/" + $(element).val() + ".gif'>&nbsp;" + $(element).attr("name") + "</div>");
				},
				templateSelection: function(state) {
					var element = $(state.element);
					return $("<div class='d-flex align-items-center'><img src='images/countries/" + $(element).val() + ".gif'>&nbsp;" + $(element).attr("name") + "</div>");
				}
			});
			</script>
		</div>
	</td>
</tr>
<tr>
	<td>
		<div class=title><?=readLanguage(passengers,passport_number)?>: <i class=requ></i></div>
			<div class=input data-icon="&#xf2c2;"><input type=text name=ssn-<?=$i?> value="<?=$post["ssn-$i"]?>" placeholder="قم بإدخال رقم جواز السفر او رقم الهوية للطيران الداخلي" data-validation=required></div>
	</td>
	<td>
		<div class=title><?=readLanguage(common,end_date)?>: <i class=requ></i></div>
		<div>
			<input class=caleran type=text name=ssn_end-<?=$i?> value="<?=$post["ssn_end-$i"]?>">
			<script>
			$("[name=ssn_end-<?=$i?>]").caleran({
				format: "D/M/YYYY",
				calendarCount: 1,
				locale: "ar",
				showHeader: false,
				showFooter: false,
				singleDate: true,
				minDate: moment() + (86400 * 6 * 30 * 1000),
				autoCloseOnSelect: true
			});
			</script>
		</div>
	</td>
</tr>
<tr>
	<td colspan=4>
		<div class="panel panel-default">
			<div class=panel-heading><a data-toggle=collapse href="#collapse-<?=$i?>"><?=readLanguage(passengers,special_requests)?></a><i class="fa fa-caret-down"></i></div>
			<div id="collapse-<?=$i?>" class="panel-collapse collapse">
			<div class="panel-body">
				<table class=form_table>
					<tr>
					<td>
						<div class=title><?=readLanguage(passengers,special_needs)?>:</div>
						<select name=special_needs-<?=$i?> id=special_needs-<?=$i?>><option value="0"><?=readLanguage(common,undefined)?></option><?=populateOptions($data_special_needs)?></select>
						<script>
						setSelectValue("#special_needs-<?=$i?>", "<?=$post["special_needs-$i"] ?: 0?>");
						</script>
					</td>
					<td>
						<div class=title><?=readLanguage(passengers,special_meals)?>:</div>
						<select name=special_meals-<?=$i?> id=special_meals-<?=$i?>><option value="0"><?=readLanguage(common,undefined)?></option><?=populateOptions($data_special_meals)?></select>
						<script>
						setSelectValue("#special_meals-<?=$i?>", "<?=$post["special_meals-$i"] ?: 0?>");
						</script>
					</td>
					</tr>
				</table>
			</div>
			</div>
		</div>
	</td>
</tr>
</table>
</div>
<? } ?>

<div class="page_subtitle margin-top-20"><?=readLanguage(common,extra_notes)?></div>
<div class=page_container>
	<textarea name=notes value="<?=$post["notes"]?>" placeholder="<?=readLanguage(common,extra_notes_placeholder)?>"></textarea>
</div>

<button type=button class="submit large margin-top-30"><?=readLanguage(passengers,goto_payment)?></button>
</form>

<script>
$("[name=passengers_selection]").select2();
$("[name=passengers_selection]").on("change", function(){
	var data = $(this).find("option:selected").attr("passenger-data");
	var target = $(this).parents(".form_table");
	if (data){
		data = JSON.parse(data);
		target.find("[name^=name_prefix]").val(data.name_prefix);
		target.find("[name^=first_name]").val(data.first_name);
		target.find("[name^=last_name]").val(data.last_name);
		target.find("[name^=birth_date]").val(moment.unix(data.birth_date).format("D/M/YYYY"));
		target.find("[name^=nationality]").val(data.nationality).trigger("change");
		target.find("[name^=ssn]").val(data.ssn);
		target.find("[name^=ssn_end]").val(moment.unix(data.ssn_end).format("D/M/YYYY"));
		target.find("[name^=special_needs]").val(data.special_needs);
		target.find("[name^=special_meals]").val(data.special_meals);
	}
});
</script>