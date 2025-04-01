<?php

use DI\ContainerBuilder;
use Dotenv\Dotenv;
use PDO;

require_once __DIR__ . '/../vendor/autoload.php';

// Charger les variables d'environnement
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad();

$containerBuilder = new ContainerBuilder();

// Connexion à la BDD via PDO
$containerBuilder->addDefinitions([
    PDO::class => function () {
        $sgbd = $_ENV['DB_SGBD'] ?? 'pgsql';
        $host = $_ENV['DB_HOST'];
        $port = $_ENV['DB_PORT'];
        $dbname = $_ENV['DB_NAME'];
        $user = $_ENV['DB_USER'];
        $pass = $_ENV['DB_PASS'];

        $dsn = match ($sgbd) {
            'pgsql' => "pgsql:host=$host;port=$port;dbname=$dbname",
            'mysql' => "mysql:host=$host;port=$port;charset=utf8mb4",
            default => throw new Exception("SGBD non supporté : $sgbd"),
        };

        return new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    },
]);

$container = $containerBuilder->build();

return $container;
