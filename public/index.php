<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Infrastructure\Config\AppConfig;
use App\Infrastructure\Http\ApiClient;
use App\Infrastructure\Repository\CsvReservationRepository;
use App\Interface\Web\Controller\ReservationController;
use App\Application\Service\ReservationService;
use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use Dotenv\Dotenv;

// Load env vars
$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// AppConfig Instance
$appConfig = new AppConfig($_ENV);

// Debug config
$debug = $appConfig->isDebug();
if ($debug) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    error_log('ENV: ' . print_r($_ENV, true));
}

// Create dependencies
$apiClient = new ApiClient(
    $appConfig->getApiBaseUrl(),
    $appConfig->getApiUsername(),
    $appConfig->getApiPassword()
);
$reservationRepository = new CsvReservationRepository($apiClient);
$reservationService = new ReservationService($reservationRepository);
$reservationController = new ReservationController($reservationService);

// Routes config
$dispatcher = FastRoute\simpleDispatcher(function (RouteCollector $r) {
    $r->addRoute('GET', '/', ['controller' => 'reservation', 'action' => 'list']);
    $r->addRoute('GET', '/download-json', ['controller' => 'reservation', 'action' => 'downloadJson']);
});

// Get method and URI from request
$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

// Delete query params
if (($pos = strpos($uri, '?')) !== false) {
    $uri = substr($uri, 0, $pos);
}

// Decode URI
$uri = rawurldecode($uri);

// Route dispatch
$routeInfo = $dispatcher->dispatch($httpMethod, $uri);

switch ($routeInfo[0]) {
    case Dispatcher::NOT_FOUND:
        http_response_code(404);
        echo 'Página no encontrada';
        break;

    case Dispatcher::METHOD_NOT_ALLOWED:
        http_response_code(405);
        echo 'Método no permitido';
        break;

    case Dispatcher::FOUND:
        $handler = $routeInfo[1];
        $vars = $routeInfo[2];

        $request = array_merge($vars, $_GET, $_POST);

        if ($handler['controller'] === 'reservation') {
            if ($handler['action'] === 'list') {
                $reservationController->listAction($request);
            } elseif ($handler['action'] === 'downloadJson') {
                $generator = $reservationController->downloadJsonAction($request);

                header('Content-Type: application/json');
                header('Content-Disposition: attachment; filename="reservations.json"');

                $output = '';
                $hasError = false;
                foreach ($generator as $chunk) {
                    $output .= $chunk;
                    if (str_contains($chunk, '"error"')) {
                        $hasError = true;
                        break;
                    }
                }

                if ($hasError) {
                    header('Content-Type: application/json', true, 500);
                    echo $output;
                } else {
                    echo $output;
                    flush();
                }
            }
        }
        break;
}
