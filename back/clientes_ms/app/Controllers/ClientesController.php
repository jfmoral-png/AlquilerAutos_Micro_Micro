<?php

namespace App\Controllers;

use App\Models\Cliente;
use Illuminate\Database\Eloquent\Collection;
use InvalidArgumentException;
use RuntimeException;

class ClientesController
{
    function get_clientes(): Collection
    {
        return Cliente::orderBy('nombre')->get();
    }

    function find_cliente(int $id): Cliente
    {
        return Cliente::findOrFail($id);
    }

    function create(array $data): Cliente
    {
        $this->validate($data);

        $cliente = new Cliente();
        $cliente->nombre = trim($data['nombre']);
        $cliente->telefono = isset($data['telefono']) ? trim($data['telefono']) : null;
        $cliente->correo = isset($data['correo']) ? trim($data['correo']) : null;
        $cliente->numero_licencia = isset($data['numero_licencia']) ? trim($data['numero_licencia']) : null;
        $cliente->save();

        return $cliente;
    }

    function update(int $id, array $data): Cliente
    {
        $this->validate($data);

        $cliente = Cliente::findOrFail($id);
        $cliente->nombre = trim($data['nombre']);
        $cliente->telefono = isset($data['telefono']) ? trim($data['telefono']) : null;
        $cliente->correo = isset($data['correo']) ? trim($data['correo']) : null;
        $cliente->numero_licencia = isset($data['numero_licencia']) ? trim($data['numero_licencia']) : null;
        $cliente->save();

        return $cliente;
    }

    function delete(int $id): Cliente
    {
        $cliente = Cliente::findOrFail($id);

        $respuesta = http_request('GET', RESERVAS_MS_URL . '/reservas/cliente/' . $id . '/total');

        if ($respuesta['status'] === 0) {
            throw new RuntimeException('No se pudo consultar el servicio de reservas', 503);
        }

        if (($respuesta['data']['total'] ?? 0) > 0) {
            throw new InvalidArgumentException('El cliente tiene reservas asociadas y no se puede eliminar', 409);
        }

        $cliente->delete();

        return $cliente;
    }

    private function validate(array $data): void
    {
        if (!isset($data['nombre']) || trim((string) $data['nombre']) === '') {
            throw new InvalidArgumentException('El nombre del cliente es obligatorio', 400);
        }

        if (isset($data['correo']) && trim((string) $data['correo']) !== '') {
            if (!filter_var(trim($data['correo']), FILTER_VALIDATE_EMAIL)) {
                throw new InvalidArgumentException('El correo del cliente no es válido', 400);
            }
        }

        if (isset($data['telefono']) && trim((string) $data['telefono']) !== '') {
            if (!preg_match('/^[0-9+()\-\s]{6,20}$/', trim((string) $data['telefono']))) {
                throw new InvalidArgumentException('El teléfono del cliente no es válido', 400);
            }
        }
    }
}
