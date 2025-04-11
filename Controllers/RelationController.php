<?php
require_once __DIR__ . '/../config/database.php';        
// require_once __DIR__ . '/../models/RelationModel.php';
class RelationController extends Controller {
    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $familyId = $_SESSION['user_id'];
            $seniorId = $_POST['senior_id'];
            
            $dbConnect = new DbConnect();
            $db = $dbConnect->getConnection();
            $stmt = $db->prepare("INSERT INTO relations (family_id, senior_id) VALUES (?, ?)");
            $result = $stmt->execute([$familyId, $seniorId]);
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Relation établie avec succès']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Échec de l\'établissement de la relation']);
            }
        } else {
            // Récupérer tous les seniors disponibles
            $dbConnect = new DbConnect();
            $db = $dbConnect->getConnection();
            $stmt = $db->prepare("SELECT id, name FROM users WHERE role = 'senior'");
            $stmt->execute();
            $seniors = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $this->render('relation/create', ['seniors' => $seniors]);
        }
    }
}
