<?php

namespace Models;

use PDO;
use PDOException;

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

        try {
            $stmt = $pdo->query("SELECT * FROM CATEGORY");
            $categories = [];

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $categories[] = [
                    $row['category_id'],
                    $row['category_name']
                ];
            }
            return $categories;
        } catch (PDOException $e) {
            error_log("Erreur lors de la récupération des categories: " . $e->getMessage());
            return [];
        }
    }
}
