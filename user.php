<? include "system/_handler.php";

requireLogin(true);

//User Dashboard Pages
$user_pages = array(
	"dashboard" => array(readLanguage(user,home),"user_dashboard","fas fa-home"),
	"reservations" => array(readLanguage(user,reservations),"user_reservations","fas fa-plane"),
	"balance-charge" => array(readLanguage(user,balance_charge),"user_balance_charge","fas fa-wallet"),
	"balance-operations" => array(readLanguage(user,balance_operations),"user_balance_operations","fas fa-search-dollar"),
	"update-passengers" => array(readLanguage(user,passengers),"user_passengers","fas fa-users-cog"),
	"update-profile" => array(readLanguage(user,update_profile),"user_profile","fas fa-user-edit"),
);

//Check if page exists
if ($get["page"] AND !$user_pages[$get["page"]]){ brokenLink(); }
$page_canonical = ($user_pages[$get["page"]] ? $get["page"] : "dashboard");

$section_information = mysqlFetch(mysqlQuery("SELECT * FROM " . $suffix . "website_pages WHERE page='" . basename($_SERVER["SCRIPT_FILENAME"], ".php") . "'"));
$section_prefix = readLanguage(user,dashboard);
$section_title = $user_pages[$page_canonical][0];

//Update Profile Picture
if ($post["token"] && $post["action"]=="update-picture"){
	$data = $_POST["image"];
	list($type,$data) = explode(";", $data);
	list(,$data) = explode(",", $data);
	$data = base64_decode($data);
	$filename = uniqid($logged_user["user_id"] . "_") . ".png";
	file_put_contents("uploads/users/" . $filename, $data);
	mysqlQuery("UPDATE users_database SET image='$filename' WHERE id='" . $logged_user["id"] . "'");
	$logged_user = mysqlFetch(mysqlQuery("SELECT * FROM users_database WHERE id='" . $logged_user["id"]. "'"));
}

include "system/header.php";
include "website/section_header.php"; ?>

<script src="plugins/croppie.min.js?v=<?=$system_settings["system_version"]?>"></script>
<link href="plugins/croppie.min.css?v=<?=$system_settings["system_version"]?>" rel="stylesheet">

<!-- Start Tags --><div class="row grid-container-15">

<!-- Main Content -->
<div class="col-md-14 grid-item">
	<? include $user_pages[$page_canonical][1] . ".php"; ?>
</div>

<!-- Side Content -->
<div class="col-md-6 grid-item">
	<div class="page_container user_card margin-bottom">
		<img src="<?=($logged_user["image"] ? "uploads/users/" . $logged_user["image"] : "images/user.png")?>">
		<div class=single-line>
			<span class=single-line><a href="user/"><b><?=$logged_user["name"]?></b></a></span>
			<span class=single-line><?=$logged_user["email"]?></span>
			<input type=hidden name=profile_image_base64>
			<label><?=readLanguage(accounts,change_picture)?><input type=file id=profile_image name=profile_image accept="image/*"></label>
		</div>	
	</div>	
	
	<? $user_balance = mysqlFetch(mysqlQuery("SELECT SUM(amount) AS total FROM users_balance WHERE currency='$user_currencyCode' AND user_id='" . $logged_user["id"] . "'"))["total"]; ?>
	<div class="page_container user_balance margin-bottom">
		<span><?=readLanguage(user,balance)?></span>
		<div><b><?=number_format(round($user_balance, 2), 2)?></b>&nbsp;&nbsp;<small><?=$user_paymentCurrency[$suffix . "name"]?></small></div>
		<div class="flex-center margin-top-10">
			<a class="btn btn-success btn-sm flex-grow-1" href="user/balance-charge/"><?=readLanguage(user,balance_charge)?></a>&nbsp;&nbsp;
			<a class="btn btn-primary btn-sm flex-grow-1" href="user/balance-operations/"><?=readLanguage(user,balance_operations)?></a>
		</div>
	</div>

	<div class=recursive_navigation>	
	<?
	print "<ul>";
	foreach ($user_pages AS $key => $value){
		$class = ($key==$page_canonical ? "active" : "standard");
		print "<li class='$class'><a href='user/$key/'><i class='" . $value[2] . "'></i>&nbsp;&nbsp;" . $value[0] . "</a></li>";
	}
	print "</ul>";
	?>
	</div>
</div>

<!-- End Tags --></div>

<script>
$(document).ready(function(){
	bindCroppie("profile_image")
});

$("[name=profile_image_base64]").change(function(){
	var image = $(this).val();
	showLoadingCover();
	postForm({ action:"update-picture", image:image });
});
</script>

<? include "website/section_footer.php";
include "system/footer.php"; ?>