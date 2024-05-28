<!DOCTYPE html>
<html lang="<?=$panel_language?>" dir="<?=$language["dir"]?>">
<head>
<title><?=$website_information["website_name"]?> | <?=readLanguage(general,control_panel)?></title>

<!-- Standard Tags -->
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=Edge">
<meta http-equiv="content-type" content="text/html">
<meta name="robots" content="index,follow">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">

<!-- Customization -->
<link rel="icon" type="image/png" href="../uploads/_website/<?=$website_information["website_icon"]?>">
<base href="<?=$base_url . $panel_folder?>/">

<!-- Variables passed from PHP to JS -->
<script>
var user_token = "<?=$token?>";
var current_platform = "<?=$current_platform?>";
var on_mobile = <?=($on_mobile ? 1 : 0)?>;
var enable_localization = <?=($enable_localization ? 1 : 0)?>;
var file_size_limit = <?=parseSize(ini_get("upload_max_filesize")) / 1024?>;
var theme_version = "<?=$website_theme["version"]?>";
</script>

<!-- Base Scripts -->
<script src="../core/_jquery.js?v=<?=$system_settings["system_version"]?>"></script>
<script src="../core/_bootstrap.js?v=<?=$system_settings["system_version"]?>"></script>
<script src="../core/_language.js?v=<?=$system_settings["system_version"]?>"></script>
<script src="../core/_functions.js?v=<?=$system_settings["system_version"]?>"></script>
<script src="../core/_plugins.js?v=<?=$system_settings["system_version"]?>"></script>

<!-- Base Sheets -->
<link href="../core/_bootstrap-<?=$language["dir"]?>.css?v=<?=$system_settings["system_version"]?>" rel="stylesheet">
<link href="../core/_fontawesome.css?v=<?=$system_settings["system_version"]?>" rel="stylesheet">
<link href="../core/_core.css?v=<?=$system_settings["system_version"]?>" rel="stylesheet">
<link href="../core/_plugins.css?v=<?=$system_settings["system_version"]?>" rel="stylesheet">

<!-- Panel Plugins -->
<script src="../plugins/timepicker.min.js?v=<?=$system_settings["system_version"]?>"></script><link href="../plugins/timepicker.min.css?v=<?=$system_settings["system_version"]?>" rel="stylesheet">
<script src="../plugins/calendar.min.js?v=<?=$system_settings["system_version"]?>"></script><link href="../plugins/calendar.min.css?v=<?=$system_settings["system_version"]?>" rel="stylesheet">
<script src="../plugins/attachments.js?v=<?=$system_settings["system_version"]?>"></script><link href="../plugins/attachments.css?v=<?=$system_settings["system_version"]?>" rel="stylesheet">
<script src="../plugins/multiple-data.js?v=<?=$system_settings["system_version"]?>"></script><link href="../plugins/multiple-data.css?v=<?=$system_settings["system_version"]?>" rel="stylesheet">
<script src="../plugins/tinymce/tinymce.min.js?v=<?=$system_settings["system_version"]?>"></script>
<script src="../plugins/bootbox.min.js?v=<?=$system_settings["system_version"]?>"></script>
<script src="../plugins/bootstrap-notify.min.js?v=<?=$system_settings["system_version"]?>"></script>

<!-- Panel Specific Core -->
<link href="core/_core.css?v=<?=$system_settings["system_version"]?>" rel="stylesheet">
<script src="core/_functions.js?v=<?=$system_settings["system_version"]?>"></script>

<!-- Load classes separately when not in production -->
<? if (!file_exists("website/website.min.css")){ ?>
	<link href="website/_theme.css?v=<?=$website_theme["version"]?>" rel="stylesheet">
<? } else { ?>
	<link href="website/website.min.css?v=<?=$website_theme["version"]?>" rel="stylesheet">
<? } ?>

<!-- Panel Specific Files -->
<link href="website/template.css?v=<?=$website_theme["version"]?>" rel="stylesheet">
<script src="website/functions.js?v=<?=$system_settings["system_version"]?>"></script>
</head>

<!-- Custom MetaData -->
<? include "website/metadata.php"; ?>
</head>

<body class="<?=($inline_page ? "inline" : "body")?> <?=($logged_user ? "logged" : "login")?>" database-language="<?=$database_language["code"]?>">

<!-- Body Headers -->
<? if (!$inline_page && $logged_user){ ?>
<div class=side-menu-overlay></div>
<div class=header id=nav-sticky>
	<div class=header_menu_toggle>
		<a side-menu-toggle><i class="fas fa-bars"></i></a>
	</div>
	<div class=header_title>
		<a href="index.php"><?=$website_information["website_name"]?></a>
	</div>
	<div class=header_links>
		<!-- Search -->
		<div class="btn-group search-group">
			<div class=search_container>
				<i class="fas fa-search"></i>
				<input id=header_search_input type=text placeholder="<?=readLanguage(general,search)?>">
			</div>
			<div class=dropdown>
				<button type=button id=header_search_button data-toggle=dropdown></button>
				<ul id=header_search_results class="dropdown-menu animate"></ul>		
			</div>
		</div>
		<script>
		//On search input change
		$("#header_search_input").on("input", function(){
			var search = $(this).val();
			var mapObj = {آ:"ا", أ:"ا", إ:"ا", ى:"ي", ة:"ه", ؤ:"و"};
			search = search.replace(/آ|أ|إ|ى|ة|ؤ/gi, function(matched){
			  return mapObj[matched];
			});
			if (search.length > 0){
				headerSearchInitialize(search);
				if (!$("#header_search_results").parent().hasClass("open")){
					$("#header_search_button").click();
				}
			} else {
				$("#header_search_results li").remove();
			}
		});
		
		//Initialize search
		function headerSearchInitialize(string){
			let count = 0;
			$("#header_search_results li").remove();
			$("#side-menu [search-normalized*='" + string + "']").each(function(){
				count++;
				let category = $(this).attr("search-category");
				let section = $(this).attr("search-section");
				let result = $(this).parent().parent().clone();
				$(result).find(".index_button b").prepend("<span class=search_location>" + category + (section ? " &raquo; " + section : "") + "</span>");
				$("#header_search_results").append("<li>" + $(result).html() + "</li>");
				if (count >= 10){
					return false;
				}
			});
			if (count <= 0){
				$("#header_search_results").append("<li><div class=empty><i class='fas fa-exclamation-circle'></i><?=readLanguage(general,search_empty)?></div></li>");
			}
		}
		</script>		

		<!-- Notifications -->
		<? include "_block_notifications.php"; ?>
		<div class="btn-group notifications-group">
			<button type=button class=dropdown-toggle data-toggle=dropdown>
				<?=($total_notifications ? "<span class=notification_count>$total_notifications</span>" : "")?>
				<i class="fas fa-bell"></i>
			</button>
			<ul class="dropdown-menu animate notifications_menu">
				<? if ($total_notifications){
					foreach ($notifications AS $key=>$value){
						print "<li><a href='" . ($value[2] ? $value[2] : $key . ".php") . "'><i class='fas fa-exclamation-triangle'></i> " . $value[0] . "</a></li>";
					}
				} else {
					print "<li><div class=empty><i class='fas fa-check-circle'></i>" . readLanguage(general,no_notifications) . "</div></li>";
				} ?>
			</ul>
		</div>

		<!-- Language -->
		<? if (count($supported_languages) > 1 && ((isset($multiple_languages) && $multiple_languages==true) || !isset($multiple_languages))){ ?>
		<div class=btn-group>
			<button type=button class=dropdown-toggle data-toggle=dropdown aria-haspopup=true aria-expanded=false>
				<?=strtoupper($database_language["code"])?>
			</button>
			<ul class="dropdown-menu animate">
				<? foreach ($supported_languages AS $value){
					print "<li><a href='" . basename($_SERVER["SCRIPT_FILENAME"]) . "?language=$value'>" . languageOptions($value)["name"] . "</a></li>";
				} ?>
			</ul>
		</div>
		<? } ?>	
		
		<!-- Website -->
		<div class=btn-group>
			<a href="../" target=_blank class=dropdown-toggle><i class="fas fa-laptop"></i></a>
		</div>
		
		<!-- User -->
		<div class="btn-group user-group">
			<button type=button class=dropdown-toggle data-toggle=dropdown aria-haspopup=true aria-expanded=false>
				<img src="<?=($logged_user["image"] ? $logged_user["image"] : "images/user.png")?>">
				<span class="text hidden-sm hidden-xs">
					<?=$logged_user["name"]?>
					<small><?=getID($logged_user["permission"], "system_permissions")["title"]?></small>
				</span>
				<span class="fas fa-angle-down hidden-sm hidden-xs"></span>
			</button>
			<ul class="dropdown-menu animate">
				<div class="user_dropdown_header">
					<div>
						<b><?=$logged_user["name"]?></b>
						<small><?=getID($logged_user["permission"], "system_permissions")["title"]?></small>
					</div>
					<a class="btn btn-danger btn-sm flex-center" href="index.php?action=logout"><i class="fas fa-power-off"></i>&nbsp;<?=readLanguage(general,user_logout)?></a>
				</div>
				<li><a href="_page_profile.php"><i class="fas fa-user-edit"></i>&nbsp;<?=readLanguage(general,user_profile)?></a></li>
			</ul>
		</div>
	</div>
</div>
<? } ?>

<!-- Start Body Container --><div class=body-container>

<!-- Body Headers -->
<? if (!$inline_page && $logged_user){ ?>
<!-- Menu -->
<div class=menu-container>
	<!-- Logo -->
	<div class=logo><a href="index.php"><img src="../uploads/_website/<?=$website_information["website_logo"]?>"></a></div>
	
	<!-- Icons -->
	<div class="panel-group side-panel" id=side-menu>
		<? include "_block_menu.php"; ?>
		<?=str_replace("{{menu_parent}}", "side-menu", $menu);?>
	</div>
	
	<!-- Version -->
	<div class=version>
		<?=readLanguage(general,control_panel)?> <b><?=$website_information["website_name"]?></b>
		<?=$powered_by?>
		<small>Version <?=$system_settings["system_version"]?></small>
		<br><small><?=(!$white_label ? "Prismatecs " : "")?>CMS Version <?=$cms_version?></small>
	</div>
</div>
<? } ?>

<!-- Start Page Container --><div class=page-container>