<? include "system/_handler.php";

$multiple_languages = false;

//Security Measures
if (!$logged_user){ header("Location: ."); exit(); }

header("HTTP/1.0 404 Not Found");

include "_header.php"; ?>

<div class=title><?=readLanguage(pages,broken_title)?></div>

<div class="alert alert-danger">
	<div class=large_icon><i class="fas fa-unlink"></i></div>
	<div><?=readLanguage(pages,broken_description)?></div>
</div>

<? include "_footer.php"; ?>