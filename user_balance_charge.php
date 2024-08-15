<? if ($_GET["method"] == null && $_GET["session"] == null) { ?>
	<style>
		.payment-options {
			display: flex;
			justify-content: center;
			flex-wrap: wrap;
			gap: 20px;
		}

		.payment-card {
			width: 120px;
			text-align: center;
			transition: transform 0.3s ease;
			text-decoration: none;
			color: #0d5c96;
			display: block;
		}

		.payment-card span {
			display: block;
			margin-top: 10px;
			font-size: 16px;
		}

		.payment-card.active {
			color: #096aad;
			font-weight: bold;
		}

		.payment-card.disabled {
			opacity: 0.5;
			pointer-events: none;
		}

		.icon-circle {
			width: 80px;
			height: 80px;
			background-color: #0d5c96;
			border-radius: 50%;
			display: flex;
			align-items: center;
			justify-content: center;
			margin: 0 auto;
		}

		.icon-circle img {
			width: 55px;
		}

		.payment-card:hover {
			transform: scale(1.1);
		}
	</style>
	<div class="payment-options">
		<a href="user/balance-charge/?method=4" class="payment-card active">
			<div class="icon-circle">
				<img src="images/payment_methods/visa-master.png" alt="فيزا">
			</div>
			<span><?= readLanguage("payment", "visa") ?></span>
		</a>
		<div class="payment-card disabled">
			<div class="icon-circle">
				<img src="images/payment_methods/bank-tranfer.png" alt="تحويل بنكي">
			</div>
			<span><?= readLanguage("payment", "bank_transfer") ?></span>
		</div>
		<div class="payment-card disabled">
			<div class="icon-circle">
				<img src="images/payment_methods/cash-pay.png" alt="الدفع كاش في المكتب">
			</div>
			<span><?= readLanguage("payment", "cash") ?></span>
		</div>
		<div class="payment-card disabled">
			<div class="icon-circle">
				<img src="images/payment_methods/jumbo4pay.png" alt="جامبو فور باي">
			</div>
			<span><?= readLanguage("payment", "jumpo4pay") ?></span>
		</div>
		<div class="payment-card disabled">
			<div class="icon-circle">
				<img src="images/payment_methods/voda-cash.png" alt="فودافون كاش">
			</div>
			<span><?= readLanguage("payment", "voda_cash") ?></span>
		</div>
		<div class="payment-card disabled">
			<div class="icon-circle">
				<img src="images/payment_methods/sdad.png" alt="خدمة سداد">
			</div>
			<span><?= readLanguage("payment", "sdad") ?></span>
		</div>
		<div class="payment-card disabled">
			<div class="icon-circle">
				<img src="images/payment_methods/coll-req.png" alt="طلب تحصيل">
			</div>
			<span><?= readLanguage("payment", "coll_req") ?></span>
		</div>
	</div>
<? } else { ?>
	<? if ($get["session"]) {
		$session = mysqlFetch(mysqlQuery("SELECT * FROM users_balance_requests WHERE user_id='" . $logged_user["id"] . "' AND session='" . $get["session"] . "'"));
	} ?>

	<!-- Proceed with payment -->
	<? if ($session) {
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
			<span><?= readLanguage(payment, balance_add_note) ?> <b><?= $user_paymentCurrency["ar_name"] ?></b></span>
			<div class="d-flex margin-top">
				<input type=number name=amount id=amount maxlength=5 style="font-size:18px">&nbsp;&nbsp;&nbsp;&nbsp;
				<input type=button class="btn btn-primary" value="<?= readLanguage(payment, procced_payment) ?>" onclick="balanceRequest()">
			</div>
		</div>

		<script>
			function balanceRequest() {
				var amount = $("#amount").val();
				var valid = (amount >= 10);
				if (!valid) {
					quickNotify("<?= readLanguage(payment, balance_add_note) ?> 10 <?= $user_paymentCurrency["ar_name"] ?>", "تعثر استكمال العملية", "danger", "fas fa-times fa-2x");
				} else {
					$.ajax({
						type: "POST",
						url: "requests/",
						data: {
							token: "<?= $token ?>",
							action: "balance_request",
							amount: amount
						}
					}).done(function(response) {
						setWindowLocation("user/balance-charge/?session=" + response + "&method=<?= $_GET["method"] ?>");
					});
				}
			}
		</script>
<? }
} ?>