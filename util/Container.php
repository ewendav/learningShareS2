<?php

namespace Util;

use PDO;
use Exception;
use Dotenv\Dotenv;
use DI\ContainerBuilder;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Psr\Log\LoggerInterface;
use Models\UserModel;

class Container
{
    private static $container;


    private function __construct()
    {
    }

    private function __clone(): void
    {
    }

    public static function setContainer(): void
    {
        $dotenv = Dotenv::createImmutable(__DIR__ . '/../');
        $dotenv->load();

        $containerBuilder = new ContainerBuilder();

        $containerBuilder->addDefinitions(
            [
                // Modèle utilisateur
                UserModel::class => function ($container) {
                    return new UserModel($container->get(PDO::class));
                },

                // ajout du pdo
                PDO::class => function () {
                    $sgbd = $_ENV['sgbd'] ?? 'mysql';
                    $host = $_ENV['host'] ?? '';
                    $port = $_ENV['port'] ?? '';
                    $dbname = $_ENV['dbname'] ?? '';
                    $user = $_ENV['user'] ?? '';
                    $pass = $_ENV['mdp'] ?? '';
                    $charset = $_ENV['charset'] ?? 'utf8';

                    // Vérifier si les variables sont définies
                    if (empty($host) || empty($port) || empty($dbname) || empty($user)) {
                        throw new Exception("Database configuration incomplete. Check your .env file.");
                    }

                    $dsn = match ($sgbd) {
                        'pgsql' => "pgsql:host=$host;port=$port;dbname=$dbname",
                        'mysql' => "mysql:host=$host;port=$port;dbname=$dbname;charset=$charset",
                        default => throw new Exception("Unsupported SGBD: $sgbd"),
                    };

                    return new PDO(
                        $dsn,
                        $user,
                        $pass,
                        [
                            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        ]
                    );
                },

                // Logger PSR-3 avec Monolog
                LoggerInterface::class => function () {
                    $logger = new Logger('app');
                    $logPath = __DIR__ . '/../logs/app.log';
                    if (!file_exists(dirname($logPath))) {
                        mkdir(dirname($logPath), 0777, true);
                    }
                    $logger->pushHandler(new StreamHandler($logPath, \Monolog\Level::Debug));
                    return $logger;
                },

                // ajout de twig
                Environment::class => function () {
                    $loader = new FilesystemLoader('../app/Views/templates/');

                    $twig = new Environment($loader, [
                        'cache' => false,
                        'debug' => true,
                    ]);

                    $twig->addExtension(new TwigExtensions());

                    return $twig;
                }
            ]
        );

        static::$container = $containerBuilder->build();
    }

    public static function getContainer(): \DI\Container
    {
        if (static::$container === null) {
            static::setContainer();
        }
        return static::$container;
    }
}


// exemple d'utilisation :
// le patern singleton permet d'isnancier qu'une seule fois
// le container et ya voir acces partout
//
//  $container = Container::getContainer();
//
// exemple d'usage des elemnts du container
//
// $twig = $container->get(Twig\Environment::class);
// Récupérer PDO depuis le container
// $pdo = $container->get(PDO::class);
