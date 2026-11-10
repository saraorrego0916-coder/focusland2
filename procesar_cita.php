<?php
/**
 * ============================================================================
 * [BLOQUE: CONTROLADOR DE AGENDAMIENTO DE CITAS - PROCESAMIENTO BACKEND]
 * ============================================================================
 * Propósito: Recepción, saneamiento, validación e inserción transaccional de 
 *            citas terapéuticas para la plataforma FocusLand mediante PDO.
 * ============================================================================
 */

// --- BLOQUE 1: VERIFICACIÓN DE SESIÓN ACTIVA Y SEGURIDAD DE ACCESO ---
session_start();

// Validar que la solicitud sea exclusivamente por método POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: cita.php");
    exit();
}

// Carga de la capa de persistencia PDO
require_once 'database/conexion.php';

// Obtener el identificador del usuario si se encuentra autenticado
$usuario_id = $_SESSION['usuario_id'] ?? $_SESSION['id'] ?? null;


// --- BLOQUE 2: CAPTURA, SANEAMIENTO Y VALIDACIÓN DE CAMPOS RECIBIDOS POR POST ---
$nombre_acudiente  = trim($_POST['nombre_acudiente'] ?? '');
$correo            = filter_var(trim($_POST['correo'] ?? ''), FILTER_SANITIZE_EMAIL);
$telefono          = trim($_POST['telefono'] ?? '');
$nombre_nino       = trim($_POST['nombre_nino'] ?? '');
$edad_nino         = filter_var($_POST['edad_nino'] ?? '', FILTER_VALIDATE_INT);
$motivo_consulta   = trim($_POST['motivo_consulta'] ?? '');
$tipo_atencion     = trim($_POST['tipo_atencion'] ?? '');
$horario_preferido = trim($_POST['horario_preferido'] ?? '');
$fecha_cita        = trim($_POST['fecha_cita'] ?? '');
$mensaje           = trim($_POST['mensaje'] ?? '');

// Si se incluyó fecha sugerida, incorporarla en el campo descriptivo
if (!empty($fecha_cita)) {
    $mensaje = !empty($mensaje) ? "[Fecha solicitada: {$fecha_cita}] " . $mensaje : "[Fecha solicitada: {$fecha_cita}]";
}

// Tratar mensaje vacío como NULL para consistencia en BD
$mensaje = !empty($mensaje) ? $mensaje : null;

// Validación estricta de presencia para todos los campos indispensables
if (
    empty($nombre_acudiente) ||
    empty($correo) ||
    empty($telefono) ||
    empty($nombre_nino) ||
    $edad_nino === false ||
    empty($motivo_consulta) ||
    empty($tipo_atencion) ||
    empty($horario_preferido)
) {
    header("Location: cita.php?error=missing_fields");
    exit();
}


// --- BLOQUE 3: PREPARACIÓN Y EJECUCIÓN DE LA CONSULTA SQL (INSERT INTO CITAS) MEDIANTE PDO ---
try {
    $db = $pdo ?? $conexion;

    $sql = "INSERT INTO citas (
        usuario_id, nombre_acudiente, correo, telefono,
        nombre_nino, edad_nino, motivo_consulta,
        tipo_atencion, horario_preferido, mensaje
    ) VALUES (
        :usuario_id, :nombre_acudiente, :correo, :telefono,
        :nombre_nino, :edad_nino, :motivo_consulta,
        :tipo_atencion, :horario_preferido, :mensaje
    )";

    $stmt = $db->prepare($sql);

    // Enlace de parámetros tipados (PDO Prepared Statements) para protección contra SQL Injection
    $stmt->bindValue(':usuario_id', $usuario_id !== null ? (int)$usuario_id : null, $usuario_id !== null ? PDO::PARAM_INT : PDO::PARAM_NULL);
    $stmt->bindValue(':nombre_acudiente', $nombre_acudiente, PDO::PARAM_STR);
    $stmt->bindValue(':correo', $correo, PDO::PARAM_STR);
    $stmt->bindValue(':telefono', $telefono, PDO::PARAM_STR);
    $stmt->bindValue(':nombre_nino', $nombre_nino, PDO::PARAM_STR);
    $stmt->bindValue(':edad_nino', (int)$edad_nino, PDO::PARAM_INT);
    $stmt->bindValue(':motivo_consulta', $motivo_consulta, PDO::PARAM_STR);
    $stmt->bindValue(':tipo_atencion', $tipo_atencion, PDO::PARAM_STR);
    $stmt->bindValue(':horario_preferido', $horario_preferido, PDO::PARAM_STR);
    $stmt->bindValue(':mensaje', $mensaje, $mensaje !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);

    $stmt->execute();


    // --- BLOQUE 4: GESTIÓN DE REDIRECCIONES CON FEEDBACK VISUAL POR URL ---
    header("Location: cita.php?status=success");
    exit();

} catch (PDOException $e) {
    error_log("Error PDO en citas: " . $e->getMessage());
    header("Location: cita.php?error=db_error");
    exit();
} catch (Exception $e) {
    error_log("Error general en citas: " . $e->getMessage());
    header("Location: cita.php?error=server_error");
    exit();
}
