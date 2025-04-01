<?php
require_once __DIR__ . '/../models/EventModel.php';
require_once __DIR__ . '/../models/Notification.php';

class AlertController {
    public function check() {
        header('Content-Type: application/json');

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $userId = $_SESSION['user_id'];
        $now = date('Y-m-d H:i:s');

        $model = new EventModel();
        $alert = $model->getAlertForTime($userId, $now);

        if ($alert) {
            $model->markAlertAsTriggered($alert['id']);
            echo json_encode([
                'should_alert' => true,
                'type' => 'event',
                'message' => $alert['notification_message'],
                'id' => $alert['id']
            ]);
            return;
        }

        // 🔔 Vérification d'autres notifications (photo, message)
        $notifs = Notification::getUnseenByUser($userId);
        if (!empty($notifs)) {
            $notif = $notifs[0];
            Notification::markAsSeen($notif['id']);
            echo json_encode([
                'should_alert' => true,
                'type' => $notif['type'],
                'message' => $notif['content'],
                'id' => $notif['id']
            ]);
            return;
        }

        echo json_encode(['should_alert' => false]);
    }
}
