// service-worker.js
const CACHE_NAME = 'sunnylink-cache-v1';
const urlsToCache = [
  '/',
  '/index.php',
  '/css/style.css',
  '/js/main.js',
  '/images/IconePhoto.png',
  '/images/iconeMusic.png',
  '/images/iconeMessage.png',
  '/images/IconeRappel.png',
  '/images/IconeSourdine.png',
  '/images/check-button.png',
  '/audio/notif-sound.mp3'
];

self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => {
        return cache.addAll(urlsToCache);
      })
  );
});

self.addEventListener('fetch', event => {
  event.respondWith(
    caches.match(event.request)
      .then(response => {
        if (response) {
          return response;
        }
        return fetch(event.request);
      })
  );
});

self.addEventListener('push', event => {
  const data = event.data.json();
  const options = {
    body: data.body,
    icon: '/images/IconeRappel.png',
    badge: '/images/IconeRappel.png',
    data: {
      url: data.url || '/'
    }
  };

  event.waitUntil(
    self.registration.showNotification(data.title, options)
  );
});

self.addEventListener('notificationclick', event => {
  event.notification.close();
  
  if (event.notification.data && event.notification.data.url) {
    event.waitUntil(
      clients.openWindow(event.notification.data.url)
    );
  }
});
