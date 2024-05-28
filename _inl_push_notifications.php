<? if ($on_mobile){ ?>
<script>
sendApplicationMessage("Push-Register");

function onDataReceived_pushNotificationsToken(token){
	$.ajax({
		method: "POST",
		url: "requests/",
		data: {
			token: user_token,
			action: "push",
			push_token: token
		},
	});	
}
</script>

<? } else { ?>
<script src="https://www.gstatic.com/firebasejs/8.4.2/firebase-app.js"></script>
<script src="https://www.gstatic.com/firebasejs/7.16.1/firebase-messaging.js"></script>

<script>
firebase.initializeApp({
	apiKey: "<?=$system_settings["firebase_app_api_key"]?>",
	projectId: "<?=$system_settings["firebase_project_id"]?>",
	messagingSenderId: "<?=$system_settings["firebase_project_number"]?>",
	appId: "<?=$system_settings["firebase_app_id"]?>"
});

const messaging = firebase.messaging();
messaging.requestPermission().then(function(){
	return messaging.getToken();
}).then(function(token){
	$.ajax({
		method: "POST",
		url: "requests/",
		data: {
			token: user_token,
			action: "push",
			push_token: token
		},
	});
});

let enableForegroundNotification = true;
messaging.onMessage(function(payload){
	if (enableForegroundNotification){
		let notification = payload.notification;
		let notificationTitle = notification.title;
		let notificationOptions = {
			body: notification.body,
			icon: notification.icon,
			image: notification.image
		};
		navigator.serviceWorker.getRegistrations().then((registration) => {
			registration[0].showNotification(
				notificationTitle,
				notificationOptions
			);
		});
	}
});
</script>
<? } ?>