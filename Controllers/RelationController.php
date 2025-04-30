<?php
require_once __DIR__ . '/../config/database.php';

// Vérifier si le fichier RelationModel existe avant de l'inclure
$relationModelPath = __DIR__ . '/../models/RelationModel.php';
if (file_exists($relationModelPath)) {
    require_once $relationModelPath;
}

class RelationController extends Controller {
    public function create() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?controller=auth&action=login');
            exit;
        }
        
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
    
    public function store() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?controller=auth&action=login');
            exit;
        }
        
        $familyId = $_SESSION['user_id'];
        $seniorId = $_POST['senior_id'] ?? null;
        
        if (!$seniorId) {
            $_SESSION['error_message'] = "Veuillez sélectionner un senior.";
            header('Location: index.php?controller=relation&action=create');
            exit;
        }
        
        $success = false;
        
        // Utiliser le modèle RelationModel si la classe existe
        if (class_exists('RelationModel')) {
            $relationModel = new RelationModel();
            $success = $relationModel->addRelation($familyId, $seniorId);
        } else {
            // Sinon, utiliser une requête directe
            try {
                $dbConnect = new DbConnect();
                $db = $dbConnect->getConnection();
                
                // Vérifier si la relation existe déjà
                $checkStmt = $db->prepare("SELECT COUNT(*) FROM relations WHERE family_id = ? AND senior_id = ?");
                $checkStmt->execute([$familyId, $seniorId]);
                
                if ($checkStmt->fetchColumn() > 0) {
                    // La relation existe déjà, on considère que c'est un succès
                    $success = true;
                } else {
                    // Ajouter la nouvelle relation
                    $stmt = $db->prepare("INSERT INTO relations (family_id, senior_id) VALUES (?, ?)");
                    $success = $stmt->execute([$familyId, $seniorId]);
                }
            } catch (Exception $e) {
                error_log("Erreur lors de l'ajout de la relation : " . $e->getMessage());
                $success = false;
            }
        }
        
        if ($success) {
            $_SESSION['success_message'] = "Relation établie avec succès avec le senior.";
            header('Location: index.php?controller=home&action=family_dashboard');
        } else {
            $_SESSION['error_message'] = "Échec de l'établissement de la relation. Veuillez réessayer.";
            header('Location: index.php?controller=relation&action=create');
        }
        exit;
    }
    
    public function index() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?controller=auth&action=login');
            exit;
        }
        
        $familyId = $_SESSION['user_id'];
        $seniors = [];
        
        // Utiliser le modèle RelationModel si la classe existe
        if (class_exists('RelationModel')) {
            $relationModel = new RelationModel();
            $seniors = $relationModel->getSeniorsForFamilyMember($familyId);
        } else {
            // Sinon, utiliser une requête directe
            try {
                $dbConnect = new DbConnect();
                $db = $dbConnect->getConnection();
                $stmt = $db->prepare("
                    SELECT u.id as user_id, u.name 
                    FROM users u 
                    JOIN relations r ON u.id = r.senior_id 
                    WHERE r.family_id = ? AND u.role = 'senior'
                ");
                $stmt->execute([$familyId]);
                $seniors = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (Exception $e) {
                error_log("Erreur lors de la récupération des seniors : " . $e->getMessage());
            }
        }
        
        $this->render('relation/index', ['seniors' => $seniors]);
    }
    
    public function delete() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?controller=auth&action=login');
            exit;
        }
        
        $familyId = $_SESSION['user_id'];
        $seniorId = $_GET['id'] ?? null;
        
        if (!$seniorId) {
            $_SESSION['error_message'] = "Senior non spécifié.";
            header('Location: index.php?controller=relation&action=index');
            exit;
        }
        
        $success = false;
        
        // Utiliser le modèle RelationModel si la classe existe
        if (class_exists('RelationModel')) {
            $relationModel = new RelationModel();
            $success = $relationModel->removeRelation($familyId, $seniorId);
        } else {
            // Sinon, utiliser une requête directe
            try {
                $dbConnect = new DbConnect();
                $db = $dbConnect->getConnection();
                $stmt = $db->prepare("DELETE FROM relations WHERE family_id = ? AND senior_id = ?");
                $success = $stmt->execute([$familyId, $seniorId]);
            } catch (Exception $e) {
                error_log("Erreur lors de la suppression de la relation : " . $e->getMessage());
                $success = false;
            }
        }
        
        if ($success) {
            $_SESSION['success_message'] = "Relation supprimée avec succès.";
        } else {
            $_SESSION['error_message'] = "Échec de la suppression de la relation.";
        }
        
        header('Location: index.php?controller=relation&action=index');
        exit;
    }
}