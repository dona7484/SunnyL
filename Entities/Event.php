<?php

class Event {
    private $id;
    private $title;
    private $description;
    private $date;
    private $lieu;
    private $userId;
    private $user_id;
    private $alert_time;
    private $notification_message;
    private $recurrence; 
    private $is_triggered;
    private $participants;
    private $is_read;

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

    public function getAlertTime() {
        return $this->alert_time;
    }

    public function getNotificationMessage() {
        return $this->notification_message;
    }

    public function getRecurrence() {
        return $this->recurrence;
    }

    public function getIsTriggered() {
        return $this->is_triggered;
    }

    public function getParticipants() {
        return $this->participants;
    }
    public function getIsRead() {
        return $this->is_read;
    }

    // Setters
    public function setId($id) {
        $this->id = $id;
    }
    public function setIsRead($is_read) {
        $this->is_read = $is_read;
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
        $this->lieu = $lieu === null ? '' : $lieu;
        return $this;
    }

    public function setUserId($userId) {
        $this->userId = $userId;
    }

    public function setAlertTime($alert_time) {
        $this->alert_time = $alert_time;
    }

    public function setNotificationMessage($msg) {
        $this->notification_message = $msg;
    }

    public function setRecurrence($recurrence) {
        $this->recurrence = $recurrence;
    }

    public function setIsTriggered($is_triggered) {
        $this->is_triggered = $is_triggered;
    }

    public function setParticipants($participants) {
        $this->participants = $participants;
    }
    public function isRead() {
        return $this->is_read == 1;
    }
    public function isTriggered() {
        return $this->is_triggered == 1;  // Retourne true si l'événement a été alerté
    }
}
