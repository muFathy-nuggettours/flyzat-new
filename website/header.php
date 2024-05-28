<!-- Website header-->
<div class=header id=nav-sticky>
<div class="container header_container">

<!-- Large screen logo -->
<div class=logo>
	<div><a href=".">
		<img src="uploads/_website/<?=$website_information["website_logo"]?>" alt="<?=$website_information["website_name"]?>">
	</a></div>
</div>

<!-- Menu contents -->
<div class=menu>
	<div class=nav-menu-container>
		<!-- Small screen menu and close buttons -->
		<div class=nav-menu-sm>
			<div class="container container-md">
				<div class=nav-cover><span class=close-nav onclick="hideNavMenu()">×</span></div>
				<div class=nav-button onclick="showNavMenu()"><i class="fas fa-bars"></i></div>
			</div>
		</div>
		
		<ul class=nav-menu>
			<!-- Small screen logo -->
			<div class=nav-menu-sm>
				<img src="uploads/_website/<?=$website_information["website_logo"]?>" alt="<?=$website_information["website_name"]?>">
			</div>
			
			<!-- Menu contents -->
			<? renderWebsiteMenu() ?>
			
			<!-- Small screen menu prefix -->
			<div class=nav-menu-sm>
				<!-- Registration -->
				<? if (!$logged_user){ ?>
				<div class=margin-bottom>
					<a class="btn btn-primary btn-sm btn-block" href="#" data-toggle=modal data-target="#loginModal"><?=readLanguage(accounts,login)?></a>
					<a class="btn btn-default btn-sm btn-block" href="signup/"><?=readLanguage(accounts,signup)?></a>
				</div>
				<? } ?>
				
				<!-- Booking -->
				<div><a class="btn btn-primary btn-block" style="padding:10px" href="user/reservations/"><?=readLanguage(user,reservations)?></a></div>
				
				<!-- Currencies -->
				<div>
				<?=readLanguage(currencies,currency)?>&nbsp;&nbsp;<img src="images/currencies/<?=$user_paymentCurrency["code"]?>.gif">&nbsp;&nbsp;<?=$user_paymentCurrency[$suffix . "name"]?>
				</div>
				
				<!-- Logout -->
				<? if ($logged_user){ ?><a class="btn btn-danger btn-sm btn-block" href="logout/"><?=readLanguage(accounts,logout)?></a><? } ?>
				
				<!-- Contact -->
				<? if ($website_information["primary_number"]){ ?>
				<div class=number_small>
					<span><a href="tel:<?=$website_information["primary_number"]?>"><?=$website_information["primary_number"]?></a></span>
					<i class="fal fa-mobile"></i>
				</div>
				<? } ?>
	
				<!-- Language -->
				<? if (count($supported_languages) > 1){
					foreach ($supported_languages AS $value){
						if ($website_language != $value){
							$language_options = languageOptions($value);
							print "<div class=links><i class='fal fa-globe'></i>&nbsp;&nbsp;<a href='" . $base_url . ($value!=$supported_languages[0] ? $value . "/" : "") . "'>" . $language_options["name"] . "</a></div>";
						} 
					} 
				} ?>
				
				<!-- Copyrights -->
				<? if (!$white_label){ ?>
					<div class=copyrights><small><?=readLanguage(footer,developer)?></small><a href="https://www.prismatecs.com/">Prismatecs Smart Solutions</a></div>
				<? } ?>
			</div>
		</ul>
	</div>
</div>

<!-- Large screen menu prefix -->
<div class=buttons>
	<!-- Booking -->
	<div><a class=negative href="user/reservations/"><?=readLanguage(user,reservations)?></a></div>

	<!-- Currencies -->
	<div class=currencies>
		<a style="pointer-events:none"><img src="images/currencies/<?=$user_paymentCurrency["code"]?>.gif">&nbsp;&nbsp;<?=$user_paymentCurrency[$suffix . "name"]?>&nbsp;&nbsp;</a>
	</div>
	
	<!-- Registration -->
	<div>
		<a data-toggle=dropdown><i class="fal fa-user"></i>&nbsp;&nbsp;<?=readLanguage(mobile,footer_account)?>&nbsp;&nbsp;<i class="fal fa-angle-down"></i></a>
		<ul class="dropdown-menu reverse animate">
			<? if (!$logged_user){ ?>
			<li><a href="#" data-toggle=modal data-target="#loginModal"><?=readLanguage(accounts,login)?></a></li>
			<li><a href="signup/"><?=readLanguage(accounts,signup)?></a></li>
			<? } else { ?>
			<li><a href="user/"><?=readLanguage(user,dashboard)?></a></li>
			<li><a href="logout/"><?=readLanguage(accounts,logout)?></a></li>
			<? } ?>
		</ul>
	</div>
	
	<!-- Language -->
	<? if (count($supported_languages) > 1){
		foreach ($supported_languages AS $value){
			if ($website_language != $value){
				$language_options = languageOptions($value);
				print "<div><a href='" . $base_url . ($value!=$supported_languages[0] ? $value . "/" : "") . "'>" . ($language_options["code"]=="ar" ? "ع" : "EN") . "</a></div>";
			} 
		} 
	} ?>
	
	<!-- Number -->
	<? if ($website_information["primary_number"]){ ?>
	<div class=number>
		<span><a href="tel:<?=$website_information["primary_number"]?>"><?=$website_information["primary_number"]?></a></span>&nbsp;&nbsp;
		<i class="fal fa-mobile"></i>
	</div>
	<? } ?>
</div>

<!-- End website header-->	
</div>
</div>

<script>
$("[change-currency]").on("click", function(){
	var target = $(this).attr("change-currency");
	postForm({
		token: "<?=$token?>",
		country: target
	});
});
</script>