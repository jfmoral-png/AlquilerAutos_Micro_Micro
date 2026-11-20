<?php

namespace App\Presentation\Repositories;

use App\Controllers\VehiculosController;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use RuntimeException;

class VehiculoRepository
{
    public function all(Request $request, Response $response, $args): Response
    {
        $controller = new VehiculosController();

        return $this->json($response, $controller->get_vehiculos()->toArray());
    }

    public function show(Request $request, Response $response, $args): Response
    {
        try {
            $controller = new VehiculosController();

            return $this->json($response, $controller->find_vehiculo((int) $args['id'])->toArray());
        } catch (ModelNotFoundException $ex) {
            return $this->error($response, 'Vehículo no encontrado', 404);
        }
    }

    public function create(Request $request, Response $response, $args): Response
    {
        try {
            $controller = new VehiculosController();

            return $this->json($response, $controller->create($this->body($request))->toArray());
        } catch (InvalidArgumentException $ex) {
            return $this->error($response, $ex->getMessage(), $ex->getCode());
        }
    }

    public function update(Request $request, Response $response, $args): Response
    {
        try {
            $controller = new VehiculosController();

            return $this->json($response, $controller->update((int) $args['id'], $this->body($request))->toArray());
        } catch (ModelNotFoundException $ex) {
            return $this->error($response, 'Vehículo no encontrado', 404);
        } catch (InvalidArgumentException $ex) {
            return $this->error($response, $ex->getMessage(), $ex->getCode());
        }
    }

    public function updateEstado(Request $request, Response $response, $args): Response
    {
        try {
            $controller = new VehiculosController();

            return $this->json($response, $controller->update_estado((int) $args['id'], $this->body($request))->toArray());
        } catch (ModelNotFoundException $ex) {
            return $this->error($response, 'Vehículo no encontrado', 404);
        } catch (InvalidArgumentException $ex) {
            return $this->error($response, $ex->getMessage(), $ex->getCode());
        }
    }

    public function delete(Request $request, Response $response, $args): Response
    {
        try {
            $controller = new VehiculosController();

            return $this->json($response, $controller->delete((int) $args['id'])->toArray());
        } catch (ModelNotFoundException $ex) {
            return $this->error($response, 'Vehículo no encontrado', 404);
        } catch (InvalidArgumentException $ex) {
            return $this->error($response, $ex->getMessage(), $ex->getCode());
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
