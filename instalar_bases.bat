@echo off
setlocal
set MYSQL=C:\xampp\mysql\bin\mysql.exe
cd /d "%~dp0"

echo Importando la base de datos de vehiculos...
"%MYSQL%" --default-character-set=utf8mb4 -u root -e "source back/vehiculos_ms/database/schema.sql"
if errorlevel 1 goto error

echo Importando la base de datos de clientes...
"%MYSQL%" --default-character-set=utf8mb4 -u root -e "source back/clientes_ms/database/schema.sql"
if errorlevel 1 goto error

echo Importando la base de datos de reservas...
"%MYSQL%" --default-character-set=utf8mb4 -u root -e "source back/reservas_ms/database/schema.sql"
if errorlevel 1 goto error

echo.
echo Bases de datos creadas con los datos de ejemplo:
echo   vehiculos_db, clientes_db y reservas_db
echo.
echo Siguiente paso: ejecute back\iniciar_servicios.bat
goto fin

:error
echo.
echo Ocurrio un error al importar las bases de datos.
echo Verifique que MySQL este encendido en XAMPP.

:fin
pause
endlocal
