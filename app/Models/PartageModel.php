<?php

namespace Models;

use Entity\Partage;
use PDO;
use PDOException;
use Psr\Log\LoggerInterface;

class PartageModel
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
        $this->logger = $logger;
        $this->sessionModel = new SessionModel($pdo, $logger);
    }

    /**
     * Sauvegarde un partage dans la base de données
     */
    public function save(Partage $partage)
    {
        $startedTransaction = false;

        try {
            // Vérifier si une transaction est déjà en cours
            if (!$this->pdo->inTransaction()) {
                // Commencer une transaction seulement si une n'est pas déjà en cours
                $this->pdo->beginTransaction();
                $startedTransaction = true;
            }

            // Sauvegarder la session parent
            if (!$this->sessionModel->save($partage)) {
                if ($startedTransaction) {
                    $this->pdo->rollBack();
                }
                $this->logger->warning("Échec de la sauvegarde de la session parent", ['session_id' => $partage->getSessionId()]);
                return false;
            }

            $session_id = $partage->getSessionId();

            // Vérifier si ce partage existe déjà
            $stmt = $this->pdo->prepare("SELECT * FROM exchange WHERE exchange_session_id = :session_id");
            $stmt->bindParam(':session_id', $session_id);
            $stmt->execute();

            if ($stmt->fetch()) {
                // Mise à jour d'un partage existant
                $stmt = $this->pdo->prepare("UPDATE exchange SET skill_requested_id = :skill_requested_id, 
                                       exchange_requester_id = :exchange_requester_id, 
                                       exchange_accepter_id = :exchange_accepter_id 
                                       WHERE exchange_session_id = :session_id");
                $this->logger->info("Mise à jour du partage existant", ['session_id' => $session_id]);
            } else {
                // Création d'un nouveau partage
                $stmt = $this->pdo->prepare("INSERT INTO exchange (exchange_session_id, skill_requested_id, 
                                       exchange_requester_id, exchange_accepter_id) 
                                       VALUES (:session_id, :skill_requested_id, 
                                       :exchange_requester_id, :exchange_accepter_id)");
                $this->logger->info("Création d'un nouveau partage", ['session_id' => $session_id]);
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

            // Valider la transaction seulement si nous l'avons commencée
            if ($startedTransaction) {
                $this->pdo->commit();
            }

            $this->logger->info("Partage sauvegardé avec succès", ['session_id' => $session_id]);
            return true;
        } catch (PDOException $e) {
            // Annuler la transaction en cas d'erreur seulement si nous l'avons commencée
            if ($startedTransaction && $this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            $this->logger->error("Erreur lors de la sauvegarde du partage", ['exception' => $e, 'session_id' => $partage->getSessionId()]);
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
                $this->logger->warning("Session non trouvée pour le partage", ['session_id' => $session_id]);
                return null;
            }

            // Récupérer les données du partage
            $stmt = $this->pdo->prepare("SELECT * FROM exchange WHERE exchange_session_id = :session_id");
            $stmt->bindParam(':session_id', $session_id);
            $stmt->execute();

            if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $this->logger->info("Partage récupéré", ['session_id' => $session_id]);
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
            $this->logger->warning("Partage non trouvé", ['session_id' => $session_id]);
            return null;
        } catch (PDOException $e) {
            $this->logger->error("Erreur lors de la récupération du partage", ['exception' => $e, 'session_id' => $session_id]);
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
            $stmt = $this->pdo->prepare("DELETE FROM exchange WHERE exchange_session_id = :session_id");
            $stmt->bindParam(':session_id', $sessionID);
            $stmt->execute();

            // Supprimer la session parent
            if (!$this->sessionModel->delete($partage)) {
                $this->pdo->rollBack();
                $this->logger->warning("Échec de la suppression de la session parent", ['session_id' => $sessionID]);
                return false;
            }

            // Valider la transaction
            $this->pdo->commit();
            $this->logger->info("Partage supprimé avec succès", ['session_id' => $sessionID]);
            return true;
        } catch (PDOException $e) {
            // Annuler la transaction en cas d'erreur
            $this->pdo->rollBack();
            $this->logger->error("Erreur lors de la suppression du partage", ['exception' => $e, 'session_id' => $partage->getSessionId()]);
            return false;
        }
    }

    /**
     * Récupérer tous les partages qui n'ont pas été acceptés
     */
    public function getDemandeDePartage(bool $retourneJson = false, string $skillName = "")
    {
        try {
            $userId = isset($_SESSION["user_id"]) ? $_SESSION["user_id"] : 0;
            $query = "
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
            req_user.user_id AS requester_user_id,
            acc_user.user_first_name AS accepter_first_name,
            acc_user.user_last_name AS accepter_last_name,
            acc_user.avatar_path AS accepter_avatar,
            taught_skill.skill_name AS skill_taught_name,
            requested_skill.skill_name AS skill_requested_name
        FROM 
            session s 
        JOIN 
            exchange e ON s.session_id = e.exchange_session_id
        JOIN 
            app_user req_user ON e.exchange_requester_id = req_user.user_id
        LEFT JOIN 
            app_user acc_user ON e.exchange_accepter_id = acc_user.user_id
        JOIN 
            skill taught_skill ON s.skill_taught_id = taught_skill.skill_id
        JOIN 
            skill requested_skill ON e.skill_requested_id = requested_skill.skill_id
        WHERE 
            e.exchange_accepter_id IS NULL
            AND e.exchange_requester_id != :user_id
            AND CONCAT(s.date_session, ' ', s.end_time) > NOW()
        ";

            if (!empty($skillName)) {
                $query .= " AND taught_skill.skill_name LIKE :skillName";
            }

            $stmt = $this->pdo->prepare($query);
            
            // Lier l'ID utilisateur
            $stmt->bindParam(':user_id', $userId);
            
            if (!empty($skillName)) {
                $stmt->bindValue(':skillName', '%' . $skillName . '%');
            }

            $stmt->execute();
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
                        'requester_user_id' => $row['requester_user_id'],
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
            $this->logger->info("Récupération de tous les partages", ['partages_count' => count($partages)]);
            return $partages;
        } catch (PDOException $e) {
            $this->logger->error("Erreur lors de la récupération des partages", ['exception' => $e]);
            return [];
        }
    }

/**
 * Récupérer les partages par utilisateur demandeur
 * qui ne sont pas déja passé
 */
    public function getByRequesterIdNotExpired($user_id, bool $retourneJson = false)
    {
        try {
            $currentDateTime = date('Y-m-d H:i:s');

            $query = "
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
        session s 
    JOIN 
        exchange e ON s.session_id = e.exchange_session_id
    JOIN 
        app_user req_user ON e.exchange_requester_id = req_user.user_id
    LEFT JOIN 
        app_user acc_user ON e.exchange_accepter_id = acc_user.user_id
    JOIN 
        skill taught_skill ON s.skill_taught_id = taught_skill.skill_id
    JOIN 
        skill requested_skill ON e.skill_requested_id = requested_skill.skill_id
    WHERE 
        e.exchange_requester_id = :user_id
        AND CONCAT(s.date_session, ' ', s.end_time) > NOW()
";

            $stmt = $this->pdo->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();
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
            $this->logger->info("Récupération des partages par demandeur", ['user_id' => $user_id, 'partages_count' => count($partages)]);
            return $partages;
        } catch (PDOException $e) {
            $this->logger->error("Erreur lors de la récupération des partages par demandeur", ['exception' => $e, 'user_id' => $user_id]);
            return [];
        }
    }

/**
 * Récupérer les partages par utilisateur accepteur
 * qui ne sont pas encore passés
 */
    public function getByAccepterIdNotExpired($user_id, bool $retourneJson = false)
    {
        try {
            $currentDateTime = date('Y-m-d H:i:s');

            $query = "
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
        session s 
    JOIN 
        exchange e ON s.session_id = e.exchange_session_id
    JOIN 
        app_user req_user ON e.exchange_requester_id = req_user.user_id
    LEFT JOIN 
        app_user acc_user ON e.exchange_accepter_id = acc_user.user_id
    JOIN 
        skill taught_skill ON s.skill_taught_id = taught_skill.skill_id
    JOIN 
        skill requested_skill ON e.skill_requested_id = requested_skill.skill_id
    WHERE 
        e.exchange_accepter_id = :user_id
        AND CONCAT(s.date_session, ' ', s.end_time) > NOW()
";

            $stmt = $this->pdo->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();
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
            $this->logger->info("Récupération des partages par accepteur", ['user_id' => $user_id, 'partages_count' => count($partages)]);
            return $partages;
        } catch (PDOException $e) {
            $this->logger->error("Erreur lors de la récupération des partages par accepteur", ['exception' => $e, 'user_id' => $user_id]);
            return [];
        }
    }
}
