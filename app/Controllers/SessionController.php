<?php

namespace Controllers;

use Entity\Session;
use Models\SessionModel;
use PDO;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

class SessionController
{
    private $sessionModel;
    private $twig;

    /**
     * Constructeur
     */
    public function __construct()
    {
        // a terme le contneu du controler sera surement supprimé pour avoir tout les méhtodes ens tatic
        $container = \Util\Container::getContainer();
        $this->sessionModel = new SessionModel($container->get(PDO::class));
        $this->twig = $container->get(\Twig\Environment::class);
    }

    // affiche la page de creation de session et recuperer toutes les categories
    // afin de pouvoir les utiliséés dans les listes déroulantes
    public static function displayCreateSession(): void
    {
        // Vérifier l'authentification
        \Util\AuthMiddleware::requireAuth();

        $container = \Util\Container::getContainer();
        $twig = $container->get(\Twig\Environment::class);

        echo $twig->render(
            'ecrans/createSession.html.twig',
            [
                'title' => 'Creation d\'un partage ou d\'un échange',
                'categories' => \Models\CategorieModel::getAll(),
                'user' => \Util\AuthMiddleware::getUser()
            ]
        );
    }


     // affiche toutes les cours et les échanges disponibles
    public static function displaySessions(): void
    {
        $container = \Util\Container::getContainer();
        $twig = $container->get(\Twig\Environment::class);

        if (($_GET['type'] ?? "") == "cours") {
            $coursModel = new \Models\CoursModel($container->get(PDO::class), $container->get(LoggerInterface::class));
            $sessions = $coursModel->getLessonDispo(true, $_GET['q'] ?? "");
        } else {
            $partageModel = new \Models\PartageModel($container->get(PDO::class), $container->get(LoggerInterface::class));
            $sessions = $partageModel->getDemandeDePartage(true, $_GET['q'] ?? "");
        }

        echo $twig->render(
            'ecrans/displaySessions.html.twig',
            [
                'title' => 'Consulter les cours et les sessions',
                'getParams' => $_GET,
                'sessions' => $sessions,
                'categories' => \Models\CategorieModel::getAll(),
                'user' => \Util\AuthMiddleware::getUser()
            ]
        );
    }


    /**
     * Affiche les détails d'une session
    *  /
    public function show($session_id)
    {
        $session = $this->sessionModel->getById($session_id);

        if (!$session) {
            // return view('errors/404', ['message' => 'Session non trouvée']);
            return [
                'status' => 'error',
                'message' => 'Session non trouvée'
            ];
        }

        // return view('sessions/show', ['session' => $session]);
        return [
            'status' => 'success',
            'data' => $session
        ];
    }

    /**
     * Affiche le formulaire de création d'une session
     */
    public function create()
    {
        // return view('sessions/create');
        return [
            'status' => 'success',
            'message' => 'Formulaire de création de session'
        ];
    }

    /**
     * Stocke une nouvelle session
     */
    public function store($request)
    {
        // Validation des données
        // ...

        $session = new Session(
            null,
            $request['start_time'],
            $request['end_time'],
            $request['date_session'],
            $request['description'],
            $request['rate_id'],
            $request['skill_taught_id']
        );

        if ($this->sessionModel->save($session)) {
            // redirect('sessions/' . $session->getSessionId());
            return [
                'status' => 'success',
                'message' => 'Session créée avec succès',
                'data' => $session
            ];
        } else {
            // return view('sessions/create', ['error' => 'Erreur lors de la création de la session']);
            return [
                'status' => 'error',
                'message' => 'Erreur lors de la création de la session'
            ];
        }
    }

    /**
     * Affiche le formulaire d'édition d'une session
     */
    public function edit($session_id)
    {
        $session = $this->sessionModel->getById($session_id);

        if (!$session) {
            // return view('errors/404', ['message' => 'Session non trouvée']);
            return [
                'status' => 'error',
                'message' => 'Session non trouvée'
            ];
        }

        // return view('sessions/edit', ['session' => $session]);
        return [
            'status' => 'success',
            'data' => $session
        ];
    }

    /**
     * Met à jour une session
     */
    public function update($session_id, $request)
    {
        $session = $this->sessionModel->getById($session_id);

        if (!$session) {
            // return view('errors/404', ['message' => 'Session non trouvée']);
            return [
                'status' => 'error',
                'message' => 'Session non trouvée'
            ];
        }

        // Mise à jour des données
        $session->setStartTime($request['start_time']);
        $session->setEndTime($request['end_time']);
        $session->setDateSession($request['date_session']);
        $session->setDescription($request['description']);
        $session->setRateId($request['rate_id']);
        $session->setSkillTaughtId($request['skill_taught_id']);

        if ($this->sessionModel->save($session)) {
            // redirect('sessions/' . $session->getSessionId());
            return [
                'status' => 'success',
                'message' => 'Session mise à jour avec succès',
                'data' => $session
            ];
        } else {
            // return view('sessions/edit', ['session' => $session, 'error' => 'Erreur lors de la mise à jour de la session']);
            return [
                'status' => 'error',
                'message' => 'Erreur lors de la mise à jour de la session'
            ];
        }
    }

    /**
     * Supprime une session
     */
    public function destroy($session_id)
    {
        $session = $this->sessionModel->getById($session_id);

        if (!$session) {
            // return view('errors/404', ['message' => 'Session non trouvée']);
            return [
                'status' => 'error',
                'message' => 'Session non trouvée'
            ];
        }

        if ($this->sessionModel->delete($session)) {
            // redirect('sessions');
            return [
                'status' => 'success',
                'message' => 'Session supprimée avec succès'
            ];
        } else {
            // return view('sessions/show', ['session' => $session, 'error' => 'Erreur lors de la suppression de la session']);
            return [
                'status' => 'error',
                'message' => 'Erreur lors de la suppression de la session'
            ];
        }
    }
}
