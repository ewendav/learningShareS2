<?php


require_once '../vendor/autoload.php';

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

$loader = new FilesystemLoader('../app/Views/templates/');
$twig = new Environment($loader, [
    'cache' => false,
    'debug' => true,
]);

$twig->addExtension(new Util\TwigExtensions());

echo $twig->render('home.html.twig', ['name' => 'World']);
