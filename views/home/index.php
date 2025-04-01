<?php
$title = "SunnyLink - Accueil";
ob_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

?>
<h2>Bonjour, vous êtes sur la page d'accueil</h2>
<p>Bienvenue sur mon site web SunnyLink.</p>
<?php
$content = ob_get_clean();
require_once __DIR__ . '/../base.php';
?>
