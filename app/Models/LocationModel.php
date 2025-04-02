<?php
namespace Models;

use PDO;
use PDOException;

class LocationModel {
    private $pdo;

    /**
     * Constructeur
     */
    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Crée une nouvelle localisation
     * 
     * @param string $address Adresse
     * @param string $zipCode Code postal
     * @param string $city Ville
     * @return int|false ID de la localisation créée ou false en cas d'échec
     */
    public function create($address, $zipCode, $city) {
        try {
            // Vérifier si cette localisation existe déjà
            $stmt = $this->pdo->prepare("SELECT location_id FROM LOCATION 
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
                return $existingLocation['location_id'];
            }
            
            // Insérer la nouvelle localisation
            $stmt = $this->pdo->prepare("INSERT INTO LOCATION (address, zip_code, city) 
                                        VALUES (:address, :zip_code, :city)");
            $stmt->bindParam(':address', $address);
            $stmt->bindParam(':zip_code', $zipCode);
            $stmt->bindParam(':city', $city);
            
            if ($stmt->execute()) {
                return $this->pdo->lastInsertId();
            }
            
            return false;
        } catch (PDOException $e) {
            error_log("Erreur lors de la création de la localisation: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Récupère une localisation par son ID
     * 
     * @param int $locationId ID de la localisation
     * @return array|null Les données de la localisation ou null si non trouvée
     */
    public function getById($locationId) {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM LOCATION WHERE location_id = :location_id");
            $stmt->bindParam(':location_id', $locationId);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erreur lors de la récupération de la localisation: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Récupère toutes les localisations
     * 
     * @return array Liste des localisations
     */
    public function getAll() {
        try {
            $stmt = $this->pdo->query("SELECT * FROM LOCATION ORDER BY city, zip_code");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erreur lors de la récupération des localisations: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Supprime une localisation
     * 
     * @param int $locationId ID de la localisation à supprimer
     * @return bool true en cas de succès, false sinon
     */
    public function delete($locationId) {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM LOCATION WHERE location_id = :location_id");
            $stmt->bindParam(':location_id', $locationId);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erreur lors de la suppression de la localisation: " . $e->getMessage());
            return false;
        }
    }
}