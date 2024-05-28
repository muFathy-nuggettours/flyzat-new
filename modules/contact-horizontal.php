<div class=module_contact_horizontal>
	<? if ($website_information["primary_number"]){ ?>
	<div>
		<i class="far fa-mobile"></i>
		<span>
			<small><?=readLanguage(mobile,contact_phone)?></small>
			<a href="tel:<?=$website_information["primary_number"]?>"><b><?=$website_information["primary_number"]?></b></a>
		</span>
	</div>
	<? } ?>
	
	<? if ($website_information["email"]){ ?>
	<div>
		<i class="far fa-envelope"></i>
		<span>
			<small><?=readLanguage(mobile,contact_email)?></small>
			<a href="mailto:<?=$website_information["email"]?>"><b><?=$website_information["email"]?></b></a>
		</span>
	</div>
	<? } ?>
	
	<? if ($website_information["whatsapp"]){ ?>
	<div>
		<i class="fab fa-whatsapp"></i>
		<span>
			<small><?=readLanguage(mobile,contact_whatsapp)?></small>
			<a href="https://wa.me/<?=$website_information["whatsapp"]?>" target=_blank><b><?=$website_information["whatsapp"]?></b></a>
		</span>
	</div>
	<? } ?>
</div>