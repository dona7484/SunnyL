self.addEventListener('push', function(event) {
    const data = event.data.json();
    
    const options = {
        body: data.body,
        icon: data.icon || '/assets/images/logo.png',
        image: data.image,
        badge: '/assets/images/badge.png',
        vibrate: [100, 50, 100],
        data: {
            url: data.url,
            type: data.type,
            id: data.id
        },
        actions: [
            {
                action: 'check',
                title: 'Consulter',
                icon: '/assets/images/check.png'
            }
        ]
    };
    
    event.waitUntil(
        self.registration.showNotification(data.title, options)
    );
});
// Dans service-worker.js (si vous l'avez)
self.addEventListener('notificationclick', function(event) {
    const notification = event.notification;
    const data = notification.data;
    
    let url = '/SunnyLink/public/index.php?controller=home&action=dashboard';
    
    if (data && data.type) {
      if (data.type === 'message' || data.type === 'audio') {
        url = '/SunnyLink/public/index.php?controller=message&action=received';
      } else if (data.type === 'photo') {
        url = '/SunnyLink/public/index.php?controller=photo&action=gallery';
      } else if (data.type === 'event') {
        url = '/SunnyLink/public/index.php?controller=event&action=index';
      }
    }
    
    event.waitUntil(clients.openWindow(url));
    notification.close();
  });
  
self.addEventListener('notificationclick', function(event) {
    event.notification.close();
    
    if (event.action === 'check') {
        // Marquer comme lu et rediriger
        const markAsReadPromise = fetch('/index.php?controller=notification&action=markAsRead&id=' + event.notification.data.id, {
            method: 'POST',
            credentials: 'include'
        });
        
        event.waitUntil(
            Promise.all([
                markAsReadPromise,
                clients.openWindow(event.notification.data.url)
            ])
        );
    } else {
        // Comportement par défaut si on clique ailleurs sur la notification
        event.waitUntil(
            clients.openWindow(event.notification.data.url)
        );
    }
});
