<?php
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use Ratchet\ConnectionInterface;

require __DIR__ . '/config/database.php'; 

class Chat implements \Ratchet\MessageComponentInterface {
    protected $clients;
    private $db;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
        $this->initDatabase();
    }

    private function initDatabase() {
        try {
            $dbConnect = new DbConnect();
            $this->db = $dbConnect->getConnection();
        } catch (Exception $e) {
            error_log("Erreur DB: " . $e->getMessage());
            exit;
        }
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        try {
            $data = json_decode($msg);
            
            // Vérifier le JSON
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception("JSON invalide: " . json_last_error_msg());
            }

            if ($data->type === 'message') {
                $this->saveMessageToDB($data);
            }

            foreach ($this->clients as $client) {
                if ($client !== $from) {
                    $client->send(json_encode($data));
                }
            }
        } catch (Exception $e) {
            error_log("Erreur onMessage: " . $e->getMessage());
            $from->close();
        }
    }

    private function saveMessageToDB($data) {
        try {
            // Utiliser la connexion existante
            $stmt = $this->db->prepare("INSERT INTO messages (sender_id, receiver_id, content) VALUES (?, ?, ?)");
            $stmt->execute([
                $data->sender ?? 0,
                $data->receiver ?? 0,
                $data->content ?? ''
            ]);
        } catch (PDOException $e) {
            error_log("Erreur DB: " . $e->getMessage());
        }
    }

    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        error_log("Erreur WebSocket: " . $e->getMessage());
        $conn->close();
    }
}

$server = IoServer::factory(
    new HttpServer(new WsServer(new Chat())),
    8080
);

echo "Serveur WebSocket démarré sur ws://localhost:8080\n";
$server->run();
