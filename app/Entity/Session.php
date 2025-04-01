<?php

namespace Entity;

class Session {
    private $session_id;
    private $start_time;
    private $end_time;
    private $date_session;
    private $description;
    private $rate_id;
    private $skill_taught_id;

    /**
     * Constructeur
     */
    public function __construct($session_id = null, $start_time = null, $end_time = null, $date_session = null,
                                $description = null, $rate_id = null, $skill_taught_id = null) {
        $this->session_id = $session_id;
        $this->start_time = $start_time;
        $this->end_time = $end_time;
        $this->date_session = $date_session;
        $this->description = $description;
        $this->rate_id = $rate_id;
        $this->skill_taught_id = $skill_taught_id;
    }

    /**
     * Getters
     */
    public function getSessionId() {
        return $this->session_id;
    }

    public function getStartTime() {
        return $this->start_time;
    }

    public function getEndTime() {
        return $this->end_time;
    }

    public function getDateSession() {
        return $this->date_session;
    }

    public function getDescription() {
        return $this->description;
    }

    public function getRateId() {
        return $this->rate_id;
    }

    public function getSkillTaughtId() {
        return $this->skill_taught_id;
    }

    /**
     * Setters
     */
    public function setSessionId($session_id) {
        $this->session_id = $session_id;
    }

    public function setStartTime($start_time) {
        $this->start_time = $start_time;
    }

    public function setEndTime($end_time) {
        $this->end_time = $end_time;
    }

    public function setDateSession($date_session) {
        $this->date_session = $date_session;
    }

    public function setDescription($description) {
        $this->description = $description;
    }

    public function setRateId($rate_id) {
        $this->rate_id = $rate_id;
    }

    public function setSkillTaughtId($skill_taught_id) {
        $this->skill_taught_id = $skill_taught_id;
}

/**
 * Sauvegarde une session dans la base de données
 */
    public function save($pdo) {
        try {
            if ($this->session_id) {
                // Mise à jour d'une session existante
                $stmt = $pdo->prepare("UPDATE SESSION SET start_time = :start_time, end_time = :end_time, 
                                      date_session = :date_session, description = :description, rate_id = :rate_id, 
                                      skill_taught_id = :skill_taught_id WHERE session_id = :session_id");
                $stmt->bindParam(':session_id', $this->session_id);
            } else {
                // Création d'une nouvelle session
                $stmt = $pdo->prepare("INSERT INTO SESSION (start_time, end_time, date_session, description, 
                                      rate_id, skill_taught_id) VALUES (:start_time, :end_time, :date_session, 
                                      :description, :rate_id, :skill_taught_id)");
            }

            $stmt->bindParam(':start_time', $this->start_time);
            $stmt->bindParam(':end_time', $this->end_time);
            $stmt->bindParam(':date_session', $this->date_session);
            $stmt->bindParam(':description', $this->description);
            $stmt->bindParam(':rate_id', $this->rate_id);
            $stmt->bindParam(':skill_taught_id', $this->skill_taught_id);
            $stmt->execute();

            if (!$this->session_id) {
                $this->session_id = $pdo->lastInsertId();
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
    public static function getById($pdo, $session_id) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM SESSION WHERE session_id = :session_id");
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
    public function delete($pdo) {
        try {
            $stmt = $pdo->prepare("DELETE FROM SESSION WHERE session_id = :session_id");
            $stmt->bindParam(':session_id', $this->session_id);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erreur lors de la suppression de la session: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Récupérer toutes les sessions
     */
    public static function getAll($pdo) {
        try {
            $stmt = $pdo->query("SELECT * FROM SESSION");
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