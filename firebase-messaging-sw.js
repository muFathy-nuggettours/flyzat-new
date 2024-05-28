importScripts("https://www.gstatic.com/firebasejs/7.16.1/firebase-app.js");
importScripts("https://www.gstatic.com/firebasejs/7.16.1/firebase-messaging.js");

firebase.initializeApp({
	apiKey: "AIzaSyDDatM4YB8engpbpbftCcaBwqorXjfymRg",
	projectId: "prismatecs",
	messagingSenderId: "978510209960",
	appId: "1:978510209960:web:6cdf8e674692dda15df39d"
});

const messaging = firebase.messaging();

messaging.setBackgroundMessageHandler(function(payload){
	let notification = payload.notification;
	let notificationTitle = notification.title;
	let notificationOptions = {
		body: notification.body,
		icon: notification.icon,
		image: notification.image
	};
    return self.registration.showNotification(
        notificationTitle,
        notificationOptions,
    );
});