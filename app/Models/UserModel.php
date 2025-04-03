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
            // Utiliser la syntaxe non bytea pour la compatibilité
            $stmt = $this->pdo->prepare(
                'INSERT INTO app_user 
                (mail, user_first_name, user_last_name, biography, avatar_path, phone, password) 
                VALUES (:mail, :firstName, :lastName, :biography, :avatarPath, :phone, :password)
                RETURNING user_id'
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
            
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            return (int)$result['user_id'];
        } catch (\PDOException $e) {
            error_log('Erreur lors de la création utilisateur: ' . $e->getMessage());
            throw $e;
        }
    }

    public function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }
}