<?php

namespace Controllers;

use Models\UserModel;

class LoginController
{
    /**
     * Constructeur
     */
    public function __construct()
    {
    }

    public static function displayLogin(): void
    {
        $container = \Util\Container::getContainer();
        $twig = $container->get(\Twig\Environment::class);

        $error = $_SESSION['login_error'] ?? null;
        unset($_SESSION['login_error']);

        echo $twig->render(
            'ecrans/login.html.twig',
            [
              'title' => 'Connexion',
              'error' => $error
            ]
        );
    }

    public static function login(): void
    {

        $container = \Util\Container::getContainer();
        $userModel = $container->get(UserModel::class);
        $logger = $container->get(\Psr\Log\LoggerInterface::class);

        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        $user = $userModel->findByEmail($email);
        $logger->info('Tentative de connexion pour: ' . $email);

        // Si l'utilisateur est trouvé, on vérifie le mot de passe
        if ($user) {
            $logger->info('Utilisateur trouvé: ' . $user['user_id']);

            // Traitement du mot de passe selon son format
            $passwordFromDb = $user['password'];

            // Si c'est un resource (bytea)
            if (is_resource($passwordFromDb)) {
                $passwordFromDb = stream_get_contents($passwordFromDb);
                $logger->info('Mot de passe est un resource, converti en string');
            }

            // Si le mot de passe a des guillemets (format texte avec quotes)
            if (substr($passwordFromDb, 0, 1) === "'" && substr($passwordFromDb, -1) === "'") {
                $passwordFromDb = substr($passwordFromDb, 1, -1);
                $logger->info('Guillemets enlevés du mot de passe');
            }

            $logger->info('Vérification du mot de passe');

            // Pour déboguer, enregistrez le hash dans les logs (ne pas faire en production!)
            $logger->debug('Hash stocké: ' . $passwordFromDb);

            if (password_verify($password, $passwordFromDb)) {
                $logger->info('Mot de passe vérifié avec succès');

                // Authentification réussie
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['user_email'] = $user['mail'];
                $_SESSION['avatar_path'] = $user['avatar_path'];
                $_SESSION['user_name'] = $user['user_first_name'] . ' ' . $user['user_last_name'];

                // Rediriger vers la page principale
                header('Location: /sessions');
                exit;
            } else {
                $logger->info('Mot de passe incorrect');
            }
        }

        // Authentification échouée
        $_SESSION['login_error'] = 'Identifiants incorrects';
        header('Location: /login');
        exit;
    }

    public static function displayRegister(): void
    {
        $container = \Util\Container::getContainer();
        $twig = $container->get(\Twig\Environment::class);

        $error = $_SESSION['register_error'] ?? null;
        unset($_SESSION['register_error']);

        echo $twig->render(
            'ecrans/register.html.twig',
            [
              'title' => 'Inscription',
              'error' => $error
            ]
        );
    }

    public static function register(): void
    {

        $container = \Util\Container::getContainer();
        $userModel = $container->get(UserModel::class);

        $email = $_POST['email'] ?? '';
        $firstName = $_POST['firstName'] ?? '';
        $lastName = $_POST['lastName'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $biography = $_POST['biography'] ?? '';
        $password = $_POST['password'] ?? '';

        // Vérifier si l'email existe déjà
        $existingUser = $userModel->findByEmail($email);
        if ($existingUser) {
            $_SESSION['register_error'] = 'Cet email est déjà utilisé';
            header('Location: /register');
            exit;
        }

        // Traitement de l'avatar
        $avatarPath = '';
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $tempFile = $_FILES['avatar']['tmp_name'];
            $targetDir = __DIR__ . '/../../web/assets/avatars/';

            // Créer le répertoire s'il n'existe pas
            if (!file_exists($targetDir)) {
                mkdir($targetDir, 0777, true);
            }

            $fileExtension = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
            $uniqueFilename = uniqid() . '.' . $fileExtension;
            $avatarPath = '/assets/avatars/' . $uniqueFilename;

            move_uploaded_file($tempFile, $targetDir . $uniqueFilename);
            error_log("Avatar path set to: " . $avatarPath);
        }

        // Créer le nouvel utilisateur
        $userId = $userModel->create([
            'email' => $email,
            'firstName' => $firstName,
            'lastName' => $lastName,
            'phone' => $phone,
            'biography' => $biography,
            'avatarPath' => $avatarPath,
            'password' => $password
        ]);

        error_log("User created with ID: " . $userId . " and avatar path: " . $avatarPath);

        // Connecter automatiquement l'utilisateur
        $_SESSION['user_id'] = $userId;
        $_SESSION['user_email'] = $email;
        $_SESSION['user_name'] = $firstName . ' ' . $lastName;
        $_SESSION['avatar_path'] = $avatarPath;

        // Rediriger vers la page principale
        header('Location: /sessions');
        exit;
    }

    public static function logout(): void
    {
        // La session est déjà démarrée dans index.php
        session_destroy();

        header('Location: /login');
        exit;
    }
}
