<? include "system/_handler.php";
$inline_page = true;

$reservation = getData("flights_reservations", "code", $get["id"]);
if (!$reservation){ brokenLink(); }

$trip = json_decode($reservation["search_object"], true);
$trips = $trip["trips"];

include "_header.php"; ?>

<ul class="nav nav-tabs tab-inline-header hide_pdf">
    <li class=active><a data-toggle=tab href="#info"><i class="fas fa-info"></i>&nbsp;&nbsp;<?=readlanguage(reservation,reservation_info)?></a></li>
	<? foreach(range(0, count($trips) - 1) as $index){
		$trip_label = readlanguage(common,flight). " (" . ($index + 1) . ") " . $trips[$index]["from"]["airport"]["ar_short_name"] . " - " . $trips[$index]["to"]["airport"]["ar_short_name"]; ?>
        <li><a data-toggle=tab href="#trip-<?=$index?>"><i class="fas fa-plane"></i>&nbsp;&nbsp;<?=$trip_label?></a></li>
    <? } ?>
</ul>

<div class=tab-content>

<div id="info" class="tab-pane fade in active">
	<table class=data_table>
	<tr>
		<td class=title><?=readlanguage(reservation,reservation_number)?></td>
		<td colspan=3><b style="font-size:14px"><?=naRes($reservation["pnr"])?></b></td>
	</tr>
	<tr>
		<td class=title><?=readlanguage(reservation,user_account)?></td>
		<td><?=$logged_user["name"]?></td>
		<td class=title><?=readlanguage(reservation,pasengers_number)?></td>
		<td class=valign-middle><?=count(explode(",", $reservation['passengers']))?></td>
	</tr>
	<tr>
		<td class=title><?=readlanguage(reservation,flight_type)?></td>
		<td>
			<? if (count($trips)==1){
				print readlanguage(reservation,going);
			} else if (count($trips)==2 && $trips[0]["from"]["airport"]["code"]==$trips[1]["to"]["airport"]["code"]){
				print readlanguage(reservation,going_comingback);
			} else {
				print readlanguage(reservation,several_distinations);
			} ?>
		</td>
		<td class=title><?=readlanguage(reservation,"class")?></td>
		<td><?=$data_flight_classes[$trip['platform']]?></td>
	</tr>
	<tr>
		<td class=title><?=readlanguage(common,price)?></td>
		<td><?=$trip['price']?></td>
		<td class=title><?=readlanguage(currencies,currency)?></td>
		<td><?=$trip['currency']?></td>
	</tr>
	<tr>
		<td class=title><?=readlanguage(common,notes)?></td>
		<td colspan=3><?=naRes($trip['notes'], nl2br($trip['notes']))?></td>
	</tr>
	</table>
	
	<div class="subtitle margin-top"><?=readlanguage(common,passengers)?></div>
	<table class=fancy>
		<tr>
			<th>#</th>
			<th><?=readlanguage(contact,name)?></th>
			<th><?=readlanguage(passengers,birth_date)?></th>
			<th><?=readlanguage(common,nationality_country)?></th>
			<th><?=readlanguage(passengers,passport_number)?></th>
			<th><?=readlanguage(common,end_date)?></th>
			<th><?=readlanguage(passengers,special_requests)?></th>
			<th><?=readlanguage(passengers,special_meals)?></th>
		</tr>	
		<? $passengers = explode(",", $reservation["passengers"]);
		foreach ($passengers AS $passenger_id){ $serial++;
			$passenger = getID($passenger_id, "users_passengers"); ?>
				<tr>
					<td class=center-large><?=$serial?></td>
					<td class=center-large><?=$data_passenger_names_prefix[$passenger["name_prefix"]] . " " . $passenger["first_name"] . " " . $passenger["last_name"]?></td>
					<td class=center-large><?=date("d/m/Y", $passenger["birth_date"])?> (<?=$data_passenger_types[$passenger["type"]]?>)</td>
					<td class=center-large><?=getData("system_database_countries", "code", $passenger["nationality"], "ar_name")?></td>
					<td class=center-large><?=($passenger["passport"] ? "<a href='uploads/passports/" . $passenger["passport"] . "' data-fancybox>" . $passenger["ssn"] . "</a>" : $passenger["ssn"])?></td>
					<td class=center-large><?=date("d/m/Y", $passenger["ssn_end"])?></td>
					<td class=center-large><?=naRes($data_special_needs[$passenger["special_needs"]])?></td>
					<td class=center-large><?=naRes($data_special_meals[$passenger["special_meals"]])?></td>
				</tr>
		<? } ?>
	</table>	
	
	<div class="subtitle margin-top"><?=readlanguage(reservation,price_details)?></div>
	<table class=fancy>
		<tr>
			<th><?=readlanguage(reservation,ticket)?></th>
			<th class=center-large><?=readlanguage(common,price)?></th>
			<th class=center-large><?=readlanguage(common,number)?></th>
			<th><?=readlanguage(common,amount)?></th>
		</tr>
		<? foreach ($trips[0]["pricing"] AS $type=>$pricing){ ?>
		<tr>
			<td class=center-large><?=getDictionary($type)?></td>
			<td class=center-large><?=number_format($pricing["units"] + $pricing["commission"], 2)?></td>
			<td class=center-large><?=$pricing["count"]?></td>
			<td class=center-large><b><?=number_format($pricing["total"], 2)?></b> <small><?=$itinerary["currency"]?></small></td>
		</tr>
		<? } ?>
	</table>
</div>

<!-- الرحلات -->
<? foreach($trips as $index => $trip){
$trip_label = readlanguage(common,flight)." (" . ($index + 1) . ") " . $trips[$index]["from"]["airport"]["ar_short_name"] . " - " . $trips[$index]["to"]["airport"]["ar_short_name"];	?>

<div id="trip-<?=$index?>" class="tab-pane fade in">
	<? foreach($trip["flights"] as $flight){ ?>
		<div class=subtitle><?=$flight["from"][$suffix . "short_name"]?> - <?=$flight["to"][$suffix . "short_name"]?></div>
		<table class=data_table>
		<tr>
			<td class=title><?=readlanguage(reservation,departure_airport)?></td>
			<td><?=naRes($flight["from"][$suffix . "name"])?> (<?=$flight["from"]["iata"]?>)</td>
			<td class=title><?=readlanguage(reservation,departure_country)?></td>
			<td><?=naRes($flight["from"]["country"], getData("system_database_countries", "code", $flight["from"]["country"], $suffix . "name"))?></td>
		</tr>
		<tr>
			<td class=title><?=readlanguage(reservation,landing_airport)?></td>
			<td><?=naRes($flight["to"][$suffix . "name"])?> (<?=$flight["to"]["iata"]?>)</td>
			<td class=title><?=readlanguage(reservation,landing_country)?></td>
			<td><?=naRes($flight["to"]["country"], getData("system_database_countries", "code", $flight["to"]["country"], $suffix . "name"))?></td>
		</tr>
		<tr>
			<td class=title><?=readlanguage(reservation,duration)?></td>
			<td><?=naRes($flight["duration"], getDuration($flight["duration"] * 60))?></td>
			<td class=title><?=readlanguage(reservation,distance)?></td>
			<td><?=naRes($flight["distance"], convertDistance($flight["distance"]))?></td>
		</tr>
		<tr>
			<td class=title><?=readlanguage(reservation,airline)?></td>
			<td><?=naRes($flight["airline"][$suffix . "name"])?></td>
			<td class=title><?=readlanguage(reservation,plane)?></td>
			<td><?=naRes($flight["equipment"][$suffix . "name"])?></td>
		</tr>
		<tr>
		<td class=title><?=readlanguage(reservation,flight_number)?></td>
			<td><?=$flight["airline"]["iata"]?>-<?=$flight["trip"]?></td>
			<td class=title><?=readlanguage(reservation,flight_class)?></td>
			<td><?=getDictionary($flight["cabin"])?></td>
		</tr>
		<tr>
			<td class=title><?=readlanguage(reservation,take_off_time)?></td>
			<td><?=naRes(dateLanguage('l, d M Y h:i A', $flight["takeoff"]["time"]))?></td>
			<td class=title><?=readlanguage(reservation,departure_station)?></td>
			<td><?=naRes($flight["takeoff"]["terminal"])?></td>
		</tr>
		<tr>
			<td class=title><?=readlanguage(reservation,landing_time)?></td>
			<td><?=naRes(dateLanguage('l, d M Y h:i A', $flight["landing"]["time"]))?></td>
			<td class=title><?=readlanguage(reservation,landing_station)?></td>
			<td><?=naRes($flight["landing"]["terminal"])?></td>
		</tr>
		<tr>
		<td class=title><?=readlanguage(reservation,luggage_allowance)?></td>
			<td><?=naRes($flight["luggage"]["pieces"])?></td>
			<td class=title><?=readlanguage(reservation,luggage_weight)?></td>
			<td><?=naRes($flight["luggage"]["weight"], $flight["luggage"]["weight"] . " " . $flight["luggage"]["unit"])?></td>
		</tr>
		</table>
	<? } ?>
	
	<div class=subtitle><?=readlanguage(reservation,canceling_change_policy)?></div>
	<table class=fancy>
	<thead>
		<th><?=readlanguage(common,passenger)?></th>
		<th><?=readlanguage(reservation,canceling_policy)?></th>
		<th><?=readlanguage(reservation,change_policy)?></th>
	</thead>
	<? foreach ($trip["penalties"] AS $key=>$value){ ?>
	<tr>
		<td class=center-large><?=getDictionary($key)?></td>
		<td class=center-large>
		<?
			$target = $value["cancel"];
			switch ($target["amount"]){
				case "100%": $amount = readlanguage(reservation,unchangable); break;
				case "0%": $amount = readlanguage(reservation,changable); break;
				default: $amount = naRes($target["amount"], $target["amount"]);
			}
			print $amount . ($target["applies"] ? " (" . getDictionary($target["applies"]) . ")" : "");
		?>
		</td>
		<td class=center-large>
		<?
			$target = $value["change"];
			switch ($target["amount"]){
				case "100%": $amount = readlanguage(reservation,unchangable); break;
				case "0%": $amount = readlanguage(reservation,changable); break;
				default: $amount = naRes($target["amount"], $target["amount"]);
			}
			print $amount . ($target["applies"] ? " (" . getDictionary($target["applies"]) . ")" : "");
		?>		
		</td>
	</tr>
	<? } ?>
	</table>
</div>
<? } ?>
	
</div>

<? include "_footer.php"; ?>