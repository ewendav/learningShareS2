<?php

namespace Controllers;

use Entity\Cours;
use Models\CoursModel;
use Models\LocationModel;
use PDO;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

class CoursController
{
    private $coursModel;

    private $locationModel;

    private $logger;

    /**
     * Constructeur
     */
    public function __construct(?PDO $pdo = null, ?Logger $logger = null)
    {
        if ($pdo === null) {
            $container = \Util\Container::getContainer();
            $pdo = $container->get(PDO::class);
        }
        if ($logger === null) {
            $container = \Util\Container::getContainer();
            $logger = $container->get(LoggerInterface::class);
        }
        $this->coursModel = new CoursModel($pdo, $logger);
        $this->locationModel = new LocationModel($pdo, $logger);
        $this->logger = $logger;
    }

    /**
     * Affiche la liste des cours
     */
    public function index()
    {
        $cours = $this->coursModel->getAll();

        // En fonction de votre système de rendu de vue
        // return view('cours/index', ['cours' => $cours]);

        // Pour l'exemple, retourne simplement les données
        return [
            'status' => 'success',
            'data' => $cours
        ];
    }

    /**
     * Affiche les cours hébergés par un utilisateur
     */
    public function userHosted($host_id)
    {
        $cours = $this->coursModel->getByHostId($host_id);

        // return view('cours/user-hosted', ['cours' => $cours]);
        return [
            'status' => 'success',
            'data' => $cours
        ];
    }

    /**
     * Affiche les détails d'un cours
     */
    public function show($session_id)
    {
        $cours = $this->coursModel->getById($session_id);

        if (!$cours) {
            // return view('errors/404', ['message' => 'Cours non trouvé']);
            $this->logger->error("Cours non trouvé", ['session_id' => $session_id]);
            return [
                'status' => 'error',
                'message' => 'Cours non trouvé'
            ];
        }

        $this->logger->info("Affichage du cours", ['session_id' => $session_id]);

        $attendees = $this->coursModel->getAttendees($session_id);

        // return view('cours/show', ['cours' => $cours, 'attendees' => $attendees]);
        return [
            'status' => 'success',
            'data' => [
                'cours' => $cours,
                'attendees' => $attendees
            ]
        ];
    }

    /**
     * Affiche le formulaire de création d'un cours
     */
    public function create()
    {
        // return view('cours/create');
        return [
            'status' => 'success',
            'message' => 'Formulaire de création de cours'
        ];
    }

    /**
     * Stocke un nouveau cours
     */
    public static function store()
    {
        // Récupération du container
        $container = \Util\Container::getContainer();
        $twig = $container->get(\Twig\Environment::class);
        $logger = $container->get(LoggerInterface::class);
        $pdo = $container->get(PDO::class);

        // Création du contrôleur
        $controller = new self();

        // Récupération des données du formulaire
        $request = $_POST;

        $logger->info("Tentative de création d'un cours", ['request' => $request]);

        // Validation des données (à implémenter)

        // Création d'une nouvelle location
        $address = isset($request['address']) ? $request['address'] : '';
        $zipCode = isset($request['zip_code']) ? $request['zip_code'] : '';
        $city = isset($request['city']) ? $request['city'] : '';

        // Créer la localisation et récupérer son ID
        $locationId = $controller->locationModel->create($address, $zipCode, $city);

        if (!$locationId) {
            // Erreur lors de la création de la localisation
            $logger->error("Échec de la création de la localisation", ['request' => $request]);
            // Redirection avec message d'erreur
            header('Location: /createSession?error=location_error');
            exit;
        }

        // Gestion du skill (nouvelle partie)
        $skillModel = new \Models\SkillModel($pdo, $logger);
        $categoryId = $request['skill_taught_id'];
        $competence = $request['description']; // Récupération du nom de compétence

        // Création ou récupération du skill
        $skillId = $skillModel->create($competence, $categoryId);

        if (!$skillId) {
            $logger->error("Échec de la création de la compétence", [
                'competence' => $competence,
                'category_id' => $categoryId
            ]);
            header('Location: /createSession?error=skill_error');
            exit;
        }

        $logger->info("Compétence créée ou récupérée avec succès", [
            'skill_id' => $skillId,
            'skill_name' => $competence
        ]);

        // Création de l'objet Cours avec la nouvelle location et le skill
        $cours = new Cours(
            null,
            $request['start_time'],
            $request['end_time'],
            $request['date_session'],
            "", // Description vide, car on utilise maintenant la table skill
            $request['rate_id'],
            $skillId, // Utilisation du skill_id créé ou récupéré
            $locationId,
            $request['lesson_host_id'],
            (int)$request['max_attendees']
        );

        // Sauvegarde dans la base de données
        if ($controller->coursModel->save($cours)) {
            // Redirection ou affichage d'un message de succès
            $logger->info("Cours créé avec succès", ['cours' => $cours]);

            // Redirection vers les sessions pour préserver l'état de session
            header('Location: /sessions?success=cours_cree');
            exit;
        } else {
            // Affichage d'un message d'erreur
            $logger->error("Erreur lors de l'enregistrement du cours", ['cours' => $cours]);

            // Redirection avec message d'erreur
            header('Location: /createSession?error=cours_error');
            exit;
        }
    }

    /**
     * Affiche le formulaire d'édition d'un cours
     */
    public function edit($session_id)
    {
        $cours = $this->coursModel->getById($session_id);

        if (!$cours) {
            // return view('errors/404', ['message' => 'Cours non trouvé']);
            return [
                'status' => 'error',
                'message' => 'Cours non trouvé'
            ];
        }

        // return view('cours/edit', ['cours' => $cours]);
        return [
            'status' => 'success',
            'data' => $cours
        ];
    }

    /**
     * Met à jour un cours
     */
    public function update($session_id, $request)
    {
        $cours = $this->coursModel->getById($session_id);

        if (!$cours) {
            // return view('errors/404', ['message' => 'Cours non trouvé']);
            $this->logger->warning("Cours non trouvé pour modification", ['session_id' => $session_id]);

            return [
                'status' => 'error',
                'message' => 'Cours non trouvé'
            ];
        }

        // Mise à jour des données de base (Session)
        $cours->setStartTime($request['start_time']);
        $cours->setEndTime($request['end_time']);
        $cours->setDateSession($request['date_session']);

        // Gestion du skill
        $skillModel = new \Models\SkillModel($this->pdo, $this->logger);
        $categoryId = $request['skill_taught_id'];
        $competence = $request['description']; // Récupération du nom de compétence

        // Création ou récupération du skill
        $skillId = $skillModel->create($competence, $categoryId);

        if (!$skillId) {
            $this->logger->error("Échec de la création/mise à jour de la compétence", [
                'competence' => $competence,
                'category_id' => $categoryId
            ]);
            return [
                'status' => 'error',
                'message' => 'Erreur lors de la mise à jour de la compétence'
            ];
        }

        $this->logger->info("Compétence créée ou mise à jour avec succès", [
            'skill_id' => $skillId,
            'skill_name' => $competence
        ]);

        $cours->setDescription(""); // Description vide, car on utilise maintenant la table skill
        $cours->setRateId($request['rate_id']);
        $cours->setSkillTaughtId($skillId); // Utilisation du skill_id créé ou récupéré

        // Gérer la mise à jour de la localisation
        if (isset($request['address']) && isset($request['zip_code']) && isset($request['city'])) {
            // Créer une nouvelle localisation ou récupérer l'existante
            $locationId = $this->locationModel->create(
                $request['address'],
                $request['zip_code'],
                $request['city']
            );

            if ($locationId) {
                $cours->setLocationId($locationId);
            }
        } elseif (isset($request['location_id'])) {
            // Si location_id est fourni directement (pour la compatibilité avec l'existant)
            $cours->setLocationId($request['location_id']);
        }

        $cours->setLessonHostId($request['lesson_host_id']);
        $cours->setMaxAttendees($request['max_attendees']);

        if ($this->coursModel->save($cours)) {
            // redirect('cours/' . $cours->getSessionId());
            $this->logger->info("Cours mis à jour", ['session_id' => $session_id]);

            return [
                'status' => 'success',
                'message' => 'Cours mis à jour avec succès',
                'data' => $cours
            ];
        } else {
            // return view('cours/edit', ['cours' => $cours, 'error' => 'Erreur lors de la mise à jour du cours']);
            return [
                'status' => 'error',
                'message' => 'Erreur lors de la mise à jour du cours'
            ];
        }
    }

    /**
     * Supprime un cours
     */
    public function destroy($session_id)
    {
        $cours = $this->coursModel->getById($session_id);

        if (!$cours) {
            // return view('errors/404', ['message' => 'Cours non trouvé']);
            $this->logger->warning("Tentative de suppression d'un cours introuvable", ['session_id' => $session_id]);

            return [
                'status' => 'error',
                'message' => 'Cours non trouvé'
            ];
        }

        if ($this->coursModel->delete($cours)) {
            // redirect('cours');
            $this->logger->info("Cours supprimé", ['session_id' => $session_id]);

            return [
                'status' => 'success',
                'message' => 'Cours supprimé avec succès'
            ];
        } else {
            // return view('cours/show', ['cours' => $cours, 'error' => 'Erreur lors de la suppression du cours']);
            $this->logger->error("Échec de la suppression du cours", ['session_id' => $session_id]);

            return [
                'status' => 'error',
                'message' => 'Erreur lors de la suppression du cours'
            ];
        }
    }

    /**
     * Inscrit un utilisateur à un cours
     */
    public function register($session_id, $user_id)
    {
        if ($this->coursModel->addAttendee($session_id, $user_id)) {
            // redirect('cours/' . $session_id);
            $this->logger->info("Inscription de l'utilisateur au cours", ['session_id' => $session_id]);

            return [
                'status' => 'success',
                'message' => 'Inscription au cours réussie'
            ];
        } else {
            // redirect('cours/' . $session_id, ['error' => 'Erreur lors de l\'inscription au cours']);
            $this->logger->error("Échec de l'inscription de l'utilisateur au cours", ['session_id' => $session_id]);

            return [
                'status' => 'error',
                'message' => 'Erreur lors de l\'inscription au cours'
            ];
        }
    }

    /**
     * Désinscrit un utilisateur d'un cours
     */
    public function unregister($session_id, $user_id)
    {
        if ($this->coursModel->removeAttendee($session_id, $user_id)) {
            // redirect('cours/' . $session_id);
            return [
                'status' => 'success',
                'message' => 'Désinscription du cours réussie'
            ];
        } else {
            // redirect('cours/' . $session_id, ['error' => 'Erreur lors de la désinscription du cours']);
            return [
                'status' => 'error',
                'message' => 'Erreur lors de la désinscription du cours'
            ];
        }
    }

    /**
     * Méthode pour rejoindre un cours depuis l'interface utilisateur
     * Tarifs : L'utilisateur perd 25 jetons, l'hôte en gagne 20
     */
    public static function joinCours($args)
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

        $logger->info("Tentative de rejoindre le cours", ['session_id' => $session_id, 'user_id' => $user_id]);

        // Récupérer les informations du cours pour obtenir l'ID de l'hôte
        $cours = $controller->coursModel->getById($session_id);
        if (!$cours) {
            $logger->error("Cours non trouvé", ['session_id' => $session_id]);
            header('Location: /sessions?error=course_not_found');
            exit;
        }

        $hostId = $cours->getLessonHostId();

        // Vérifier si l'utilisateur a suffisamment de jetons (25 requis)
        if (!$userModel->hasEnoughTokens($user_id, 25)) {
            $logger->error("L'utilisateur n'a pas assez de jetons", ['user_id' => $user_id]);
            header('Location: /sessions?error=not_enough_tokens');
            exit;
        }

        // Obtenir PDO une fois pour toutes les opérations
        $pdo = $container->get(\PDO::class);

        try {
            // Débuter une transaction pour s'assurer que toutes les opérations sont atomiques
            $pdo->beginTransaction();

            // Ajouter l'utilisateur comme participant
            if (!$controller->coursModel->addAttendee($session_id, $user_id)) {
                throw new \Exception("Échec de l'ajout de l'utilisateur au cours");
            }

            // Déduire 25 jetons de l'utilisateur
            if (!$userModel->updateBalance($user_id, -25)) {
                throw new \Exception("Échec de la déduction des jetons pour l'utilisateur");
            }

            // Ajouter 20 jetons à l'hôte
            if (!$userModel->updateBalance($hostId, 20)) {
                throw new \Exception("Échec de l'ajout des jetons pour l'hôte");
            }

            // Valider la transaction
            $pdo->commit();

            $logger->info("Utilisateur a rejoint le cours avec succès", [
                'session_id' => $session_id,
                'user_id' => $user_id,
                'tokens_deducted' => 25,
                'host_id' => $hostId,
                'tokens_added_to_host' => 20
            ]);

            header('Location: /sessions?success=joined_course');
            exit;
        } catch (\Exception $e) {
            // En cas d'erreur, annuler toutes les modifications seulement si une transaction est active
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            $logger->error("Échec pour rejoindre le cours: " . $e->getMessage(), [
                'session_id' => $session_id,
                'user_id' => $user_id,
                'exception' => $e
            ]);

            header('Location: /sessions?error=cannot_join_course');
            exit;
        }
    }
}
