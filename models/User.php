<?php
require_once __DIR__ . '/../config/database.php';

class User {

    /**
     * Récupère un utilisateur par son email.
     *
     * @param string $email
     * @return array|false Retourne un tableau associatif contenant les données de l'utilisateur ou false si non trouvé.
     */
    public static function getByEmail($email) {
        $dbConnect = new DbConnect();
        $db = $dbConnect->getConnection();
        $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère un utilisateur par son ID.
     *
     * @param int $id
     * @return array|false Retourne les données de l'utilisateur ou false si non trouvé.
     */
    public static function getById($id) {
        $dbConnect = new DbConnect();
        $db = $dbConnect->getConnection();
        $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Inscrit un nouvel utilisateur.
     *
     * @param string $name
     * @param string $email
     * @param string $password En clair (sera haché)
     * @param string $role Par défaut 'famille' (peut être 'senior' selon le cas)
     * @return bool Retourne true si l'inscription réussit, false si l'email est déjà utilisé ou en cas d'erreur.
     */
    public static function register($name, $email, $password, $role = 'famille') {
        $dbConnect = new DbConnect();
        $db = $dbConnect->getConnection();
        // Vérifier si l'email existe déjà
        $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->rowCount() > 0) {
            return false;
        }
        
        if ($role === 'familymember') {
            $role = 'famille';
        }
        
        if ($role !== 'famille' && $role !== 'senior') {
            $role = 'famille'; // Valeur par défaut si le rôle est invalide
        }
        
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $db->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$name, $email, $hashedPassword, $role]);
    }

    /**
     * Met à jour le profil de l'utilisateur.
     *
     * @param int $id
     * @param string $name
     * @param string $email
     * @param string|null $password Si fourni, le mot de passe sera mis à jour
     * @param string|null $avatar Nom du fichier avatar (optionnel)
     * @return bool Retourne true si la mise à jour réussit, false sinon (par exemple si l'email est déjà utilisé par un autre utilisateur)
     */
    public static function updateProfile($id, $name, $email, $password = null, $avatar = null) {
        $dbConnect = new DbConnect();
        $db = $dbConnect->getConnection();

        // Vérifier que l'email n'est pas déjà utilisé par un autre utilisateur
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$email, $id]);
        if ($stmt->rowCount() > 0) {
            return false;
        }

        if ($password !== null && !empty($password)) {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $db->prepare("UPDATE users SET name = ?, email = ?, password = ? WHERE id = ?");
            $result = $stmt->execute([$name, $email, $hashedPassword, $id]);
        } else {
            $stmt = $db->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
            $result = $stmt->execute([$name, $email, $id]);
        }

        if ($avatar !== null) {
            $stmt = $db->prepare("UPDATE users SET avatar = ? WHERE id = ?");
            $stmt->execute([$avatar, $id]);
        }
        return $result;
    }
}
?>