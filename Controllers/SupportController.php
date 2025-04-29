<?php
require_once __DIR__ . '/../config/database.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class SupportController extends Controller {
    
    public function faq() {
        $this->render('support/faq');
    }
    
    public function contact() {
        $message = '';
        $success = false;
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Traitement du formulaire de contact
            $name = $_POST['name'] ?? '';
            $email = $_POST['email'] ?? '';
            $subject = $_POST['subject'] ?? '';
            $message_content = $_POST['message'] ?? '';
            
            if (empty($name) || empty($email) || empty($subject) || empty($message_content)) {
                $message = "Tous les champs sont obligatoires.";
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $message = "L'adresse email n'est pas valide.";
            } else {
                // Utilisation de PHPMailer au lieu de la fonction mail() native
                require_once __DIR__ . '/../vendor/autoload.php';
                
                $mail = new PHPMailer(true);
                try {
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'dona7484@gmail.com';
                    $mail->Password = '..'; // Remplacez par votre mot de passe réel
                    $mail->SMTPSecure = 'tls';
                    $mail->Port = 587;
                    
                    $mail->setFrom('dona7484@gmail.com', 'SunnyLink Support');
                    $mail->addAddress('dona7484@gmail.com'); // L'adresse qui recevra les messages de contact
                    $mail->addReplyTo($email, $name); // Pour que les réponses aillent à l'expéditeur
                    
                    // Contenu du message
                    $mail->isHTML(true);
                    $mail->Subject = "Contact SunnyLink: $subject";
                    $mail->Body = "
                        <h3>Nouveau message de contact</h3>
                        <p><strong>Nom:</strong> $name</p>
                        <p><strong>Email:</strong> $email</p>
                        <p><strong>Sujet:</strong> $subject</p>
                        <p><strong>Message:</strong></p>
                        <p>$message_content</p>
                    ";
                    $mail->AltBody = "Nouveau message de contact\n\nNom: $name\nEmail: $email\nSujet: $subject\n\nMessage:\n$message_content";
                    
                    $mail->send();
                    $success = true;
                    $message = "Votre message a été envoyé avec succès.";
                } catch (Exception $e) {
                    $message = "Une erreur est survenue lors de l'envoi du message: " . $mail->ErrorInfo;
                }
            }
        }
        
        $this->render('support/contact', [
            'message' => $message,
            'success' => $success
        ]);
    }
}
