<?php
/**
 * Contrôleur pour la gestion des messages
 * 
 * Ce fichier contient la classe MessageController qui gère toutes les 
 * fonctionnalités liées aux messages dans l'application SunnyLink :
 * envoi, réception, affichage et suppression de messages texte et audio
 * entre les seniors et les membres de leur famille.
 * 
 * @author Votre Nom
 * @version 1.0
 */

// Importation des dépendances
require_once __DIR__ . '/../config/database.php';        // Configuration de la base de données
require_once __DIR__ . '/../models/Message.php';         // Modèle pour les messages texte
require_once __DIR__ . '/../models/NotificationModel.php'; // Modèle pour les notifications
require_once __DIR__ . '/NotificationController.php';    // Contrôleur pour les notifications
require_once __DIR__ . '/../models/AudioMessage.php';    // Modèle pour les messages audio
require_once __DIR__ . '/../models/User.php';            // Modèle pour les utilisateurs

/**
 * Classe MessageController
 * 
 * Gère toutes les opérations liées aux messages et à la messagerie
 * Hérite de la classe Controller pour utiliser ses méthodes de rendu
 */
class MessageController extends Controller {
    
    /**
     * Récupère le contenu d'un message spécifique (texte ou audio)
     * 
     * Cette méthode est appelée via AJAX pour obtenir le contenu 
     * d'un message en fonction de son ID et de son type.
     * Elle retourne le contenu au format JSON.
     */
    public function getContent() {
        // Définir l'en-tête pour la réponse JSON
        header('Content-Type: application/json');
        
        try {
            // Récupérer l'ID du message depuis les paramètres GET
            $messageId = $_GET['id'] ?? null;
            
            // Vérifier si l'ID est présent
            if (!$messageId) {
                throw new Exception("ID de message manquant");
            }
            
            // Déterminer si c'est un message texte ou audio
            $db = (new DbConnect())->getConnection();
            
            // Vérifier d'abord si c'est un message texte
            $checkStmt = $db->prepare("SELECT COUNT(*) FROM messages WHERE id = ?");
            $checkStmt->execute([$messageId]);
            $isTextMessage = $checkStmt->fetchColumn() > 0;
            
            if ($isTextMessage) {
                // Récupérer le contenu d'un message texte
                $stmt = $db->prepare("SELECT message FROM messages WHERE id = ?");
                $stmt->execute([$messageId]);
                $content = $stmt->fetchColumn();
                
                // Retourner le contenu du message en JSON
                echo json_encode(['content' => $content]);
            } else {
                // Si ce n'est pas un message texte, vérifier si c'est un message audio
                $checkStmt = $db->prepare("SELECT COUNT(*) FROM audio_messages WHERE id = ?");
                $checkStmt->execute([$messageId]);
                $isAudioMessage = $checkStmt->fetchColumn() > 0;
                
                if ($isAudioMessage) {
                    // Pour les messages audio, retourner un texte descriptif
                    echo json_encode(['content' => "Un message audio a été reçu. Veuillez l'écouter."]);
                } else {
                    // Si ni texte ni audio, le message n'existe pas
                    throw new Exception("Message introuvable");
                }
            }
        } catch (Exception $e) {
            // En cas d'erreur, retourner un message d'erreur en JSON
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    /**
     * Gère l'envoi de messages texte
     * 
     * Cette méthode gère à la fois l'affichage du formulaire d'envoi de message
     * et le traitement de la soumission. Elle prend en charge les requêtes AJAX et standards.
     */
    public function send() {
        // Vérifier si le formulaire a été soumis (méthode POST)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Détecter si c'est une requête AJAX (JSON) ou un formulaire classique
            $isAjaxRequest = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                             strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
            
            try {
                // Récupérer les données de la requête selon son type
                if ($isAjaxRequest || $this->isJsonRequest()) {
                    // Pour les requêtes AJAX/JSON
                    $data = json_decode(file_get_contents('php://input'), true);
                    $senderId = $_SESSION['user_id'];
                    $receiverId = $data['receiver_id'] ?? null;
                    $messageText = $data['message'] ?? null;
                } else {
                    // Pour les formulaires classiques
                    $senderId = $_SESSION['user_id'];
                    $receiverId = $_POST['receiver_id'] ?? null;
                    $messageText = $_POST['message'] ?? null;
                }
                
                // Vérifier que l'utilisateur est authentifié
                if (!$senderId) {
                    throw new Exception("Utilisateur non authentifié");
                }
                
                // Vérifier que les données requises sont présentes
                if (!$receiverId || !$messageText) {
                    throw new Exception("Données de message incomplètes");
                }
                
                // Log pour le débogage
                error_log("Tentative d'envoi de message - De: $senderId, À: $receiverId, Message: $messageText");
                
                // Enregistrer le message dans la base de données
                $result = Message::save($senderId, $receiverId, $messageText);
                
                // Si l'enregistrement a réussi
                if ($result) {
                    // Récupérer le nom de l'expéditeur pour la notification
                    $userModel = new User();
                    $sender = $userModel->getById($senderId);
                    $senderName = $sender ? $sender['name'] : 'Utilisateur inconnu';
                    
                    // Créer une notification pour le destinataire
                    $notificationController = new NotificationController();
                    $notifId = $notificationController->sendNotification(
                        $receiverId,
                        'message',
                        'Nouveau message de ' . $senderName,
                        $result // ID du message comme relatedId
                    );
                    
                    error_log("Message envoyé avec succès - ID notification: $notifId");
                    
                    // Retourner une réponse selon le type de requête
                    if ($isAjaxRequest || $this->isJsonRequest()) {
                        // Réponse JSON pour AJAX
                        header('Content-Type: application/json');
                        echo json_encode([
                            'status' => 'success', 
                            'message' => 'Message envoyé avec succès',
                            'message_id' => $result,
                            'notification_id' => $notifId
                        ]);
                    } else {
                        // Redirection pour soumission de formulaire classique
                        $_SESSION['message_sent'] = true;
                        $_SESSION['success_message'] = 'Message envoyé avec succès';
                        header('Location: index.php?controller=message&action=sent');
                        exit;
                    }
                } else {
                    // En cas d'échec de l'enregistrement
                    error_log("Échec de l'envoi du message");
                    
                    if ($isAjaxRequest || $this->isJsonRequest()) {
                        header('Content-Type: application/json');
                        echo json_encode(['status' => 'error', 'message' => 'Échec de l\'envoi du message']);
                    } else {
                        $_SESSION['error_message'] = 'Échec de l\'envoi du message';
                        header('Location: index.php?controller=message&action=send');
                        exit;
                    }
                }
            } catch (Exception $e) {
                // En cas d'erreur dans le processus
                error_log("Erreur lors de l'envoi du message: " . $e->getMessage());
                
                if ($isAjaxRequest || $this->isJsonRequest()) {
                    header('Content-Type: application/json');
                    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
                } else {
                    $_SESSION['error_message'] = $e->getMessage();
                    header('Location: index.php?controller=message&action=send');
                    exit;
                }
            }
        } else {
            // Affichage du formulaire d'envoi de message (méthode GET)
            // Vérifier que l'utilisateur est connecté
            if (!isset($_SESSION['user_id'])) {
                header("Location: index.php?controller=auth&action=login");
                exit;
            }
            
            // Récupérer l'ID et le rôle de l'utilisateur
            $userId = $_SESSION['user_id'];
            $userRole = $_SESSION['role'] ?? 'famille';
            
            // Connexion à la base de données
            $db = new DbConnect();
            $pdo = $db->getConnection();
            
            // Récupérer la liste des destinataires possibles selon le rôle
            if ($userRole === 'famille' || $userRole === 'familymember') {
                // Si l'utilisateur est un membre de la famille, récupérer les seniors associés
                $stmt = $pdo->prepare("SELECT u.id as user_id, u.name FROM users u 
                            JOIN relations r ON u.id = r.senior_id 
                            WHERE r.family_id = :family_id");
                $stmt->execute([':family_id' => $userId]);
                $seniors = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                error_log("Seniors trouvés pour family member $userId: " . count($seniors));
            } else if ($userRole === 'senior') {
                // Si l'utilisateur est un senior, récupérer les membres de la famille associés
                $stmt = $pdo->prepare("SELECT u.id as user_id, u.name FROM users u 
                            JOIN relations r ON u.id = r.family_id 
                            WHERE r.senior_id = :senior_id");
                $stmt->execute([':senior_id' => $userId]);
                $seniors = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                error_log("Family members trouvés pour senior $userId: " . count($seniors));
            } else {
                $seniors = [];
            }
            
            // Rendre la vue avec les destinataires et les messages de succès/erreur
            $this->render('message/send', [
                'seniors' => $seniors,
                'userRole' => $userRole,
                'success_message' => $_SESSION['success_message'] ?? null,
                'error_message' => $_SESSION['error_message'] ?? null
            ]);
            
            // Nettoyer les messages de session après les avoir affichés
            unset($_SESSION['success_message']);
            unset($_SESSION['error_message']);
        }
    }
    
    /**
     * Affiche les messages envoyés par l'utilisateur
     * 
     * Cette méthode récupère et affiche tous les messages (texte et audio)
     * que l'utilisateur a envoyés aux autres utilisateurs.
     */
    public function sent() {
        // Démarrer la session si elle n'est pas déjà active
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Vérifier que l'utilisateur est connecté
        if (!isset($_SESSION['user_id'])) {
            header("Location: index.php?controller=auth&action=login");
            exit;
        }
        
        // Log pour le débogage
        error_log("Récupération des messages envoyés par l'utilisateur ID: " . $_SESSION['user_id']);
        
        // Récupérer les messages texte envoyés
        $messages = Message::getSentMessages($_SESSION['user_id']);
        
        // Récupérer les messages audio envoyés (si la classe existe)
        $audioMessages = [];
        if (class_exists('AudioMessage')) {
            $audioMessages = AudioMessage::getSentMessages($_SESSION['user_id']);
        }
        
        // Rendre la vue avec les messages et les données associées
        $this->render('message/sent', [
            'messages' => $messages,
            'audioMessages' => $audioMessages,
            'current_user' => $_SESSION['user_id'],
            'success_message' => $_SESSION['success_message'] ?? null
        ]);
        
        // Nettoyer les messages de session après les avoir affichés
        unset($_SESSION['success_message']);
    }
    
    /**
     * Gère l'envoi de messages audio
     * 
     * Cette méthode traite l'envoi de messages audio depuis le formulaire d'envoi
     * ou via AJAX. Elle enregistre l'audio et crée une notification pour le destinataire.
     */
 public function sendAudio() {
    // Définir le header content-type au tout début
    header('Content-Type: application/json');
    
    // Désactiver l'affichage des erreurs pour éviter de corrompre le JSON
    ini_set('display_errors', 0);
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        try {
            // Récupérer les données JSON
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($_SESSION['user_id'])) {
                throw new Exception("Utilisateur non authentifié");
            }
            
            $senderId = $_SESSION['user_id'];
            $receiverId = $data['receiver_id'] ?? null;
            $audioData = $data['audio_data'] ?? null;
            
            if (!$receiverId || !$audioData) {
                throw new Exception("Données audio incomplètes");
            }
            
            // Log pour le débogage (à un fichier plutôt que sur la sortie standard)
            error_log("Tentative d'envoi de message audio - De: $senderId, À: $receiverId");
            
            // Sauvegarder l'audio
            if (class_exists('AudioMessage')) {
                $messageId = AudioMessage::save($senderId, $receiverId, $audioData);
            } else {
                $messageId = Message::saveAudio($senderId, $receiverId, $audioData);
            }
            
            if ($messageId) {
                // Créer la notification
                $userModel = new User();
                $sender = $userModel->getById($senderId);
                $senderName = $sender ? $sender['name'] : 'Utilisateur';
                
                $notificationController = new NotificationController();
                $notifId = $notificationController->sendNotification(
                    $receiverId,
                    'audio',
                    'Nouveau message audio de ' . $senderName,
                    $messageId
                );
                
                echo json_encode([
                    'status' => 'success', 
                    'message' => 'Message audio envoyé avec succès',
                    'message_id' => $messageId,
                    'notification_id' => $notifId
                ]);
            } else {
                echo json_encode([
                    'status' => 'error', 
                    'message' => 'Échec de l\'enregistrement du message audio'
                ]);
            }
        } catch (Exception $e) {
            echo json_encode([
                'status' => 'error', 
                'message' => $e->getMessage()
            ]);
        }
    } else {
        echo json_encode([
            'status' => 'error', 
            'message' => 'Méthode non autorisée, utilisez POST'
        ]);
    }
    
    // Sortir pour éviter toute sortie supplémentaire
    exit;
}

    /**
     * Supprime un message audio
     * 
     * Cette méthode permet à l'utilisateur de supprimer un message audio
     * qu'il a envoyé, après vérification des autorisations.
     */
    public function deleteAudio() {
        // Démarrer la session si elle n'est pas déjà active
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Vérifier que l'utilisateur est connecté
        if (!isset($_SESSION['user_id'])) {
            header("Location: index.php?controller=auth&action=login");
            exit;
        }
        
        // Récupérer l'ID du message audio à supprimer
        $id = isset($_GET['id']) ? intval($_GET['id']) : null;
        
        // Vérifier que l'ID est présent
        if (!$id) {
            $_SESSION['error_message'] = "ID de message audio manquant.";
            header('Location: index.php?controller=message&action=sent');
            exit;
        }
        
        try {
            // Vérifier que le message existe et appartient à l'utilisateur courant
            require_once __DIR__ . '/../models/AudioMessage.php';
            $message = AudioMessage::getById($id);
            
            if (!$message) {
                throw new Exception("Message audio introuvable.");
            }
            
            // Vérifier que l'utilisateur est bien l'expéditeur du message
            if ($message->sender_id != $_SESSION['user_id']) {
                throw new Exception("Vous n'êtes pas autorisé à supprimer ce message.");
            }
            
            // Supprimer le message
            AudioMessage::delete($id);
            
            // Rediriger avec un message de succès
            $_SESSION['success_message'] = "Message audio supprimé avec succès.";
            header('Location: index.php?controller=message&action=sent');
            exit;
        } catch (Exception $e) {
            // En cas d'erreur, rediriger avec un message d'erreur
            $_SESSION['error_message'] = $e->getMessage();
            header('Location: index.php?controller=message&action=sent');
            exit;
        }
    }

    /**
     * Marque un message comme lu et envoie une notification de lecture
     * 
     * Cette méthode est appelée via AJAX lorsqu'un utilisateur lit un message.
     * Elle met à jour le statut du message et notifie l'expéditeur.
     */
    public function markAsRead() {
        // Définir l'en-tête pour la réponse JSON
        header('Content-Type: application/json');
        
        try {
            // Récupérer les données JSON de la requête
            $data = json_decode(file_get_contents('php://input'), true);
            $messageId = $data['message_id'] ?? null;

            // Vérifier que l'ID est valide
            if (!$messageId || !is_numeric($messageId)) {
                throw new Exception("ID de message invalide");
            }

            // Connexion à la base de données
            $db = (new DbConnect())->getConnection();
            
            // Récupérer l'expéditeur du message
            $stmt = $db->prepare("SELECT sender_id FROM messages WHERE id = ?");
            $stmt->execute([$messageId]);
            $senderId = $stmt->fetchColumn();

            // Vérifier que le message existe
            if (!$senderId) {
                throw new Exception("Message introuvable");
            }

            // Marquer le message comme lu dans la base de données
            $stmt = $db->prepare("UPDATE messages SET is_read = 1 WHERE id = ?");
            $stmt->execute([$messageId]);

            // Envoyer une notification de lecture à l'expéditeur
            $notifController = new NotificationController();
            $notifId = $notifController->sendNotification(
                $senderId,
                'read_confirmation',
                "Votre message a été lu",
                $messageId,
                true
            );

            // Retourner une réponse de succès
            echo json_encode([
                'success' => true, 
                'message' => 'Message marqué comme lu',
                'notification_id' => $notifId
            ]);

        } catch (Exception $e) {
            // En cas d'erreur, retourner un code d'erreur 400 et un message
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    /**
     * Affiche les messages reçus par l'utilisateur
     * 
     * Cette méthode récupère et affiche tous les messages (texte et audio)
     * que l'utilisateur a reçus des autres utilisateurs.
     */
    public function received() {
        // Démarrer la session si elle n'est pas déjà active
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Vérifier que l'utilisateur est connecté
        if (!isset($_SESSION['user_id'])) {
            header("Location: index.php?controller=auth&action=login");
            exit;
        }
        
        // Log pour le débogage
        error_log("Récupération des messages pour l'utilisateur ID: " . $_SESSION['user_id']);
        
        // Récupérer les messages texte reçus
        $messages = Message::getReceivedMessages($_SESSION['user_id']);
        
        // Récupérer les messages audio reçus (si la classe existe)
        $audioMessages = [];
        if (class_exists('AudioMessage')) {
            $audioMessages = AudioMessage::getReceivedMessages($_SESSION['user_id']);
        }
        
        // Log pour le débogage
        error_log("Nombre de messages texte trouvés: " . count($messages));
        error_log("Nombre de messages audio trouvés: " . (isset($audioMessages) ? count($audioMessages) : 0));
        
        // Rendre la vue avec les messages
        $this->render('message/received', [
            'messages' => $messages,
            'audioMessages' => $audioMessages,
            'current_user' => $_SESSION['user_id']
        ]);
    }
    
    /**
     * Vérifie si la requête est au format JSON
     * 
     * Cette méthode utilitaire détecte si la requête entrante
     * est de type JSON en vérifiant son en-tête Content-Type.
     * 
     * @return bool Vrai si la requête est de type JSON, faux sinon
     */
    private function isJsonRequest() {
        return (isset($_SERVER['CONTENT_TYPE']) && 
                (strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false));
    }

    /**
     * Supprime un message texte
     * 
     * Cette méthode permet à l'utilisateur de supprimer un message texte
     * qu'il a envoyé ou reçu.
     * 
     * @param int $id L'identifiant du message à supprimer
     */
    public function delete($id) {
        // Démarrer la session si elle n'est pas déjà active
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Vérifier que l'utilisateur est connecté
        if (!isset($_SESSION['user_id'])) {
            header("Location: index.php?controller=auth&action=login");
            exit;
        }
    
        // Supprimer le message (pas de vérification d'autorisations supplémentaires)
        Message::delete($id);
    
        // Rediriger avec un message de succès
        $_SESSION['success_message'] = "Message supprimé avec succès.";
        header('Location: index.php?controller=message&action=received');
        exit;
    }
    
    /**
     * Vérifie que l'utilisateur est authentifié
     * 
     * Cette méthode utilitaire vérifie si un utilisateur est connecté
     * et redirige vers la page de connexion si ce n'est pas le cas.
     */
    private function checkAuthentication() {
        // Démarrer la session si elle n'est pas déjà active
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Vérifier si l'utilisateur est connecté
        if (!isset($_SESSION['user_id'])) {
            header("Location: index.php?controller=auth&action=login");
            exit;
        }
    }
}