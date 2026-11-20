-- =====================================================================
-- Sistema web de control de asistencia y registro del personal
-- Tecnología Infortec E.I.R.L. - Nueva Cajamarca (2026)
-- Base de datos: bd_infortec  (MySQL / MariaDB - XAMPP)
-- Esta estructura coincide con la que usa el código de la carpeta app/.
-- =====================================================================
CREATE DATABASE IF NOT EXISTS bd_infortec CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE bd_infortec;

-- HU-01: Inicio de sesión del administrador
-- La contraseña se guarda cifrada (password_hash). Tras 5 intentos fallidos
-- la cuenta se bloquea 15 minutos (intentos_fallidos / bloqueado_hasta).
CREATE TABLE IF NOT EXISTS usuarios (
    id_usuario        INT AUTO_INCREMENT PRIMARY KEY,
    usuario           VARCHAR(30)  NOT NULL UNIQUE,
    clave             VARCHAR(255) NOT NULL,
    nombre            VARCHAR(60)  NOT NULL,
    intentos_fallidos INT          NOT NULL DEFAULT 0,
    bloqueado_hasta   DATETIME     NULL DEFAULT NULL
) ENGINE=InnoDB;

-- HU-02: Registro y edición de los datos del personal
-- hora_entrada = horario de ingreso con el que se calcula Puntual / Tardanza
CREATE TABLE IF NOT EXISTS personal (
    id_personal  INT AUTO_INCREMENT PRIMARY KEY,
    nombres      VARCHAR(100) NOT NULL,
    dni          CHAR(8)      NOT NULL UNIQUE,
    cargo        VARCHAR(60)  NOT NULL,
    hora_entrada TIME         NOT NULL DEFAULT '08:00:00'
) ENGINE=InnoDB;

-- HU-03: Registro de ingreso y salida
-- HU-04: Historial de asistencia
-- HU-05: Tardanzas e inasistencias (estado 'Tardanza' o 'Falta')
-- HU-06: Reportes por fecha o trabajador
-- Un solo registro por trabajador y por día (un_dia).
CREATE TABLE IF NOT EXISTS asistencia (
    id_asistencia INT AUTO_INCREMENT PRIMARY KEY,
    id_personal   INT  NOT NULL,
    fecha         DATE NOT NULL,
    hora_entrada  TIME NULL DEFAULT NULL,
    hora_salida   TIME NULL DEFAULT NULL,
    estado        ENUM('Puntual','Tardanza','Falta') NOT NULL,
    UNIQUE KEY un_dia (id_personal, fecha),
    CONSTRAINT asistencia_ibfk_1 FOREIGN KEY (id_personal) REFERENCES personal (id_personal)
) ENGINE=InnoDB;

-- Usuario administrador inicial
--   Usuario: admin
-- La contraseña se guarda cifrada (password_hash) en la columna clave.
-- Cámbiela después de instalar con:
--   c:\xampp\php\php.exe herramientas\clave.php restablecer admin
INSERT IGNORE INTO usuarios (usuario, clave, nombre)
VALUES ('admin', '$2y$10$y2RfDJAO3GdceRrRfcLzyOZ3l3zWlEwNFnch8u7wNiZzo5mO.ctq.', 'Administrador Infortec');
