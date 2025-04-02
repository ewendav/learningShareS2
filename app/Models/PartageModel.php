<?php

namespace Models;

use Entity\Partage;
use PDO;
use PDOException;

class PartageModel
{
    private $pdo;
    private $sessionModel;

    /**
     * Constructeur
     */
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->sessionModel = new SessionModel($pdo);
    }

    /**
     * Sauvegarde un partage dans la base de données
     */
    public function save(Partage $partage)
    {
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
    public function getById($session_id)
    {
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
    public function delete(Partage $partage)
    {
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
     * Récupérer tous les partages qui nont pas été acepté
     */
    public function getDemandeDePartage(bool $retourneJson = false)
    {
        try {
            $stmt = $this->pdo->query("
            SELECT 
                s.session_id,
                s.start_time,
                s.end_time,
                s.date_session,
                s.description,
                s.rate_id,
                taught_skill.skill_id AS skill_taught_id,
                taught_skill.category_id AS skill_taught_category_id,
                e.skill_requested_id,
                requested_skill.category_id AS skill_requested_category_id,
                e.exchange_requester_id,
                e.exchange_accepter_id,
                req_user.user_first_name AS requester_first_name,
                req_user.user_last_name AS requester_last_name,
                req_user.avatar_path AS requester_avatar,
                acc_user.user_first_name AS accepter_first_name,
                acc_user.user_last_name AS accepter_last_name,
                acc_user.avatar_path AS accepter_avatar,
                taught_skill.skill_name AS skill_taught_name,
                requested_skill.skill_name AS skill_requested_name
            FROM 
                SESSION s 
            JOIN 
                EXCHANGE e ON s.session_id = e.exchange_session_id
            JOIN 
                APP_USER req_user ON e.exchange_requester_id = req_user.user_id
            LEFT JOIN 
                APP_USER acc_user ON e.exchange_accepter_id = acc_user.user_id
            JOIN 
                SKILL taught_skill ON s.skill_taught_id = taught_skill.skill_id
            JOIN 
                SKILL requested_skill ON e.skill_requested_id = requested_skill.skill_id
            WHERE 
                e.exchange_accepter_id IS NULL
        ");
            $partages = [];

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                if ($retourneJson) {
                    $partages[] = [
                        'session_id' => $row['session_id'],
                        'start_time' => $row['start_time'],
                        'end_time' => $row['end_time'],
                        'date_session' => $row['date_session'],
                        'description' => $row['description'],
                        'rate_id' => $row['rate_id'],
                        'skill_taught_id' => $row['skill_taught_id'],
                        'skill_taught_category_id' => $row['skill_taught_category_id'],
                        'skill_requested_id' => $row['skill_requested_id'],
                        'skill_requested_category_id' => $row['skill_requested_category_id'],
                        'exchange_requester_id' => $row['exchange_requester_id'],
                        'exchange_accepter_id' => $row['exchange_accepter_id'],
                        'requester_first_name' => $row['requester_first_name'],
                        'requester_last_name' => $row['requester_last_name'],
                        'requester_avatar' => $row['requester_avatar'],
                        'accepter_first_name' => $row['accepter_first_name'],
                        'accepter_last_name' => $row['accepter_last_name'],
                        'accepter_avatar' => $row['accepter_avatar'],
                        'skill_taught_name' => $row['skill_taught_name'],
                        'skill_requested_name' => $row['skill_requested_name']
                    ];
                } else {
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
    public function getByRequesterId($user_id)
    {
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
    public function getByAccepterId($user_id)
    {
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

