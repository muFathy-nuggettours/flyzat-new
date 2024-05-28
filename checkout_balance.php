<? include "system/_handler.php";

requireLogin(true);

//Validate Balance Session
$balance_session = mysqlFetch(mysqlQuery("SELECT * FROM users_balance_requests WHERE session='" . $get["session"] . "'"));
if (!$balance_session){
	brokenLink();
}

//========================================
//Validate payment
//========================================

switch ($balance_session["payment_method"]){
	//Banque Misr EGP
	case 1:
		$valid = false;
	break;
	
	//Banque Misr USD
	case 2:
		$valid = false;
	break;
	
	//Hyperpay Visa
	case 3:
		$validation = validateHyperPay($get["id"], "visa");
		$valid = $validation[0];
		$payment_error = $validation[1];
		$transaction = $validation[2];
	break;
	
	//Hyperpay Mada
	case 4:
		$validation = validateHyperPay($get["id"], "mada");
		$valid = $validation[0];
		$payment_error = $validation[1];
		$transaction = $validation[2];
	break;
	
	//Vodafone cash
	case 5:
		$valid = false;
	break;
	
	//Cash
	case 6:
		$valid = false;
	break;
}

//========================================
//Update validation and add to balance
//========================================

if ($valid){
	$payment_record_id = newRecordID("payment_records");
	$query = "INSERT INTO payment_records (
		user_id,
		method,
		amount,
		currency,
		transaction,
		date
	) VALUES (
		'" . $balance_session["user_id"] . "',
		'" . $balance_session["payment_method"] . "',
		'" . $balance_session["amount"] . "',
		'" . $user_currencyCode . "',
		'" . escapeJson($transaction) . "',
		'" . time() . "'
	)";
	mysqlQuery($query);	
	
	$query = "INSERT INTO users_balance (
		payment_record_id,
		user_id,
		title,
		amount,
		currency,
		date
	) VALUES (
		'" . $payment_record_id . "',
		'" . $balance_session["user_id"] . "',
		'" . readLanguage(payment,recharging) . "',
		'" . $balance_session["amount"] . "',
		'" . $user_currencyCode . "',
		'" . time() . "'
	)";
	mysqlQuery($query);

	//Delete request
	mysqlQuery("DELETE FROM users_balance_requests WHERE id='" . $balance_session["id"] . "'");
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

include "system/header.php";
include "website/section_header.php"; ?>

<? if (!$valid){ ?>
<div class="alert alert-danger"><?=readLanguage(payment,payment_failed)?></div>
<div class=page_container>
	<div class=message>
		<i class="fas fa-times-circle"></i>
		<b><?=readLanguage(payment,recharge_payment_failed)?></b>
		<? if ($payment_error){ ?><small><?=$payment_error?></small><? } ?>
		<a class="btn btn-primary btn-upload margin-top-20" href="user/balance-charge/?session=<?=$balance_session["session"]?>"><?=readLanguage(mobile,retry)?></a>
	</div>
</div>

<? } else { ?>
<div class="alert alert-success"><?=readLanguage(payment,payment_success)?></div>
<div class=page_container>
	<div class=message>
		<i class="fas fa-check-circle"></i>
		<b><?=readLanguage(payment,add_balance_success)?></b>
		<small><?=readLanguage(payment,payment_success)?></small>
	</div>
</div>

<? } ?>

<? include "website/section_footer.php";
include "system/footer.php"; ?>