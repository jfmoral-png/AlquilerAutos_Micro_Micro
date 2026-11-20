<?php

use App\Presentation\Repositories\ClienteRepository;
use Slim\App;

return function (App $app) {
    $app->get('/clientes', [ClienteRepository::class, 'all']);
    $app->post('/clientes', [ClienteRepository::class, 'create']);
    $app->get('/clientes/{id}', [ClienteRepository::class, 'show']);
    $app->put('/clientes/{id}', [ClienteRepository::class, 'update']);
    $app->delete('/clientes/{id}', [ClienteRepository::class, 'delete']);
};
