<?php

namespace Models;

use Entity\Cours;
use PDO;
use PDOException;
use Psr\Log\LoggerInterface;

class CoursModel
{
    private $pdo;
    private $sessionModel;
    private $logger;

    /**
     * Constructeur
     */
    public function __construct(PDO $pdo, LoggerInterface $logger)
    {
        $this->pdo = $pdo;
        $this->sessionModel = new SessionModel($pdo, $logger);
        $this->logger = $logger;
    }

    /**
     * Sauvegarde un cours dans la base de données
     */
    public function save(Cours $cours)
    {
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
            $this->logger->info("Cours sauvegardé avec succès", ['session_id' => $session_id]);
            return true;
        } catch (PDOException $e) {
            // Annuler la transaction en cas d'erreur
            $this->pdo->rollBack();
            $this->logger->error("Erreur lors de la sauvegarde du cours: " . $e->getMessage(), ['exception' => $e]);
            return false;
        }
    }

    /**
     * Récupérer un cours par son ID de session
     */
    public function getById($session_id)
    {
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
            $this->logger->error("Erreur lors de la récupération du cours: " . $e->getMessage(), ['exception' => $e]);
            return null;
        }
    }

    /**
     * Supprimer un cours
     */
    public function delete(Cours $cours)
    {
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
            $this->logger->error("Erreur lors de la suppression du cours: " . $e->getMessage(), ['exception' => $e]);
            return false;
        }
    }

    /**
     * Récupérer tous les cours
     */
/**
 * Récupérer toutes les leçons qui ont encore de la place pour des participants et dont la date de fin n'est pas expirée
 */
    public function getLessonDispo(bool $retourneJson = false)
    {
        try {
            $currentDateTime = date('Y-m-d H:i:s'); // Get the current date and time
            $stmt = $this->pdo->query("
SELECT
    l.lesson_session_id,
    s.start_time,
    s.end_time,
    s.date_session,
    s.description,
    s.rate_id,
    l.max_attendees,
    COUNT(a.attend_user_id) AS current_attendees,
    taught_skill.skill_id AS skill_taught_id,
    taught_skill.category_id AS skill_taught_category_id,
    req_user.user_first_name AS host_first_name,
    req_user.user_last_name AS host_last_name,
    req_user.avatar_path AS host_avatar,
    taught_skill.skill_name AS skill_taught_name,
    CONCAT(loc.address, ', ', loc.zip_code, ', ', loc.city) AS full_address
FROM
    LESSON l
JOIN
    SESSION s ON l.lesson_session_id = s.session_id
JOIN
    APP_USER req_user ON l.lesson_host_id = req_user.user_id
LEFT JOIN
    ATTEND a ON l.lesson_session_id = a.attend_lesson_id
JOIN
    SKILL taught_skill ON s.skill_taught_id = taught_skill.skill_id
JOIN
    LOCATION loc ON l.location_id = loc.location_id
WHERE
    (s.date_session + s.end_time) > NOW()
GROUP BY
    l.lesson_session_id, s.start_time, s.end_time, s.date_session, s.description, s.rate_id, l.max_attendees, req_user.user_first_name, req_user.user_last_name, req_user.avatar_path, taught_skill.skill_id, taught_skill.category_id, taught_skill.skill_name, loc.address, loc.zip_code, loc.city
HAVING
    l.max_attendees > COUNT(a.attend_user_id) OR COUNT(a.attend_user_id) IS NULL;
        ");

            $lessons = [];

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                if ($retourneJson) {
                    $lessons[] = [
                    'lesson_session_id' => $row['lesson_session_id'],
                    'start_time' => $row['start_time'],
                    'end_time' => $row['end_time'],
                    'date_session' => $row['date_session'],
                    'description' => $row['description'],
                    'rate_id' => $row['rate_id'],
                    'max_attendees' => $row['max_attendees'],
                    'current_attendees' => $row['current_attendees'],
                    'host_first_name' => $row['host_first_name'],
                    'host_last_name' => $row['host_last_name'],
                    'host_avatar' => $row['host_avatar'],
                    'skill_taught_id' => $row['skill_taught_id'],
                    'skill_taught_category_id' => $row['skill_taught_category_id'],
                    'skill_taught_name' => $row['skill_taught_name'],
                    'full_address' => $row['full_address'],
                    ];
                }
            }
            $this->logger->info("Récupération de toutes les leçons disponibles", ['lessons_count' => count($lessons)]);
            return $lessons;
        } catch (PDOException $e) {
            $this->logger->error("Erreur lors de la récupération des leçons disponibles", ['exception' => $e]);
            return [];
        }
    }

    /**
     * Récupérer les cours par hôte
     */
    public function getByHostId($host_id)
    {
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
            $this->logger->error("Erreur lors de la récupération des cours par hôte: " . $e->getMessage(), ['exception' => $e]);
            return [];
        }
    }

    /**
     * Récupérer les participants à un cours
     */
    public function getAttendees($cours_id)
    {
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
            $this->logger->error("Erreur lors de la récupération des participants: " . $e->getMessage(), ['exception' => $e]);
            return [];
            return [];
        }
    }

    /**
     * Ajouter un participant à un cours
     */
    public function addAttendee($cours_id, $user_id)
    {
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
            $this->logger->error("Erreur lors de l'ajout d'un participant: " . $e->getMessage(), ['exception' => $e]);
            return false;
        }
    }

    /**
     * Supprimer un participant d'un cours
     */
    public function removeAttendee($cours_id, $user_id)
    {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM ATTEND WHERE attend_lesson_id = :cours_id AND attend_user_id = :user_id");
            $stmt->bindParam(':cours_id', $cours_id);
            $stmt->bindParam(':user_id', $user_id);
            return $stmt->execute();
        } catch (PDOException $e) {
            $this->logger->error("Erreur lors de la suppression d'un participant: " . $e->getMessage(), ['exception' => $e]);
            return false;
        }
    }

    /**
     * Compter le nombre de participants à un cours
     */
    public function countAttendees($cours_id)
    {
        try {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM ATTEND WHERE attend_lesson_id = :cours_id");
            $stmt->bindParam(':cours_id', $cours_id);
            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['count'];
        } catch (PDOException $e) {
            $this->logger->error("Erreur lors du comptage des participants: " . $e->getMessage(), ['exception' => $e]);
            return 0;
        }
    }

    /**
     * Vérifier si un cours est complet
     */
    public function isFull($cours_id)
    {
        try {
            $cours = $this->getById($cours_id);
            if (!$cours) {
                return false;
            }

            $count = $this->countAttendees($cours_id);
            return $count >= $cours->getMaxAttendees();
        } catch (PDOException $e) {
            $this->logger->error("Erreur lors de la vérification si le cours est complet: " . $e->getMessage(), ['exception' => $e]);
            return false;
        }
    }

    /**
     * Récupérer les cours auxquels un utilisateur participe
     */
    public function getByAttendeeId($user_id)
    {
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
            $this->logger->error("Erreur lors de la récupération des cours par participant: " . $e->getMessage(), ['exception' => $e]);
            return [];
        }
    }

    /**
     * Vérifier si un utilisateur est déjà inscrit à un cours
     */
    public function isUserRegistered($cours_id, $user_id)
    {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM ATTEND WHERE attend_lesson_id = :cours_id AND attend_user_id = :user_id");
            $stmt->bindParam(':cours_id', $cours_id);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();

            return $stmt->fetch() !== false;
        } catch (PDOException $e) {
            $this->logger->error("Erreur lors de la vérification de l'inscription: " . $e->getMessage(), ['exception' => $e]);
            return false;
        }
    }
}
