<?php

namespace App\Controllers;

use App\Models\Vehiculo;
use Illuminate\Database\Eloquent\Collection;
use InvalidArgumentException;
use RuntimeException;

class VehiculosController
{
    function get_vehiculos(): Collection
    {
        return Vehiculo::orderBy('marca')->orderBy('modelo')->get();
    }

    function find_vehiculo(int $id): Vehiculo
    {
        return Vehiculo::findOrFail($id);
    }

    function create(array $data): Vehiculo
    {
        $this->validate($data);

        $vehiculo = new Vehiculo();
        $vehiculo->marca = trim($data['marca']);
        $vehiculo->modelo = trim($data['modelo']);
        $vehiculo->anio = (int) $data['anio'];
        $vehiculo->categoria = isset($data['categoria']) ? trim($data['categoria']) : null;
        $vehiculo->estado = $data['estado'] ?? 'disponible';
        $vehiculo->save();

        return $vehiculo;
    }

    function update(int $id, array $data): Vehiculo
    {
        $this->validate($data);

        $vehiculo = Vehiculo::findOrFail($id);
        $vehiculo->marca = trim($data['marca']);
        $vehiculo->modelo = trim($data['modelo']);
        $vehiculo->anio = (int) $data['anio'];
        $vehiculo->categoria = isset($data['categoria']) ? trim($data['categoria']) : null;
        $vehiculo->estado = $data['estado'] ?? $vehiculo->estado;
        $vehiculo->save();

        return $vehiculo;
    }

    function update_estado(int $id, array $data): Vehiculo
    {
        $estado = $data['estado'] ?? '';

        if (!in_array($estado, ['disponible', 'alquilado', 'mantenimiento'], true)) {
            throw new InvalidArgumentException('El estado del vehículo no es válido', 400);
        }

        $vehiculo = Vehiculo::findOrFail($id);
        $vehiculo->estado = $estado;
        $vehiculo->save();

        return $vehiculo;
    }

    function delete(int $id): Vehiculo
    {
        $vehiculo = Vehiculo::findOrFail($id);

        $respuesta = http_request('GET', RESERVAS_MS_URL . '/reservas/vehiculo/' . $id . '/total');

        if ($respuesta['status'] === 0) {
            throw new RuntimeException('No se pudo consultar el servicio de reservas', 503);
        }

        if (($respuesta['data']['total'] ?? 0) > 0) {
            throw new InvalidArgumentException('El vehículo tiene reservas asociadas y no se puede eliminar', 409);
        }

        $vehiculo->delete();

        return $vehiculo;
    }

    private function validate(array $data): void
    {
        foreach (['marca', 'modelo', 'anio'] as $campo) {
            if (!isset($data[$campo]) || trim((string) $data[$campo]) === '') {
                throw new InvalidArgumentException("El campo $campo es obligatorio", 400);
            }
        }

        $anio = (int) $data['anio'];

        if ($anio < 1900 || $anio > ((int) date('Y') + 1)) {
            throw new InvalidArgumentException('El año del vehículo no es válido', 400);
        }

        if (isset($data['estado']) && !in_array($data['estado'], ['disponible', 'alquilado', 'mantenimiento'], true)) {
            throw new InvalidArgumentException('El estado del vehículo no es válido', 400);
        }
    }
}
