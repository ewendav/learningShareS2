<?php
namespace Models;

use Entity\Partage;
use PDO;
use PDOException;

class PartageModel {
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
     * Sauvegarde un partage dans la base de données
     */
    public function save(Partage $partage) {
        try {
            // Commencer une transaction
            $this->pdo->beginTransaction();

            // Sauvegarder la session parent
            if (!$this->sessionModel->save($partage)) {
                $this->pdo->rollBack();
                return false;
            }

            $session_id = $partage->getSessionId();

            // Vérifier si ce partage existe déjà
            $stmt = $this->pdo->prepare("SELECT * FROM EXCHANGE WHERE exchange_session_id = :session_id");
            $stmt->bindParam(':session_id', $session_id);
            $stmt->execute();

            if ($stmt->fetch()) {
                // Mise à jour d'un partage existant
                $stmt = $this->pdo->prepare("UPDATE EXCHANGE SET skill_requested_id = :skill_requested_id, 
                                       exchange_requester_id = :exchange_requester_id, 
                                       exchange_accepter_id = :exchange_accepter_id 
                                       WHERE exchange_session_id = :session_id");
            } else {
                // Création d'un nouveau partage
                $stmt = $this->pdo->prepare("INSERT INTO EXCHANGE (exchange_session_id, skill_requested_id, 
                                       exchange_requester_id, exchange_accepter_id) 
                                       VALUES (:session_id, :skill_requested_id, 
                                       :exchange_requester_id, :exchange_accepter_id)");
            }

            // Stocker les valeurs dans des variables intermédiaires
            $skill_requested_id = $partage->getSkillRequestedId();
            $exchange_requester_id = $partage->getExchangeRequesterId();
            $exchange_accepter_id = $partage->getExchangeAccepterId();

            // Utiliser les variables pour bindParam
            $stmt->bindParam(':session_id', $session_id);
            $stmt->bindParam(':skill_requested_id', $skill_requested_id);
            $stmt->bindParam(':exchange_requester_id', $exchange_requester_id);
            $stmt->bindParam(':exchange_accepter_id', $exchange_accepter_id);
            $stmt->execute();

            // Valider la transaction
            $this->pdo->commit();
            return true;
        } catch (PDOException $e) {
            // Annuler la transaction en cas d'erreur
            $this->pdo->rollBack();
            error_log("Erreur lors de la sauvegarde du partage: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Récupérer un partage par son ID de session
     */
    public function getById($session_id) {
        try {
            // Récupérer d'abord la session
            $session = $this->sessionModel->getById($session_id);

            if (!$session) {
                return null;
            }

            // Récupérer les données du partage
            $stmt = $this->pdo->prepare("SELECT * FROM EXCHANGE WHERE exchange_session_id = :session_id");
            $stmt->bindParam(':session_id', $session_id);
            $stmt->execute();

            if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                return new Partage(
                    $session->getSessionId(),
                    $session->getStartTime(),
                    $session->getEndTime(),
                    $session->getDateSession(),
                    $session->getDescription(),
                    $session->getRateId(),
                    $session->getSkillTaughtId(),
                    $row['skill_requested_id'],
                    $row['exchange_requester_id'],
                    $row['exchange_accepter_id']
                );
            }
            return null;
        } catch (PDOException $e) {
            error_log("Erreur lors de la récupération du partage: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Supprimer un partage
     */
    public function delete(Partage $partage) {
        try {
            // Commencer une transaction
            $this->pdo->beginTransaction();
            $sessionID = $partage->getSessionId();

            // Supprimer le partage
            $stmt = $this->pdo->prepare("DELETE FROM EXCHANGE WHERE exchange_session_id = :session_id");
            $stmt->bindParam(':session_id', $sessionID);
            $stmt->execute();

            // Supprimer la session parent
            if (!$this->sessionModel->delete($partage)) {
                $this->pdo->rollBack();
                return false;
            }

            // Valider la transaction
            $this->pdo->commit();
            return true;
        } catch (PDOException $e) {
            // Annuler la transaction en cas d'erreur
            $this->pdo->rollBack();
            error_log("Erreur lors de la suppression du partage: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Récupérer tous les partages
     */
    public function getAll() {
        try {
            $stmt = $this->pdo->query("SELECT s.*, e.skill_requested_id, e.exchange_requester_id, e.exchange_accepter_id 
                                FROM SESSION s 
                                JOIN EXCHANGE e ON s.session_id = e.exchange_session_id");
            $partages = [];

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $partages[] = new Partage(
                    $row['session_id'],
                    $row['start_time'],
                    $row['end_time'],
                    $row['date_session'],
                    $row['description'],
                    $row['rate_id'],
                    $row['skill_taught_id'],
                    $row['skill_requested_id'],
                    $row['exchange_requester_id'],
                    $row['exchange_accepter_id']
                );
            }
            return $partages;
        } catch (PDOException $e) {
            error_log("Erreur lors de la récupération des partages: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Récupérer les partages par utilisateur demandeur
     */
    public function getByRequesterId($user_id) {
        try {
            $stmt = $this->pdo->prepare("SELECT s.*, e.skill_requested_id, e.exchange_requester_id, e.exchange_accepter_id 
                                  FROM SESSION s 
                                  JOIN EXCHANGE e ON s.session_id = e.exchange_session_id 
                                  WHERE e.exchange_requester_id = :user_id");
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();

            $partages = [];

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $partages[] = new Partage(
                    $row['session_id'],
                    $row['start_time'],
                    $row['end_time'],
                    $row['date_session'],
                    $row['description'],
                    $row['rate_id'],
                    $row['skill_taught_id'],
                    $row['skill_requested_id'],
                    $row['exchange_requester_id'],
                    $row['exchange_accepter_id']
                );
            }
            return $partages;
        } catch (PDOException $e) {
            error_log("Erreur lors de la récupération des partages par demandeur: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Récupérer les partages par utilisateur accepteur
     */
    public function getByAccepterId($user_id) {
        try {
            $stmt = $this->pdo->prepare("SELECT s.*, e.skill_requested_id, e.exchange_requester_id, e.exchange_accepter_id 
                                  FROM SESSION s 
                                  JOIN EXCHANGE e ON s.session_id = e.exchange_session_id 
                                  WHERE e.exchange_accepter_id = :user_id");
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();

            $partages = [];

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $partages[] = new Partage(
                    $row['session_id'],
                    $row['start_time'],
                    $row['end_time'],
                    $row['date_session'],
                    $row['description'],
                    $row['rate_id'],
                    $row['skill_taught_id'],
                    $row['skill_requested_id'],
                    $row['exchange_requester_id'],
                    $row['exchange_accepter_id']
                );
            }
            return $partages;
        } catch (PDOException $e) {
            error_log("Erreur lors de la récupération des partages par accepteur: " . $e->getMessage());
            return [];
        }
    }
}