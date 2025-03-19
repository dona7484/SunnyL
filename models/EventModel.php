<?php
require_once __DIR__ . '/../Entities/Event.php';


class EventModel extends DbConnect {
    public function findAll() {
        $this->request = "SELECT * FROM events";
        $result = $this->connection->query($this->request);
        return $result->fetchAll(PDO::FETCH_CLASS, 'Event');
    }

    public function find($id) {
        $this->request = $this->connection->prepare("SELECT * FROM events WHERE id = :id");
        $this->request->bindParam(":id", $id, PDO::PARAM_INT);
        $this->request->execute();
        return $this->request->fetchObject('Event');
    }

    public function create(Event $event) {
        $this->request = $this->connection->prepare("INSERT INTO events (title, description, date, lieu, user_id) VALUES (:title, :description, :date, :lieu, :user_id)");
        $this->request->bindValue(":title", $event->getTitle());
        $this->request->bindValue(":description", $event->getDescription());
        $this->request->bindValue(":date", $event->getDate());
        $this->request->bindValue(":lieu", $event->getLieu()); // Ajoutez cette ligne pour le lieu
        $this->request->bindValue(":user_id", $event->getUserId());
        $this->executeTryCatch();
    }

    public function update($id, Event $event) {
        $this->request = $this->connection->prepare("UPDATE events SET title = :title, description = :description, date = :date, lieu = :lieu WHERE id = :id");
        $this->request->bindValue(":id", $id);
        $this->request->bindValue(":title", $event->getTitle());
        $this->request->bindValue(":description", $event->getDescription());
        $this->request->bindValue(":date", $event->getDate());
        $this->request->bindValue(":lieu", $event->getLieu()); // Ajoutez cette ligne pour le lieu
        $this->executeTryCatch();
    }

    public function delete($id) {
        $this->request = $this->connection->prepare("DELETE FROM events WHERE id = :id");
        $this->request->bindParam(":id", $id);
        $this->executeTryCatch();
    }

    private function executeTryCatch() {
        try {
            $this->request->execute();
        } catch (Exception $e) {
            die('Erreur : ' . $e->getMessage());
        }
        $this->request->closeCursor();
    }
}
