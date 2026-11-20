<?php

use App\Presentation\Repositories\VehiculoRepository;
use Slim\App;

return function (App $app) {
    $app->get('/vehiculos', [VehiculoRepository::class, 'all']);
    $app->post('/vehiculos', [VehiculoRepository::class, 'create']);
    $app->put('/vehiculos/{id}/estado', [VehiculoRepository::class, 'updateEstado']);
    $app->get('/vehiculos/{id}', [VehiculoRepository::class, 'show']);
    $app->put('/vehiculos/{id}', [VehiculoRepository::class, 'update']);
    $app->delete('/vehiculos/{id}', [VehiculoRepository::class, 'delete']);
};
