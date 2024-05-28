<? include "system/_handler.php";

$multiple_languages = false;

//Security Measures
if (!$logged_user){ header("Location: ."); exit(); }

header("HTTP/1.0 401 Unauthorized");

include "_header.php"; ?>

<div class=title><?=readLanguage(pages,unauthorized_title)?></div>

<div class="alert alert-danger">
	<div class=large_icon><i class="fas fa-lock"></i></div>
	<div><?=readLanguage(pages,unauthorized_description)?></div>
</div>

<? include "_footer.php"; ?>