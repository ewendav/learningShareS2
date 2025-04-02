<?php

namespace Controllers;

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

        echo $twig->render(
            'ecrans/login.html.twig',
            [
              'title' => 'Connexion',
            ]
        );
    }


    public static function displayRegister(): void
    {
        $container = \Util\Container::getContainer();
        $twig = $container->get(\Twig\Environment::class);

        echo $twig->render(
            'ecrans/register.html.twig',
            [
              'title' => 'Inscription',
            ]
        );
    }
}
