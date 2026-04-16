import { initializeApp } from "firebase/app";
import { getMessaging, getToken, onMessage } from "firebase/messaging";
import { usePage, router } from "@inertiajs/vue3";

const initializeFCM = async () => {
    const page = usePage();
    const config = page.props.firebase_config as any;

    if (!config || !config.apiKey) {
        console.warn("Firebase not configured.");
        return;
    }

    const app = initializeApp(config);
    const messaging = getMessaging(app);

    try {
        const currentToken = await getToken(messaging, {
            vapidKey: "H7EDb05W0-Y_u4QsBk0kku1AbftpyByhQkTJAckUOmk"
        });

        if (currentToken) {
            // Send token to backend
            router.post(route('fcm.token'), {
                token: currentToken
            }, {
                preserveScroll: true,
                onSuccess: () => console.log("FCM Token updated successfully"),
            });
        } else {
            console.log("No registration token available. Request permission to generate one.");
        }
    } catch (err) {
        console.log("An error occurred while retrieving token. ", err);
    }

    onMessage(messaging, (payload) => {
        console.log("Message received. ", payload);
        // We can show a toast here or rely on AuthenticatedLayout's existing toast system
        if (payload.notification) {
            // Custom toast event or handling
            const event = new CustomEvent('fcm-notification', { detail: payload.notification });
            window.dispatchEvent(event);
        }
    });
};

export { initializeFCM };
