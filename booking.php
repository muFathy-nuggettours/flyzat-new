<? include "system/_handler.php";

//Validate Booking Session
$booking_session = mysqlFetch(mysqlQuery("SELECT * FROM flights_reqeusts WHERE session='" . $get["id"] . "'"));
if (!$booking_session) {
    brokenLink();
}
$itinerary = json_decode($booking_session["search_object"], true);

//If itinerary currency is not equal to user currency
if ($itinerary["currency"] != $user_paymentCurrency["code"]) {
    brokenLink();
}

//========================================
//Step [1] - Register and assign travelers
//========================================

if ($booking_session["status"] == 0 && $post["token"]) {
    //Create user account if not logged in
    if ($post['email'] && !$logged_user) {
        $new_record_id = newRecordID("users_database");
        $mobile_phone_code = getData("system_database_countries", "code", $post["country"], "phone_code");
        $mobile_prefix = "+" . $mobile_phone_code;
        $mobile = $mobile_prefix . cltrim($post["mobile"], "0");
        $mobile_conventional = "0" . cltrim($post["mobile"], "0");
        $email = strtolower($post["email"]);
        $user_id = generateUserID("users_database");
        $password = generateHash(8, 1, 1, 1);
        $hash = md5(uniqid($new_record_id, true));

        //Server side validation
        $errors = array();
        if (mysqlNum(mysqlQuery("SELECT * FROM users_database WHERE mobile='$mobile' OR mobile_conventional='$mobile_conventional'"))) {
            array_push($errors, readLanguage('accounts', 'mobile_registered') . " <a class=alert-link href='reset-password/'>" . readLanguage('accounts', 'reset_password') . "</a>");
        }
        if (mysqlNum(mysqlQuery("SELECT id FROM users_database WHERE email='$email'"))) {
            array_push($errors, readLanguage('accounts', 'email_registered') . " <a class=alert-link href='reset-password/'>" . readLanguage('accounts', 'reset_password') . "</a>");
        }
        $rules["name"] = array("required", "max_length(100)");
        $rules["email"] = array("required", "max_length(100)", "email");
        $validation_result = SimpleValidator\Validator::validate($post, $rules);

        if ($validation_result->isSuccess() == false) {
            $error = readLanguage('general', 'error');
        } else if ($errors) {
            $error = "<ul><li>" . implode("</li><li>", $errors) . "</li></ul>";
        } else {
            $query = "INSERT INTO users_database (
				user_id,
				user_country,
				user_currency,
				name,
				email,
				country,
				mobile_prefix,
				mobile,
				mobile_conventional,
				password,
				hash,
				date
			) VALUES (
				'" . $user_id . "',
				'" . $user_countryCode . "',
				'" . $user_paymentCurrency["code"] . "',
				'" . $post["name"] . "',
				'" . $email . "',
				'" . $post["country"] . "',
				'" . $mobile_prefix . "',
				'" . $mobile . "',
				'" . $mobile_conventional . "',
				'" . password_hash($password, PASSWORD_DEFAULT) . "',
				'" . $hash . "',
				'" . time() . "'
			)";
            mysqlQuery($query);

            userLogin($hash, true);
            $logged_user = mysqlFetch(mysqlQuery("SELECT * FROM users_database WHERE hash='$hash'"));
        }
    }

    //Insert passengers
    if (!$error) {
        $passengers = array();
        for ($i = 0; $i < intval($post['travelers']); $i++) {
            $exists = mysqlFetch(mysqlQuery("SELECT id FROM users_passengers WHERE user_id='" . $logged_user["id"] . "' AND ssn='" . $post["ssn-$i"] . "'"));
            if ($exists) {
                $query = "UPDATE users_passengers SET
					type='" . $post["type-$i"] . "',
					name_prefix='" . $post["name_prefix-$i"] . "',
					first_name='" . $post["first_name-$i"] . "',
					last_name='" . $post["last_name-$i"] . "',
					birth_date='" . getTimestamp($post["birth_date-$i"]) . "',
					nationality='" . $post["nationality-$i"] . "',
					passport='" . ($post["passport-$i"] ? $post["passport-$i"] : $exists["passport"]) . "',
					ssn='" . $post["ssn-$i"] . "',
					ssn_end='" . getTimestamp($post["ssn_end-$i"]) . "',
					special_needs='" . $post["special_needs-$i"] . "',
					special_meals='" . $post["special_meals-$i"] . "',
					removed=0
				WHERE id=" . $exists["id"];
                mysqlQuery($query);
                array_push($passengers, $exists["id"]);
            } else {
                $query = "INSERT INTO users_passengers (
					type,
					code,
					user_id,
					name_prefix,
					first_name,
					last_name,
					birth_date,
					nationality,
					passport,
					ssn,
					ssn_end,
					special_needs,
					special_meals
				) VALUES (
					'" . $post["type-$i"] . "',
					'" . base64_encode(uniqid($logged_user['user_id'], true)) . "',
					'" . $logged_user['id'] . "',
					'" . $post["name_prefix-$i"] . "',
					'" . $post["first_name-$i"] . "',
					'" . $post["last_name-$i"] . "',
					'" . getTimestamp($post["birth_date-$i"]) . "',
					'" . $post["nationality-$i"] . "',
					'" . $post["passport-$i"] . "',
					'" . $post["ssn-$i"] . "',
					'" . getTimestamp($post["ssn_end-$i"]) . "',
					'" . $post["special_needs-$i"] . "',
					'" . $post["special_meals-$i"] . "'
				)";
                mysqlQuery($query);
                array_push($passengers, newRecordID("users_passengers") - 1);
            }
        }

        //Update passengers & reload session
        mysqlQuery("UPDATE flights_reqeusts SET
			user_id='" . $logged_user["id"] . "',
			passengers='" . implode(",", $passengers) . "',
			notes='" . $post["notes"] . "',
			status=1
		WHERE id='" . $booking_session["id"] . "'");

        //Creat AirPrice Request
        travelportAirPrice($booking_session["id"]);

        $booking_session = mysqlFetch(mysqlQuery("SELECT * FROM flights_reqeusts WHERE id='" . $booking_session["id"] . "'"));
    }

    if ($error) {
        $message = "<div class='alert alert-danger'>$error</div>";
    }
}

//========================================
//Calculate subtraction from user balance
//========================================

$trip_currency = getData("system_payment_currencies", "code", $itinerary["currency"]);
$trip_price = $itinerary["price"];
if ($logged_user && $user_currencyCode == $trip_currency["code"]) {
    $user_balance = mysqlFetch(mysqlQuery("SELECT SUM(amount) AS total FROM users_balance WHERE currency='$user_currencyCode' AND user_id='" . $logged_user["id"] . "'"))["total"];
    if ($user_balance > 0) {
        $subtract_from_balance = min($user_balance, $trip_price);
    }
}
$trip_price = $trip_price - $subtract_from_balance;

//========================================

//Section Information
$section_information = mysqlFetch(mysqlQuery("SELECT * FROM " . $suffix . "website_pages WHERE page='" . basename($_SERVER["SCRIPT_FILENAME"], ".php") . "'"));
if ($section_information["hidden"]) {
    brokenLink();
}
$section_title = $section_information["title"];
$section_description = $section_information["description"];
$section_header_image = ($section_information["header_image"] ? "uploads/pages/" . $section_information["header_image"] : null);
$section_cover_image = ($section_information["cover_image"] ? "uploads/pages/" . $section_information["cover_image"] : null);
$section_header = $section_information["section_header"];
$section_footer = $section_information["module_footer"];
$section_layout = $section_information["layout"];

//Breadcrumbs
$breadcrumbs = array();
array_push($breadcrumbs, "<li><a href='.'>" . readLanguage('general', 'home') . "</a></li>");
array_push($breadcrumbs, "<li>" . $section_information["title"] . "</li>");

include "system/header.php";
include "website/section_header.php"; ?>

<?= $message ?>

<script src="plugins/moment.min.js?v=<?= $system_settings["system_version"] ?>"></script>
<script src="plugins/caleran.min.js?v=<?= $system_settings["system_version"] ?>"></script>
<link href="plugins/caleran.min.css?v=<?= $system_settings["system_version"] ?>" rel="stylesheet">

<!-- Flight Details Modal -->
<div class="modal fade modal_flight_details">
    <div class=modal-dialog>
        <div class=modal-content>
            <div class=modal-header>
                <button type=button class=close data-dismiss=modal><span>&times;</span></button>
                <h4 class=modal-title><?= readlanguage('booking', 'flight_details') ?></h4>
            </div>
            <div class=modal-body></div>
        </div>
    </div>
</div>

<!-- Flight Details Content -->
<? foreach ($itinerary["trips"] as $index => $trip) { ?>
    <div trip-index=<?= $index ?>><?= renderFlightDetails($trip["flights"], $trip["penalties"]) ?></div>
<? } ?>

<!-- Page Content -->
<div class="row grid-container-15">

    <!-- Passengers & booking -->
    <div class="col-md-14 grid-item">
        <?
        //Register passengers
        if ($booking_session["status"] == 0) {
            include "_inl_booking_passengers.php";

            //Payment
        } else if ($booking_session["status"] == 1) {
            $payment_redirect = $base_url . "checkout/" . $booking_session["session"] . "/";
            $payment_amount = $trip_price;
            $payment_currency = $itinerary["currency"];
            include "_inl_booking_payment.php";
        }
        ?>
    </div>

    <!-- Side Column -->
    <div class="col-md-6 grid-item">
        <div class=page_container>
            <div id="container" class="container justify-content-center row" style="border: 5px solid #2073ba; margin-bottom: 10px; padding: 10px; display: flex; align-items: center; justify-content: center;">
                <strong>متبقي:&nbsp;&nbsp;&nbsp;</strong>
                <strong id="display">05:00</strong>
            </div>

            <style>
                .red-border {
                    border-color: #a94442;
                    animation: blink 1s infinite;
                }

                @keyframes blink {

                    0%,
                    100% {
                        border-color: #a94442;
                    }

                    50% {
                        border-color: transparent;
                    }
                }
            </style>
            <div class=page_subtitle><?= readlanguage('booking', 'flight_summary') ?></div>
            <? foreach ($itinerary["trips"] as $index => $trip) { ?>
                <div class=trip_summary>
                    <i class="fas fa-plane"></i>&nbsp;&nbsp;&nbsp;&nbsp;
                    <div>
                        <b><?= $trip["from"]["airport"]["ar_short_name"] ?> - <?= $trip["to"]["airport"]["ar_short_name"] ?></b>
                        <span><?= dateLanguage("l, d M Y", $trip["date"]) ?></span>
                        <small><?= dateLanguage("H:i A", $trip["flights"][0]["takeoff"]["time"]) ?></small>
                    </div>
                    <a class="btn btn-default btn-sm" onclick="$('.modal_flight_details').find('.modal-body').html($('[trip-index=<?= $index ?>]').html()); $('.modal_flight_details').modal('show');"><?= readlanguage('common', 'details') ?></a>
                </div>
            <? } ?>

            <div class="page_subtitle margin-top-20"><?= readlanguage('reservation', 'price_details') ?></div>
            <table class=trip_price_summary>
                <tr>
                    <th>التذكرة</th>
                    <th class=center-large><?= readlanguage('common', 'price') ?></th>
                    <!--<th>الضرائب</th>-->
                    <!--<th>العمولة</th>-->
                    <th class=center-large><?= readlanguage('common', 'number') ?></th>
                    <th><?= readlanguage('common', 'amount') ?></th>
                </tr>
                <? foreach ($itinerary["trips"][0]["pricing"] as $type => $pricing) { ?>
                    <tr>
                        <td><?= getDictionary($type) ?></td>
                        <td class=center-large><?= number_format($pricing["units"] + $pricing["commission"], 2) ?></td>
                        <!--<td class=center-large><?= $pricing["taxes"] ?></td>-->
                        <!--<td class=center-large><?= $pricing["commission"] ?></td>-->
                        <td class=center-large><?= $pricing["count"] ?></td>
                        <td><b><?= number_format($pricing["total"], 2) ?></b> <small><?= $itinerary["currency"] ?></small></td>
                    </tr>
                <? } ?>
            </table>

            <? if ($subtract_from_balance) { ?>
                <div class="page_subtitle margin-top-20"><?= readLanguage('booking', 'subtract_from_balance') ?></div>
                <div class=blance_price><b><?= number_format($subtract_from_balance, 2) ?></b> <small><?= $trip_currency["ar_name"] ?></small></div>
                <span><?= readLanguage('booking', 'balance_after_subtract') ?> <b><?= number_format($user_balance - $subtract_from_balance, 2) ?></b> <small><?= $trip_currency["ar_name"] ?></small></span>
            <? } ?>

            <div class="page_subtitle margin-top-20"><?= readLanguage('common', 'total') ?></div>
            <div class=trip_price><b style="color:<?= ($trip_price ? "red" : "green") ?>"><?= number_format($trip_price, 2) ?></b> <small><?= $trip_currency["ar_name"] ?></small></div>
        </div>
    </div>
    <? if ($booking_session["status"] == 1) { ?>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
        <style>
            .share-container {
                text-align: center;
                padding: 20px;
                width: 100%;
            }

            .share-button {
                display: inline-block;
                margin: 10px;
                padding: 10px 20px;
                font-size: 16px;
                text-decoration: none;
                color: white !important;
                background-color: #25D366;
                border: none;
                border-radius: 5px;
                cursor: pointer;
                transition: background-color 0.3s;
            }

            .share-button:hover {
                background-color: #1DA851;
            }

            .copy-button {
                display: inline-block;
                margin: 10px;
                padding: 10px 20px;
                font-size: 16px;
                text-decoration: none;
                color: #fff;
                background-color: #007BFF;
                border: none;
                border-radius: 5px;
                cursor: pointer;
                transition: background-color 0.3s;
            }

            .copy-button:hover {
                background-color: #0056b3;
            }
        </style>
        <div class="share-container">
            <h4 class="text-strong">Share Payment Link</h4>
            <hr width="30%">
            <a href="whatsapp://send?text=<?php echo $base_url . $_SERVER['REQUEST_URI']; ?>" class="share-button text-white" target="_blank">
                <i class="fab fa-whatsapp"></i>
                Share on WhatsApp
            </a>
            <button class="copy-button" id="copy-button">
                <i class="fas fa-copy"></i> Copy Link
            </button>
        </div>

        <script>
            document.getElementById('copy-button').addEventListener('click', function() {
                var tempInput = document.createElement('input');
                tempInput.value = window.location.href;
                document.body.appendChild(tempInput);
                tempInput.select();
                document.execCommand('copy');
                document.body.removeChild(tempInput);
                quickNotify('', 'URL copied to clipboard!', "success", "fas fa-times fa-2x");
            });
        </script>
    <? } ?>
</div>

<? include "website/section_footer.php";
include "system/footer.php"; ?>
<script>
    let timer;
    let minutes = 19;
    let seconds = 5;
    let display = document.getElementById('display');
    let container = document.getElementById('container');

    function updateDisplay() {
        let min = minutes < 10 ? '0' + minutes : minutes;
        let sec = seconds < 10 ? '0' + seconds : seconds;
        display.textContent = min + ':' + sec;
    }

    timer = setInterval(function() {
        if (minutes == 1 && seconds == 0) {
            display.classList.add("text-danger");
            container.classList.add('red-border');
        }

        if (seconds === 0) {
            if (minutes === 0) {
                clearInterval(timer);
                display.textContent = "00:00";
                // Handle timer end (optional)
                quickNotify("You exceeded the time limit!", "", "danger", "fas fa-times fa-2x");
                setTimeout(() => {
                    window.history.back();
                }, 2000);
                return;
            } else {
                minutes--;
                seconds = 59;
            }
        } else {
            seconds--;
        }
        updateDisplay();
    }, 1000);
</script>