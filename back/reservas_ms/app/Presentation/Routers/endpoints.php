<?php

use App\Presentation\Repositories\ReservaRepository;
use Slim\App;

return function (App $app) {
    $app->get('/reservas', [ReservaRepository::class, 'all']);
    $app->post('/reservas', [ReservaRepository::class, 'create']);

    $app->get('/reservas/disponibles', [ReservaRepository::class, 'disponibles']);
    $app->get('/reservas/estadisticas', [ReservaRepository::class, 'estadisticas']);
    $app->get('/reservas/vehiculo/{id}/total', [ReservaRepository::class, 'totalVehiculo']);
    $app->get('/reservas/cliente/{id}/total', [ReservaRepository::class, 'totalCliente']);
    $app->get('/reservas/historial/vehiculo/{id}', [ReservaRepository::class, 'historialVehiculo']);
    $app->get('/reservas/historial/cliente/{id}', [ReservaRepository::class, 'historialCliente']);

    $app->get('/reservas/{id}', [ReservaRepository::class, 'show']);
    $app->put('/reservas/{id}/devolver', [ReservaRepository::class, 'devolver']);
    $app->put('/reservas/{id}/cancelar', [ReservaRepository::class, 'cancelar']);
    $app->put('/reservas/{id}', [ReservaRepository::class, 'update']);
    $app->delete('/reservas/{id}', [ReservaRepository::class, 'delete']);
};
