<?php

namespace Models;

use Entity\Session;
use PDO;
use PDOException;

class SessionModel
{
    private $pdo;

    /**
     * Constructeur
     */
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }





    /**
     * Sauvegarde une session dans la base de données
     */
    public function save(Session $session)
    {
        try {
            if ($session->getSessionId()) {
                $sessionID = $session->getSessionId();
                // Mise à jour d'une session existante
                $stmt = $this->pdo->prepare(
                    "UPDATE SESSION SET start_time = :start_time, end_time = :end_time, 
                                      date_session = :date_session, description = :description, rate_id = :rate_id, 
                                      skill_taught_id = :skill_taught_id WHERE session_id = :session_id"
                );
                $stmt->bindParam(':session_id', $sessionID);
            } else {
                // Création d'une nouvelle session
                $stmt = $this->pdo->prepare(
                    "INSERT INTO SESSION (start_time, end_time, date_session, description, 
                                      rate_id, skill_taught_id) VALUES (:start_time, :end_time, :date_session, 
                                      :description, :rate_id, :skill_taught_id)"
                );
            }

            // Stocker les valeurs dans des variables intermédiaires
            $start_time = $session->getStartTime();
            $end_time = $session->getEndTime();
            $date_session = $session->getDateSession();
            $description = $session->getDescription();
            $rate_id = $session->getRateId();
            $skill_taught_id = $session->getSkillTaughtId();

            // Utiliser les variables pour bindParam
            $stmt->bindParam(':start_time', $start_time);
            $stmt->bindParam(':end_time', $end_time);
            $stmt->bindParam(':date_session', $date_session);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':rate_id', $rate_id);
            $stmt->bindParam(':skill_taught_id', $skill_taught_id);
            $stmt->execute();

            if (!$session->getSessionId()) {
                $session->setSessionId($this->pdo->lastInsertId());
            }

            return true;
        } catch (PDOException $e) {
            error_log("Erreur lors de la sauvegarde de la session: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Récupérer une session par son ID
     */
    public function getById($session_id)
    {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM SESSION WHERE session_id = :session_id");
            $stmt->bindParam(':session_id', $session_id);
            $stmt->execute();

            if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                return new Session(
                    $row['session_id'],
                    $row['start_time'],
                    $row['end_time'],
                    $row['date_session'],
                    $row['description'],
                    $row['rate_id'],
                    $row['skill_taught_id']
                );
            }
            return null;
        } catch (PDOException $e) {
            error_log("Erreur lors de la récupération de la session: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Supprimer une session
     */
    public function delete(Session $session)
    {
        try {
            $sessionID = $session->getSessionId();
            $stmt = $this->pdo->prepare("DELETE FROM SESSION WHERE session_id = :session_id");
            $stmt->bindParam(':session_id', $sessionID);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erreur lors de la suppression de la session: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Récupérer toutes les sessions
     */
    public function getAll()
    {
        try {
            $stmt = $this->pdo->query("SELECT * FROM SESSION");
            $sessions = [];

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $sessions[] = new Session(
                    $row['session_id'],
                    $row['start_time'],
                    $row['end_time'],
                    $row['date_session'],
                    $row['description'],
                    $row['rate_id'],
                    $row['skill_taught_id']
                );
            }
            return $sessions;
        } catch (PDOException $e) {
            error_log("Erreur lors de la récupération des sessions: " . $e->getMessage());
            return [];
        }
    }
}
