import { initializeApp } from 'firebase/app';
import { getMessaging, getToken, onMessage, isSupported } from 'firebase/messaging';

// Resolve Firebase config from env or window.FIREBASE_PUBLIC_CONFIG
const firebaseConfig = (() => {
    const fromEnv = {
        apiKey: import.meta.env.VITE_FIREBASE_API_KEY,
        authDomain: import.meta.env.VITE_FIREBASE_AUTH_DOMAIN,
        projectId: import.meta.env.VITE_FIREBASE_PROJECT_ID,
        storageBucket: import.meta.env.VITE_FIREBASE_STORAGE_BUCKET,
        messagingSenderId: import.meta.env.VITE_FIREBASE_MESSAGING_SENDER_ID,
        appId: import.meta.env.VITE_FIREBASE_APP_ID,
        measurementId: import.meta.env.VITE_FIREBASE_MEASUREMENT_ID,
    };
    const fromWindow = (typeof window !== 'undefined' && window.FIREBASE_PUBLIC_CONFIG) ? window.FIREBASE_PUBLIC_CONFIG : {};
    // env has priority; fallback to window
    return Object.assign({}, fromWindow, fromEnv);
})();

async function registerServiceWorker() {
    if (!('serviceWorker' in navigator)) return null;
    try {
        const reg = await navigator.serviceWorker.register('/sijil-fcm-sw.js');
        // Send runtime config to SW (so it can initialize messaging for background notifications)
        const cfg = {
            apiKey: firebaseConfig.apiKey,
            authDomain: firebaseConfig.authDomain,
            projectId: firebaseConfig.projectId,
            storageBucket: firebaseConfig.storageBucket,
            messagingSenderId: firebaseConfig.messagingSenderId,
            appId: firebaseConfig.appId,
            measurementId: firebaseConfig.measurementId,
        };
        const sendConfig = (sw) => { if (sw && sw.postMessage) { sw.postMessage({ type: 'SET_CONFIG', config: cfg }); } };
        if (reg.active) {
            sendConfig(reg.active);
        }
        navigator.serviceWorker.ready.then(r => {
            sendConfig(r.active);
        });
        return reg;
    } catch (e) {
        console.error('[FCM] SW registration failed', e);
        return null;
    }
}

async function requestPermission() {
    try {
        const result = await Notification.requestPermission();
        return result === 'granted';
    } catch (e) {
        console.error('[FCM] Permission request error', e);
        return false;
    }
}

async function sendTokenToServer(token) {
    try {
        await fetch('/fcm/register', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({ token }),
        });
    } catch (e) {
        console.error('[FCM] Failed to register token', e);
    }
}

function playBellSound() {
    try {
        const audio = new Audio('/sounds/notification.mp3');
        audio.play().catch(() => {});
    } catch (_) {}
}

(async () => {
    // Skip FCM on pages without the bell (e.g., login page)
    if (!document.getElementById('notification-container')) {
        return;
    }
    try {
        if (!(await isSupported())) {
            return;
        }

        if (!firebaseConfig || !firebaseConfig.apiKey || !firebaseConfig.messagingSenderId || !firebaseConfig.appId) {
            console.error('[FCM] Missing Firebase web config. Set VITE_FIREBASE_* envs or window.FIREBASE_PUBLIC_CONFIG.');
            return;
        }

        const app = initializeApp(firebaseConfig);
        const swReg = await registerServiceWorker();
        if (!swReg) return;

        const granted = await requestPermission();
        if (!granted) return;

        const messaging = getMessaging(app);
        const vapidKey = import.meta.env.VITE_FIREBASE_VAPID_KEY || (firebaseConfig && firebaseConfig.vapidKey ? firebaseConfig.vapidKey : undefined);
        const opts = { serviceWorkerRegistration: swReg };
        if (vapidKey) opts.vapidKey = vapidKey;
        const token = await getToken(messaging, opts);
        if (token) {
            await sendTokenToServer(token);
        }

        onMessage(messaging, (payload) => {
            // Update the in-app bell list if present
            const container = document.getElementById('notification-container');
            if (container && container.__x) {
                const data = container.__x.$data;
                const notification = {
                    id: 'fcm_' + Date.now(),
                    title: (payload && payload.notification && payload.notification.title) ? payload.notification.title : 'Notification',
                    message: (payload && payload.notification && payload.notification.body) ? payload.notification.body : '',
                    icon: 'notifications',
                    read_at: null,
                    time: 'Just now',
                    url: (payload && payload.data && payload.data.url) ? payload.data.url : '#'
                };
                data.notifications.unshift(notification);
                data.unreadCount++;
                if (data.notifications.length > 20) data.notifications.pop();
            }
            playBellSound();

            // If this is a helpdesk message and current page is the ticket, refresh
            try {
                const type = (payload && payload.data) ? payload.data.type : undefined;
                const ticketId = (payload && payload.data) ? payload.data.ticket_id : undefined;
                if (type === 'helpdesk_message' && ticketId) {
                    const match = window.location.pathname.match(/^\/helpdesk\/(\d+)/);
                    if (match && match[1] === String(ticketId)) {
                        // Jangan reload. Dispatch event supaya UI boleh append mesej tanpa hilangkan teks yang ditaip
                        window.dispatchEvent(new CustomEvent('helpdesk:new-message', { detail: { ticketId } }));
                    }
                }
            } catch (_) {}
        });
    } catch (e) {
        console.error('[FCM] Init error', e);
    }
})();


