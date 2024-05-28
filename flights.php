<? include "system/_handler.php";

setCurrentPageRedirect();

//Validation
$from = mysqlFetch(mysqlQuery("SELECT * FROM system_database_airports WHERE iata='" . $get["from"] . "'"));
$to = mysqlFetch(mysqlQuery("SELECT * FROM system_database_airports WHERE iata='" . $get["to"] . "'"));

//Rebuild incorrect search parameters to search object
$search["type"] = ($get["type"] && $data_flight_types[$get["type"]] ? $get["type"] : 1);
$search["from"] = $get["from"];
$search["to"] = $get["to"];
$departure_timestamp = getTimestamp($get["departure"], "j-n-Y");
if (!$departure_timestamp || $departure_timestamp < time()){
	$search["departure"] = date("j-n-Y", time());
	$departure_timestamp = getTimestamp($search["departure"], "j-n-Y");	
} else {
	$search["departure"] = $get["departure"];
}
if ($search["type"]==2){
	$arrival_timestamp = getTimestamp($get["arrival"], "j-n-Y");
	if (!$arrival_timestamp || $arrival_timestamp < time()){
		$search["arrival"] = date("j-n-Y", time() + 172800);
		$arrival_timestamp = getTimestamp($search["arrival"], "j-n-Y");	
	} else {
		$search["arrival"] = $get["arrival"];
	}
}
$search["class"] = ($get["class"] && $data_flight_classes[$get["class"]] ? $get["class"] : 1);
$search["adults"] = ($get["adults"] && is_numeric($get["adults"]) ? $get["adults"] : 1);
if ($get["children"] && is_numeric($get["children"])){
	$search["children"] = $get["children"];
}
if ($get["toddlers"] && is_numeric($get["toddlers"])){
	$search["toddlers"] = $get["toddlers"];
}
if ($get["nonstop"]){
	$search["nonstop"] = true;
}
if ($get["flexible"]){
	$search["flexible"] = true;
}
if ($get["trips"]){
	$trips = array();
	for ($x = 1; $x <= $get["trips"]; $x++){
		array_push($trips, array(
			"from" => $get["trip{$x}from"],
			"to" => $get["trip{$x}to"],
			"departure" => $get["trip{$x}departure"],
		));
	}
	$search["trips"] = $trips;
}
if ($get["carrier"] && getData("system_database_airlines", "iata", $get["carrier"], "id")){
	$search["carrier"] = $get["carrier"];
}

//Check validity
$valid = ($from && $to && $from["iata"]!=$to["iata"] && (!$arrival_timestamp || $arrival_timestamp > $departure_timestamp));

//Save to search history if valid
if ($valid && !strpos(json_encode($search_history), json_encode($search))){
	array_unshift($search_history, $search);
	if (count($search_history) > 4){
		array_pop($search_history);
	}
	writeCookie($search_cookie, base64_encode(json_encode($search_history)), time() + (86400 * 30), "/");
}

//Set section title
$section_title = $from[$suffix . "short_name"] . " - " . $to[$suffix . "short_name"];
$section_description = $section_information["description"];

include "system/header.php";  ?>

<? if ($valid){ ?>
<!-- Flight Summary -->
<div class=flights_header style="background-image:url('<?=$header_image?>')"><div class=overlay><div class=container>
<h1><?=$section_title?></h1>
<div class=page_container>
	<div class=bar>
		<div>
			<i class="icon fal fa-plane"></i>&nbsp;&nbsp;&nbsp;
			<div class="align-items-center align-center">
				<b><?=$from["iata"]?></b>
				<p><?=$from[$suffix . "name"]?></p>
				<small><?=getData("system_database_countries", "code", $from["country"], $suffix . "name")?></small>
			</div>
			<i class="exchange <?=($search["type"]==2 ? "fal fa-exchange" : "fal fa-long-arrow-left")?>"></i>
			<div class="align-items-center align-center">
				<b><?=$to["iata"]?></b>
				<p><?=$to[$suffix . "name"]?></p>
				<small><?=getData("system_database_countries", "code", $to["country"], $suffix . "name")?></small>
			</div>
		</div>
		<div>
			<i class="icon fal fa-plane-departure"></i>&nbsp;&nbsp;&nbsp;
			<div>
				<small><?=readLanguage(reservation,departure_time)?></small>
				<b><?=dateLanguage("l, d M Y", $departure_timestamp)?></b>
			</div>
		</div>
		<? if ($get["type"]==2){ ?>
		<div>
			<i class="icon fal fa-plane-arrival"></i>&nbsp;&nbsp;&nbsp;
			<div>
				<small><?=readLanguage(reservation,"return")?></small>
				<b><?=dateLanguage("l, d M Y", $arrival_timestamp)?></b>
			</div>
		</div>
		<? } ?>
		<? if ($get["type"]==3){ ?>
		<div>
			<i class="icon fal fa-sync"></i>&nbsp;&nbsp;&nbsp;
			<div>
				<b><?=readLanguage(reservation,several_distinations)?></b>
			</div>
		</div>
		<? } ?>
		<div>
			<i class="icon fal fa-chair-office"></i>&nbsp;&nbsp;&nbsp;
			<div>
				<small><?=readLanguage(reservation,"class")?></small>
				<b><?=$data_flight_classes[$search["class"]]?></b>
			</div>
		</div>
		<div>
			<i class="icon fal fa-users"></i>&nbsp;&nbsp;&nbsp;
			<div>
				<small><?=readLanguage(common,passengers)?></small>
				<b><?=($search["adults"] + $search["children"] + $search["toddlers"])?></b>
			</div>
		</div>
		<a class="btn btn-primary btn-sm flex-center collapsed" data-toggle=collapse onclick="$(this).toggleClass('open')" href="#flight_search"><?=readLanguage(filter,update_search)?>&nbsp;&nbsp;<i class="fas fa-chevron-down"></i></a>
	</div>
	<div id=flight_search class="panel-collapse collapse">
		<div><? include "modules/search.php"; ?></div>
	</div>
</div>
</div></div></div>

<!-- Flight Template -->
<label class="flight template">
	<div class=airline>
		<div class=selection><input type=radio><span></span></div>
		<img data-target=flight_airline_logo src="uploads/airlines/00.png" onerror="$(this).attr('src', 'uploads/airlines/00.png')">&nbsp;&nbsp;&nbsp;
		<div>
			<div data-target=flight_airline><?=readLanguage(filter,airline)?></div>
			<small data-target=flight_airline_trip><?=readLanguage(reservation,flight_number)?></small>
			<div class=stars data-target=rating></div>
		</div>
	</div>&nbsp;&nbsp;&nbsp;
	<div class=path_container>
		<div class=time><b data-target=from_time><?=readLanguage(reservation,departure_time)?></b><span data-target=from_airport><?=readLanguage(reservation,departure_airport_code)?></span></div>
		<div class=path>
			<div class=stops>
				<span data-target=stops_status><?=readLanguage(filter,stops)?></span>
				<div data-target=stops_wait><?=readLanguage(reservation,waiting_time)?></div>
			</div>
			<div class=line></div>
			<div class=duration_luggage>
				<!--<div><i class="far fa-clock"></i>&nbsp;&nbsp;<span data-target=flight_duration><?=readLanguage(filter,flight_duration)?></span></div>-->
				<div><span class=icons><i class="far fa-suitcase-rolling"></i>&nbsp;&nbsp;<i class="fas fa-times-circle"></i><i class="fas fa-check-circle"></i></span>&nbsp;&nbsp;<span data-target=flight_luggage><?=readLanguage(reservation,luggage_inclusion)?></span></div>
			</div>
		</div>
		<div class=time><b data-target=to_time><?=readLanguage(reservation,arrival_time)?></b><span data-target=to_airport><?=readLanguage(reservation,arrival_airport_code)?></span></div>
	</div>
	<button data-target=flight_details class="btn btn-default btn-sm color"><?=readLanguage(common,details)?></button>
</label>

<!-- Trip Template -->
<div class="trip template">
	<div class=trip_header>
		<div class=description>
			<i class="fad fa-globe-africa"></i>&nbsp;&nbsp;
			<b data-target=title><?=readLanguage(reservation,departure_flight)?></b>
		</div>
		<div class=journey>
			<div data-target=trip_date><?=readLanguage(reservation,trip_date)?></div>&nbsp;&nbsp;&nbsp;&nbsp;
			<div><b data-target=trip_from_city><?=readLanguage(reservation,trip_from_city)?></b><small data-target=trip_from_airport><?=readLanguage(reservation,departure_airport)?></small></div>&nbsp;&nbsp;&nbsp;&nbsp;
			<div><i class="fas fa-plane"></i></div>&nbsp;&nbsp;&nbsp;&nbsp;
			<div><b data-target=arrival_city><?=readLanguage(reservation,arrival_city)?> </b><small data-target=trip_to_airport><?=readLanguage(reservation,arrival_airport)?></small></div>
		</div>
	</div>
	<div class=trip_flights></div>
</div>

<!-- Result Template -->
<div class="page_container flight_search_result template">
	<div class=trips></div>
	<div class=price>
		<div>
			<b data-target=result_price><?=readLanguage(common,price)?></b>
			<span data-target=result_currency><?=readLanguage(currencies,currency)?></span>
			<small><?=readLanguage(common,for_number)?> <b data-target=result_passengers><?=readLanguage(common,number)?></b> <?=readLanguage(common,passengers2)?></small>
		</div>
		<button onclick="bookNow(this)" class="btn btn-primary btn-sm"><?=readLanguage(booking,book_now)?></button>
	</div>
</div>

<!-- Flight Details Modal -->
<div class="modal fade modal_flight_details"><div class=modal-dialog><div class=modal-content>
	<div class=modal-header>
		<button type=button class=close data-dismiss=modal><span>&times;</span></button>
		<h4 class=modal-title><?=readLanguage(booking,flight_details)?></h4>
	</div>
	<div class=modal-body></div>
</div></div></div>

<div class="container inner">

<!-- Search loading -->
<div class="page_container search_loading">
	<div class=grapic>
		<svg class="svg-calLoader" xmlns="http://www.w3.org/2000/svg" width="230" height="230"><path class="cal-loader__path" d="M86.429 40c63.616-20.04 101.511 25.08 107.265 61.93 6.487 41.54-18.593 76.99-50.6 87.643-59.46 19.791-101.262-23.577-107.142-62.616C29.398 83.441 59.945 48.343 86.43 40z" fill="none" stroke="#0099cc" stroke-width="4" stroke-linecap="round" stroke-linejoin="round" stroke-dasharray="10 10 10 10 10 10 10 432" stroke-dashoffset="77"/><path class="cal-loader__plane" d="M141.493 37.93c-1.087-.927-2.942-2.002-4.32-2.501-2.259-.824-3.252-.955-9.293-1.172-4.017-.146-5.197-.23-5.47-.37-.766-.407-1.526-1.448-7.114-9.773-4.8-7.145-5.344-7.914-6.327-8.976-1.214-1.306-1.396-1.378-3.79-1.473-1.036-.04-2-.043-2.153-.002-.353.1-.87.586-1 .952-.139.399-.076.71.431 2.22.241.72 1.029 3.386 1.742 5.918 1.644 5.844 2.378 8.343 2.863 9.705.206.601.33 1.1.275 1.125-.24.097-10.56 1.066-11.014 1.032a3.532 3.532 0 0 1-1.002-.276l-.487-.246-2.044-2.613c-2.234-2.87-2.228-2.864-3.35-3.309-.717-.287-2.82-.386-3.276-.163-.457.237-.727.644-.737 1.152-.018.39.167.805 1.916 4.373 1.06 2.166 1.964 4.083 1.998 4.27.04.179.004.521-.076.75-.093.228-1.109 2.064-2.269 4.088-1.921 3.34-2.11 3.711-2.123 4.107-.008.25.061.557.168.725.328.512.72.644 1.966.676 1.32.029 2.352-.236 3.05-.762.222-.171 1.275-1.313 2.412-2.611 1.918-2.185 2.048-2.32 2.45-2.505.241-.111.601-.232.82-.271.267-.058 2.213.201 5.912.8 3.036.48 5.525.894 5.518.914 0 .026-.121.306-.27.638-.54 1.198-1.515 3.842-3.35 9.021-1.029 2.913-2.107 5.897-2.4 6.62-.703 1.748-.725 1.833-.594 2.286.137.46.45.833.872 1.012.41.177 3.823.24 4.37.085.852-.25 1.44-.688 2.312-1.724 1.166-1.39 3.169-3.948 6.771-8.661 5.8-7.583 6.561-8.49 7.387-8.702.233-.065 2.828-.056 5.784.011 5.827.138 6.64.09 8.62-.5 2.24-.67 4.035-1.65 5.517-3.016 1.136-1.054 1.135-1.014.207-1.962-.357-.38-.767-.777-.902-.893z" class="cal-loader__plane" fill="#000033"/></svg>
	</div>
	<b><?=readLanguage(mobile,loading)?></b>
	<span><?=readLanguage(reservation,search_loading_small)?></span>
</div>

<!-- Search empty -->
<div class="page_container search_empty"  style="display:none">
	<div class=message>
		<i class="fal fa-plane-alt"></i>
		<b><?=readLanguage(reservation,no_search_results)?></b>
		<small><?=readLanguage(reservation,try_search_again)?></small>
	</div>
</div>

<!-- Warnings -->
<div id=warnings></div>

<!-- Search results -->
<div class="row grid-container-15 search_results_container" style="display:none">
	
	<!-- Filters -->
	<div class="col-md-5 col-sm-20 grid-item">
		<? include "_inl_filter.php"; ?>
	</div>

	<!-- Search results -->
	<div class="col-md-15 col-sm-20 grid-item">
		<div class=search_results></div>
	</div>
</div>

<!-- Search errors -->
<div class=search_errors style="display:none"></div>

</div>

<script>
var results = 0;
var requests = [];
var platforms = ["custom", "travelport"];
var warnings = [];

platforms.forEach(function(item, index){
	var ajax = $.ajax({
		type: "POST",
		url: "requests/",
		data: {
			token: "<?=$token?>",
			action: item + "_search",
			parameters: <?=json_encode($search)?>
		}
	}).done(function(response){
		try {
			//Insert results
			var json = JSON.parse(response);
			json.forEach(function(item, index){
				insertResult(item);
			});

			//Render warnings
			warnings.forEach(function(item, index){
				if (!$("#warnings").find("[warning='" + item + "']").length){
					$.ajax({
						type: "POST",
						url: "requests/",
						data: {
							token: "<?=$token?>",
							action: "render_warning",
							origin_destination: item
						}
					}).done(function(response){
						if (response){
							$("#warnings").append("<div class='page_container margin-bottom-30' warning=" + item + ">" + response + "</div>");
						}
					});
				}
			});
		} catch (e){
			//If index exception for custom flights
			if (index){
				$(".search_errors").append("<div class='alert alert-danger'><b>Platform [" + index + "] Error</b><br>Failed to parse results " + e.message + "</div>");
			}
		}
	}).fail(function(response){
		//If index exception for custom flights
		if (index){
			$(".search_errors").append("<div class='alert alert-danger'><b>Platform [" + index + "] Error</b><br>" + response.responseText + "</div>");
		}
	});
	requests.push(ajax);
});

//When all results are done
$.when.apply(this, requests).always(function(){
	$(".search_errors").show();
	results = $(".flight_search_result:not(.template)").length;
	$(".search_loading").hide();
	if (!results){
		$(".search_empty").show();
	} else {
		initializeFilters();
		mixer.sort("price:asc");
		$(".search_results_container").show();
	}
});

//Insert result itinerary
function insertResult(itinerary){
	var suffix = "<?=$website_language?>";
	var result_template = $(".flight_search_result.template");
	var trip_template = $(".trip.template");
	var flight_template = $(".flight.template");
	var result_dom = result_template.clone().removeClass("template").data("itinerary", itinerary);
	var filter_object = {
		price: 0,
		date: 0,
		duration: 0,
		takeoff: 0,
		stops: [],
		airlines: [],
	};
	
	//Loop through trips
	itinerary.trips.forEach(function(trip, trip_index){
		var trip_dom = trip_template.clone().removeClass("template");

		//Loop through trip flights
		trip.flights.forEach(function(flight_data, flight_index){
			var flight_dom = flight_template.clone().removeClass("template");
			var flights = flight_data.flights;
			var booking = flight_data.booking;
			var first_flight = flights[0];
			var last_flight = flights[flights.length - 1];

			//Handle flight dom inputs
			flight_dom.find("input[type=radio]").val(booking).data("flight-index", flight_index);
			flight_dom.find("[data-target=flight_airline_logo]").attr("src", "uploads/airlines/" + first_flight.airline[`iata`] + ".png");
			flight_dom.find("[data-target=flight_airline]").text(first_flight.airline[`${suffix}_name`]);
			flight_dom.find("[data-target=flight_airline_trip]").text(first_flight.airline[`iata`] + "-" + first_flight.trip);
			if (first_flight.rating){	
				flight_dom.find("[data-target=rating]").html(ratingStars(first_flight.rating["value"]));
			}
			flight_dom.find("[data-target=from_time]").text(dateUTC("h:i A", first_flight.takeoff.time)).attr("data-toggle","tooltip").attr("title", dateUTC("l, d F Y h:i A", first_flight.takeoff.time)).tooltip();
			flight_dom.find("[data-target=from_airport]").text(first_flight.from.iata);
			flight_dom.find("[data-target=to_time]").text(dateUTC("h:i A", last_flight.landing.time)).attr("data-toggle","tooltip").attr("title", dateUTC("l, d F Y h:i A", last_flight.landing.time)).tooltip();
			flight_dom.find("[data-target=to_airport]").text(last_flight.to.iata);
			//flight_dom.find("[data-target=flight_duration]").text(getDuration(last_flight.landing.time - first_flight.takeoff.time));
			var stops = (flights.length - 1);
			if (!stops){
				flight_dom.find("[data-target=stops_status]").text("<?=readLanguage(reservation,stops_direct)?>").addClass("direct");
				flight_dom.find("[data-target=stops_wait]").hide();
			} else {
				var stops_array = [];
				for (let i = 1; i <= flights.length - 1; i++){
					stops_array.push("<b>"+ readLanguage.reservation.stop + " " + i + "</b> " + flights[i].from.iata + " - " + flights[i].from[`${suffix}_short_name`]);
				}
				flight_dom.find("[data-target=stops_status]").text(stops + " " + readLanguage.reservation.stops).attr("title", stops_array.join("<br>")).tooltip({html:true});
				flight_dom.find("[data-target=stops_wait]").text(readLanguage.reservation.waiting_time + " " + getDuration(last_flight.takeoff.time - first_flight.landing.time));
			}
			var luggage = [];
			if (first_flight.luggage.pieces){
				luggage.push(" <b>"+readLanguage.reservation.luggage_number + first_flight.luggage.pieces + "</b>");
			}
			if (first_flight.luggage.weight){
				luggage.push(" <b>"+ readLanguage.reservation.max_luggage_weight + first_flight.luggage.weight + (first_flight.luggage.unit ? " " + first_flight.luggage.unit : "") + "</b>");
			}
			if (luggage.length){
				flight_dom.find("[data-target=flight_luggage]").text(readLanguage.reservation.luggage_include).attr("title", luggage.join("<br>")).tooltip({html:true}).parent().addClass("included");
			} else {
				flight_dom.find("[data-target=flight_luggage]").text(readLanguage.reservation.no_luggage_include);
			}
			flight_dom.find("[data-target=flight_details]").on("click", function(){
				flightDetails(flights, trip.penalties);
			});
			
			//Warnings
			flights.forEach(function(item, index){
				var warning = item.from.country + "," + item.to.country;
				if (!warnings.includes(warning)){
					warnings.push(warning);
				}
			});
			
			//Filter values
			filter_object.duration = last_flight.takeoff.time - first_flight.landing.time;
			filter_object.takeoff = dateUTC("G", first_flight.takeoff.time);
			filter_object.stops.push(stops);
			filter_object.airlines.push(first_flight.airline[`iata`]);
			
			//Append flight dom to trip dom
			flight_dom.appendTo($(trip_dom).find(".trip_flights"));
		});
		
		//Handle trip dom inputs
		trip_dom.data("trip-index", trip_index);
		trip_dom.find("input[type=radio]").attr("name", $(".flight_search_result").length + trip.key);
		trip_dom.find("[data-target=title]").text((!$(result_dom).find(".trips .trip").length ? "<?=readLanguage(reservation,trip_departure)?>" : (<?=$get["type"]?>==2 ? "<?=readLanguage(reservation,trip_return)?>" : "<?=readLanguage(reservation,trip)?> " + (parseInt($(result_dom).find(".trips .trip").length) + 1))));
		trip_dom.find("[data-target=trip_date]").text(dateUTC("l, d F Y", trip.date));
		trip_dom.find("[data-target=trip_from_city]").text(trip.from.airport[`${suffix}_short_name`]);
		trip_dom.find("[data-target=trip_from_airport]").text(trip.from.airport[`${suffix}_name`]);
		trip_dom.find("[data-target=arrival_city]").text(trip.to.airport[`${suffix}_short_name`]);
		trip_dom.find("[data-target=trip_to_airport]").text(trip.to.airport[`${suffix}_name`]);
		
		//Hide radio if not needed
		if (trip_dom.find("input[type=radio]").length==1){
			trip_dom.find("input[type=radio]").prop("checked", true).parent().hide();
		}
		
		//Filter values
		filter_object.date = trip.date;
		
		//Append trip dom to search result
		trip_dom.appendTo($(result_dom).find(".trips"));
	});
	
	//Handle result dom inputs
	result_dom.find("[data-target=result_price]").text(numberFormat(itinerary.price));
	result_dom.find("[data-target=result_currency]").text(itinerary.currency);
	result_dom.find("[data-target=result_passengers]").text(itinerary.travelers);
	
	//Build filtering object
	filter_object.price = Math.round(itinerary.price);
	for (const [key, value] of Object.entries(filter_object)){
		var attribute_value = (Array.isArray(value) ? value.filter((v, i, a) => a.indexOf(v) === i).join(",") : value);
		result_dom.attr("data-" + key, attribute_value);
	}
	result_dom.addClass("mix");
	
	//Append result to search results
	result_dom.appendTo(".search_results");
}

//========== Booking ==========

function bookNow(target){
	var itinerary = $(target).parents(".flight_search_result");
	var search_object = itinerary.data("itinerary");
	var errors = [];
	var selections = [];
	
	itinerary.find(".trip").each(function(){
		var title = $(this).find("[data-target=title]").text();
		var radio = $(this).find("input[type=radio]:checked");
		var trip_index = $(this).data("trip-index");
		var flight_index = radio.data("flight-index");
		if (!radio.length){
			errors.push(readLanguage.filter.choose_choice + title);
		} else {
			selections.push($(radio).val());
			//Set only selected flight on flights object
			search_object.trips[trip_index].flights = search_object.trips[trip_index].flights[flight_index].flights;
		}
	});
	
	if (errors.length){
		quickNotify(errors.join("<br>"),readLanguage.filter.not_enough_trips , "danger", "fas fa-times fa-2x");
	} else {
		$.ajax({
			type: "POST",
			url: "requests/",
			data: {
				token: "<?=$token?>",
				action: "flight_request",
				search_object: JSON.stringify(search_object),
				selections: JSON.stringify(selections)
			}
		}).done(function(response){
			setWindowLocation("booking/" + response + "/");
		});
	}
}

//========== Flight Details ==========

function flightDetails(flight, penalties){
	var modal = $(".modal_flight_details");
	modal.find(".modal-body").html("<div class=inline_loading_container><div class=inline_loading><div></div><div></div><div></div><div></div></div>"+readLanguage.mobile.loading+"</div>");
	modal.modal("show");
	$.ajax({
		type: "POST",
		url: "requests/",
		data: {
			token: "<?=$token?>",
			action: "flight_details",
			flight: flight,
			penalties: penalties
		}
	}).done(function(response){
		modal.find(".modal-body").html(response);
	});
}
</script>

<? } ?>

<!-- Invalid Search -->
<? if (!$valid){ ?>
	<div class="container inner">
		<div class="inline_search margin-bottom-30">
			<? include "modules/search.php"; ?>
		</div>
		<div class=message>
			<i class="fal fa-plane-alt"></i>
			<b><?=readLanguage(reservation,no_search_results)?></b>
			<small><?=readLanguage(reservation,try_search_again)?></small>
		</div>
	</div>
<? } ?>

<? include "system/footer.php"; ?>