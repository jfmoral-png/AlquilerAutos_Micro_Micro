<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../app/global/database_config.php';
require __DIR__ . '/../app/global/services_config.php';
require __DIR__ . '/../app/global/http_client.php';

$cors = require __DIR__ . '/../app/Middlewares/cors.php';
$reservas_routers = require __DIR__ . '/../app/Presentation/Routers/endpoints.php';

$app = AppFactory::create();

$cors($app);

$reservas_routers($app);

$app->get('/inicio', function (Request $request, Response $response, $args) {
    $response->getBody()->write("Microservicio de reservas");
    return $response;
});

$app->run();
