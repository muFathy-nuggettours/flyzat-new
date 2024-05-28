<? if ($get["session"]){
	$session = mysqlFetch(mysqlQuery("SELECT * FROM users_balance_requests WHERE user_id='" . $logged_user["id"] . "' AND session='" . $get["session"] . "'"));
} ?>

<!-- Proceed with payment -->
<? if ($session){
$pyament_type = "balance";
$payment_redirect = $base_url . "checkout-balance/" . $session["session"] . "/";
$payment_amount = $session["amount"];
$payment_currency = $user_paymentCurrency["code"];
include "_inl_online_payment.php";
mysqlQuery("UPDATE users_balance_requests SET payment_method=$selected_method WHERE id='" . $session["id"] . "'");
?>

<!-- Insert Amount -->
<? } else { ?>
<div class=page_container>
	<span><?=readLanguage(payment,balance_add_note)?> <b><?=$user_paymentCurrency["ar_name"]?></b></span>
	<div class="d-flex margin-top">
		<input type=number name=amount id=amount maxlength=5 style="font-size:18px">&nbsp;&nbsp;&nbsp;&nbsp;
		<input type=button class="btn btn-primary" value="<?=readLanguage(payment,procced_payment)?>" onclick="balanceRequest()">
	</div>
</div>

<script>
function balanceRequest(){
	var amount = $("#amount").val();
	var valid = (amount >= 10);
	if (!valid){
		quickNotify("<?=readLanguage(payment,balance_add_note)?> 10 <?=$user_paymentCurrency["ar_name"]?>", "تعثر استكمال العملية", "danger", "fas fa-times fa-2x");
	} else {
		$.ajax({
			type: "POST",
			url: "requests/",
			data: {
				token: "<?=$token?>",
				action: "balance_request",
				amount: amount
			}
		}).done(function(response){
			setWindowLocation("user/balance-charge/?session=" + response);
		});
	}
}
</script>
<? } ?>