<?php
/**
 * ============================================================================
 * [BLOQUE: MÓDULO DE BASE DE DATOS - CONEXIÓN PRINCIPAL]
 * ============================================================================
 * Propósito: Proveer la capa central de conexión a la base de datos MySQL para 
 *            la plataforma FocusLand, soportando tanto el driver moderno PDO
 *            (con sentencias preparadas y transacciones seguras) como MySQLi
 *            para compatibilidad con módulos procedimentales heredados.
 * Host:      localhost
 * BD:        focusland
 * Charset:   utf8mb4 (Soporte internacional completo y caracteres multilingües)
 * ============================================================================
 */

// --- BLOQUE 1: CONFIGURACIÓN DE PARÁMETROS Y CREDENCIALES ---
$db_host = "localhost";
$db_name = "focusland";
$db_user = "root";
$db_pass = "";
$charset = "utf8mb4";

$dsn = "mysql:host=$db_host;dbname=$db_name;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Lanzamiento estricto de excepciones
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Captura de resultados como arreglos asociativos
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Consultas nativas preparadas por el motor MySQL
];

// --- BLOQUE 2: INSTANCIACIÓN DE PDO CON MANEJO SEGURO DE EXCEPCIONES ---
try {
    $pdo = new PDO($dsn, $db_user, $db_pass, $options);
} catch (\PDOException $e) {
    die("Error de conexión PDO a la base de datos: " . $e->getMessage());
}

// --- BLOQUE 3: CONEXIÓN ALTERNATIVA MYSQLI Y VARIABLES DE COMPATIBILIDAD ---
$conexion = @new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conexion && !$conexion->connect_error) {
    $conexion->set_charset($charset);
} else {
    // Registro de incidencias sin detener el flujo si PDO está operativo
    error_log("Error de conexión MySQLi: " . ($conexion->connect_error ?? 'Desconocido'));
}

// Variables de compatibilidad global utilizadas por los módulos
$db   = $pdo;
$conn = $conexion;
?>
