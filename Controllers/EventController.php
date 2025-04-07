<?php
class EventController extends Controller {
   // Dans EventController.php
public function index() {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    
    try {
        $eventModel = new EventModel();
        $events = $eventModel->findAll();
        $this->render('events/index', ['list' => $events]);
    } catch (Exception $e) {
        die("Erreur dans EventController::index : " . $e->getMessage());
    }
}

    public function createEvent() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title = $_POST['title'];
            $date = $_POST['date']; // Assurez-vous de bien formater cette valeur en date
            $description = $_POST['description'];
            $location = $_POST['location'];
            $notificationMessage = $_POST['notification_message'];
    
            // Sauvegarder l'événement dans la base de données
            $eventModel = new EventModel();
            $eventId = $eventModel->createEvent($title, $date, $description, $location);
    
            // Si l'événement est créé avec succès, envoyer une notification
            if ($eventId) {
                // Récupérer tous les seniors associés à ce familymember
                $familyMemberId = $_SESSION['user_id'];
                $seniorModel = new SeniorModel();
                $seniors = $seniorModel->getSeniorsForFamilyMember($familyMemberId);
    
                // Envoyer une notification personnalisée à chaque senior
                $notificationController = new NotificationController();
                foreach ($seniors as $senior) {
                    $notificationController->sendNotification($senior['user_id'], 'event', $notificationMessage, $eventId);
                }
    
                echo json_encode(['success' => true, 'message' => 'Événement créé et notification envoyée!']);
            } else {
                echo json_encode(['error' => 'Erreur lors de la création de l\'événement']);
            }
        }
    }
    
    public function show($id) {
        $eventModel = new EventModel();
        $event = $eventModel->find($id);
    
        if ($event === false) {
            echo json_encode(['error' => "L'événement avec l'ID $id n'a pas été trouvé."]);
            exit;
        }
    
        // Récupérer les participants associés à cet événement
        $participants = $eventModel->getParticipantsByEventId($id);
    
        // Récupérer les notifications liées à cet événement (si nécessaire)
        $notifications = $eventModel->getNotificationsByEventId($id); // Assurez-vous que cette méthode existe dans votre modèle
    
        // Passer l'événement, les participants et les notifications à la vue
        $this->render('events/show', [
            'events' => $event,
            'participants' => $participants,
            'notifications' => $notifications
        ]);
    }
    
    
    
    public function add() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Créer une nouvelle instance de l'événement
            $event = new Event();
            $event->setTitle($_POST['title']);
            $event->setDescription($_POST['description']);
            $event->setDate($_POST['date']);
            $event->setLieu($_POST['lieu']);
            $event->setRecurrence($_POST['recurrence']);
            $event->setAlertTime($_POST['alertTime']);
            $event->setNotificationMessage($_POST['notificationMessage']);
            $event->setUserId($_SESSION['user_id']);
    
            // Vérifie si 'recurrence' est défini et récupère sa valeur, sinon la valeur par défaut est 'none'
            $recurrence = $_POST['recurrence'] ?? 'none'; 
            $event->setRecurrence($recurrence);
            
            $participants = isset($_POST['participants']) ? $_POST['participants'] : [];
            $event->setParticipants($participants);

            // Calculer l'heure d'alerte (si alertTime est défini dans le formulaire)
            $eventTimestamp = strtotime($_POST['date']);
            $alertDelay = $_POST['alertTime']; // '1h', '30m', '15m'
            
            $delayInSeconds = match($alertDelay) {
                '1h' => 3600,
                '30m' => 1800,
                '15m' => 900,
                default => 0
            };
    
            $alertTimestamp = $eventTimestamp - $delayInSeconds;
            $alertTime = date('Y-m-d H:i:s', $alertTimestamp);
            $event->setAlertTime($alertTime);
    
            // Créer l'événement avec récurrence ou unique
            $eventModel = new EventModel();
            if ($recurrence !== 'none') {
                $eventModel->createRecurringEvent($event); // Créer un événement récurrent
            } else {
                $eventModel->create($event); // Créer un événement unique

                header('Location: index.php?controller=event&action=index');
                exit;
            }

// Créer une notification associée à l'événement
$notifController = new NotificationController();
$notifController->sendNotification($event->getUserId(), 'event', $event->getNotificationMessage(), $event->getId());

            // Rediriger l'utilisateur après la création de l'événement
            header('Location: index.php?controller=event&action=index');
            exit;
        } else {
            // Affichage du formulaire d'ajout
            $addForm = new Form();
            $addForm->startForm('index.php?controller=event&action=add', 'POST')
                    ->addLabel('title', 'Titre de l\'événement')
                    ->addInput('text', 'title')
                    ->addLabel('date', 'Date et heure')
                    ->addInput('datetime-local', 'date')
                    ->addLabel('description', 'Description')
                    ->addTextarea('description')
                    ->addLabel('lieu', 'Lieu')
                    ->addInput('text', 'lieu')
                    ->addLabel('recurrence', 'Fréquence de l\'événement')
                    ->addSelect('recurrence', ['none' => 'Pas de récurrence', 'daily' => 'Quotidien', 'weekly' => 'Hebdomadaire', 'monthly' => 'Mensuel'])
                    ->addLabel('alertTime', 'Temps avant alerte')
                    ->addSelect('alertTime', ['1h' => '1 heure avant', '30m' => '30 minutes avant', '15m' => '15 minutes avant'])
                    ->addLabel('notificationMessage', 'Message de notification personnalisé')
                    ->addTextarea('notificationMessage')
                    ->addInput('submit', 'create', ['value' => 'Créer l\'événement'])
                    ->endForm();
    
            $this->render('events/add', [
                'addForm' => $addForm
            ]);
        }
    }
    public function markEventAsRead() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $eventId = $_POST['event_id'];
    
            // Mettre à jour l'état de l'événement dans la base de données
            $eventModel = new EventModel();
            $eventModel->markEventAsRead($eventId);
    
            echo json_encode(['success' => true, 'message' => 'Événement marqué comme lu']);
        }
    }
    
public function update($id) {
    // Récupérez l'événement à mettre à jour
    $eventModel = new EventModel();
    $event = $eventModel->find($id);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Mettez à jour les propriétés de l'événement avec les données du formulaire
        $event->setTitle($_POST['title']);
        $event->setDescription($_POST['description']);
        $event->setDate($_POST['date']);
        // Calculer l'heure d'alerte à partir du champ alertTime
$eventTimestamp = strtotime($_POST['date']);
$alertDelay = $_POST['alertTime']; // ex: '1h', '30m', '15m'

$delayInSeconds = match($alertDelay) {
    '1h' => 3600,
    '30m' => 1800,
    '15m' => 900,
    default => 0
};

$alertTimestamp = $eventTimestamp - $delayInSeconds;
$alertTime = date('Y-m-d H:i:s', $alertTimestamp);
$event->setAlertTime($alertTime);
        $event->setLieu($_POST['lieu']);

        // Enregistrez les modifications dans la base de données
        $eventModel->update($event->getId(), $event);


        // Redirigez l'utilisateur vers la liste des événements après la mise à jour
        header('Location: index.php?controller=event&action=index');
        exit;
    } else {
        // Créez une instance de Form pour générer le formulaire
        $eventUpdateForm = new Form();
        $eventUpdateForm->startForm('index.php?controller=event&action=update&id=' . $id, 'POST')
                        ->addLabel('title', 'Titre de l\'événement')
                        ->addInput('text', 'title', ['value' => $event->getTitle()])
                        ->addLabel('date', 'Date et heure')
                        ->addInput('datetime-local', 'date', ['value' => $event->getDate()])
                        ->addLabel('description', 'Description')
                        ->addTextarea('description', $event->getDescription())
                        ->addLabel('lieu', 'Lieu')
                        ->addInput('text', 'lieu', ['value' => $event->getLieu()])
                        ->addInput('submit', 'update', ['value' => 'Mettre à jour l\'événement'])
                        ->endForm();

        // Affichez le formulaire de mise à jour
        $this->render('events/eventUpdateForm', [
            'eventUpdateForm' => $eventUpdateForm,
            'events' => $event
        ]);
    }
}


public function delete($id) {
    $eventModel = new EventModel();
    $eventModel->delete($id);

    $notifController = new NotificationController();
    $notifController->sendNotification(1, 'event', 'Un événement a été supprimé.');
}
// Nouvelle action pour récupérer les événements pour un senior
public function receive() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Vérifier que l'utilisateur est connecté et qu'il est un senior
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'senior') {
        echo json_encode(['error' => 'Accès interdit.']);
        exit;
    }

    // Récupérer les événements associés à l'utilisateur senior
    $eventModel = new EventModel();
    $events = $eventModel->findEventsForUser($_SESSION['user_id']); // Méthode à définir dans ton modèle

    // Passer les événements à la vue
    $this->render('events/received', ['events' => $events]);
}

}

