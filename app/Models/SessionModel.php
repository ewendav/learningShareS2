<?php

namespace Models;

use Entity\Session;
use PDO;
use PDOException;
use Psr\Log\LoggerInterface;

class SessionModel
{
    private $pdo;
    private $logger;

    /**
     * Constructeur
     */
    public function __construct(PDO $pdo, LoggerInterface $logger)
    {
        $this->pdo = $pdo;
        $this->logger = $logger;
    }


    public function getAllAttendableUserSession()
    {
        $user = \Util\AuthMiddleware::getUser();

        if ($user) {
            $partageModel = new PartageModel($this->pdo, $this->logger);
            $coursModel = new CoursModel($this->pdo, $this->logger);

            $exhangesRequested = $partageModel->getByRequesterIdNotExpired($user['id'], true);
            $exhangesAccepted = $partageModel->getByAccepterIdNotExpired($user['id'], true);
            $coursHosted = $coursModel->getByHostIdNotExpired($user['id']);
            $coursAttended = $coursModel->getByAttendeeIdNotExpired($user['id']);

            $cours = array_merge($coursHosted, $coursAttended);
            $partage = array_merge($exhangesRequested, $exhangesAccepted);

            return [
              "cours" => $cours,
              "partage" => $partage,
            ];
        } else {
            return [];
        }
    }





    /**
     * Sauvegarde une session dans la base de données
     */
    public function save(Session $session)
    {
        try {
            $this->logger->info("SessionModel::save - Début de la sauvegarde");

            if ($session->getSessionId()) {
                $sessionID = $session->getSessionId();
                $this->logger->info("SessionModel::save - Mise à jour de la session existante ID=" . $sessionID);
                // Mise à jour d'une session existante
                $stmt = $this->pdo->prepare(
                    "UPDATE session SET start_time = :start_time, end_time = :end_time, 
                                      date_session = :date_session, description = :description, rate_id = :rate_id, 
                                      skill_taught_id = :skill_taught_id WHERE session_id = :session_id"
                );
                $stmt->bindParam(':session_id', $sessionID);
            } else {
                $this->logger->info("SessionModel::save - Création d'une nouvelle session");
                // Création d'une nouvelle session
                $stmt = $this->pdo->prepare(
                    "INSERT INTO session (start_time, end_time, date_session, description, 
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

            $this->logger->info("SessionModel::save - Paramètres: start_time=$start_time, end_time=$end_time, date_session=$date_session, description=$description, rate_id=$rate_id, skill_taught_id=$skill_taught_id");

            // Utiliser les variables pour bindParam
            $stmt->bindParam(':start_time', $start_time);
            $stmt->bindParam(':end_time', $end_time);
            $stmt->bindParam(':date_session', $date_session);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':rate_id', $rate_id);
            $stmt->bindParam(':skill_taught_id', $skill_taught_id);

            $this->logger->info("SessionModel::save - Exécution de la requête SQL");
            $result = $stmt->execute();

            if (!$result) {
                $errorInfo = $stmt->errorInfo();
                error_log("SessionModel::save - Erreur SQL: " . $errorInfo[2]);
                return false;
            }

            if (!$session->getSessionId()) {
                $lastId = $this->pdo->lastInsertId();
                $this->logger->info("SessionModel::save - Nouvelle session créée avec ID=" . $lastId);
                $session->setSessionId($lastId);
            }

            $this->logger->info("SessionModel::save - Session sauvegardée avec succès");
            return true;
        } catch (PDOException $e) {
            $this->logger->error("Erreur lors de la sauvegarde de la session: " . $e->getMessage());
            $this->logger->error("SessionModel::save - Trace: " . $e->getTraceAsString());
            return false;
        }
    }

    /**
     * Récupérer une session par son ID
     */
    public function getById($session_id)
    {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM session WHERE session_id = :session_id");
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
            $this->logger->info("Session récupérée ID =" . $session_id);
            return null;
        } catch (PDOException $e) {
            $this->logger->error("Erreur lors de la récupération de la session: " . $e->getMessage());
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
            $stmt = $this->pdo->prepare("DELETE FROM session WHERE session_id = :session_id");
            $stmt->bindParam(':session_id', $sessionID);

            $result = $stmt->execute();

            if ($result) {
                $this->logger->info("SessionModel::delete - Session ID={$sessionID} supprimée avec succès");
            } else {
                $this->logger->error("SessionModel::delete - Impossible de supprimer la session ID={$sessionID}");
            }

            return $result;
        } catch (PDOException $e) {
            $this->logger->error("Erreur lors de la suppression de la session: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Récupérer toutes les sessions
     */
    public function getAll()
    {
        try {
            $stmt = $this->pdo->query("SELECT * FROM session");
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
            $this->logger->info("SessionModel::getAll - Nombre total de sessions récupérées: " . count($sessions));
            return $sessions;
        } catch (PDOException $e) {
            $this->logger->error("Erreur lors de la récupération des sessions: " . $e->getMessage());
            return [];
        }
    }
}
