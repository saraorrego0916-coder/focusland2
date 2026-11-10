-- ============================================================================
-- [BLOQUE: ESQUEMA DE BASE DE DATOS FOCUSLAND - DEFINICIÓN DDL]
-- ============================================================================
-- Propósito: Definición formal de la estructura de tablas relacionales para el
--            sistema FocusLand (Gestión de usuarios, citas terapéuticas y contacto).
-- Host:      localhost / 127.0.0.1
-- BD:        focusland
-- Cotejamiento: utf8mb4 / utf8mb4_unicode_ci
-- Motor:     InnoDB (Garantía de integridad referencial y transacciones ACID)
-- ============================================================================

CREATE DATABASE IF NOT EXISTS `focusland` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `focusland`;

-- ============================================================================
-- [BLOQUE: TABLA DE USUARIOS Y AUTENTICACIÓN]
-- ============================================================================
-- Estructura para el registro de acudientes, usuarios del portal y administradores.
-- Campos:
--   - id: Identificador único autoincrementable.
--   - nombre: Nombre completo del usuario / acudiente.
--   - correo: Dirección de correo electrónico única (identificador de login).
--   - password: Hash seguro generado mediante password_hash() (BCRYPT / DEFAULT).
--   - rol: Perfil de acceso ('usuario' para familias, 'admin' para terapeutas/admin).
--   - fecha_registro: Marca de tiempo de registro en la plataforma.
-- ============================================================================
CREATE TABLE IF NOT EXISTS `registrar` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nombre` VARCHAR(150) NOT NULL,
    `correo` VARCHAR(150) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `rol` ENUM('usuario', 'admin') NOT NULL DEFAULT 'usuario',
    `fecha_registro` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- [BLOQUE: TABLA DE CITAS ESPECIALIZADAS]
-- ============================================================================
-- Gestión integral de solicitudes de citas psicopedagógicas y terapéuticas.
-- Campos organizados por segmentos funcionales:
--   1. Identificación y Relación:
--      - id: Identificador primario de la reserva.
--      - usuario_id: Clave foránea opcional asociada al acudiente registrado.
--   2. Datos del Acudiente / Contacto:
--      - nombre_acudiente: Nombre de la persona a cargo.
--      - correo: Email de notificación y confirmación.
--      - telefono: Celular / WhatsApp de contacto directo.
--   3. Datos del Menor:
--      - nombre_nino: Nombre completo del menor a atender.
--      - edad_nino: Edad en años (rango pedagógico 1-18 años).
--   4. Detalles de la Cita:
--      - motivo_consulta: Especialidad requerida (Psicología, TDAH, Motriz, etc.).
--      - tipo_atencion: Modalidad de sesión ('Virtual' o 'Presencial').
--      - horario_preferido: Franja deseada ('Mañana' o 'Tarde').
--      - mensaje: Observaciones, antecedentes o fechas solicitadas.
--   5. Auditoría y Control de Estado:
--      - estado: Estado de gestión ('Pendiente', 'Confirmada', 'Cancelada').
--      - fecha_creacion: Marca temporal del agendamiento.
-- ============================================================================
CREATE TABLE IF NOT EXISTS `citas` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `usuario_id` INT NULL,
    `nombre_acudiente` VARCHAR(150) NOT NULL,
    `correo` VARCHAR(150) NOT NULL,
    `telefono` VARCHAR(50) NOT NULL,
    `nombre_nino` VARCHAR(150) NOT NULL,
    `edad_nino` INT NOT NULL,
    `motivo_consulta` VARCHAR(255) NOT NULL,
    `tipo_atencion` VARCHAR(50) NOT NULL,
    `horario_preferido` VARCHAR(50) NOT NULL,
    `mensaje` TEXT NULL,
    `estado` ENUM('Pendiente', 'Confirmada', 'Cancelada') NOT NULL DEFAULT 'Pendiente',
    `fecha_creacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_citas_usuario` FOREIGN KEY (`usuario_id`) 
        REFERENCES `registrar`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- [BLOQUE: TABLA DE MENSAJES DE CONTACTO GENERAL]
-- ============================================================================
-- Recepción y seguimiento de inquietudes formuladas desde la sección de contacto.
-- Campos:
--   - id: Identificador único del mensaje.
--   - nombre_acudiente: Remitente.
--   - correo / telefono: Canales de respuesta directa.
--   - nombre_nino / edad_nino: Datos preliminares del infante (opcionales).
--   - motivo_consulta / mensaje: Consulta detallada de los padres.
--   - estado: Estado de respuesta ('Nuevo', 'Atendido').
--   - fecha_registro: Timestamp de recepción del mensaje.
-- ============================================================================
CREATE TABLE IF NOT EXISTS `contacto` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nombre_acudiente` VARCHAR(150) NOT NULL,
    `correo` VARCHAR(150) NOT NULL,
    `telefono` VARCHAR(50) NOT NULL,
    `nombre_nino` VARCHAR(150) NULL,
    `edad_nino` INT NULL,
    `motivo_consulta` VARCHAR(255) NOT NULL,
    `mensaje` TEXT NOT NULL,
    `tipo_atencion` VARCHAR(50) NULL,
    `horario_preferido` VARCHAR(50) NULL,
    `estado` ENUM('Nuevo', 'Atendido') NOT NULL DEFAULT 'Nuevo',
    `fecha_registro` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
