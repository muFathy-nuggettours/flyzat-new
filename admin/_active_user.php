<?php
include "system/_handler.php";

checkPermissions("users_database");

$user_data = getID($get["id"], "users_database");

if (!$user_data) {
	brokenLink();
}

$query = "UPDATE users_database SET is_active = 1 WHERE id =" . $get["id"];
mysqlQuery($query);

$message = "<strong> تم تفعيل حسابكم بنجاح على موقع فلاي ذات </strong>";
sendMail(array($user_data["email"]), 'Your account successfully activated', nl2br($message));


header("Location:" . $base_url . "admin/users_activation.php?message=تم تفعيل المستخدم بنجاح.");
?>

<div class="title"><?= getPageTitle($base_name) ?></div>
<?= isset($message) ? $message : '' ?>


