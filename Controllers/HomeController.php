<?php
require_once __DIR__ . '/../config/database.php';

class HomeController extends Controller {
    // Action pour afficher la page d'accueil
    public function index() {
        $this->render('home/index');
    }

    // Action pour afficher les notifications
    public function dashboardNotifs() {
        $this->render('home/notifications');
    }

    // Action pour afficher les alertes
    public function alert() {
        $this->render('home/alert');
    }

    // Action pour afficher l'alerte d'événement
    public function eventAlert() {
        $this->render('home/event_alert');
    }

    // Action pour afficher le tableau de bord du senior ou du membre de la famille
    public function dashboard() {
        // Vérifier si l'utilisateur est connecté
        if (!isset($_SESSION['user_id'])) {
            header("Location: index.php?controller=auth&action=login");
            exit;
        }
        
        $role = $_SESSION['role'] ?? 'familymember';
        $notifications = [];
    
        // Récupérer les notifications si l'utilisateur est connecté
        if ($role === 'senior' || $role === 'familymember') {
            $notificationModel = new NotificationModel();
            $notifications = $notificationModel->getUnreadNotifications($_SESSION['user_id']);
        }
    
        // Si l'utilisateur est un senior, récupérer les membres de la famille
        if ($role === 'senior') {
            $familyMembers = $this->getFamilyMembersForSenior($_SESSION['user_id']);
            // Affichage du tableau de bord avec notifications et membres de la famille
            $this->render('home/dashboard', [
                'role' => $role,
                'familyMembers' => $familyMembers,
                'notifications' => $notifications
            ]);
        } else {
            // Si l'utilisateur est un membre de la famille
            $this->render('home/dashboard', [
                'role' => $role,
                'notifications' => $notifications
            ]);
        }
    }

    // Action pour afficher le tableau de bord du membre de la famille
    public function family_dashboard() {
        // Vérifier si l'utilisateur est connecté
        if (!isset($_SESSION['user_id'])) {
            header("Location: index.php?controller=auth&action=login");
            exit;
        }
        
        $role = $_SESSION['role'] ?? 'familymember';
        $notifications = [];
    
        // Récupérer les notifications si l'utilisateur est connecté
        if ($role === 'senior' || $role === 'familymember') {
            $notificationModel = new NotificationModel();
            $notifications = $notificationModel->getUnreadNotifications($_SESSION['user_id']);
        }

        // Rendu du tableau de bord du membre de la famille
        $this->render('home/family_dashboard', [
            'role' => $role,
            'notifications' => $notifications
        ]);
    }

    // Méthode pour récupérer les membres de la famille associés au senior
    private function getFamilyMembersForSenior($seniorId) {
        $dbConnect = new DbConnect();
        $db = $dbConnect->getConnection();
        $stmt = $db->prepare("SELECT u.* FROM users u
                              JOIN relations r ON u.id = r.family_id
                              WHERE r.senior_id = ?");
        $stmt->execute([$seniorId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
