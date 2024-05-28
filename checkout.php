<? include "system/_handler.php";

requireLogin(true);

//Validate Booking Session
$target_session = mysqlFetch(mysqlQuery("SELECT *, 'request' AS type FROM flights_reqeusts WHERE session='" . $get["session"] . "' AND status=1"));
if (!$target_session){
	$target_session = mysqlFetch(mysqlQuery("SELECT *, 'reservation' AS type FROM flights_reservations WHERE session='" . $get["session"] . "' AND status IN (0,1)"));
	if (!$target_session){
		brokenLink();
	}
}
$itinerary = json_decode($target_session["search_object"], true);

//Proceed if is request
if ($target_session["type"]=="request"){
	//========================================
	//Calculate subtraction from user balance
	//========================================

	$trip_currency = getData("system_payment_currencies", "code", $itinerary["currency"]);
	$trip_price = $itinerary["price"];
	if ($logged_user && $user_currencyCode==$trip_currency["code"]){
		$user_balance = mysqlFetch(mysqlQuery("SELECT SUM(amount) AS total FROM users_balance WHERE currency='$user_currencyCode' AND user_id='" . $logged_user["id"] . "'"))["total"];
		if ($user_balance > 0){
			$subtract_from_balance = min($user_balance, $trip_price);
		}
	}
	$trip_price = $trip_price - $subtract_from_balance;

	//========================================
	//Validate payment
	//========================================

	switch ($target_session["payment_method"]){
		//Balance
		case 0:
			$valid = ($subtract_from_balance >= $trip_price);
			$payment_error = readLanguage(payment,balance_not_enough);
			$issue_pnr = $valid;
		break;
		
		//Banque Misr EGP
		case 1:
			$valid = false;
			$issue_pnr = $valid;
		break;
		
		//Banque Misr USD
		case 2:
			$valid = false;
			$issue_pnr = $valid;
		break;
		
		//Hyperpay Visa
		case 3:
			$validation = validateHyperPay($get["id"], "visa");
			$valid = $validation[0];
			$payment_error = $validation[1];
			$transaction = $validation[2];
			$issue_pnr = $valid;
		break;
		
		//Hyperpay Mada
		case 4:
			$validation = validateHyperPay($get["id"], "mada");
			$valid = $validation[0];
			$payment_error = $validation[1];
			$transaction = $validation[2];
			$issue_pnr = $valid;
		break;
		
		//Vodafone cash
		case 5:
			$issue_pnr = false;
			$valid = false;
		break;
		
		//Cash
		case 6:
			$issue_pnr = false;
			$valid = true;
		break;
	}

	//========================================
	//Update validation and subtract from balance
	//========================================

	if ($valid){
		//Get next reservation ID and code
		$reservation_id = newRecordID("flights_reservations");
		$reservation_code = generateUserID("flights_reservations");

		//Insert online payment record
		if ($trip_price > 0){
			$query = "INSERT INTO payment_records (
				user_id,
				reservation_id,
				method,
				amount,
				currency,
				transaction,
				date
			) VALUES (
				'" . $target_session["user_id"] . "',
				'" . $reservation_id . "',
				'" . $target_session["payment_method"] . "',
				'" . $trip_price . "',
				'" . $trip_currency["code"] . "',
				'" . escapeJson($transaction) . "',
				'" . time() . "'
			)";
			mysqlQuery($query);	
		}
		
		//Insert balance payment record
		if ($subtract_from_balance){
			$query = "INSERT INTO users_balance (
				user_id,
				reservation_id,
				title,
				amount,
				currency,
				date
			) VALUES (
				'" . $target_session["user_id"] . "',
				'" . $reservation_id . "',
				'#" . $reservation_code . "',
				'" . ($subtract_from_balance * -1) . "',
				'" . $trip_currency["code"] . "',
				'" . time() . "'
			)";
			mysqlQuery($query);
		}
		
		//Insert reservation
		$search_object = json_decode($target_session["search_object"], true);
		$query = "INSERT INTO flights_reservations (
			code,
			session,
			search_object,
			selections,
			selection_parameters,
			user_id,
			user_ip,
			passengers,
			notes,
			status,
			date,
			so_platform,
			so_trips,
			so_passengers,
			so_start,
			so_end,
			so_price,
			so_currency
		) VALUES (
			'" . $reservation_code . "',
			'" . $target_session["session"] . "',
			'" . $target_session["search_object"] . "',
			'" . $target_session["selections"] . "',
			'" . $target_session["selection_parameters"] . "',
			'" . $target_session["user_id"] . "',
			'" . $user_ip . "',
			'" . $target_session["passengers"] . "',
			'" . $target_session["notes"] . "',
			'" . ($issue_pnr ? 1 : 0) . "',
			'" . time() . "',
			'" . $search_object["platform"] . "',
			'" . count($search_object["trips"]) . "',
			'" . $search_object["travelers"] . "',
			'" . $search_object["trips"][0]["date"] . "',
			'" . $search_object["trips"][(count($search_object["trips"]) - 1)]["date"] . "',
			'" . $search_object["price"] . "',
			'" . $search_object["currency"] . "'
		)";
		mysqlQuery($query);
		
		//Delete booking request
		mysqlQuery("DELETE FROM flights_reqeusts WHERE id='" . $target_session["id"] . "'");
	}

//Otherwise proceed with PNR
} else {
	$valid = true;
	$reservation_id = $target_session["id"];
}

//Section Information
$section_information = mysqlFetch(mysqlQuery("SELECT * FROM " . $suffix . "website_pages WHERE page='" . basename($_SERVER["SCRIPT_FILENAME"], ".php") . "'"));
if ($section_information["hidden"]){ brokenLink(); }
$section_title = $section_information["title"];
$section_description = $section_information["description"];
$section_header_image = ($section_information["header_image"] ? "uploads/pages/" . $section_information["header_image"] : null);
$section_cover_image = ($section_information["cover_image"] ? "uploads/pages/" . $section_information["cover_image"] : null);
$section_header = $section_information["section_header"];
$section_footer = $section_information["module_footer"];
$section_layout = $section_information["layout"];

//Breadcrumbs
$breadcrumbs = array();
array_push($breadcrumbs,"<li><a href='.'>" . readLanguage(general,home) . "</a></li>");
array_push($breadcrumbs,"<li>" . $section_information["title"] . "</li>");
$issue_pnr = true;
include "system/header.php";
include "website/section_header.php"; ?>

<? if (!$valid){ ?>
<div class="alert alert-danger"><?=readLanguage(payment,payment_failed)?></div>
<div class=page_container>
	<div class=message>
		<i class="fas fa-times-circle"></i>
		<b><?=readLanguage(payment,reserve_payment_failed)?></b>
		<? if ($payment_error){ ?><small><?=$payment_error?></small><? } ?>
		<a class="btn btn-primary btn-upload margin-top-20" href="booking/<?=$target_session["session"]?>/"><?=readLanguage(mobile,retry)?></a>
	</div>
</div>

<? } else { ?>

<? if ($issue_pnr){ ?>
	<!-- Booking loading -->
	<div class="page_container search_loading">
		<div class=grapic>
			<svg class="svg-calLoader" xmlns="http://www.w3.org/2000/svg" width="230" height="230"><path class="cal-loader__path" d="M86.429 40c63.616-20.04 101.511 25.08 107.265 61.93 6.487 41.54-18.593 76.99-50.6 87.643-59.46 19.791-101.262-23.577-107.142-62.616C29.398 83.441 59.945 48.343 86.43 40z" fill="none" stroke="#0099cc" stroke-width="4" stroke-linecap="round" stroke-linejoin="round" stroke-dasharray="10 10 10 10 10 10 10 432" stroke-dashoffset="77"/><path class="cal-loader__plane" d="M141.493 37.93c-1.087-.927-2.942-2.002-4.32-2.501-2.259-.824-3.252-.955-9.293-1.172-4.017-.146-5.197-.23-5.47-.37-.766-.407-1.526-1.448-7.114-9.773-4.8-7.145-5.344-7.914-6.327-8.976-1.214-1.306-1.396-1.378-3.79-1.473-1.036-.04-2-.043-2.153-.002-.353.1-.87.586-1 .952-.139.399-.076.71.431 2.22.241.72 1.029 3.386 1.742 5.918 1.644 5.844 2.378 8.343 2.863 9.705.206.601.33 1.1.275 1.125-.24.097-10.56 1.066-11.014 1.032a3.532 3.532 0 0 1-1.002-.276l-.487-.246-2.044-2.613c-2.234-2.87-2.228-2.864-3.35-3.309-.717-.287-2.82-.386-3.276-.163-.457.237-.727.644-.737 1.152-.018.39.167.805 1.916 4.373 1.06 2.166 1.964 4.083 1.998 4.27.04.179.004.521-.076.75-.093.228-1.109 2.064-2.269 4.088-1.921 3.34-2.11 3.711-2.123 4.107-.008.25.061.557.168.725.328.512.72.644 1.966.676 1.32.029 2.352-.236 3.05-.762.222-.171 1.275-1.313 2.412-2.611 1.918-2.185 2.048-2.32 2.45-2.505.241-.111.601-.232.82-.271.267-.058 2.213.201 5.912.8 3.036.48 5.525.894 5.518.914 0 .026-.121.306-.27.638-.54 1.198-1.515 3.842-3.35 9.021-1.029 2.913-2.107 5.897-2.4 6.62-.703 1.748-.725 1.833-.594 2.286.137.46.45.833.872 1.012.41.177 3.823.24 4.37.085.852-.25 1.44-.688 2.312-1.724 1.166-1.39 3.169-3.948 6.771-8.661 5.8-7.583 6.561-8.49 7.387-8.702.233-.065 2.828-.056 5.784.011 5.827.138 6.64.09 8.62-.5 2.24-.67 4.035-1.65 5.517-3.016 1.136-1.054 1.135-1.014.207-1.962-.357-.38-.767-.777-.902-.893z" class="cal-loader__plane" fill="#000033"/></svg>
		</div>
		<b><?=readLanguage(reservation,reserve_loading)?>..</b>
		<span><?=readLanguage(reservation,reserve_loading_small)?>..</span>
	</div>
<? } else { ?>
	<!-- Payment request received -->
	<div class=page_container>
		<div class=message>
			<div class=success_icon></div>
			<b><?=readLanguage(reservation,request_received)?></b>
			<small><?=readLanguage(reservation,request_received_message)?></small>
		</div>
	</div>
<? } ?>

<!-- PNR Error -->
<div class="page_container pnr_error" style="display:none">
	<div class="alert alert-danger"></div>
	<div class=message>
		<i class="fal fa-times-circle"></i>
		<b><?=readLanguage(reservation,reserve_failed)?></b>
		<small><?=readLanguage(reservation,reserve_failed_small)?></small>
	</div>
</div>

<!-- PNR Success -->
<div class="page_container pnr_success" style="display:none">
	<div class=message>
		<i class="fal fa-check-circle"></i>
		<b><?=readLanguage(reservation,reserve_success)?></b>
		<small><?=readLanguage(reservation,reserve_success_small)?></small>
		<div class="pnr_number margin-top-20"></div>
	</div>
</div>

<? if ($issue_pnr){ ?>
<script>
//Create PNR
$.ajax({
	type: "POST",
	url: "requests/",
	data: {
		token: "<?=$token?>",
		action: "issue_pnr",
		reservation: "<?=$reservation_id?>"
	}
	
}).done(function(response){
	if (response){
		$(".pnr_success .pnr_number").text(response);
		$(".pnr_success").show();
	} else {
		$(".pnr_error .alert").text("Invalid PNR");
		$(".pnr_error").show();		
	}
	
}).fail(function(response){
	$(".pnr_error .alert").text(response.responseText);
	$(".pnr_error").show();
	
}).always(function(){
	$(".search_loading").hide();
});
</script>
<? } ?>

<? } ?>

<? include "website/section_footer.php";
include "system/footer.php"; ?>