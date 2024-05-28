<? $payment_methods_ids = explode(",", getData("system_payment_currencies", "code", $payment_currency)["payment_methods"]);

//Check availability
$payment_methods = array();
foreach ($payment_methods_ids AS $id){
	$available = paymentMethodAvailable($id);
	
	//Exclude cash payment from balance charge
	if ($id==6 && $pyament_type=="balance"){
		$available = false;
	}
	
	if ($available){
		array_push($payment_methods, $id);
	}
}

$selected_method = ($get["method"] && in_array($get["method"], $payment_methods) ? $get["method"] : $payment_methods[0]); ?>

<? if (!count($payment_methods)){ ?>
<div class=page_container>
	<div class=message>
		<i class="fas fa-money-check-alt"></i>
		<b><?=readlanguage(payment,methods_unavailable)?></b>
		<small><?=readlanguage(payment,methods_unavailable_alert)?></small>
	</div>
</div>

<? } else { ?>
	<!-- Payment method selection -->
	<? if (count($payment_methods) > 1){ ?>
	<div><?=readlanguage(payment,choose_method)?></div>
	<div class=payment_methods_selector>
		<? foreach ($payment_methods AS $method){ ?>
		<a class="payment_method <?=($method==$selected_method ? "active" : "inactive")?>" href="<?=parse_url($_SERVER["REQUEST_URI"],PHP_URL_PATH)?><?=rebuildQueryParameters(array("method"), array("method"=>$method))?>">
			<i class="fas fa-check"></i>
			<img src="images/payment_methods/<?=$method?>.png">
		</a>&nbsp;&nbsp;
		<? } ?>
	</div>
	<? } ?>

	<!-- Include platform payment page -->
	<?
	//بنك مصر - مصري
	if ($selected_method==1){
		include "_payment_banquemisr.php";
		
	//بنك مصر - دولار
	} else if ($selected_method==2){
		include "_payment_banquemisr.php";
		
	//هايبر باي - فيزا
	} else if ($selected_method==3){
		$entity = "visa";
		include "_payment_hyperpay.php";
		
	//هايبر باي مدي
	} else if ($selected_method==4){
		$entity = "mada";
		include "_payment_hyperpay.php";	

	//فودافون كاش
	} else if ($selected_method==5){
		include "_payment_vodafone.php";
		
	//نقدي
	} else if ($selected_method==6){
		include "_payment_cash.php";
		
	} ?>
<? } ?>