<?
if ($entity=="mada"){
	$entity_id = $system_settings["hyperpay_entity_mada"];
	$brands = "MADA";
} else if ($entity=="visa"){
	$entity_id = $system_settings["hyperpay_entity_visa"];
	$brands = "VISA MASTER AMEX";	
}

$city = mysqlFetch(mysqlQuery("SELECT en_name FROM system_database_regions WHERE country='" . $logged_user["country"] . "' ORDER BY priority DESC LIMIT 0,1"))["en_name"];
$options = [
	"entityId" => $entity_id,
	"merchantTransactionId" => md5(uniqid() . rand(1000,9999)),
	"paymentType" => "DB",
	"currency" => "SAR",
	"amount" => round($payment_amount, 2),
	"customer.givenName" => $logged_user["name"],
	"customer.surname" => $logged_user["name"],
	"customer.email" => $logged_user["email"],
	"billing.street1" => getData("system_database_countries", "code", $logged_user["country"], "en_name"),
	"billing.country" => strtoupper($logged_user["country"]),
	"billing.city" => $city,
	"billing.state" => $city
];

//CURL Request
//https://eu-prod.oppwa.com/
//https://oppwa.com/v1/checkouts
//https://eu-prod.oppwa.com/v1/checkouts
$url = ($system_settings["hyperpay_live"] ? "https://eu-prod.oppwa.com/v1/checkouts" : "https://test.oppwa.com/v1/checkouts");
$curl = curl_init($url);
curl_setopt($curl, CURLOPT_POST, 1);
curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($options));
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, ($system_settings["hyperpay_live"] ? true : false));
curl_setopt($curl, CURLOPT_HTTPHEADER, ['Authorization:Bearer ' . $system_settings["hyperpay_access_token"]]);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($curl);

$payment_id = json_decode($response, true)["id"];
?>

<style>
.wpwl-form {
    background: #fff !important;
    box-shadow: 2px 2px 10px -4px rgb(0 0 0 / 10%) !important;
    border: none !important;
    border-radius: 6px !important;
    padding: 24px 20px 0 !important;
}

.wpwl-group.wpwl-group-brand {
    margin-bottom: 25px !important;
}

.wpwl-wrapper-submit {
    margin-top: 10px !important;
}

.wpwl-control {
    border-radius: 0px;
    min-height: 40px;
    font-size: 14px;
    text-align: left;
    direction: ltr !important;
}

.wpwl-label {
    margin-bottom: 5px !important;
}

.wpwl-label.wpwl-label-brand {
    display: none !important;
}
</style>

<!-- https://eu-test.oppwa.com/v1/paymentWidgets.js?checkoutId= -->
<script src="https://eu-prod.oppwa.com/v1/paymentWidgets.js?checkoutId=<?=$payment_id?>" async></script>

<div class="container margin-top" dir=ltr>
<form action="<?=$payment_redirect?>" class="paymentWidgets" data-brands="<?=$brands?>"></form>
<? if ($website_language=="ar"){ ?>
<script>
var wpwlOptions = {
	onReady: function() {
		$('.wpwl-label-cardNumber').html('رقم البطاقة');
		$('.wpwl-label-expiry').html('تاريخ الإنتهاء');
		$('.wpwl-label-cardHolder').html('الإسم علي البطاقة');
		$('.wpwl-button-pay').html('ادفع الآن');
	}
}
</script>
<? } ?>
</div>