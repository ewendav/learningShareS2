<?php

namespace Controllers;

use Entity\Partage;
use Models\PartageModel;
use PDO;

class PartageController
{
    private $partageModel;

    /**
     * Constructeur
     */
    public function __construct(PDO $pdo = null)
    {
        if ($pdo === null) {
            $container = \Util\Container::getContainer();
            $pdo = $container->get(PDO::class);
        }
        $this->partageModel = new PartageModel($pdo);
    }

    /**
     * Affiche la liste des partages
     */
    public function index()
    {
        $partages = $this->partageModel->getAll();

        // En fonction de votre système de rendu de vue
        // return view('partages/index', ['partages' => $partages]);

        // Pour l'exemple, retourne simplement les données
        return [
            'status' => 'success',
            'data' => $partages
        ];
    }

    /**
     * Affiche les partages d'un utilisateur (comme demandeur)
     */
    public function userRequests($user_id)
    {
        $partages = $this->partageModel->getByRequesterId($user_id);

        // return view('partages/user-requests', ['partages' => $partages]);
        return [
            'status' => 'success',
            'data' => $partages
        ];
    }

    /**
     * Affiche les partages d'un utilisateur (comme accepteur)
     */
    public function userAccepts($user_id)
    {
        $partages = $this->partageModel->getByAccepterId($user_id);

        // return view('partages/user-accepts', ['partages' => $partages]);
        return [
            'status' => 'success',
            'data' => $partages
        ];
    }

    /**
     * Affiche les détails d'un partage
     */
    public function show($session_id)
    {
        $partage = $this->partageModel->getById($session_id);

        if (!$partage) {
            // return view('errors/404', ['message' => 'Partage non trouvé']);
            return [
                'status' => 'error',
                'message' => 'Partage non trouvé'
            ];
        }

        // return view('partages/show', ['partage' => $partage]);
        return [
            'status' => 'success',
            'data' => $partage
        ];
    }

    /**
     * Affiche le formulaire de création d'un partage
     */
    public function create()
    {
        // return view('partages/create');
        return [
            'status' => 'success',
            'message' => 'Formulaire de création de partage'
        ];
    }

    /**
     * Stocke un nouveau partage
     */
    public static function store()
    {
        // Récupération du container
        $container = \Util\Container::getContainer();
        $twig = $container->get(\Twig\Environment::class);

        // Création du contrôleur
        $controller = new self();

        // Récupération des données du formulaire
        $request = $_POST;

        // Validation des données (à implémenter)

        // Création de l'objet Partage
        $partage = new Partage(
            null,
            $request['start_time'],
            $request['end_time'],
            $request['date_session'],
            $request['description'],
            $request['rate_id'],
            $request['skill_taught_id'],
            $request['skill_requested_id'],
            $request['exchange_requester_id'],
            null // exchange_accepter_id est null au début
        );

        // Sauvegarde dans la base de données
        if ($controller->partageModel->save($partage)) {
            // Redirection ou affichage d'un message de succès
            echo $twig->render('ecrans/createSession.html.twig', [
                'title' => 'Création d\'un échange',
                'message' => 'Échange créé avec succès',
                'success' => true,
                'categories' => \Models\CategorieModel::getAll()
            ]);
        } else {
            // Affichage d'un message d'erreur
            echo $twig->render('ecrans/createSession.html.twig', [
                'title' => 'Création d\'un échange',
                'message' => 'Erreur lors de la création de l\'échange',
                'success' => false,
                'categories' => \Models\CategorieModel::getAll()
            ]);
        }
    }

    /**
     * Affiche le formulaire d'édition d'un partage
     */
    public function edit($session_id)
    {
        $partage = $this->partageModel->getById($session_id);

        if (!$partage) {
            // return view('errors/404', ['message' => 'Partage non trouvé']);
            return [
                'status' => 'error',
                'message' => 'Partage non trouvé'
            ];
        }

        // return view('partages/edit', ['partage' => $partage]);
        return [
            'status' => 'success',
            'data' => $partage
        ];
    }

    /**
     * Met à jour un partage
     */
    public function update($session_id, $request)
    {
        $partage = $this->partageModel->getById($session_id);

        if (!$partage) {
            // return view('errors/404', ['message' => 'Partage non trouvé']);
            return [
                'status' => 'error',
                'message' => 'Partage non trouvé'
            ];
        }

        // Mise à jour des données de base (Session)
        $partage->setStartTime($request['start_time']);
        $partage->setEndTime($request['end_time']);
        $partage->setDateSession($request['date_session']);
        $partage->setDescription($request['description']);
        $partage->setRateId($request['rate_id']);
        $partage->setSkillTaughtId($request['skill_taught_id']);

        // Mise à jour des données spécifiques au partage
        $partage->setSkillRequestedId($request['skill_requested_id']);
        $partage->setExchangeRequesterId($request['exchange_requester_id']);
        $partage->setExchangeAccepterId($request['exchange_accepter_id']);

        if ($this->partageModel->save($partage)) {
            // redirect('partages/' . $partage->getSessionId());
            return [
                'status' => 'success',
                'message' => 'Partage mis à jour avec succès',
                'data' => $partage
            ];
        } else {
            // return view('partages/edit', ['partage' => $partage, 'error' => 'Erreur lors de la mise à jour du partage']);
            return [
                'status' => 'error',
                'message' => 'Erreur lors de la mise à jour du partage'
            ];
        }
    }

    /**
     * Supprime un partage
     */
    public function destroy($session_id)
    {
        $partage = $this->partageModel->getById($session_id);

        if (!$partage) {
            // return view('errors/404', ['message' => 'Partage non trouvé']);
            return [
                'status' => 'error',
                'message' => 'Partage non trouvé'
            ];
        }

        if ($this->partageModel->delete($partage)) {
            // redirect('partages');
            return [
                'status' => 'success',
                'message' => 'Partage supprimé avec succès'
            ];
        } else {
            // return view('partages/show', ['partage' => $partage, 'error' => 'Erreur lors de la suppression du partage']);
            return [
                'status' => 'error',
                'message' => 'Erreur lors de la suppression du partage'
            ];
        }
    }

    /**
     * Accepte un partage (ajoute l'ID de l'accepteur)
     */
    public function accept($session_id, $user_id)
    {
        $partage = $this->partageModel->getById($session_id);

        if (!$partage) {
            return [
                'status' => 'error',
                'message' => 'Partage non trouvé'
            ];
        }

        // Vérifier que le partage n'a pas déjà été accepté
        if ($partage->getExchangeAccepterId()) {
            return [
                'status' => 'error',
                'message' => 'Ce partage a déjà été accepté'
            ];
        }

        $partage->setExchangeAccepterId($user_id);

        if ($this->partageModel->save($partage)) {
            return [
                'status' => 'success',
                'message' => 'Partage accepté avec succès',
                'data' => $partage
            ];
        } else {
            return [
                'status' => 'error',
                'message' => 'Erreur lors de l\'acceptation du partage'
            ];
        }
    }
}

