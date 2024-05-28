<? include "system/_handler.php";

header("HTTP/1.0 404 Not Found");
$section_title = readLanguage(general,broken_title);

include "system/header.php";
include "website/section_header.php"; ?>

<div class=page_container>
	<div class="row align-items-center">
		<div class="col-md-5 align-center">
			<span class="fas fa-unlink broken_link"></span>
		</div>
		<div class="col-md-15 align-center">
			<h5><b><?=readLanguage(general,broken_description)?></b></h5>
			<div class=margin-top><?=readLanguage(general,broken_content)?></div>
		</div>
	</div>
</div>

<? include "website/section_footer.php";
include "system/footer.php"; ?>