<?php

namespace Controllers;

use Models\UserModel;
use Models\SessionModel;
use Models\CategorieModel;
use Models\CoursModel;
use Models\PartageModel;
use Psr\Log\LoggerInterface;
use PDO;

class UserProfilController
{
    /**
     * Affiche le profil public d'un utilisateur.
     * Cette méthode est statique afin de permettre son appel direct depuis le routeur.
     */
    public static function displayProfil(): void
    {
        // Récupération du container et des dépendances
        $container = \Util\Container::getContainer();

        $pdo     = $container->get(PDO::class);

        $twig    = $container->get(\Twig\Environment::class);

        $logger  = $container->get(LoggerInterface::class);

        // Instanciation des modèles
        $userModel      = new UserModel($pdo, $logger);
        $categorieModel = new CategorieModel($pdo, $logger);
        $coursModel     = new CoursModel($pdo, $logger);
        $partageModel   = new PartageModel($pdo, $logger);

        // Récupération des paramètres GET
        $userId = $_GET['user_id'] ?? null;
        $type   = $_GET['type'] ?? 'cours';  // Par défaut, afficher les cours

        // Vérification de l'ID utilisateur
        if (!$userId || !is_numeric($userId)) {
            header('Location: /?error=user_not_found');
            exit;
        }

        // Récupération des données utilisateur via le modèle
        $userData = $userModel->findById($userId);
        if (!$userData) {
            header('Location: /?error=user_not_found');
            exit;
        }

        // Récupération des sessions selon le type
        $sessions = [];
        if ($type === 'cours') {
            $sessions = $coursModel->getByHostIdNotExpired($userId);
        } elseif ($type === 'echange' || $type === 'echanges') {
            // Accepter 'echange' et 'echanges' pour compatibilité
            $sessions = $partageModel->getByRequesterIdNotExpired($userId, true);
        }

        // Rendu de la vue Twig
        echo $twig->render('ecrans/userProfil.html.twig', [
            'title'             => 'Profil de ' . htmlspecialchars($userData['user_first_name'] ?? ''),
            'userData'          => $userData,
            'sessions'          => $sessions,
            'categoriesIndexed' => \Models\CategorieModel::getAllIndexedById(), // Utiliser la nouvelle méthode
            'getParams'         => ['type' => $type],
            'user'              => \Util\AuthMiddleware::getUser(),
        ]);
    }
}
