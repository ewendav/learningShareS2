<?php
namespace Util;

use Dotenv\Dotenv;

class ConnexionBDD
{
    private static ?self $instance = null;
    private ?PDO $pdo = null;

    private function __construct()
    {
        $path = __DIR__ . "/../..";
        $resolvedPath = realpath($path);
        $dotenv = Dotenv::createImmutable($resolvedPath);
        $dotenv->load();

        $sgbd = $_ENV['DB_SGBD'] ?? 'pgsql'; // Par défaut PostgreSQL
        $host = $_ENV['DB_HOST'];
        $port = $_ENV['DB_PORT'];
        $dbname = $_ENV['DB_NAME'];
        $user = $_ENV['DB_USER'];
        $password = $_ENV['DB_PASS'];

        $db_co = match ($sgbd) {
            'pgsql' => "pgsql:host=$host;port=$port;dbname=$dbname",
            'mysql' => "mysql:host=$host;port=$port;dbname=$dbname",
            default => throw new PDOException("SGBD non supporté : $sgbd")
        };

        try {
            $this->pdo = new PDO(
                $db_co, $user, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]
            );
        } catch (PDOException $e) {
            error_log($e->getMessage());
            die("Erreur de connexion à la base de données.");
        }
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getPdo(): ?PDO
    {
        return $this->pdo;
    }
}
