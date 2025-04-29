<?php
require_once __DIR__ . '/../config/database.php';

class RelationModel {
    private $pdo;
    
    public function __construct() {
        $db = new DbConnect();
        $this->pdo = $db->getConnection();
    }
    
    public function getSeniorsForFamilyMember($familyId) {
        try {
            $sql = "SELECT u.id as user_id, u.name FROM users u 
                    JOIN relations r ON u.id = r.senior_id 
                    WHERE r.family_id = :family_id AND u.role = 'senior'";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':family_id' => $familyId]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Erreur dans getSeniorsForFamilyMember: " . $e->getMessage());
            return [];
        }
    }
    
    public function addRelation($familyId, $seniorId) {
        try {
            $sql = "INSERT INTO relations (family_id, senior_id) VALUES (:family_id, :senior_id)";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                ':family_id' => $familyId,
                ':senior_id' => $seniorId
            ]);
        } catch (Exception $e) {
            error_log("Erreur dans addRelation: " . $e->getMessage());
            return false;
        }
    }
    
    public function removeRelation($familyId, $seniorId) {
        try {
            $sql = "DELETE FROM relations WHERE family_id = :family_id AND senior_id = :senior_id";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                ':family_id' => $familyId,
                ':senior_id' => $seniorId
            ]);
        } catch (Exception $e) {
            error_log("Erreur dans removeRelation: " . $e->getMessage());
            return false;
        }
    }
}
