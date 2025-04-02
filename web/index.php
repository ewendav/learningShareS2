<?php

require_once '../vendor/autoload.php';
use FastRoute;

// recupère et instancie le contenur d'outils
// via l'injection de dépendance
$container = Util\Container::getContainer();
// recupération de l'outil twig du container de dependances
$twig = $container->get(Twig\Environment::class);

// création des routes
$dispatcher = FastRoute\simpleDispatcher(
    function (FastRoute\RouteCollector $r) use ($twig) {

        // route home
        $r->get(
            '/',
            function () use ($twig) {
                echo $twig->render('base.html.twig');
            }
        );

        // route création de session
        $r->get(
            '/createSession',
            function () use ($twig) {
                echo $twig->render(
                    'ecrans/createSession.html.twig',
                    [
                    'title' => 'Creation d\'un partage ou d\'un échange',
                    'fileToInclude' => 'components/CreateSession.html.twig'
                    ]
                );
            }
        );

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
$uri = $_SERVER['REQUEST_URI'];


// Dispatch the request through FastRoute
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
