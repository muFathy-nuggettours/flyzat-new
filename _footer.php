<!-- ========== Full Page ========== -->
<? if (!$inline_page){ ?>
	<!-- Google Analytics -->
	<? if ($system_settings["google_analytics_id"]){ ?>
	<script async src="https://www.googletagmanager.com/gtag/js?id=<?=$system_settings["google_analytics_id"]?>"></script>
	<script>
	window.dataLayer = window.dataLayer || [];
	function gtag(){ dataLayer.push(arguments); }
	gtag("js", new Date());
	gtag("config", "<?=$system_settings["google_analytics_id"]?>");
	</script>
	<? } ?>

	<!-- Facebook Pixel Code -->
	<? if ($system_settings["facebook_pixel"]){ ?>
	<script>
	!function(f,b,e,v,n,t,s)
	{if(f.fbq)return;n=f.fbq=function(){n.callMethod?
	n.callMethod.apply(n,arguments):n.queue.push(arguments)};
	if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version="2.0";
	n.queue=[];t=b.createElement(e);t.async=!0;
	t.src=v;s=b.getElementsByTagName(e)[0];
	s.parentNode.insertBefore(t,s)}(window, document,"script",
	"https://connect.facebook.net/en_US/fbevents.js");
	fbq("init","<?=$system_settings["facebook_pixel"]?>");
	fbq("track","PageView");
	</script>
	<noscript><img height="1" width="1" style="display:none" src="https://www.facebook.com/tr?id=<?=$system_settings["facebook_pixel"]?>&ev=PageView&noscript=1"/></noscript>
	<? } ?>

	<!-- Facebook SDK -->
	<? if ($system_settings["facebook_app_id"]){ ?>
	<script>
	window.fbAsyncInit = function(){
		FB.init({
			appId: "<?=$system_settings["facebook_app_id"]?>",
			autoLogAppEvents : true,
			xfbml: true,
			version: "v7.0"
		});
	};
	(function(d,s,id){
		var js, fjs = d.getElementsByTagName(s)[0];
		if (d.getElementById(id)){return;}
		js = d.createElement(s); js.id = id;
		js.src = "https://connect.facebook.net/en_US/sdk/xfbml.customerchat.js"; //Supports login & customer chat
		fjs.parentNode.insertBefore(js, fjs);
	}(document,"script","facebook-jssdk"));
	</script>
	<script async defer crossorigin="anonymous" src="https://connect.facebook.net/en_US/sdk.js"></script>
	<? } ?>

	<!-- HoverSignal -->
	<? if ($system_settings["hover_signal"]){ ?>
	<script type="text/javascript" >
	(function (d, w) {
	var n = d.getElementsByTagName("script")[0],
	s = d.createElement("script"),
	f = function () { n.parentNode.insertBefore(s, n); };
	s.type = "text/javascript";
	s.async = true;
	s.src = "https://app.hoversignal.com/Api/Script/<?=$system_settings["hover_signal"]?>";
	if (w.opera == "[object Opera]") {
	d.addEventListener("DOMContentLoaded", f, false);
	} else { f(); }
	})(document, window);
	</script>
	<? } ?>

	<!-- Push Notifications -->
	<? if ($system_settings["firebase_app_api_key"] && $system_settings["firebase_project_id"] && $system_settings["firebase_project_number"] && $system_settings["firebase_app_id"]){
		include "_inl_push_notifications.php";
	} ?>

	<!--Modals-->
	<? include "_modals.php"; ?>

	<!-- Include Footer -->
	<? include "website/footer.php"; ?>
<? } ?>

<!-- ========== Fancybox Page ========== -->
<? if ($inline_page){ ?>
	<!-- Hide loading cover -->
	<script>hideLoadingCover();</script>
<? } ?>

<!-- Javascript Setup -->
<script src="core/_setup.js?v=<?=$system_settings["system_version"]?>"></script>
<script src="website/setup.js?v=<?=$system_settings["system_version"]?>"></script>

<div id="modal" class="modal">
                                <div class="modal-content">
                                    <div style="margin: 50px;">
                                        <span id="closeModal" class="close-button"><span class="fa fa-times"></span></span>
                                    </div>
                                    <div class="age-input">
                                        <div class="col-6">
                                            <b><?= readLanguage('common', 'adult') ?></b>
                                            <span>(12 <?= readLanguage('common', 'years_more') ?>)</span>
                                        </div>
                                        <div class="input-group col-6">
                                            <button style="color: #0d5c96; font-size: larger;" class="btn" type="button" onclick="updateTravelerCount('adults', 1)"><span class="fa fa-plus"></span></button>
                                            <input class="form-control" data-input=adults onchange="updateTravelers()" type="number" id="adults" value="1" min="1" max="9" readonly>
                                            <button style="color: #0d5c96; font-size: larger;" class="btn" type="button" onclick="updateTravelerCount('adults', -1)"><span class="fa fa-minus"></span></button>
                                        </div>
                                    </div>
                                    <div class="age-input">
                                        <div class="col-6">
                                            <b><?= readLanguage('common', 'child') ?></b>
                                            <span>(<?= readLanguage('common', 'from') ?> 2 <?= readLanguage('common', 'to') ?> 12 <?= readLanguage('common', 'years_old') ?>)</span>
                                        </div>
                                        <div class="input-group col-6">
                                            <button style="color: #0d5c96; font-size: larger;" class="btn" type="button" onclick="updateTravelerCount('children', 1)"><span class="fa fa-plus"></span></button>
                                            <input class="form-control" data-input=children onchange="updateTravelers()" type="number" id="children" value="0" min="0" max="8" readonly>
                                            <button style="color: #0d5c96; font-size: larger;" class="btn" type="button" onclick="updateTravelerCount('children', -1)"><span class="fa fa-minus"></span></button>
                                        </div>
                                    </div>
                                    <div class="age-input">
                                        <div class="col-6">
                                            <b><?= readLanguage('common', 'infant') ?></b>
                                            <span>(<?= readLanguage('common', 'less_than') ?> <?= readLanguage('common', 'two_years') ?>)</span>
                                        </div>
                                        <div class="input-group col-6">
                                            <button style="color: #0d5c96; font-size: larger;" class="btn" type="button" onclick="updateTravelerCount('toddlers', 1)"><span class="fa fa-plus"></span></button>
                                            <input class="form-control" data-input=toddlers onchange="updateTravelers()" type="number" id="toddlers" value="0" min="0" max="8" readonly>
                                            <button style="color: #0d5c96; font-size: larger;" class="btn" type="button" onclick="updateTravelerCount('toddlers', -1)"><span class="fa fa-minus"></span></button>
                                        </div>
                                    </div>
                                </div>
                            </div>
</body></html>
<? if ($connection){ mysqlClose(); } ob_end_flush(); ?>