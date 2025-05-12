<?php

/**
 * Classe DbConnect
 * 
 * Cette classe gère la connexion à la base de données MySQL pour l'application SunnyLink.
 * Elle utilise PDO (PHP Data Objects) pour assurer une connexion sécurisée et flexible.
 */
class DbConnect
{
    /**
     * Variable qui stocke l'objet de connexion PDO
     * @var PDO
     */
    protected $connection;

    /**
     * Constante définissant le serveur de base de données
     * @var string
     */
    const SERVER = 'localhost';
    
    /**
     * Constante définissant l'utilisateur de la base de données
     * @var string
     */
    const USER = 'sunnylink_user';
    
    /**
     * Constante définissant le mot de passe d'accès à la base de données
     * Actuellement configuré sans mot de passe pour simplifier le développement
     * @var string
     */
    const PASSWORD = '';  // Mot de passe vide comme configuré
    
    /**
     * Constante définissant le nom de la base de données
     * @var string
     */
    const BASE = 'sunnylink'; 

    /**
     * Configuration alternative commentée pour l'environnement de développement local WAMP
     */
    // const SERVER = 'localhost';
    // const USER = 'root';
    // const PASSWORD = '';  // Par défaut sur WAMP, pas de mot de passe
    // const BASE = 'sunnylink';

    /**
     * Constructeur - établit la connexion à la base de données dès l'instanciation de la classe
     * 
     * Configure plusieurs attributs importants de PDO :
     * - Mode d'erreur : exceptions (pour capturer et gérer les erreurs proprement)
     * - Mode de récupération par défaut : objets (pour récupérer les données sous forme d'objets)
     * - Encodage : UTF-8 (pour supporter correctement les caractères internationaux)
     */
    public function __construct()
    {
        try {
            // Création de la connexion PDO avec les constantes de configuration
            $this->connection = new PDO('mysql:host=' . self::SERVER . ';dbname=' . self::BASE, self::USER, self::PASSWORD);
            
            // Configuration du mode d'erreur : génère des exceptions en cas d'erreur
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Configuration du mode de récupération : les résultats sont retournés comme des objets
            $this->connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
            
            // Configuration de l'encodage : utilise UTF-8 pour la communication avec la base de données
            $this->connection->setAttribute(PDO::MYSQL_ATTR_INIT_COMMAND, "SET NAMES utf8");
        } catch (Exception $e) {
            // En cas d'erreur de connexion, arrête l'exécution et affiche le message d'erreur
            die('Erreur : ' . $e->getMessage());
        }
    }

    /**
     * Méthode pour obtenir l'objet de connexion PDO
     * 
     * Cette méthode permet aux autres classes d'accéder à la connexion
     * pour exécuter des requêtes SQL
     *
     * @return PDO L'objet de connexion à la base de données
     */
    public function getConnection()
    {
        return $this->connection;
    }
}