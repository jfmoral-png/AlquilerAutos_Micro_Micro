# Alquiler de Vehículos - Microservicios

Aplicación web en PHP + MySQL para gestionar el alquiler de vehículos:
vehículos, clientes, reservas, devoluciones, disponibilidad e historial.

Proyecto construido siguiendo la estructura del proyecto
`programacion_g1_s2_2026-micro`, con tres microservicios independientes y un
frontend en HTML, CSS y JavaScript.

## Requisitos

- XAMPP con Apache y MySQL/MariaDB iniciados
- PHP 8.x (incluido en XAMPP)
- Composer (solo si hay que reinstalar las dependencias en `vendor/`)

## Servicios

| Servicio | Base de datos | Puerto |
| --- | --- | --- |
| `vehiculos_ms` | `vehiculos_db` | 8001 |
| `clientes_ms` | `clientes_db` | 8002 |
| `reservas_ms` | `reservas_db` | 8003 |

## Instalación

1. Copiar el proyecto en `C:\xampp\htdocs\alquiler_vehiculos_micro` (ya está).
2. Iniciar Apache y MySQL desde el panel de XAMPP.
3. Importar las tres bases de datos desde la raíz del proyecto:

```bat
instalar_bases.bat
```

4. Iniciar los tres microservicios:

```bat
back\iniciar_servicios.bat
```

5. Abrir <http://localhost/alquiler_vehiculos_micro/front/alquiler_app/index.html>
   en el navegador.

Cada servicio se puede iniciar también por separado, desde su carpeta y con su
puerto:

```bat
cd back\vehiculos_ms
php -S 127.0.0.1:8001 -t public
```

Si falta la carpeta `vendor/`, se instala con `composer install` dentro de cada
servicio. Las credenciales de conexión están en
`back/<servicio>/app/global/database_config.php` (`127.0.0.1`, usuario `root`,
sin contraseña), que son las de XAMPP por defecto.

## Funcionalidades

| Requisito del documento | Dónde está |
| --- | --- |
| Registrar vehículos (marca, modelo, año, categoría, estado) | `vehiculos_ms` y `vehiculos.html` |
| Registrar clientes (nombre, contacto, licencia) | `clientes_ms` y `clientes.html` |
| Reservas por periodo con fechas de inicio y fin | `reservas_ms` y `reservas.html` |
| Devolución de vehículos actualizando su estado | `PUT /reservas/{id}/devolver` |
| Historial de alquileres por vehículo y por cliente | `reservas_ms` y `reportes.html` |
| Consulta de vehículos disponibles | `GET /reservas/disponibles` |
| Cambio automático de estado del vehículo | `ReservasController::sincronizar_estado_vehiculo()` |
| Evitar conflictos de reservas (solapamiento) | `ReservasController::hay_solapamiento()` |

Reglas de negocio implementadas:

- Un vehículo en `mantenimiento` no se puede reservar.
- No se permiten dos reservas `activas` solapadas para el mismo vehículo.
- Al crear una reserva vigente para hoy, el vehículo pasa a `alquilado`.
- Al devolver o cancelar, el vehículo vuelve a `disponible` (salvo que esté en
  mantenimiento o tenga otra reserva vigente).
- No se pueden eliminar vehículos ni clientes con reservas registradas.

## Arquitectura

```
back/
  vehiculos_ms/            Puerto 8001
  clientes_ms/             Puerto 8002
  reservas_ms/             Puerto 8003
    app/
      Controllers/         Reglas de negocio
      global/              Conexión, URLs y cliente HTTP
      Middlewares/         CORS
      Models/              Modelos Eloquent
      Presentation/
        Repositories/      Entrada y salida HTTP
        Routers/           Rutas
    database/schema.sql    Script de la base de datos
    public/index.php       Arranque de Slim
  endpoints.http           Ejemplos de las peticiones
front/
  alquiler_app/
    index.html             Panel de control
    vehiculos.html  clientes.html  reservas.html  reportes.html
    css/index.css
    js/                    Un archivo por página y api.js compartido
```

Cada servicio tiene su propia base de datos y no hay llaves foráneas entre
ellas. El frontend consume las tres APIs por HTTP, y `reservas_ms` consulta a
`vehiculos_ms` y `clientes_ms` para validar y completar las reservas.

## Diferencias respecto del proyecto de referencia

- Se usan tres microservicios (vehículos, clientes y reservas) en lugar de uno,
  cada uno con su base de datos.
- Los servicios se comunican por HTTP con cURL: `reservas_ms` valida clientes y
  vehículos, y `vehiculos_ms` y `clientes_ms` consultan las reservas antes de
  eliminar un registro.
- No hay login: el frontend entra directo al panel de gestión.

Las dependencias se instalan con Composer (Slim 4 y Eloquent), igual que en el
proyecto de referencia.
