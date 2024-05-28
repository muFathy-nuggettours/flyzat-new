<?
//Define 1 Year in timestamp value
const YEAR_TIMESTAMP = 31556926;

//Remove
if ($post["token"] && $post["remove"]){
	mysqlQuery("UPDATE users_passengers SET removed=1 WHERE user_id={$logged_user['id']} AND id={$post['remove']}");
	$success = readLanguage(passengers,delete_msg);

//Create / Update passenger
} else if ($post["token"]){
	$exists = mysqlFetch(mysqlQuery("SELECT * FROM users_passengers WHERE user_id='" . $logged_user["id"] . "' AND ssn='" . $post["ssn"] . "'"));
	$update = ($post["passenger_id"] ? $post["passenger_id"] : mysqlFetch(mysqlQuery("SELECT * FROM users_passengers WHERE user_id='" . $logged_user["id"] . "' AND ssn='" . $post["ssn"] . "' AND removed=1"))["id"]);

	//Create passenger
	if (!$exists && !$update){
        //Calculate passenger age to determine his type
        $birth_date = getTimestamp($post["birth_date"]);
        $age = floor((time() - $birth_date) / YEAR_TIMESTAMP);
        $type = $age >= 18 ? 0 : ($age >= 2 ? 1 : 2);
        
		$query = "INSERT INTO users_passengers (
			type,
			code,
			user_id,
			name_prefix,
			first_name,
			last_name,
            image,
			birth_date,
			nationality,
			passport,
			ssn,
			ssn_end,
            special_needs,
            special_meals
		) VALUES (
			'$type',
			'" . base64_encode(uniqid($logged_user["user_id"], true)) . "',
			'" . $logged_user['id'] . "',
			'" . $post["name_prefix"] . "',
			'" . $post["first_name"] . "',
			'" . $post["last_name"] . "',
            '" . imgUploadBase64($_POST["passenger_image_base64"], "uploads/users/") . "',
			'" . $birth_date . "',
			'" . $post["nationality"] . "',
			'" . $post["passport"] . "',
			'" . $post["ssn"] . "',
			'" . getTimestamp($post["ssn_end"]) . "',
			'" . $post["special_needs"] . "',
			'" . $post["special_meals"] . "'
		)";
        mysqlQuery($query);
		
		$success = readLanguage(passengers,add_msg);

	//Update passenger
	} else if ($update){
		if (mysqlNum(mysqlQuery("SELECT * FROM users_passengers WHERE user_id='" . $logged_user["id"] . "' AND ssn='" . $post["ssn"] . "' AND id!=$update"))){
			$error = readLanguage(passengers,already_added);
		} else {
			$record_data = getID($update, "users_passengers");

			//Calculate passenger age to determine his type
			$birth_date = getTimestamp($post["birth_date"]);
			$age = floor((time() - $birth_date) / YEAR_TIMESTAMP);
			$type = $age >= 18 ? 0 : ($age >= 2 ? 1 : 2);

			$query = "UPDATE users_passengers SET
				type='$type',
				name_prefix='{$post["name_prefix"]}',
				first_name='{$post["first_name"]}',
				last_name='{$post["last_name"]}',
				image='" . imgUploadBase64($_POST["passenger_image_base64"], "uploads/users/", $record_data["image"]) . "',
				birth_date='$birth_date',
				nationality='{$post["nationality"]}',
				passport='" . $post["passport"] . "',
				ssn='{$post["ssn"]}',
				ssn_end='" . getTimestamp($post["ssn_end"]) . "',
				special_needs='{$post["special_needs"]}',
				special_meals='{$post["special_meals"]}',
				removed=0
			WHERE user_id={$logged_user["id"]} AND id=$update";
			mysqlQuery($query);
			
			$success = readLanguage(passengers,update_msg);
		}
	}
}

$country_result = mysqlQuery("SELECT code, phone_code, en_name, ar_name FROM system_database_countries ORDER BY phone_code ASC");
$country_array = mysqlFetchAll($country_result);

if ($success){ $message = "<div class='alert alert-success'>$success</div>"; }
if ($error){ $message = "<div class='alert alert-danger'>$error</div>"; }
?>

<script src="plugins/moment.min.js?v=<?=$system_settings["system_version"]?>"></script>
<script src="plugins/caleran.min.js?v=<?=$system_settings["system_version"]?>"></script>
<link href="plugins/caleran.min.css?v=<?=$system_settings["system_version"]?>" rel="stylesheet">

<?=$message?>

<div class=passengers_container>
    <? $user_passengers = mysqlQuery("SELECT * FROM users_passengers WHERE removed=0 AND user_id={$logged_user['id']} ORDER BY id DESC"); ?>
	
	<? if (!mysqlNum($user_passengers)){ ?>
	<div class=page_container>
		<div class=message>
			<i class="fas fa-users"></i>
			<b><?=readLanguage(passengers,no_passengers_added)?></b>
			<small><?=readLanguage(passengers,no_passengers_added_small)?></small>
		</div>
		<div class="align-center margin-bottom">
			<button class="btn btn-success btn-upload" onclick="insertPassenger()"><i class="fa fa-plus"></i> <?=readLanguage(passengers,add_passenger)?></button>
		</div>
	</div>
	
	<? } else { ?>
	<div class="flex-center margin-bottom">
		<div class="page_subtitle clear-margin flex-grow-1"><?=readLanguage(common,you_have)?> <b><?=mysqlNum($user_passengers)?></b> <?=readLanguage(passengers,passengers_reg)?></div>
		<button class="btn btn-success btn-sm" onclick="insertPassenger()"><i class="fa fa-plus"></i><?=readLanguage(passengers,add_passenger)?></button>
	</div>
    <? while ($user_passenger = mysqlFetch($user_passengers)){ ?>
    <div class="page_container margin-bottom margin-bottom-progressive" data-parent="<?=$user_passenger['id']?>">
        <div class=passenger_card>
            <img src="<?=($user_passenger["image"] ? "uploads/users/" . $user_passenger["image"] : "images/user.png")?>">
            <div class="d-flex align-items-start flex-grow-1 flex-wrap">
                <div class=flex-grow-1>
					<b><?=$data_passenger_names_prefix[$user_passenger["name_prefix"]] . " " . $user_passenger["first_name"] . " " . $user_passenger["last_name"]?></b>
					<br><small><?=$data_passenger_types[$user_passenger["type"]]?></small>
					<div class="d-flex align-items-center margin-top-5"><img src="images/countries/<?=$user_passenger["nationality"]?>.gif">&nbsp;&nbsp;<?=$user_passenger["ssn"]?></div>
				</div>&nbsp;&nbsp;
                <div class="d-flex margin-top-small">
                    <button class="btn btn-primary btn-sm" onclick="updatePassenger(this)" data-content='<?=json_encode($user_passenger, true)?>' data-id="<?=$user_passenger['id']?>"><i class="fa fa-edit"></i> <?=readLanguage(plugins,message_update)?></button>&nbsp;&nbsp;
                    <button class="btn btn-danger btn-sm" onclick="deletePassenger(this)" data-id="<?=$user_passenger['id']?>"><i class="fa fa-trash"></i> <?=readLanguage(plugins,message_delete)?></button>
                </div>
            </div>
        </div>
    </div>
    <? } ?>
	<? } ?>
</div>

<div id=passengerModal class="modal fade"><div class=modal-dialog><div class=modal-content>
	<div class=modal-header>
		<button type=button class=close data-dismiss=modal>&times;</button>
		<h4 class=modal-title><?=readLanguage(passengers,add_passenger)?></h4>
	</div>
	<div class=modal-body>
		<form method=post>
		<input type=hidden name=token value="<?=$token?>">
		<input type=hidden name=passenger_id>
		<div class="alert alert-warning"><?=readLanguage(passengers,passengers_data_alert)?></div>
		
		<table class=form_table>
		<!-- Passenger Picture -->
		<tr>
			<td colspan=4>
				<div class="flex-center margin-bottom-10">
					<label>
						<img class=profile_picture image-placeholder=passenger_image src="uploads/users/images/user.png">
						<input class=d-none type=file name=passenger_image id=passenger_image accept="image/*">
						<input type=hidden name=passenger_image_base64>
						<script>$(document).ready(function(){ bindCroppie("passenger_image") })</script>
					</label>
					&nbsp;&nbsp;&nbsp;&nbsp;
					<label>
						<img class=passport_picture image-placeholder=passport_image src="uploads/users/images/passport.png">
						<input class=d-none type=file name=passport_image id=passport_image accept="image/*">
						<input type=hidden name=passport>
						<script>
						//On passport file change
						$("[name=passport_image]").on("change", function(){
							parsePassport($(this));
						});
						bindImage("passport_image");
						</script>
					</label>
				</div>
			</td>
		</tr>

		<!-- First & Last Name -->
		<tr>
			<td>
				<div class=title><?=readLanguage(common,first_name)?>: <i class=requ></i></div>
				<div class=d-flex>
					<div class=input style="width:40%" data-icon="&#xf007;"><select name=name_prefix id=name_prefix><?=populateOptions($data_passenger_names_prefix)?></select></div>&nbsp;&nbsp;
					<div class=input style="width:60%" data-icon="&#xf007;"><input type=text name=first_name maxlength=255 placeholder="<?=readLanguage(common,first_name)?>" data-validation=alphanumeric data-validation-error-msg="<?=readLanguage(common,first_name_validate)?>"></div>
				</div>
			</td>
			<td>
				<div class=title><?=readLanguage(common,last_name)?>: <i class=requ></i></div>
				<div class=input data-icon="&#xf007;"><input type=text name=last_name maxlength=255 placeholder="<?=readLanguage(common,last_name)?>" data-validation=alphanumeric data-validation-error-msg="<?=readLanguage(common,last_name_validate)?>"></div>
			</td>
		</tr>

		<!-- Birth date & Nationality -->
		<tr>
			<td>
				<div class=title><?=readLanguage(passengers,birth_date)?>: <i class=requ></i></div>
				<div>
					<input class=caleran name=birth_date type=text>
					<script>
						$("[name='birth_date']").caleran({
							format: "D/M/YYYY",
							calendarCount: 1,
							locale: "ar",
							showHeader: false,
							showFooter: false,
							singleDate: true,
							autoCloseOnSelect: true,
							maxDate: moment(),
							DOBCalendar: true,
							hideOutOfRange: true
						});
					</script>
				</div>
			</td>
			<td>
				<div class=title><?=readLanguage(common,nationality_country)?>: <i class=requ></i></div>
				<div>
					<select name=nationality id=nationality>
						<? foreach ($country_array as $country) {?>
							<option value="<?=$country["code"]?>" name="<?=$country[$website_language . "_name"]?>" data-phone-code="+<?=$country["phone_code"]?>">
								<?=$country[$website_language . "_name"]?>
							</option>
						<? }?>
					</select>
					<script>
						$("#nationality").select2({
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

		<!-- Passport Information -->
		<tr>
			<td>
				<div class=title><?=readLanguage(passengers,passport_number)?>: <i class=requ></i></div>
				<div class=input data-icon="&#xf2c2;"><input type=text name=ssn placeholder="<?=readLanguage(passengers,passport_number)?>" data-validation=required></div>
			</td>
			<td>
				<div class=title><?=readLanguage(common,end_date)?>: <i class=requ></i></div>
				<div>
					<input class=caleran name=ssn_end type=text>
					<script>
						$("[name='ssn_end']").caleran({
							format: "D/M/YYYY",
							calendarCount: 1,
							locale: "ar",
							showHeader: false,
							showFooter: false,
							singleDate: true,
							autoCloseOnSelect: true
						});
					</script>
				</div>
			</td>
		</tr>

		<!-- Specials -->
		<tr>
			<td colspan=4>
				<div class="panel panel-default">
				<div class=panel-heading><a data-toggle=collapse href="#collapse"><?=readLanguage(passengers,special_requests)?></a><i class="fa fa-caret-down"></i></div>
				<div id="collapse" class="panel-collapse collapse">
				<div class="panel-body">
					<table class=form_table>
						<tr>
						<td>
							<div class=title><?=readLanguage(passengers,special_needs)?>:</div>
							<select name=special_needs id=special_needs><option value="0"><?=readLanguage(common,undefined)?></option><?=populateOptions($data_special_needs)?></select>
						</td>
						<td>
							<div class=title><?=readLanguage(passengers,special_meals)?>:</div>
							<select name=special_meals id=special_meals><option value="0"><?=readLanguage(common,undefined)?></option><?=populateOptions($data_special_meals)?></select>
						</td>
						</tr>
					</table>
				</div>
				</div>
				</div>
			</td>
		</tr>
		</table>
		
		<div class="submit_container">
			<button type=button class=submit><?=readLanguage(common,add)?></button>
		</div>
		</form>
	</div>
</div></div></div>

<script>
//Delete passenger
function deletePassenger(element){
    let passengerId = $(element).attr("data-id");
    $.confirm({
        title: readLanguage.plugins.message_delete,
        content: readLanguage.plugins.data_delete,
        icon: "fas fa-trash",
        buttons: {
            yes: {
                text: readLanguage.plugins.message_yes,
                btnClass: "btn-red",
                action: function() {
                    postForm({
						remove: passengerId
					});
                }
            },
            cancel: {
                text: readLanguage.plugins.message_cancel
            }
        }
    });
}

//Insert passenger
function insertPassenger(){
    $("#passengerModal").find(".modal-title").text(readLanguage.passengers.add_passenger);
    $("#passengerModal").find("button.submit").text(readLanguage.common.add);
	$("#passengerModal").find("input[name='passenger_id']").val("");
    $("#passengerModal").find("img[class='profile_picture']").attr("src", `images/user.png`);
	$("#passengerModal").find("img[class='passport_picture']").attr("src", `images/passport.png`);
    $("#passengerModal").find("select").each((i, el) => $(el).find("option:selected").prop("selected", false).trigger("change"));
    $("#passengerModal").find("input").each((i, el) => {
        if ($(el).data("caleran")) $(el).val(moment().format("D/M/YYYY"));
        else if ($(el).attr("type") !== "hidden") $(el).val("");
    });
    $("#passengerModal").modal('toggle');
}


//Update passenger
function updatePassenger(element){
    let passengerData = JSON.parse($(element).attr("data-content"));
    $("#passengerModal").find(".modal-title").text(readLanguage.passengers.update_passenger);
    $("#passengerModal").find("button.submit").text(readLanguage.plugins.message_update);
    $("#passengerModal").find("input[name='passenger_id']").val($(element).attr("data-id"));
	$("#passengerModal").find("img[class='profile_picture']").attr("src", (passengerData.image ? `uploads/users/${passengerData.image}` : `images/user.png`));
	$("#passengerModal").find("img[class='passport_picture']").attr("src", (passengerData.passport ? `uploads/passports/${passengerData.passport}` : `images/passport.png`));
    $("#passengerModal").find("select").each((i, el) => passengerData[$(el).attr("id")] ? $(el).val(passengerData[$(el).attr("id")]).trigger("change") : void(0));
    $("#passengerModal").find("input").each((i, el) => {
        if (passengerData[$(el).attr("name")]){
            if ($(el).data("caleran")) $(el).val(moment.unix(passengerData[$(el).attr("name")]).format("D/M/YYYY"));
            else if ($(el).attr("type") !== "hidden") $(el).val(passengerData[$(el).attr("name")]);
        }
    });
    $("#passengerModal").modal('toggle');
}
</script>