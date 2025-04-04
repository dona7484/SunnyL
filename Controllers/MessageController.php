<?php
class MessageController extends Controller {

    // Afficher la page de messagerie pour envoyer un message
    public function send() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['user_id'])) {
            header("Location: index.php?controller=auth&action=login");
            exit;
        }

        // Récupérer les données envoyées par le formulaire
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $senderId = $_SESSION['user_id'];
            $receiverId = $_POST['receiver_id'];
            $messageText = $_POST['message'];

            // Appeler le modèle pour sauvegarder le message
            $messageSaved = Message::save($senderId, $receiverId, $messageText);

            if ($messageSaved) {
                echo json_encode([
                    "status" => "ok",
                    "message" => "Message envoyé avec succès."
                ]);
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => "Erreur lors de l'envoi du message."
                ]);
            }
        } else {
            // Afficher le formulaire pour envoyer un message
            $this->render('message/send');
        }
    }

    // Afficher les messages reçus
    public function received() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['user_id'])) {
            header("Location: index.php?controller=auth&action=login");
            exit;
        }

        $userId = $_SESSION['user_id'];
        // Récupérer les messages reçus pour cet utilisateur
        $messages = Message::getReceivedMessages($userId);

        $this->render('message/received', ['messages' => $messages]);
    }
}
?>
