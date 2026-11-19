@echo off
REM ==================================================================
REM Copia de seguridad de la base de datos bd_infortec
REM Uso: doble clic en este archivo (MySQL de XAMPP debe estar iniciado)
REM Las copias se guardan en la carpeta "respaldos" con fecha y hora.
REM ==================================================================
setlocal
set MYSQLDUMP=c:\xampp\mysql\bin\mysqldump.exe
set DESTINO=%~dp0..\respaldos
if not exist "%DESTINO%" mkdir "%DESTINO%"

for /f %%i in ('powershell -NoProfile -Command "Get-Date -Format yyyy-MM-dd_HHmm"') do set FECHA=%%i
set ARCHIVO=%DESTINO%\bd_infortec_%FECHA%.sql

"%MYSQLDUMP%" -u root --databases bd_infortec --routines --single-transaction > "%ARCHIVO%"
if errorlevel 1 (
    echo ERROR: no se pudo crear la copia. Verifique que MySQL este iniciado en XAMPP.
    del "%ARCHIVO%" 2>nul
) else (
    echo Copia creada: %ARCHIVO%
)
pause
