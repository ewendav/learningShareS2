<?php

namespace Models;

class UserModel
{
    private \PDO $pdo;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function findByEmail(string $email)
    {
        try {
            $stmt = $this->pdo->prepare('SELECT * FROM app_user WHERE mail = :email');
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch(\PDO::FETCH_ASSOC);

            // Journaliser pour débogage
            if ($user) {
                error_log('Utilisateur trouvé: ID=' . $user['user_id']);

                // Vérifier le type de données pour le mot de passe
                if (is_resource($user['password'])) {
                    $user['password'] = stream_get_contents($user['password']);
                    error_log('Mot de passe converti de resource à string');
                }

                // Si stocké avec des guillemets simples, enlever les
                if (substr($user['password'], 0, 1) === "'" && substr($user['password'], -1) === "'") {
                    $user['password'] = substr($user['password'], 1, -1);
                    error_log('Guillemets enlevés du mot de passe');
                }
            } else {
                error_log('Aucun utilisateur trouvé pour: ' . $email);
            }

            return $user;
        } catch (\PDOException $e) {
            error_log('Erreur lors de la recherche par email: ' . $e->getMessage());
            throw $e;
        }
    }

    public function create(array $userData): int
    {
        // Hasher le mot de passe
        $hashedPassword = password_hash($userData['password'], PASSWORD_DEFAULT);

        try {
            // Utiliser la syntaxe compatible avec MySQL (sans RETURNING)
            $stmt = $this->pdo->prepare(
                'INSERT INTO app_user 
                (mail, user_first_name, user_last_name, biography, avatar_path, phone, password) 
                VALUES (:mail, :firstName, :lastName, :biography, :avatarPath, :phone, :password)'
            );

            $stmt->execute([
                'mail' => $userData['email'],
                'firstName' => $userData['firstName'],
                'lastName' => $userData['lastName'],
                'biography' => $userData['biography'] ?? '',
                'avatarPath' => $userData['avatarPath'] ?? '',
                'phone' => $userData['phone'],
                'password' => $hashedPassword
            ]);

            // Utiliser lastInsertId() pour récupérer l'ID généré
            return (int)$this->pdo->lastInsertId();
        } catch (\PDOException $e) {
            error_log('Erreur lors de la création utilisateur: ' . $e->getMessage());
            throw $e;
        }
    }

    public function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    /**
     * Récupérer le solde de jetons d'un utilisateur
     */
    public function getBalance(int $userId): int
    {
        try {
            $stmt = $this->pdo->prepare('SELECT balance FROM app_user WHERE user_id = :userId');
            $stmt->execute(['userId' => $userId]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($result) {
                return (int)$result['balance'];
            }

            return 0;
        } catch (\PDOException $e) {
            error_log('Erreur lors de la récupération du solde: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Mettre à jour le solde de jetons d'un utilisateur
     */
    public function updateBalance(int $userId, int $amount): bool
    {
        try {
            // Vérifier que le solde ne devient pas négatif
            $currentBalance = $this->getBalance($userId);
            if ($currentBalance + $amount < 0) {
                return false;
            }

            // Ne pas gérer la transaction ici, elle sera gérée par l'appelant
            $stmt = $this->pdo->prepare('UPDATE app_user SET balance = balance + :amount WHERE user_id = :userId');
            $stmt->execute([
                'amount' => $amount,
                'userId' => $userId
            ]);

            return true;
        } catch (\PDOException $e) {
            error_log('Erreur lors de la mise à jour du solde: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Vérifier si un utilisateur a suffisamment de jetons
     */
    public function hasEnoughTokens(int $userId, int $requiredAmount): bool
    {
        $balance = $this->getBalance($userId);
        return $balance >= $requiredAmount;
    }

    /**
     * Récupérer un utilisateur par son ID
     */
    public function findById(int $userId)
    {
        try {
            $stmt = $this->pdo->prepare('SELECT * FROM app_user WHERE user_id = :userId');
            $stmt->execute(['userId' => $userId]);
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log('Erreur lors de la recherche par ID: ' . $e->getMessage());
            throw $e;
        }
    }
}
