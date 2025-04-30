<?php
require_once __DIR__ . '/../config/database.php';

class RelationModel {
    private $pdo;
    
    public function __construct() {
        $db = new DbConnect();
        $this->pdo = $db->getConnection();
    }
    
    /**
     * Récupère tous les seniors associés à un membre de la famille
     * 
     * @param int $familyId ID du membre de la famille
     * @return array Liste des seniors associés
     */
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
    
    /**
     * Ajoute une relation entre un membre de la famille et un senior
     * 
     * @param int $familyId ID du membre de la famille
     * @param int $seniorId ID du senior
     * @return bool Succès ou échec de l'opération
     */
    public function addRelation($familyId, $seniorId) {
        try {
            // Vérifier si la relation existe déjà
            $checkSql = "SELECT COUNT(*) FROM relations WHERE family_id = :family_id AND senior_id = :senior_id";
            $checkStmt = $this->pdo->prepare($checkSql);
            $checkStmt->execute([
                ':family_id' => $familyId,
                ':senior_id' => $seniorId
            ]);
            
            if ($checkStmt->fetchColumn() > 0) {
                // La relation existe déjà, on considère que c'est un succès
                return true;
            }
            
            // Ajouter la nouvelle relation
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
    
    /**
     * Supprime une relation entre un membre de la famille et un senior
     * 
     * @param int $familyId ID du membre de la famille
     * @param int $seniorId ID du senior
     * @return bool Succès ou échec de l'opération
     */
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
    
    /**
     * Récupère tous les membres de la famille associés à un senior
     * 
     * @param int $seniorId ID du senior
     * @return array Liste des membres de la famille
     */
    public function getFamilyMembersForSenior($seniorId) {
        try {
            $sql = "SELECT u.id as user_id, u.name, u.email FROM users u 
                    JOIN relations r ON u.id = r.family_id 
                    WHERE r.senior_id = :senior_id AND u.role = 'famille'";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':senior_id' => $seniorId]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Erreur dans getFamilyMembersForSenior: " . $e->getMessage());
            return [];
        }
    }
}