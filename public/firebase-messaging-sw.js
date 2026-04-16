// This is the service worker file required for Firebase Messaging
importScripts('https://www.gstatic.com/firebasejs/9.0.0/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/9.0.0/firebase-messaging-compat.js');

// Since we are dynamic, we might need a way to pass config to the worker.
// Usually, for the service worker, we hardcode the configuration as it's separate from the main app.
// But we could use indexedDB or a specific endpoint to fetch it if we want it fully dynamic.
// For now, the user must provide the basic config here or we can try to automate it.

firebase.initializeApp({
    apiKey: "AIzaSyCt_ZnTdWNbqsH_Dksqg_UGPDDxL2RL59o",
    authDomain: "turnomedicords.firebaseapp.com",
    projectId: "turnomedicords",
    storageBucket: "turnomedicords.firebasestorage.app",
    messagingSenderId: "495355940071",
    appId: "1:495355940071:web:f183ae21e6d1d10fb8a642",
});

const messaging = firebase.messaging();

messaging.onBackgroundMessage((payload) => {
    console.log('[firebase-messaging-sw.js] Received background message ', payload);
    const notificationTitle = payload.notification.title;
    const notificationOptions = {
        body: payload.notification.body,
        icon: '/favicon.ico'
    };

    self.registration.showNotification(notificationTitle,
        notificationOptions);
});
