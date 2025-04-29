<?php

class DbConnect
{
    protected $connection;

    const SERVER = 'localhost';
    const USER = 'sunnylink_user';
    const PASSWORD = '';  // Mot de passe vide comme configuré
    const BASE = 'sunnylink'; 


    // const SERVER = 'localhost';
    // const USER = 'root';
    // const PASSWORD = '';  // Par défaut sur WAMP, pas de mot de passe
    // const BASE = 'sunnylink';

    public function __construct()
    {
        try {
            $this->connection = new PDO('mysql:host=' . self::SERVER . ';dbname=' . self::BASE, self::USER, self::PASSWORD);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
            $this->connection->setAttribute(PDO::MYSQL_ATTR_INIT_COMMAND, "SET NAMES utf8");
        } catch (Exception $e) {
            die('Erreur : ' . $e->getMessage());
        }
    }

    public function getConnection()
    {
        return $this->connection;
    }
}