<? include "system/_handler.php";

$mysqltable = "users_database";
$section_information = mysqlFetch(mysqlQuery("SELECT * FROM " . $suffix . "website_pages WHERE page='" . basename($_SERVER["SCRIPT_FILENAME"], ".php") . "'"));
if ($section_information["hidden"]) {
	brokenLink();
}
$header_image = $section_information["image"];

//Valid social media login platforms
$valid_platforms = array("facebook", "google");


//Validate social media platform
if ($post["platform"] && in_array($post["platform"], $valid_platforms)) {
	$platform = $post["platform"];
}

include "system/header.php";
include "website/section_header.php"; ?>

<? if ($system_settings["recaptcha_secret_key"]) { ?><script src="https://www.google.com/recaptcha/api.js" async defer></script><? } ?>

<!-- Start Tags -->
<div style="width:600px; max-width:100%; margin:0 auto 0 auto">
	<div class=page_container>
		<?
		if (!isset($logged_user)) {
			header("Location:" . $base_url . ($get["language"] ? $get["language"] . "/" : "") . "login/");
			exit();
		} else if ($logged_user['is_active'] == 1) {
			header("Location:" . $base_url . ($get["language"] ? $get["language"] . "/" : "") . "user/");
			exit();
		} else { ?>
			<center>
				<div class=login_message>
					<img src="<?= ($logged_user["image"] ? "uploads/users/" . $logged_user["image"] : "images/user.png") ?>">
					<span><?= readLanguage('accounts', 'welcome') ?> <b><?= $logged_user["name"] ?></b></span>
					<!-- <?= readLanguage('accounts', 'redirect') ?> -->
					<span class="text-danger" style="font-size: large; font-weight: bold;"> تم تسجيل حساب حضرتكم بنجاح ، سوف يتم التواصل معكم قريبا من إدارة المبيعات. </span>
				</div>
			</center>
		<? } ?>
	</div>
</div>

<?
include "website/section_footer.php";
include "system/footer.php";
?>