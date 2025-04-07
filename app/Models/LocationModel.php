<?php

namespace Models;

use PDO;
use PDOException;
use Psr\Log\LoggerInterface;
use Monolog\Logger;

class LocationModel
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
     * Crée une nouvelle localisation
     *
     * @param string $address Adresse
     * @param string $zipCode Code postal
     * @param string $city Ville
     * @return int|false ID de la localisation créée ou false en cas d'échec
     */
    public function create($address, $zipCode, $city)
    {
        try {
            // Vérifier si cette localisation existe déjà
            $stmt = $this->pdo->prepare("SELECT location_id FROM location 
                                        WHERE address = :address 
                                        AND zip_code = :zip_code 
                                        AND city = :city");
            $stmt->bindParam(':address', $address);
            $stmt->bindParam(':zip_code', $zipCode);
            $stmt->bindParam(':city', $city);
            $stmt->execute();

            $existingLocation = $stmt->fetch(PDO::FETCH_ASSOC);

            // Si la localisation existe déjà, retourner son ID
            if ($existingLocation) {
                $this->logger->info("Localisation déjà existante", ['location_id' => $existingLocation['location_id']]);
                return $existingLocation['location_id'];
            }

            // Insérer la nouvelle localisation
            $stmt = $this->pdo->prepare("INSERT INTO location (address, zip_code, city) 
                                        VALUES (:address, :zip_code, :city)");
            $stmt->bindParam(':address', $address);
            $stmt->bindParam(':zip_code', $zipCode);
            $stmt->bindParam(':city', $city);

            if ($stmt->execute()) {
                $this->logger->info("Nouvelle localisation créée", ['address' => $address]);
                return $this->pdo->lastInsertId();
            }

            return false;
        } catch (PDOException $e) {
            $this->logger->error("Erreur lors de la création de la localisation: " . $e->getMessage(), ['exception' => $e]);
            return false;
        }
    }

    /**
     * Récupère une localisation par son ID
     *
     * @param int $locationId ID de la localisation
     * @return array|null Les données de la localisation ou null si non trouvée
     */
    public function getById($locationId)
    {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM location WHERE location_id = :location_id");
            $stmt->bindParam(':location_id', $locationId);
            $stmt->execute();

            $this->logger->info("Localisation récupérée", ['location_id' => $locationId]);

            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->logger->error("Erreur lors de la récupération de la localisation: " . $e->getMessage(), ['exception' => $e]);
            return null;
        }
    }

    /**
     * Récupère toutes les localisations
     *
     * @return array Liste des localisations
     */
    public function getAll()
    {
        try {
            $stmt = $this->pdo->query("SELECT * FROM location ORDER BY city, zip_code");
            $this->logger->info("Récupération de toutes les localisations", ['locations_count' => count($stmt->fetchAll(PDO::FETCH_ASSOC))]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->logger->error("Erreur lors de la récupération des localisations: " . $e->getMessage(), ['exception' => $e]);
            return [];
        }
    }

    /**
     * Supprime une localisation
     *
     * @param int $locationId ID de la localisation à supprimer
     * @return bool true en cas de succès, false sinon
     */
    public function delete($locationId)
    {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM location WHERE location_id = :location_id");
            $stmt->bindParam(':location_id', $locationId);

            $result = $stmt->execute();

            if ($result) {
                $this->logger->info("Localisation supprimée", ['location_id' => $locationId]);
            } else {
                $this->logger->warning("Échec de la suppression de la localisation", ['location_id' => $locationId]);
            }
            return $result;
        } catch (PDOException $e) {
            $this->logger->error("Erreur lors de la suppression de la localisation: " . $e->getMessage(), ['exception' => $e]);
            return false;
        }
    }
}
