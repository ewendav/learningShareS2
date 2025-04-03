<?php

require_once '../vendor/autoload.php';

// recupère et instancie le conteneur
// qui utilise l'injection de dépendance
$container = Util\Container::getContainer();
// recupération de l'outil twig du container de dependances
$twig = $container->get(Twig\Environment::class);



// création des routes
$dispatcher = FastRoute\simpleDispatcher(
    function (FastRoute\RouteCollector $r) use ($twig, $container) {

        // route home - redirection vers la page de login
        $r->addRoute('GET', '/', [Controllers\LoginController::class, 'displayLogin']);

        // ROUTES GET
        // route création de session
        $r->addRoute('GET', '/createSession', [Controllers\SessionController::class, 'displayCreateSession']);
        // page de login et de register
        $r->addRoute('GET', '/login', [Controllers\LoginController::class, 'displayLogin']);
        $r->addRoute('GET', '/register', [Controllers\LoginController::class, 'displayRegister']);
        $r->addRoute('GET', '/logout', [Controllers\LoginController::class, 'logout']);
        // page de consultation des cours et des échanges disponibles
        $r->addRoute('GET', '/sessions', [Controllers\SessionController::class, 'displaySessions']);


        // ROUTES POST
        // routes d'authentification
        $r->addRoute('POST', '/login', [Controllers\LoginController::class, 'login']);
        $r->addRoute('POST', '/register', [Controllers\LoginController::class, 'register']);
        
        // creation d'un échange

        // routes pour créer un cours ou un partage
        $r->addRoute('POST', '/createCours', [Controllers\CoursController::class, 'store']);
        $r->addRoute('POST', '/createPartage', [Controllers\PartageController::class, 'store']);

        // route de base non utilisées :
        $r->addRoute(['GET', 'POST'], '/web/users', 'getUsers');
        $r->addRoute('GET', '/web/hello', ['Hello', 'sayHello']);
        $r->get(
            '/web/books/{id}',
            function ($args) {
                echo "Book #" . $args['id'];
            }
        );
        $r->addRoute(
            'GET',
            '/web/user/{id:\d+}',
            function ($args) {
                echo "User #" . $args['id'];
            }
        );
        $r->addRoute(
            'GET',
            '/web/articles/{id:\d+}[/{title}]',
            function ($args) {
                echo "User #" . $args['id'];
                echo "<br>Title: " . $args['title'];
            }
        );
    }
);

// Fetch method and URI from the request
$httpMethod = $_SERVER['REQUEST_METHOD'];

// méthode qui n'accepte pas les parametre get
// $uri = $_SERVER['REQUEST_URI'];
// // Dispatch the request through FastRoute
// $routeInfo = $dispatcher->dispatch($httpMethod, $uri);

// méthode qui accepte les parametre get
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$routeInfo = $dispatcher->dispatch($httpMethod, $uri);


switch ($routeInfo[0]) {
    case FastRoute\Dispatcher::NOT_FOUND:
        http_response_code(404);
        echo '404 Not Found';
        break;
    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        // 405 Method Not Allowed
        http_response_code(405);
        echo '405 Method Not Allowed';
        break;
    case FastRoute\Dispatcher::FOUND:
        $handler = $routeInfo[1];
        $vars = $routeInfo[2];

        // Call the handler (which is a closure in this case)
        $handler($vars);
        break;
}
