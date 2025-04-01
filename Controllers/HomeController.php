<?php
require_once __DIR__ . '/../config/database.php';

class HomeController extends Controller {
    public function index() {
        $this->render('home/index');
    }

    public function dashboardNotifs() {
        $this->render('home/notifications');
    }
    public function alert() {
        $this->render('home/alert');
    }

    public function eventAlert() {
        $this->render('home/event_alert');
    }
    
    public function dashboard() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: index.php?controller=auth&action=login");
            exit;
        }
        $role = $_SESSION['role'] ?? 'familymember';
        if ($role === 'senior') {
            // Par exemple, récupérer la liste des family members associés au senior
            $familyMembers = $this->getFamilyMembersForSenior($_SESSION['user_id']);
            $this->render('home/dashboard', ['role' => $role, 'familyMembers' => $familyMembers]);
        } else {
            $this->render('home/dashboard', ['role' => $role]);
        }
    }

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
