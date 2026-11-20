<?php

namespace App\Controllers;

use App\Models\Reserva;
use DateTime;
use InvalidArgumentException;
use RuntimeException;

class ReservasController
{
    function get_reservas(): array
    {
        $reservas = Reserva::orderBy('fecha_inicio', 'desc')->orderBy('id', 'desc')->get();

        return $this->enrich($reservas);
    }

    function find_reserva(int $id): array
    {
        return $this->enrich_one(Reserva::findOrFail($id));
    }

    function create(array $data): array
    {
        $this->validate($data);

        $cliente_id = (int) $data['cliente_id'];
        $vehiculo_id = (int) $data['vehiculo_id'];
        $inicio = $data['fecha_inicio'];
        $fin = $data['fecha_fin'];

        $this->validar_cliente($cliente_id);

        $vehiculo = $this->obtener_vehiculo($vehiculo_id);

        if ($vehiculo === null) {
            throw new InvalidArgumentException('El vehículo no existe', 400);
        }

        if (($vehiculo['estado'] ?? '') === 'mantenimiento') {
            throw new InvalidArgumentException('El vehículo está en mantenimiento y no se puede reservar', 400);
        }

        if ($this->hay_solapamiento($vehiculo_id, $inicio, $fin)) {
            throw new InvalidArgumentException('El vehículo ya tiene una reserva activa en ese periodo', 409);
        }

        $reserva = new Reserva();
        $reserva->cliente_id = $cliente_id;
        $reserva->vehiculo_id = $vehiculo_id;
        $reserva->fecha_inicio = $inicio;
        $reserva->fecha_fin = $fin;
        $reserva->estado = 'activa';
        $reserva->save();

        $this->sincronizar_estado_vehiculo($vehiculo_id);

        return $this->enrich_one($reserva);
    }

    function update(int $id, array $data): array
    {
        $reserva = Reserva::findOrFail($id);

        if ($reserva->estado !== 'activa') {
            throw new InvalidArgumentException('Solo se pueden modificar reservas activas', 400);
        }

        $this->validate($data);

        $cliente_id = (int) $data['cliente_id'];
        $vehiculo_id = (int) $data['vehiculo_id'];
        $inicio = $data['fecha_inicio'];
        $fin = $data['fecha_fin'];

        $this->validar_cliente($cliente_id);

        $vehiculo = $this->obtener_vehiculo($vehiculo_id);

        if ($vehiculo === null) {
            throw new InvalidArgumentException('El vehículo no existe', 400);
        }

        if (($vehiculo['estado'] ?? '') === 'mantenimiento') {
            throw new InvalidArgumentException('El vehículo está en mantenimiento y no se puede reservar', 400);
        }

        if ($this->hay_solapamiento($vehiculo_id, $inicio, $fin, $id)) {
            throw new InvalidArgumentException('El vehículo ya tiene una reserva activa en ese periodo', 409);
        }

        $vehiculo_anterior = (int) $reserva->vehiculo_id;

        $reserva->cliente_id = $cliente_id;
        $reserva->vehiculo_id = $vehiculo_id;
        $reserva->fecha_inicio = $inicio;
        $reserva->fecha_fin = $fin;
        $reserva->save();

        $this->sincronizar_estado_vehiculo($vehiculo_anterior);
        $this->sincronizar_estado_vehiculo($vehiculo_id);

        return $this->enrich_one($reserva);
    }

    function devolver(int $id): array
    {
        $reserva = Reserva::findOrFail($id);

        if ($reserva->estado !== 'activa') {
            throw new InvalidArgumentException('La reserva no está activa', 400);
        }

        $reserva->estado = 'completada';
        $reserva->save();

        $this->sincronizar_estado_vehiculo((int) $reserva->vehiculo_id);

        return $this->enrich_one($reserva);
    }

    function cancelar(int $id): array
    {
        $reserva = Reserva::findOrFail($id);

        if ($reserva->estado !== 'activa') {
            throw new InvalidArgumentException('La reserva no está activa', 400);
        }

        $reserva->estado = 'cancelada';
        $reserva->save();

        $this->sincronizar_estado_vehiculo((int) $reserva->vehiculo_id);

        return $this->enrich_one($reserva);
    }

    function delete(int $id): array
    {
        $reserva = Reserva::findOrFail($id);
        $vehiculo_id = (int) $reserva->vehiculo_id;
        $data = $this->enrich_one($reserva);

        $reserva->delete();

        $this->sincronizar_estado_vehiculo($vehiculo_id);

        return $data;
    }

    function disponibles(): array
    {
        $respuesta = http_request('GET', VEHICULOS_MS_URL . '/vehiculos');

        if ($respuesta['status'] !== 200 || !is_array($respuesta['data'])) {
            throw new RuntimeException('No se pudo consultar el servicio de vehículos', 503);
        }

        $hoy = date('Y-m-d');

        $ocupados = Reserva::where('estado', 'activa')
            ->where('fecha_inicio', '<=', $hoy)
            ->where('fecha_fin', '>=', $hoy)
            ->pluck('vehiculo_id')
            ->all();

        $ocupados = array_map('intval', $ocupados);
        $disponibles = [];

        foreach ($respuesta['data'] as $vehiculo) {
            if (($vehiculo['estado'] ?? '') !== 'disponible') {
                continue;
            }

            if (in_array((int) $vehiculo['id'], $ocupados, true)) {
                continue;
            }

            $disponibles[] = $vehiculo;
        }

        return array_values($disponibles);
    }

    function historial_vehiculo(int $id): array
    {
        $reservas = Reserva::where('vehiculo_id', $id)
            ->orderBy('fecha_inicio', 'desc')
            ->get();

        return $this->enrich($reservas);
    }

    function historial_cliente(int $id): array
    {
        $reservas = Reserva::where('cliente_id', $id)
            ->orderBy('fecha_inicio', 'desc')
            ->get();

        return $this->enrich($reservas);
    }

    function contar_por_vehiculo(int $id): array
    {
        return ['total' => Reserva::where('vehiculo_id', $id)->count()];
    }

    function contar_por_cliente(int $id): array
    {
        return ['total' => Reserva::where('cliente_id', $id)->count()];
    }

    function estadisticas(): array
    {
        $vehiculos = http_request('GET', VEHICULOS_MS_URL . '/vehiculos');
        $clientes = http_request('GET', CLIENTES_MS_URL . '/clientes');

        if ($vehiculos['status'] !== 200 || $clientes['status'] !== 200) {
            throw new RuntimeException('No se pudieron consultar los servicios de vehículos y clientes', 503);
        }

        $reservas = Reserva::all();
        $estados = ['activa' => 0, 'completada' => 0, 'cancelada' => 0];

        foreach ($reservas as $reserva) {
            $estados[$reserva->estado] = ($estados[$reserva->estado] ?? 0) + 1;
        }

        $hoy = date('Y-m-d');
        $ocupados = Reserva::where('estado', 'activa')
            ->where('fecha_inicio', '<=', $hoy)
            ->where('fecha_fin', '>=', $hoy)
            ->pluck('vehiculo_id')
            ->all();
        $ocupados = array_map('intval', $ocupados);

        $disponibles = 0;
        $alquilados = 0;
        $mantenimiento = 0;

        foreach ($vehiculos['data'] as $vehiculo) {
            $estado = $vehiculo['estado'] ?? '';

            if ($estado === 'mantenimiento') {
                $mantenimiento++;
            }

            if ($estado === 'alquilado') {
                $alquilados++;
            }

            if ($estado === 'disponible' && !in_array((int) $vehiculo['id'], $ocupados, true)) {
                $disponibles++;
            }
        }

        return [
            'vehiculos' => count($vehiculos['data']),
            'clientes' => count($clientes['data']),
            'reservas' => $reservas->count(),
            'activas' => $estados['activa'],
            'completadas' => $estados['completada'],
            'canceladas' => $estados['cancelada'],
            'disponibles' => $disponibles,
            'alquilados' => $alquilados,
            'mantenimiento' => $mantenimiento,
        ];
    }

    private function enrich($reservas): array
    {
        $clientes = $this->map_clientes();
        $vehiculos = $this->map_vehiculos();
        $lista = [];

        foreach ($reservas as $reserva) {
            $lista[] = $this->to_array($reserva, $clientes, $vehiculos);
        }

        return $lista;
    }

    private function enrich_one(Reserva $reserva): array
    {
        return $this->to_array($reserva, $this->map_clientes(), $this->map_vehiculos());
    }

    private function to_array(Reserva $reserva, array $clientes, array $vehiculos): array
    {
        $data = $reserva->toArray();
        $cliente = $clientes[(int) $reserva->cliente_id] ?? null;
        $vehiculo = $vehiculos[(int) $reserva->vehiculo_id] ?? null;

        $data['cliente_nombre'] = $cliente['nombre'] ?? 'Cliente no disponible';
        $data['cliente_correo'] = $cliente['correo'] ?? '';
        $data['cliente_telefono'] = $cliente['telefono'] ?? '';
        $data['vehiculo'] = $vehiculo
            ? trim(($vehiculo['marca'] ?? '') . ' ' . ($vehiculo['modelo'] ?? ''))
            : 'Vehículo no disponible';
        $data['vehiculo_marca'] = $vehiculo['marca'] ?? '';
        $data['vehiculo_modelo'] = $vehiculo['modelo'] ?? '';
        $data['vehiculo_anio'] = $vehiculo['anio'] ?? '';

        return $data;
    }

    private function map_clientes(): array
    {
        $respuesta = http_request('GET', CLIENTES_MS_URL . '/clientes');
        $mapa = [];

        if ($respuesta['status'] === 200 && is_array($respuesta['data'])) {
            foreach ($respuesta['data'] as $cliente) {
                $mapa[(int) $cliente['id']] = $cliente;
            }
        }

        return $mapa;
    }

    private function map_vehiculos(): array
    {
        $respuesta = http_request('GET', VEHICULOS_MS_URL . '/vehiculos');
        $mapa = [];

        if ($respuesta['status'] === 200 && is_array($respuesta['data'])) {
            foreach ($respuesta['data'] as $vehiculo) {
                $mapa[(int) $vehiculo['id']] = $vehiculo;
            }
        }

        return $mapa;
    }

    private function validate(array $data): void
    {
        foreach (['cliente_id', 'vehiculo_id', 'fecha_inicio', 'fecha_fin'] as $campo) {
            if (!isset($data[$campo]) || trim((string) $data[$campo]) === '') {
                throw new InvalidArgumentException("El campo $campo es obligatorio", 400);
            }
        }

        if (!is_string($data['fecha_inicio']) || !is_string($data['fecha_fin'])
            || !$this->fecha_valida($data['fecha_inicio']) || !$this->fecha_valida($data['fecha_fin'])) {
            throw new InvalidArgumentException('Las fechas deben tener el formato AAAA-MM-DD', 400);
        }

        if ($data['fecha_fin'] < $data['fecha_inicio']) {
            throw new InvalidArgumentException('La fecha de fin no puede ser anterior a la fecha de inicio', 400);
        }
    }

    private function fecha_valida(string $valor): bool
    {
        $fecha = DateTime::createFromFormat('Y-m-d', $valor);

        return $fecha !== false && $fecha->format('Y-m-d') === $valor;
    }

    private function validar_cliente(int $id): void
    {
        $respuesta = http_request('GET', CLIENTES_MS_URL . '/clientes/' . $id);

        if ($respuesta['status'] === 404) {
            throw new InvalidArgumentException('El cliente no existe', 400);
        }

        if ($respuesta['status'] !== 200) {
            throw new RuntimeException('No se pudo consultar el servicio de clientes', 503);
        }
    }

    private function obtener_vehiculo(int $id): ?array
    {
        $respuesta = http_request('GET', VEHICULOS_MS_URL . '/vehiculos/' . $id);

        if ($respuesta['status'] === 404) {
            return null;
        }

        if ($respuesta['status'] !== 200 || !is_array($respuesta['data'])) {
            throw new RuntimeException('No se pudo consultar el servicio de vehículos', 503);
        }

        return $respuesta['data'];
    }

    private function hay_solapamiento(int $vehiculo_id, string $inicio, string $fin, ?int $excluir_id = null): bool
    {
        $consulta = Reserva::where('vehiculo_id', $vehiculo_id)
            ->where('estado', 'activa')
            ->where('fecha_inicio', '<=', $fin)
            ->where('fecha_fin', '>=', $inicio);

        if ($excluir_id !== null) {
            $consulta->where('id', '!=', $excluir_id);
        }

        return $consulta->exists();
    }

    private function sincronizar_estado_vehiculo(int $vehiculo_id): void
    {
        $vehiculo = $this->obtener_vehiculo($vehiculo_id);

        if ($vehiculo === null || ($vehiculo['estado'] ?? '') === 'mantenimiento') {
            return;
        }

        $hoy = date('Y-m-d');

        $activa = Reserva::where('vehiculo_id', $vehiculo_id)
            ->where('estado', 'activa')
            ->where('fecha_inicio', '<=', $hoy)
            ->where('fecha_fin', '>=', $hoy)
            ->exists();

        $nuevo_estado = $activa ? 'alquilado' : 'disponible';

        if (($vehiculo['estado'] ?? '') === $nuevo_estado) {
            return;
        }

        $respuesta = http_request('PUT', VEHICULOS_MS_URL . '/vehiculos/' . $vehiculo_id . '/estado', [
            'estado' => $nuevo_estado,
        ]);

        if ($respuesta['status'] !== 200) {
            error_log('No se pudo actualizar el estado del vehículo ' . $vehiculo_id);
        }
    }
}
