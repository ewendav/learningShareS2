<?php
namespace Controller;

use Entity\Cours;
use Model\CoursModel;
use PDO;

class CoursController {
    private $coursModel;

    /**
     * Constructeur
     */
    public function __construct(PDO $pdo) {
        $this->coursModel = new CoursModel($pdo);
    }

    /**
     * Affiche la liste des cours
     */
    public function index() {
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
    public function userHosted($host_id) {
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
    public function show($session_id) {
        $cours = $this->coursModel->getById($session_id);

        if (!$cours) {
            // return view('errors/404', ['message' => 'Cours non trouvé']);
            return [
                'status' => 'error',
                'message' => 'Cours non trouvé'
            ];
        }

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
    public function create() {
        // return view('cours/create');
        return [
            'status' => 'success',
            'message' => 'Formulaire de création de cours'
        ];
    }

    /**
     * Stocke un nouveau cours
     */
    public function store($request) {
        // Validation des données
        // ...

        $cours = new Cours(
            null,
            $request['start_time'],
            $request['end_time'],
            $request['date_session'],
            $request['description'],
            $request['rate_id'],
            $request['skill_taught_id'],
            $request['location_id'],
            $request['lesson_host_id'],
            $request['max_attendees']
        );

        if ($this->coursModel->save($cours)) {
            // redirect('cours/' . $cours->getSessionId());
            return [
                'status' => 'success',
                'message' => 'Cours créé avec succès',
                'data' => $cours
            ];
        } else {
            // return view('cours/create', ['error' => 'Erreur lors de la création du cours']);
            return [
                'status' => 'error',
                'message' => 'Erreur lors de la création du cours'
            ];
        }
    }

    /**
     * Affiche le formulaire d'édition d'un cours
     */
    public function edit($session_id) {
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
    public function update($session_id, $request) {
        $cours = $this->coursModel->getById($session_id);

        if (!$cours) {
            // return view('errors/404', ['message' => 'Cours non trouvé']);
            return [
                'status' => 'error',
                'message' => 'Cours non trouvé'
            ];
        }

        // Mise à jour des données de base (Session)
        $cours->setStartTime($request['start_time']);
        $cours->setEndTime($request['end_time']);
        $cours->setDateSession($request['date_session']);
        $cours->setDescription($request['description']);
        $cours->setRateId($request['rate_id']);
        $cours->setSkillTaughtId($request['skill_taught_id']);

        // Mise à jour des données spécifiques au cours
        $cours->setLocationId($request['location_id']);
        $cours->setLessonHostId($request['lesson_host_id']);
        $cours->setMaxAttendees($request['max_attendees']);

        if ($this->coursModel->save($cours)) {
            // redirect('cours/' . $cours->getSessionId());
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
    public function destroy($session_id) {
        $cours = $this->coursModel->getById($session_id);

        if (!$cours) {
            // return view('errors/404', ['message' => 'Cours non trouvé']);
            return [
                'status' => 'error',
                'message' => 'Cours non trouvé'
            ];
        }

        if ($this->coursModel->delete($cours)) {
            // redirect('cours');
            return [
                'status' => 'success',
                'message' => 'Cours supprimé avec succès'
            ];
        } else {
            // return view('cours/show', ['cours' => $cours, 'error' => 'Erreur lors de la suppression du cours']);
            return [
                'status' => 'error',
                'message' => 'Erreur lors de la suppression du cours'
            ];
        }
    }

    /**
     * Inscrit un utilisateur à un cours
     */
    public function register($session_id, $user_id) {
        if ($this->coursModel->addAttendee($session_id, $user_id)) {
            // redirect('cours/' . $session_id);
            return [
                'status' => 'success',
                'message' => 'Inscription au cours réussie'
            ];
        } else {
            // redirect('cours/' . $session_id, ['error' => 'Erreur lors de l\'inscription au cours']);
            return [
                'status' => 'error',
                'message' => 'Erreur lors de l\'inscription au cours'
            ];
        }
    }

    /**
     * Désinscrit un utilisateur d'un cours
     */
    public function unregister($session_id, $user_id) {
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
}