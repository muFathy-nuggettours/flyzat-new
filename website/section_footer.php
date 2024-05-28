<? //End container if custom page (or) built-in page (or) neither not set
if (!$exclude_inner_container){ ?>
<!-- End Container --></div>
<? } ?>

<!-- Modules After Content -->
<? $modules_before = array_filter(explode(",", explode("content", $section_layout)[1]));
foreach ($modules_before AS $module){
	echo customModuleRender($module);
} ?>