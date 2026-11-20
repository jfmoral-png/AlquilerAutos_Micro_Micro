@echo off
setlocal
set PHP=C:\xampp\php\php.exe
cd /d "%~dp0"

start "vehiculos_ms 8001" "%PHP%" -S 127.0.0.1:8001 -t "%~dp0vehiculos_ms\public"
start "clientes_ms 8002" "%PHP%" -S 127.0.0.1:8002 -t "%~dp0clientes_ms\public"
start "reservas_ms 8003" "%PHP%" -S 127.0.0.1:8003 -t "%~dp0reservas_ms\public"

echo Microservicios iniciados en los puertos 8001, 8002 y 8003.
echo Abra http://localhost/alquiler_vehiculos_micro/front/alquiler_app/index.html
endlocal
