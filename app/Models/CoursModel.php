<?php
namespace Models;

use Entity\Cours;
use PDO;
use PDOException;

class CoursModel {
    private $pdo;
    private $sessionModel;

    /**
     * Constructeur
     */
    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
        $this->sessionModel = new SessionModel($pdo);
    }

    /**
     * Sauvegarde un cours dans la base de données
     */
    public function save(Cours $cours) {
        try {
            // Commencer une transaction
            $this->pdo->beginTransaction();


            // Sauvegarder la session parent
            if (!$this->sessionModel->save($cours)) {
                $this->pdo->rollBack();
                return false;
            }

            $session_id = $cours->getSessionId();

            // Vérifier si ce cours existe déjà
            $stmt = $this->pdo->prepare("SELECT * FROM LESSON WHERE lesson_session_id = :session_id");
            $stmt->bindParam(':session_id', $session_id);
            $stmt->execute();

            if ($stmt->fetch()) {
                // Mise à jour d'un cours existant
                $stmt = $this->pdo->prepare("UPDATE LESSON SET location_id = :location_id, 
                                       lesson_host_id = :lesson_host_id, 
                                       max_attendees = :max_attendees 
                                       WHERE lesson_session_id = :session_id");
            } else {
                // Création d'un nouveau cours
                $stmt = $this->pdo->prepare("INSERT INTO LESSON (lesson_session_id, location_id, 
                                       lesson_host_id, max_attendees) 
                                       VALUES (:session_id, :location_id, 
                                       :lesson_host_id, :max_attendees)");
            }

            // Stocker les valeurs dans des variables intermédiaires
            $location_id = $cours->getLocationId();
            $lesson_host_id = $cours->getLessonHostId();
            $max_attendees = $cours->getMaxAttendees();

            // Utiliser les variables pour bindParam
            $stmt->bindParam(':session_id', $session_id);
            $stmt->bindParam(':location_id', $location_id);
            $stmt->bindParam(':lesson_host_id', $lesson_host_id);
            $stmt->bindParam(':max_attendees', $max_attendees);

            $stmt->execute();

            // Valider la transaction
            $this->pdo->commit();
            error_log("CoursModel::save - Cours sauvegardé avec succès");
            return true;
        } catch (PDOException $e) {
            // Annuler la transaction en cas d'erreur
            $this->pdo->rollBack();
            error_log("Erreur lors de la sauvegarde du cours: " . $e->getMessage());
            error_log("CoursModel::save - Trace: " . $e->getTraceAsString());
            return false;
        }
    }

    /**
     * Récupérer un cours par son ID de session
     */
    public function getById($session_id) {
        try {
            // Récupérer d'abord la session
            $session = $this->sessionModel->getById($session_id);

            if (!$session) {
                return null;
            }

            // Récupérer les données du cours
            $stmt = $this->pdo->prepare("SELECT * FROM LESSON WHERE lesson_session_id = :session_id");
            $stmt->bindParam(':session_id', $session_id);
            $stmt->execute();

            if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                return new Cours(
                    $session->getSessionId(),
                    $session->getStartTime(),
                    $session->getEndTime(),
                    $session->getDateSession(),
                    $session->getDescription(),
                    $session->getRateId(),
                    $session->getSkillTaughtId(),
                    $row['location_id'],
                    $row['lesson_host_id'],
                    $row['max_attendees']
                );
            }
            return null;
        } catch (PDOException $e) {
            error_log("Erreur lors de la récupération du cours: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Supprimer un cours
     */
    public function delete(Cours $cours) {
        try {
            // Commencer une transaction
            $this->pdo->beginTransaction();

            // Récupérer l'ID de session
            $session_id = $cours->getSessionId();

            // Supprimer d'abord toutes les participations à ce cours
            $stmt = $this->pdo->prepare("DELETE FROM ATTEND WHERE attend_lesson_id = :session_id");
            $stmt->bindParam(':session_id', $session_id);
            $stmt->execute();

            // Supprimer le cours
            $stmt = $this->pdo->prepare("DELETE FROM LESSON WHERE lesson_session_id = :session_id");
            $stmt->bindParam(':session_id', $session_id);
            $stmt->execute();

            // Supprimer la session parent
            if (!$this->sessionModel->delete($cours)) {
                $this->pdo->rollBack();
                return false;
            }

            // Valider la transaction
            $this->pdo->commit();
            return true;
        } catch (PDOException $e) {
            // Annuler la transaction en cas d'erreur
            $this->pdo->rollBack();
            error_log("Erreur lors de la suppression du cours: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Récupérer tous les cours
     */
    public function getAll() {
        try {
            $stmt = $this->pdo->query("SELECT s.*, l.location_id, l.lesson_host_id, l.max_attendees 
                                FROM SESSION s 
                                JOIN LESSON l ON s.session_id = l.lesson_session_id");
            $cours = [];

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $cours[] = new Cours(
                    $row['session_id'],
                    $row['start_time'],
                    $row['end_time'],
                    $row['date_session'],
                    $row['description'],
                    $row['rate_id'],
                    $row['skill_taught_id'],
                    $row['location_id'],
                    $row['lesson_host_id'],
                    $row['max_attendees']
                );
            }
            return $cours;
        } catch (PDOException $e) {
            error_log("Erreur lors de la récupération des cours: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Récupérer les cours par hôte
     */
    public function getByHostId($host_id) {
        try {
            $stmt = $this->pdo->prepare("SELECT s.*, l.location_id, l.lesson_host_id, l.max_attendees 
                                  FROM SESSION s 
                                  JOIN LESSON l ON s.session_id = l.lesson_session_id 
                                  WHERE l.lesson_host_id = :host_id");
            $stmt->bindParam(':host_id', $host_id);
            $stmt->execute();

            $cours = [];

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $cours[] = new Cours(
                    $row['session_id'],
                    $row['start_time'],
                    $row['end_time'],
                    $row['date_session'],
                    $row['description'],
                    $row['rate_id'],
                    $row['skill_taught_id'],
                    $row['location_id'],
                    $row['lesson_host_id'],
                    $row['max_attendees']
                );
            }
            return $cours;
        } catch (PDOException $e) {
            error_log("Erreur lors de la récupération des cours par hôte: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Récupérer les participants à un cours
     */
    public function getAttendees($cours_id) {
        try {
            $stmt = $this->pdo->prepare("SELECT u.* FROM APP_USER u 
                                  JOIN ATTEND a ON u.user_id = a.attend_user_id 
                                  WHERE a.attend_lesson_id = :cours_id");
            $stmt->bindParam(':cours_id', $cours_id);
            $stmt->execute();

            $attendees = [];

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                // Retourne les données brutes des utilisateurs
                // Note: Idéalement, on utiliserait un UserModel pour créer des objets User
                $attendees[] = $row;
            }
            return $attendees;
        } catch (PDOException $e) {
            error_log("Erreur lors de la récupération des participants: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Ajouter un participant à un cours
     */
    public function addAttendee($cours_id, $user_id) {
        try {
            // Récupérer le cours pour vérifier s'il n'est pas déjà complet
            $cours = $this->getById($cours_id);
            if (!$cours) {
                return false;
            }

            $currentAttendees = count($this->getAttendees($cours_id));

            if ($currentAttendees >= $cours->getMaxAttendees()) {
                return false; // Le cours est complet
            }

            // Vérifier si l'utilisateur n'est pas déjà inscrit
            $stmt = $this->pdo->prepare("SELECT * FROM ATTEND WHERE attend_lesson_id = :cours_id AND attend_user_id = :user_id");
            $stmt->bindParam(':cours_id', $cours_id);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();

            if ($stmt->fetch()) {
                return false; // L'utilisateur est déjà inscrit
            }

            // Générer un nouvel ID de participation
            $stmt = $this->pdo->query("SELECT MAX(attend_id) as max_id FROM ATTEND");
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $new_attend_id = ($row['max_id'] ?? 0) + 1;

            // Inscrire l'utilisateur au cours
            $stmt = $this->pdo->prepare("INSERT INTO ATTEND (attend_id, attend_lesson_id, attend_user_id) 
                                  VALUES (:attend_id, :cours_id, :user_id)");
            $stmt->bindParam(':attend_id', $new_attend_id);
            $stmt->bindParam(':cours_id', $cours_id);
            $stmt->bindParam(':user_id', $user_id);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erreur lors de l'ajout d'un participant: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Supprimer un participant d'un cours
     */
    public function removeAttendee($cours_id, $user_id) {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM ATTEND WHERE attend_lesson_id = :cours_id AND attend_user_id = :user_id");
            $stmt->bindParam(':cours_id', $cours_id);
            $stmt->bindParam(':user_id', $user_id);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erreur lors de la suppression d'un participant: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Compter le nombre de participants à un cours
     */
    public function countAttendees($cours_id) {
        try {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM ATTEND WHERE attend_lesson_id = :cours_id");
            $stmt->bindParam(':cours_id', $cours_id);
            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['count'];
        } catch (PDOException $e) {
            error_log("Erreur lors du comptage des participants: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Vérifier si un cours est complet
     */
    public function isFull($cours_id) {
        try {
            $cours = $this->getById($cours_id);
            if (!$cours) {
                return false;
            }

            $count = $this->countAttendees($cours_id);
            return $count >= $cours->getMaxAttendees();
        } catch (PDOException $e) {
            error_log("Erreur lors de la vérification si le cours est complet: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Récupérer les cours auxquels un utilisateur participe
     */
    public function getByAttendeeId($user_id) {
        try {
            $stmt = $this->pdo->prepare("SELECT s.*, l.location_id, l.lesson_host_id, l.max_attendees 
                                  FROM SESSION s 
                                  JOIN LESSON l ON s.session_id = l.lesson_session_id 
                                  JOIN ATTEND a ON l.lesson_session_id = a.attend_lesson_id 
                                  WHERE a.attend_user_id = :user_id");
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();

            $cours = [];

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $cours[] = new Cours(
                    $row['session_id'],
                    $row['start_time'],
                    $row['end_time'],
                    $row['date_session'],
                    $row['description'],
                    $row['rate_id'],
                    $row['skill_taught_id'],
                    $row['location_id'],
                    $row['lesson_host_id'],
                    $row['max_attendees']
                );
            }
            return $cours;
        } catch (PDOException $e) {
            error_log("Erreur lors de la récupération des cours par participant: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Vérifier si un utilisateur est déjà inscrit à un cours
     */
    public function isUserRegistered($cours_id, $user_id) {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM ATTEND WHERE attend_lesson_id = :cours_id AND attend_user_id = :user_id");
            $stmt->bindParam(':cours_id', $cours_id);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();

            return $stmt->fetch() !== false;
        } catch (PDOException $e) {
            error_log("Erreur lors de la vérification de l'inscription: " . $e->getMessage());
            return false;
        }
    }
}