<?
$facebook_login = ($system_settings["facebook_app_id"] && $system_settings["facebook_login"]);
$google_login = ($system_settings["google_client_id"] && $system_settings["google_login"]);
$social_login = $facebook_login || $google_login;
$or_separator = "<div class=separator_or><label>" . readLanguage(accounts,separator) . "</label></div>";
?>

<!-- Start Platform Condition (only show if platform is not set "not completing signup profile" and not on mobile application) -->
<? if ($social_login && !$post["social"] && !$platform && !$on_mobile){ ?>

<? if (!$buttons_on_top){ print $or_separator; } ?>
<div class=social_login_buttons>
	<!-- Facebook Login -->
	<? if ($facebook_login){ ?><a class="login_social facebook" onclick="facebookLogin()">Login with&nbsp;&nbsp;<i class="fab fa-facebook-square"></i></a><? } ?>

	<!-- Google Login -->
	<? if ($google_login){ ?><a class="login_social google"></a><? } ?>
</div>
<? if ($buttons_on_top){ print $or_separator; } ?>

<script>
//Complete social media login after token retrieval
function completeSocialLogin(platform, token){
	$.confirm({
		title: readLanguage.accounts.login,
		theme: "light-noborder",
		closeIcon: false,
		buttons: {
			cancel: {
				text: readLanguage.plugins.message_close,
				isHidden: true
			}
		},
		onOpenBefore: function(){
			var dialog = this;
			dialog.showLoading();
			$.ajax({
				method: "POST",
				url: "requests/",
				data: {
					token: "<?=$token?>",
					action: "social",
					platform: platform,
					access_token: token
				}
			}).done(function(response){
				dialog.setContent(response);
			}).fail(function(response){
				dialog.hideLoading();
				dialog.setContent(readLanguage.accounts.social_error);
				dialog.buttons.cancel.show();						
			});	
		}
	});
}
</script>

<!-- ======================================== -->
<!-- ========== [FACEBOOK] ========== -->
<!-- ======================================== -->

<? if ($facebook_login){ ?>
<script>
function facebookLogin(){
	if (typeof FB !== "undefined"){
		FB.login(function(response){
			var access_token = response.authResponse.accessToken;
			completeSocialLogin("facebook", access_token);
		}, { scope: "public_profile,email" });
	} else {
		messageBox(readLanguage.general.error, readLanguage.accounts.social_error, "fas fa-times-circle", "red");
	}
}
</script>
<? } ?>

<!-- ======================================== -->
<!-- ========== [GOOGLE] ========== -->
<!-- ======================================== -->

<? if ($google_login){ ?>
<script src="https://accounts.google.com/gsi/client" async defer></script>
<script>
window.onload = function () {
	google.accounts.id.initialize({
		client_id: "<?=$system_settings["google_client_id"]?>",
		callback: function(response){
			completeSocialLogin("google", response.credential);
		}
	});
	$(".login_social.google").each(function(){
		var self = $(this)[0];
		google.accounts.id.renderButton(self, {
			theme: "outline",
			size: "large",
			width: "310px"
		});
	});
}
</script>
<? } ?>

<? } ?><!-- End Platform Condition -->