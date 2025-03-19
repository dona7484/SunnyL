<?php
class EventController extends Controller {
    public function index() {
        $eventModel = new EventModel();
        $events = $eventModel->findAll();
        $this->render('events/index', ['list' => $events]);
    }

    public function show($id) {
        $eventModel = new EventModel();
        $event = $eventModel->find($id);
        if ($event) {
            $this->render('events/show', ['events' => $event]);
        } else {
            // Gérer le cas où l'événement n'est pas trouvé
            echo "Événement non trouvé.";
        }
    }
    

    public function add() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Créez une nouvelle instance de l'événement
        $event = new Event();

        // Remplissez les propriétés de l'événement avec les données du formulaire
        $event->setTitle($_POST['title']);
        $event->setDescription($_POST['description']);
        $event->setDate($_POST['date']);
        $event->setLieu($_POST['lieu']); // Ajoutez cette ligne pour le lieu

        // Définissez l'ID de l'utilisateur à partir de la session
        $event->setUserId($_SESSION['user_id']);

        // Créez une instance du modèle EventModel
        $eventModel = new EventModel();

        // Appelez la méthode create du modèle pour ajouter l'événement à la base de données
        $eventModel->create($event);

        // Redirigez l'utilisateur vers la liste des événements après l'ajout
        header('Location: index.php?controller=event&action=index');
        exit;
    } else {
        // Créez une instance de Form pour générer le formulaire
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
                ->addLabel('participants', 'Participants')
                ->addInput('text', 'participants')
                ->addLabel('notificationEmail', 'Notification par Email')
                ->addInput('checkbox', 'notificationEmail')
                ->addLabel('notificationSMS', 'Notification par SMS')
                ->addInput('checkbox', 'notificationSMS')
                ->addLabel('alertFloat', 'Activer alerte flottante')
                ->addInput('checkbox', 'alertFloat')
                ->addLabel('alertTime', 'Temps avant alerte')
                ->addSelect('alertTime', ['1h' => '1 heure avant', '30m' => '30 minutes avant', '15m' => '15 minutes avant'])
                ->addInput('submit', 'create', ['value' => 'Créer l\'événement'])
                ->endForm();

        // Affichez le formulaire d'ajout
        $this->render('events/add', [
            'addForm' => $addForm
        ]);
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
        $event->setLieu($_POST['lieu']);

        // Enregistrez les modifications dans la base de données
        $eventModel->update($event);

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

        header('Location: index.php?controller=event&action=index');
        exit;
    }
}
