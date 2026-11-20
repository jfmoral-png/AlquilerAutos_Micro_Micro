<?php

namespace App\Presentation\Repositories;

use App\Controllers\ReservasController;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use RuntimeException;

class ReservaRepository
{
    public function all(Request $request, Response $response, $args): Response
    {
        $controller = new ReservasController();

        return $this->json($response, $controller->get_reservas());
    }

    public function show(Request $request, Response $response, $args): Response
    {
        try {
            $controller = new ReservasController();

            return $this->json($response, $controller->find_reserva((int) $args['id']));
        } catch (ModelNotFoundException $ex) {
            return $this->error($response, 'Reserva no encontrada', 404);
        }
    }

    public function create(Request $request, Response $response, $args): Response
    {
        try {
            $controller = new ReservasController();

            return $this->json($response, $controller->create($this->body($request)));
        } catch (InvalidArgumentException $ex) {
            return $this->error($response, $ex->getMessage(), $ex->getCode());
        } catch (RuntimeException $ex) {
            return $this->error($response, $ex->getMessage(), $ex->getCode());
        }
    }

    public function update(Request $request, Response $response, $args): Response
    {
        try {
            $controller = new ReservasController();

            return $this->json($response, $controller->update((int) $args['id'], $this->body($request)));
        } catch (ModelNotFoundException $ex) {
            return $this->error($response, 'Reserva no encontrada', 404);
        } catch (InvalidArgumentException $ex) {
            return $this->error($response, $ex->getMessage(), $ex->getCode());
        } catch (RuntimeException $ex) {
            return $this->error($response, $ex->getMessage(), $ex->getCode());
        }
    }

    public function devolver(Request $request, Response $response, $args): Response
    {
        try {
            $controller = new ReservasController();

            return $this->json($response, $controller->devolver((int) $args['id']));
        } catch (ModelNotFoundException $ex) {
            return $this->error($response, 'Reserva no encontrada', 404);
        } catch (InvalidArgumentException $ex) {
            return $this->error($response, $ex->getMessage(), $ex->getCode());
        } catch (RuntimeException $ex) {
            return $this->error($response, $ex->getMessage(), $ex->getCode());
        }
    }

    public function cancelar(Request $request, Response $response, $args): Response
    {
        try {
            $controller = new ReservasController();

            return $this->json($response, $controller->cancelar((int) $args['id']));
        } catch (ModelNotFoundException $ex) {
            return $this->error($response, 'Reserva no encontrada', 404);
        } catch (InvalidArgumentException $ex) {
            return $this->error($response, $ex->getMessage(), $ex->getCode());
        } catch (RuntimeException $ex) {
            return $this->error($response, $ex->getMessage(), $ex->getCode());
        }
    }

    public function delete(Request $request, Response $response, $args): Response
    {
        try {
            $controller = new ReservasController();

            return $this->json($response, $controller->delete((int) $args['id']));
        } catch (ModelNotFoundException $ex) {
            return $this->error($response, 'Reserva no encontrada', 404);
        } catch (RuntimeException $ex) {
            return $this->error($response, $ex->getMessage(), $ex->getCode());
        }
    }

    public function disponibles(Request $request, Response $response, $args): Response
    {
        try {
            $controller = new ReservasController();

            return $this->json($response, $controller->disponibles());
        } catch (RuntimeException $ex) {
            return $this->error($response, $ex->getMessage(), $ex->getCode());
        }
    }

    public function historialVehiculo(Request $request, Response $response, $args): Response
    {
        $controller = new ReservasController();

        return $this->json($response, $controller->historial_vehiculo((int) $args['id']));
    }

    public function historialCliente(Request $request, Response $response, $args): Response
    {
        $controller = new ReservasController();

        return $this->json($response, $controller->historial_cliente((int) $args['id']));
    }

    public function totalVehiculo(Request $request, Response $response, $args): Response
    {
        $controller = new ReservasController();

        return $this->json($response, $controller->contar_por_vehiculo((int) $args['id']));
    }

    public function totalCliente(Request $request, Response $response, $args): Response
    {
        $controller = new ReservasController();

        return $this->json($response, $controller->contar_por_cliente((int) $args['id']));
    }

    public function estadisticas(Request $request, Response $response, $args): Response
    {
        try {
            $controller = new ReservasController();

            return $this->json($response, $controller->estadisticas());
        } catch (RuntimeException $ex) {
            return $this->error($response, $ex->getMessage(), $ex->getCode());
        }
    }

    private function body(Request $request): array
    {
        $data = json_decode($request->getBody()->getContents(), true);

        if (!is_array($data)) {
            throw new InvalidArgumentException('El cuerpo de la petición debe ser JSON válido', 400);
        }

        return $data;
    }

    private function json(Response $response, array $data, int $status = 200): Response
    {
        $response->getBody()->write(json_encode($data, JSON_UNESCAPED_UNICODE));

        return $response->withStatus($status)->withHeader('Content-Type', 'application/json');
    }

    private function error(Response $response, string $message, int $status): Response
    {
        if ($status < 400 || $status > 599) {
            $status = 500;
        }

        return $this->json($response, ['error' => $message], $status);
    }
}
