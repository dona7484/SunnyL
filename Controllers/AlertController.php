<?php
require_once __DIR__ . '/../models/EventModel.php';

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

        // DEBUG
        error_log("🧪 ALERTE - user_id = $userId | NOW = $now");
        error_log("🧪 Résultat SQL : " . json_encode($alert));

        if ($alert) {
            $model->markAlertAsTriggered($alert['id']);
            echo json_encode([
                'should_alert' => true,
                'message' => $alert['notification_message'],
                'id' => $alert['id']
            ]);
        } else {
            echo json_encode(['should_alert' => false]);
        }
    }
}
