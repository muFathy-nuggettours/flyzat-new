<!-- ===== Footer ===== -->
<div class=footer>
	<!-- Footer Module -->
	<? if ($website_information["module_footer"] && $section_footer!="none"){
		echo customModuleRender($website_information["module_footer"]);
	} ?>
	
	<!-- Copyrights -->
	<div class=copyrights>
		<div class=container>
			<div><?=readLanguage(footer,copyright)?> © <?=$website_information["website_name"]?> <?=dateLanguage("Y",time())?></div>
			<? if (!$white_label){ ?>
				<div class=copyrights_links>
					<?=readLanguage(footer,developer)?>&nbsp;&nbsp;<a href="https://www.prismatecs.com/" target=_blank>Prismatecs Smart Solutions</a>
				</div>
			<? } ?>
		</div>
	</div>
</div>

<!-- ===== Website Anchors ===== -->

<!-- Scroll top -->
<script src="plugins/scroll-top-percentage.js?v=<?=$system_settings["system_version"]?>"></script>
<link href="plugins/scroll-top-percentage.css?v=<?=$system_settings["system_version"]?>" rel="stylesheet">
<a href="#" class="scroll-top"><i class="fas fa-angle-up"></i><svg><circle r=25% cx=50% cy=50% stroke-dasharray=157%></svg></a>