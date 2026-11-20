# Alquiler de Vehículos - Microservicios

Aplicación web para registrar vehículos, clientes y reservas de alquiler. Está construida con tres microservicios independientes en PHP, cada uno con su propia base de datos MySQL, y un frontend en HTML, CSS y JavaScript vanilla.

## Arquitectura

| Servicio | Base de datos | Puerto | Responsabilidad |
| --- | --- | --- | --- |
| `vehiculos_ms` | `vehiculos_db` | 8001 | CRUD de vehículos y cambio de estado |
| `clientes_ms` | `clientes_db` | 8002 | CRUD de clientes |
| `reservas_ms` | `reservas_db` | 8003 | Reservas, devoluciones, disponibilidad e historiales |

Cada servicio usa **PHP 8 + Slim 4 + Eloquent**, con la misma estructura de carpetas del proyecto guía:

```
back/
  vehiculos_ms/
    app/
      Controllers/              logica de negocio
      global/                   conexion, URLs y cliente HTTP
      Middlewares/              CORS
      Models/                   modelos Eloquent
      Presentation/
        Repositories/           entrada y salida HTTP
        Routers/                rutas
    database/schema.sql
    public/index.php
    composer.json
  clientes_ms/
  reservas_ms/
  endpoints.http
front/
  alquiler_app/
    index.html                  panel de control
    vehiculos.html
    clientes.html
    reservas.html
    reportes.html
    css/index.css
    js/api.js
    js/inicio.js
    js/vehiculos.js
    js/clientes.js
    js/reservas.js
    js/reportes.js
```

## Comunicación entre servicios

- El frontend consume las tres APIs por HTTP con `fetch`.
- `reservas_ms` consulta a `vehiculos_ms` y `clientes_ms` para validar y enriquecer las reservas.
- `vehiculos_ms` y `clientes_ms` consultan a `reservas_ms` antes de eliminar un registro con reservas asociadas.
- No hay llaves foráneas entre bases de datos: cada servicio es dueño de sus datos.

## Requisitos

- XAMPP con PHP 8 y MySQL encendidos.
- Composer (solo para instalar dependencias; el proyecto ya incluye `vendor/` en local).

## Instalación

1. Importar las tres bases de datos. Desde la raíz del proyecto:

```bat
instalar_bases.bat
```

O manualmente:

```bat
C:\xampp\mysql\bin\mysql.exe -u root -e "source back/vehiculos_ms/database/schema.sql"
C:\xampp\mysql\bin\mysql.exe -u root -e "source back/clientes_ms/database/schema.sql"
C:\xampp\mysql\bin\mysql.exe -u root -e "source back/reservas_ms/database/schema.sql"
```

2. Instalar dependencias de cada servicio (solo si no existe `vendor/`):

```bat
cd back\vehiculos_ms
php C:\xampp\php\composer.phar install

cd ..\clientes_ms
php C:\xampp\php\composer.phar install

cd ..\reservas_ms
php C:\xampp\php\composer.phar install
```

3. Iniciar los tres microservicios:

```bat
back\iniciar_servicios.bat
```

O manualmente, en tres terminales:

```bat
cd C:\xampp\htdocs\alquiler_vehiculos_micro\back\vehiculos_ms
C:\xampp\php\php.exe -S 127.0.0.1:8001 -t public

cd C:\xampp\htdocs\alquiler_vehiculos_micro\back\clientes_ms
C:\xampp\php\php.exe -S 127.0.0.1:8002 -t public

cd C:\xampp\htdocs\alquiler_vehiculos_micro\back\reservas_ms
C:\xampp\php\php.exe -S 127.0.0.1:8003 -t public
```

4. Abrir la aplicación en el navegador:

```
http://localhost/alquiler_vehiculos_micro/front/alquiler_app/index.html
```

Apache debe estar encendido para servir el frontend.

## Datos de ejemplo

- 6 vehículos: Toyota Corolla, Hyundai Tucson, Kia Picanto, Nissan Frontier, Mazda CX-5 y Renault Logan.
- 5 clientes: Ana Rodríguez, Carlos Méndez, María Fernández, Luis Gómez y Laura Torres.
- 5 reservas: 3 activas y 2 completadas.

El vehículo Nissan Frontier aparece alquilado porque tiene una reserva activa que cubre la fecha actual. El Kia Picanto está en mantenimiento.

## Funcionalidades

- Panel con totales de vehículos, clientes, reservas, activas, disponibles y mantenimiento.
- CRUD completo de vehículos y clientes.
- Creación, edición, devolución, cancelación y eliminación de reservas.
- Validación de solapamiento de reservas por vehículo.
- Cambio automático del estado del vehículo entre `disponible` y `alquilado`.
- Protección contra eliminar vehículos o clientes con reservas.
- Consulta de vehículos disponibles en la fecha actual.
- Historial de reservas por vehículo y por cliente.

## Endpoints

Los ejemplos de todas las peticiones están en `back/endpoints.http`.

| Método | Ruta | Descripción |
| --- | --- | --- |
| GET | `/vehiculos` | Lista de vehículos |
| POST | `/vehiculos` | Crear vehículo |
| PUT | `/vehiculos/{id}` | Modificar vehículo |
| PUT | `/vehiculos/{id}/estado` | Cambiar estado |
| DELETE | `/vehiculos/{id}` | Eliminar vehículo |
| GET | `/clientes` | Lista de clientes |
| POST | `/clientes` | Crear cliente |
| PUT | `/clientes/{id}` | Modificar cliente |
| DELETE | `/clientes/{id}` | Eliminar cliente |
| GET | `/reservas` | Reservas con cliente y vehículo |
| POST | `/reservas` | Crear reserva |
| PUT | `/reservas/{id}` | Modificar reserva |
| PUT | `/reservas/{id}/devolver` | Registrar devolución |
| PUT | `/reservas/{id}/cancelar` | Cancelar reserva |
| DELETE | `/reservas/{id}` | Eliminar reserva |
| GET | `/reservas/disponibles` | Vehículos disponibles hoy |
| GET | `/reservas/historial/vehiculo/{id}` | Historial por vehículo |
| GET | `/reservas/historial/cliente/{id}` | Historial por cliente |
| GET | `/reservas/estadisticas` | Totales del panel |

## Notas

- Los puertos 8001, 8002 y 8003 deben estar libres.
- Si un servicio no está encendido, el frontend muestra un mensaje de conexión y el panel marca el servicio como "Sin conexión".
- Para reiniciar los datos, vuelva a ejecutar `instalar_bases.bat`; los scripts eliminan y recrean las tablas.
