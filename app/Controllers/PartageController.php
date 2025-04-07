<?php

namespace Controllers;

use Entity\Partage;
use Models\PartageModel;
use PDO;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

class PartageController
{
    private $partageModel;

    private $logger;

    /**
     * Constructeur
     */
    public function __construct(?PDO $pdo = null, ?LoggerInterface $logger = null)
    {
        if ($pdo === null) {
            $container = \Util\Container::getContainer();
            $pdo = $container->get(PDO::class);
        }

        if ($logger === null) {
            $container = \Util\Container::getContainer();
            $logger = $container->get(LoggerInterface::class);
        }

        $this->partageModel = new PartageModel($pdo, $logger);
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
        $logger = $container->get(\Psr\Log\LoggerInterface::class);
        $pdo = $container->get(\PDO::class);

        // Création du contrôleur
        $controller = new self();

        // Récupération des données du formulaire
        $request = $_POST;

        // Validation des données (à implémenter)

        // Gestion du skill enseigné
        $skillModel = new \Models\SkillModel($pdo, $logger);
        $taughtCategoryId = $request['skill_taught_id'];
        $taughtCompetence = $request['description']; // Récupération du nom de compétence enseignée

        // Création ou récupération du skill enseigné
        $skillTaughtId = $skillModel->create($taughtCompetence, $taughtCategoryId);

        if (!$skillTaughtId) {
            $logger->error("Échec de la création de la compétence enseignée", [
                'competence' => $taughtCompetence,
                'category_id' => $taughtCategoryId
            ]);
            header('Location: /createSession?error=skill_taught_error');
            exit;
        }

        // Gestion du skill demandé
        $requestedCategoryId = $request['skill_requested_id'];
        $requestedCompetence = $request['competence_requested']; // Récupération du nom de compétence demandée

        // Création ou récupération du skill demandé
        $skillRequestedId = $skillModel->create($requestedCompetence, $requestedCategoryId);

        if (!$skillRequestedId) {
            $logger->error("Échec de la création de la compétence demandée", [
                'competence' => $requestedCompetence,
                'category_id' => $requestedCategoryId
            ]);
            header('Location: /createSession?error=skill_requested_error');
            exit;
        }

        $logger->info("Compétences créées ou récupérées avec succès", [
            'skill_taught_id' => $skillTaughtId,
            'skill_taught_name' => $taughtCompetence,
            'skill_requested_id' => $skillRequestedId,
            'skill_requested_name' => $requestedCompetence
        ]);

        // Création de l'objet Partage avec les skills
        $partage = new Partage(
            null,
            $request['start_time'],
            $request['end_time'],
            $request['date_session'],
            "", // Description vide, car on utilise maintenant la table skill
            $request['rate_id'],
            $skillTaughtId, // Utilisation du skill_id enseigné
            $skillRequestedId, // Utilisation du skill_id demandé
            $request['exchange_requester_id'],
            null // exchange_accepter_id est null au début
        );

        // Sauvegarde dans la base de données
        if ($controller->partageModel->save($partage)) {
            // Redirection ou affichage d'un message de succès
            // Redirection vers les sessions pour préserver l'état de session
            header('Location: /sessions?success=partage_cree');
            exit;
        } else {
            // Redirection avec message d'erreur
            header('Location: /createSession?error=partage_error');
            exit;
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

        // Gestion du skill enseigné
        $skillModel = new \Models\SkillModel($this->pdo, $this->logger);
        $taughtCategoryId = $request['skill_taught_id'];
        $taughtCompetence = $request['description']; // Récupération du nom de compétence enseignée

        // Création ou récupération du skill enseigné
        $skillTaughtId = $skillModel->create($taughtCompetence, $taughtCategoryId);

        if (!$skillTaughtId) {
            $this->logger->error("Échec de la création/mise à jour de la compétence enseignée", [
                'competence' => $taughtCompetence,
                'category_id' => $taughtCategoryId
            ]);
            return [
                'status' => 'error',
                'message' => 'Erreur lors de la mise à jour de la compétence enseignée'
            ];
        }

        // Gestion du skill demandé
        $requestedCategoryId = $request['skill_requested_id'];
        $requestedCompetence = $request['competence_requested']; // Récupération du nom de compétence demandée

        // Création ou récupération du skill demandé
        $skillRequestedId = $skillModel->create($requestedCompetence, $requestedCategoryId);

        if (!$skillRequestedId) {
            $this->logger->error("Échec de la création/mise à jour de la compétence demandée", [
                'competence' => $requestedCompetence,
                'category_id' => $requestedCategoryId
            ]);
            return [
                'status' => 'error',
                'message' => 'Erreur lors de la mise à jour de la compétence demandée'
            ];
        }

        $this->logger->info("Compétences créées ou mises à jour avec succès", [
            'skill_taught_id' => $skillTaughtId,
            'skill_taught_name' => $taughtCompetence,
            'skill_requested_id' => $skillRequestedId,
            'skill_requested_name' => $requestedCompetence
        ]);

        $partage->setDescription(""); // Description vide, car on utilise maintenant la table skill
        $partage->setRateId($request['rate_id']);
        $partage->setSkillTaughtId($skillTaughtId);

        // Mise à jour des données spécifiques au partage
        $partage->setSkillRequestedId($skillRequestedId);
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

    /**
     * Méthode pour rejoindre un partage depuis l'interface utilisateur
     * Tarifs : L'utilisateur gagne 40 jetons, le créateur du partage gagne aussi 40 jetons
     */
    public static function joinPartage($args)
    {
        // Vérifier l'authentification
        if (!\Util\AuthMiddleware::isAuthenticated()) {
            header('Location: /login');
            exit;
        }

        $container = \Util\Container::getContainer();
        $logger = $container->get(\Psr\Log\LoggerInterface::class);
        $userModel = $container->get(\Models\UserModel::class);

        // Création du contrôleur
        $controller = new self();

        $session_id = $args['id'];
        $user_id = $_SESSION['user_id'];

        $logger->info("Tentative de rejoindre le partage", ['session_id' => $session_id, 'user_id' => $user_id]);

        $partage = $controller->partageModel->getById($session_id);

        if (!$partage) {
            $logger->error("Partage non trouvé", ['session_id' => $session_id]);
            header('Location: /sessions?error=exchange_not_found');
            exit;
        }

        // Vérifier que le partage n'a pas déjà été accepté
        if ($partage->getExchangeAccepterId()) {
            $logger->warning("Partage déjà accepté", ['session_id' => $session_id]);
            header('Location: /sessions?error=exchange_already_accepted');
            exit;
        }

        // Récupérer l'ID du créateur du partage
        $requesterId = $partage->getExchangeRequesterId();

        // Obtenir PDO une fois pour toutes les opérations
        $pdo = $container->get(\PDO::class);

        try {
            // Débuter une transaction pour s'assurer que toutes les opérations sont atomiques
            $pdo->beginTransaction();

            // Définir l'utilisateur comme accepteur du partage
            $partage->setExchangeAccepterId($user_id);

            if (!$controller->partageModel->save($partage)) {
                throw new \Exception("Échec de l'enregistrement de l'acceptation du partage");
            }

            // Ajouter 40 jetons à l'utilisateur qui rejoint le partage
            if (!$userModel->updateBalance($user_id, 40)) {
                throw new \Exception("Échec de l'ajout des jetons pour l'accepteur");
            }

            // Ajouter 40 jetons au créateur du partage
            if (!$userModel->updateBalance($requesterId, 40)) {
                throw new \Exception("Échec de l'ajout des jetons pour le créateur");
            }

            // Valider la transaction
            $pdo->commit();

            $logger->info("Utilisateur a rejoint le partage avec succès", [
                'session_id' => $session_id,
                'user_id' => $user_id,
                'tokens_added_to_user' => 40,
                'requester_id' => $requesterId,
                'tokens_added_to_requester' => 40
            ]);

            header('Location: /sessions?success=joined_exchange');
            exit;
        } catch (\Exception $e) {
            // En cas d'erreur, annuler toutes les modifications seulement si une transaction est active
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            $logger->error("Échec pour rejoindre le partage: " . $e->getMessage(), [
                'session_id' => $session_id,
                'user_id' => $user_id,
                'exception' => $e
            ]);

            header('Location: /sessions?error=cannot_join_exchange');
            exit;
        }
    }
}
