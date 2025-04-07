<?php

require_once __DIR__ . '/../vendor/autoload.php';

// Charger le container
$container = include __DIR__ . '/../config/container.php';

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
