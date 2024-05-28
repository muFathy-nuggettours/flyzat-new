<? if ($section_header!=="none"){ ?>
	<div class=section_header style="background-image:url('<?=$header_image?>')">
		<div class=overlay>
			<div class=container>
				<div class=section_titles>
					<? if ($section_prefix){ print "<h2>$section_prefix</h2>"; } ?>
					<? if ($section_title){ print "<h1>$section_title</h1>"; } ?>
					<? if ($section_description){ print "<span>$section_description</span>"; } ?>
				</div>
				
				<? if ($section_rating){ ?>
				<div class=ratings>
					<div class=stars><?=ratingStars($section_rating["value"])?></div>&nbsp;&nbsp;&nbsp;&nbsp;
					<div>(<?=$section_rating["value"]?>/5) <?=readLanguage(common,based_on)?> <i><?=$section_rating["total"]?></i> <?=readLanguage(reservation,rate)?></div>
				</div>
				<? } ?>
			</div>
			
			<? if ($breadcrumbs){ ?>
			<div class=container>
				<ul class=breadcrumb>
					<? foreach ($breadcrumbs as $key=>$value){
						print $value;
					} ?>
				</ul>
			</div>
			<? } ?>
		</div>
	</div>
<? } ?>

<!-- Modules Before Content -->
<? $modules_before = array_filter(explode(",", explode("content", $section_layout)[0]));
foreach ($modules_before AS $module){
	echo customModuleRender($module);
} ?>

<? //Start container if custom page (or) built-in page (or) neither not set
if (!$exclude_inner_container){ ?>
<!-- Start Container --><div class="container inner">
<? } ?>