/* global self */
importScripts('/fcm-sw-config.js');
importScripts('https://www.gstatic.com/firebasejs/10.13.2/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/10.13.2/firebase-messaging-compat.js');

self.addEventListener('install', () => self.skipWaiting());
self.addEventListener('activate', (event) => event.waitUntil(self.clients.claim()));

let appInitialized = false;
let firebaseConfig = {
  apiKey: self.FIREBASE_API_KEY || null,
  authDomain: self.FIREBASE_AUTH_DOMAIN || null,
  projectId: self.FIREBASE_PROJECT_ID || null,
  storageBucket: self.FIREBASE_STORAGE_BUCKET || null,
  messagingSenderId: self.FIREBASE_MESSAGING_SENDER_ID || null,
  appId: self.FIREBASE_APP_ID || null,
  measurementId: self.FIREBASE_MEASUREMENT_ID || null,
};

function tryInit() {
  if (appInitialized) return;
  if (!firebaseConfig || !firebaseConfig.apiKey || !firebaseConfig.messagingSenderId || !firebaseConfig.appId) return;
  try {
    firebase.initializeApp(firebaseConfig);
    const messaging = firebase.messaging();

    messaging.onBackgroundMessage((payload) => {
      const title = (payload && payload.notification && payload.notification.title) ? payload.notification.title : 'Notification';
      const options = {
        body: (payload && payload.notification && payload.notification.body) ? payload.notification.body : '',
        icon: '/favicon.ico',
        data: { url: (payload && payload.data && payload.data.url) ? payload.data.url : '/' },
      };
      self.registration.showNotification(title, options);
    });
    appInitialized = true;
  } catch (e) {
    // no-op
  }
}

try {
  tryInit();

  self.addEventListener('notificationclick', function(event) {
    event.notification.close();
    const url = (event && event.notification && event.notification.data && event.notification.data.url) ? event.notification.data.url : '/';
    event.waitUntil(
      self.clients.matchAll({ type: 'window', includeUncontrolled: true }).then((clientList) => {
        for (const client of clientList) {
          if ('focus' in client) {
            client.focus();
            client.postMessage({ type: 'FCM_NOTIFICATION_CLICKED', url });
            return;
          }
        }
        if (self.clients.openWindow) {
          return self.clients.openWindow(url);
        }
      })
    );
  });
  // Accept runtime config from page
  self.addEventListener('message', (event) => {
    if (event.data && event.data.type === 'SET_CONFIG') {
      firebaseConfig = Object.assign({}, firebaseConfig, event.data.config || {});
      tryInit();
    }
  });
} catch (e) {
  // no-op to avoid SW crash
}


