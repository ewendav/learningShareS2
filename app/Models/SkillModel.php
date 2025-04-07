<?php

namespace Models;

use PDO;
use PDOException;
use Psr\Log\LoggerInterface;

class SkillModel
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

    /**
     * Récupère une compétence par son nom et catégorie
     *
     * @param string $skillName Nom de la compétence
     * @param int $categoryId ID de la catégorie associée
     * @return array|null Les données de la compétence ou null si non trouvée
     */
    public function getByNameAndCategory($skillName, $categoryId)
    {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM skill WHERE skill_name = :skillName AND category_id = :categoryId");
            $stmt->bindParam(':skillName', $skillName);
            $stmt->bindParam(':categoryId', $categoryId);
            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result) {
                $this->logger->info("Compétence existante trouvée", [
                    'skill_id' => $result['skill_id'],
                    'skill_name' => $result['skill_name'],
                    'category_id' => $result['category_id']
                ]);
            }

            return $result;
        } catch (PDOException $e) {
            $this->logger->error("Erreur lors de la recherche de compétence: " . $e->getMessage(), ['exception' => $e]);
            return null;
        }
    }

    /**
     * Crée une nouvelle compétence
     *
     * @param string $skillName Nom de la compétence
     * @param int $categoryId ID de la catégorie associée
     * @return int|false ID de la compétence créée ou false en cas d'échec
     */
    public function create($skillName, $categoryId)
    {
        try {
            // Vérifier si cette compétence existe déjà
            $existingSkill = $this->getByNameAndCategory($skillName, $categoryId);

            if ($existingSkill) {
                $this->logger->info("Compétence déjà existante, retour de l'ID existant", [
                    'skill_id' => $existingSkill['skill_id'],
                    'skill_name' => $existingSkill['skill_name']
                ]);
                return $existingSkill['skill_id'];
            }

            // Créer une nouvelle compétence
            $stmt = $this->pdo->prepare("INSERT INTO skill (skill_name, category_id, search_counter) VALUES (:skillName, :categoryId, 0)");
            $stmt->bindParam(':skillName', $skillName);
            $stmt->bindParam(':categoryId', $categoryId);

            if ($stmt->execute()) {
                $newId = $this->pdo->lastInsertId();
                $this->logger->info("Nouvelle compétence créée avec succès", [
                    'skill_id' => $newId,
                    'skill_name' => $skillName,
                    'category_id' => $categoryId
                ]);
                return $newId;
            }

            $this->logger->error("Échec de la création de compétence", [
                'skill_name' => $skillName,
                'category_id' => $categoryId
            ]);
            return false;
        } catch (PDOException $e) {
            $this->logger->error("Erreur lors de la création de compétence: " . $e->getMessage(), [
                'exception' => $e,
                'skill_name' => $skillName,
                'category_id' => $categoryId
            ]);
            return false;
        }
    }

    /**
     * Récupère une compétence par son ID
     */
    public function getById($skillId)
    {
        try {
            $stmt = $this->pdo->prepare("SELECT s.*, c.category_name FROM skill s 
                                        JOIN category c ON s.category_id = c.category_id
                                        WHERE s.skill_id = :skillId");
            $stmt->bindParam(':skillId', $skillId);
            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result) {
                $this->logger->info("Compétence récupérée par ID", [
                    'skill_id' => $result['skill_id'],
                    'skill_name' => $result['skill_name']
                ]);
            }

            return $result;
        } catch (PDOException $e) {
            $this->logger->error("Erreur lors de la récupération de compétence par ID: " . $e->getMessage(), [
                'exception' => $e,
                'skill_id' => $skillId
            ]);
            return null;
        }
    }

    /**
     * Récupère toutes les compétences par catégorie
     */
    public function getAllByCategory($categoryId)
    {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM skill WHERE category_id = :categoryId ORDER BY skill_name");
            $stmt->bindParam(':categoryId', $categoryId);
            $stmt->execute();

            $skills = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $this->logger->info("Compétences récupérées par catégorie", [
                'category_id' => $categoryId,
                'count' => count($skills)
            ]);

            return $skills;
        } catch (PDOException $e) {
            $this->logger->error("Erreur lors de la récupération des compétences par catégorie: " . $e->getMessage(), [
                'exception' => $e,
                'category_id' => $categoryId
            ]);
            return [];
        }
    }

    /**
     * Incrémente le compteur de recherche pour une compétence
     */
    public function incrementSearchCounter($skillId)
    {
        try {
            $stmt = $this->pdo->prepare("UPDATE skill SET search_counter = search_counter + 1 WHERE skill_id = :skillId");
            $stmt->bindParam(':skillId', $skillId);

            $result = $stmt->execute();

            if ($result) {
                $this->logger->info("Compteur de recherche incrémenté", ['skill_id' => $skillId]);
            }

            return $result;
        } catch (PDOException $e) {
            $this->logger->error("Erreur lors de l'incrémentation du compteur: " . $e->getMessage(), [
                'exception' => $e,
                'skill_id' => $skillId
            ]);
            return false;
        }
    }

    /**
     * Récupère toutes les compétences
     */
    public function getAll()
    {
        try {
            $stmt = $this->pdo->query("SELECT s.*, c.category_name FROM skill s 
                                      JOIN category c ON s.category_id = c.category_id
                                      ORDER BY s.skill_name");

            $skills = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $this->logger->info("Toutes les compétences récupérées", ['count' => count($skills)]);

            return $skills;
        } catch (PDOException $e) {
            $this->logger->error("Erreur lors de la récupération de toutes les compétences: " . $e->getMessage(), [
                'exception' => $e
            ]);
            return [];
        }
    }
}
