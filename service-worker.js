// service-worker.js
const CACHE_NAME = 'sunnylink-cache-v1';
const urlsToCache = [
  '/sunnylink/',
  '/sunnylink/index.php',
  '/sunnylink/css/style.css',
  '/sunnylink/js/main.js',
  '/sunnylink/images/IconePhoto.png',
  '/sunnylink/images/iconeMusic.png',
  '/sunnylink/images/iconeMessage.png',
  '/sunnylink/images/IconeRappel.png',
  '/sunnylink/images/IconeSourdine.png',
  '/sunnylink/images/check-button.png',
  '/sunnylink/audio/notif-sound.mp3'
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
