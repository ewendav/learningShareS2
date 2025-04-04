<?php

namespace Models;

use PDO;
use PDOException;
use Psr\Log\LoggerInterface;

class CategorieModel
{
    /**
     * Constructeur
     */
    public function __construct()
    {
    }

    /**
     * Récupérer toutes les Categories
     *
     */
    public static function getAll()
    {
        $container = \Util\Container::getContainer();
        $pdo = $container->get(PDO::class);
        $logger = $container->get(LoggerInterface::class);


        try {
            $logger->info("Début de récupération des catégories");

            $stmt = $pdo->query("SELECT * FROM category");
            $categories = [];

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $categories[] = [
                    $row['category_id'],
                    $row['category_name']
                ];
            }
            $logger->info("Récupération des catégories réussie", ['count' => count($categories)]);
            return $categories;
        } catch (PDOException $e) {
            error_log("Erreur lors de la récupération des categories: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Récupère une catégorie par son ID
     */
    public static function getById($categoryId)
    {
        $container = \Util\Container::getContainer();
        $pdo = $container->get(PDO::class);
        $logger = $container->get(LoggerInterface::class);

        try {
            $stmt = $pdo->prepare("SELECT * FROM category WHERE category_id = :categoryId");
            $stmt->bindParam(':categoryId', $categoryId);
            $stmt->execute();
            
            if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                return [
                    'category_id' => $row['category_id'],
                    'category_name' => $row['category_name']
                ];
            }
            
            return null;
        } catch (PDOException $e) {
            $logger->error("Erreur lors de la récupération de la catégorie par ID: " . $e->getMessage());
            return null;
        }
    }
}
