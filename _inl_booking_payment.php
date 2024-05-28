<!-- ===== No Payment Required ===== -->
<? if (!$payment_amount){ ?>
<div class=page_container>
	<div class="message fancy">
		<i class="fas fa-laugh-beam"></i>
		<b><?=readLanguage(payment,no_payment)?></b>
		<small><?=readLanguage(payment,payment_covered)?></small>
		<button type=button class="submit large" style="margin:30px auto 0 auto" onclick="setWindowLocation('checkout/<?=$booking_session["session"]?>/')"><?=readLanguage(booking,book_now)?></button>
	</div>
</div>

<!-- ===== Payment Required ===== -->
<? } else { ?>

<? include "_inl_online_payment.php"; ?>
<? mysqlQuery("UPDATE flights_reqeusts SET payment_method=$selected_method WHERE id='" . $booking_session["id"] . "'"); ?>

<? } ?>