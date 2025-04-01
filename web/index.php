<?php


require_once '../vendor/autoload.php';

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

$loader = new FilesystemLoader('../app/Views/templates/');

$twig = new Environment($loader, [
    'cache' => false,
    'debug' => true,
]);


// Charger le container
$container = require __DIR__ . '/../config/container.php';

//echo $twig->render('home.html.twig', ['name' => 'World']);

try {
    // Récupérer PDO depuis le container
    $pdo = $container->get(PDO::class);

    // Vérifier la connexion avec une requête simple
    $result = $pdo->query('SELECT mail FROM app_user')->fetchColumn();

    echo "<h2 style='color: green;'>✅ Connexion réussie à la base de données !</h2>";
    echo "<p>Test requête : $result</p>";

} catch (Exception $e) {
    echo "<h2 style='color: red;'>❌ Erreur de connexion</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
}

//$twig->addExtension(new Util\TwigExtensions());

// Initialiser le système d'authentification
$auth = new \Delight\Auth\Auth($pdo);

// INSCRIPTION UTILISATEUR
try {
    $userId = $auth->register('email@example.com', 'mot_de_passe', 'nom_utilisateur', function ($selector, $token) {
        // Envoi d'un email de confirmation avec $selector et $token
    });
}
catch (\Delight\Auth\InvalidEmailException $e) {
    // Email invalide
}
catch (\Delight\Auth\InvalidPasswordException $e) {
    // Mot de passe invalide
}
catch (\Delight\Auth\UserAlreadyExistsException $e) {
    // Utilisateur déjà existant
}

// CONNEXION UTILISATEUR
try {
    $auth->login('email@example.com', 'mot_de_passe');

    // L'utilisateur est maintenant connecté
}
catch (\Delight\Auth\InvalidEmailException $e) {
    // Email incorrect
}
catch (\Delight\Auth\InvalidPasswordException $e) {
    // Mot de passe incorrect
}
catch (\Delight\Auth\EmailNotVerifiedException $e) {
    // Email non vérifié
}
catch (\Delight\Auth\TooManyRequestsException $e) {
    // Trop de tentatives (protection contre les attaques par force brute)
}

// Si l'utilisateur est connecté
if ($auth->isLoggedIn()) {
    // L'utilisateur est connecté
    $userId = $auth->getUserId();
    $email = $auth->getEmail();
}

// Déconnexion
$auth->logOut();
