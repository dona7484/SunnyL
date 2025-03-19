<?php

class Event {
    private $id;
    private $title;
    private $description;
    private $date;
    private $lieu;
    private $userId;

    // Getters
    public function getId() {
        return $this->id;
    }

    public function getTitle() {
        return $this->title;
    }

    public function getDescription() {
        return $this->description;
    }

    public function getDate() {
        return $this->date;
    }

    public function getLieu() {
        return $this->lieu;
    }

    public function getUserId() {
        return $this->userId;
    }

    // Setters
    public function setId($id) {
        $this->id = $id;
    }

    public function setTitle($title) {
        $this->title = $title;
    }

    public function setDescription($description) {
        $this->description = $description;
    }

    public function setDate($date) {
        $this->date = $date;
    }

    public function setLieu($lieu) {
        $this->lieu = $lieu;
    }

    public function setUserId($userId) {
        $this->userId = $userId;
    }
}
