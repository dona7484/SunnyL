<?php
require_once __DIR__ . '/../config/database.php';

class SeniorModel {
    private $pdo;

    public function __construct() {
        $db = new DbConnect();
        $this->pdo = $db->getConnection();
    }

    public function getSeniorsForFamilyMember($familyMemberId) {
        try {
            $sql = "SELECT u.id as user_id, u.name FROM users u 
                    JOIN relations r ON u.id = r.senior_id 
                    WHERE r.family_id = :family_id AND u.role = 'senior'";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':family_id' => $familyMemberId]);
            
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $result;
        } catch (Exception $e) {
            error_log("Erreur dans getSeniorsForFamilyMember: " . $e->getMessage());
            return [];
        }
    }
}    
