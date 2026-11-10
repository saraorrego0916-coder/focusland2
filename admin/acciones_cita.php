<?php
/**
 * ============================================================================
 * [BLOQUE: CONTROLADOR CRUD ADMINISTRATIVO - ACCIONES DE CITAS Y CONTACTO]
 * ============================================================================
 * Propósito: Procesar mutaciones de datos solicitadas desde el dashboard:
 *            transición de estados y eliminación permanente de registros en las
 *            tablas `citas` y `contacto` mediante sentencias preparadas PDO.
 * ============================================================================
 */
session_start();

require_once '../database/conexion.php';

$accion = $_POST['accion'] ?? $_GET['accion'] ?? '';
$id     = filter_var($_POST['id'] ?? $_GET['id'] ?? '', FILTER_VALIDATE_INT);
$db     = $pdo ?? $conexion;

if (!$id || empty($accion)) {
    header("Location: dashboard.php?error=invalid_request");
    exit();
}

try {
    switch ($accion) {
        // --- BLOQUE 1: ACTUALIZACIÓN DE ESTADO (CITAS Y MENSAJES) ---
        case 'cambiar_estado':
            $nuevo_estado = trim($_POST['nuevo_estado'] ?? $_GET['nuevo_estado'] ?? '');
            $estados_validos = ['Pendiente', 'Confirmada', 'Cancelada'];
            
            if (!in_array($nuevo_estado, $estados_validos)) {
                header("Location: dashboard.php?error=invalid_status&tab=citas");
                exit();
            }

            $stmt = $db->prepare("UPDATE citas SET estado = :estado WHERE id = :id");
            $stmt->bindValue(':estado', $nuevo_estado, PDO::PARAM_STR);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            header("Location: dashboard.php?msg=status_updated&tab=citas");
            exit();

        case 'cambiar_estado_contacto':
            $nuevo_estado = trim($_POST['nuevo_estado'] ?? $_GET['nuevo_estado'] ?? '');
            $estados_validos_contacto = ['Nuevo', 'Atendido'];

            if (!in_array($nuevo_estado, $estados_validos_contacto)) {
                header("Location: dashboard.php?error=invalid_status&tab=contacto");
                exit();
            }

            $stmt = $db->prepare("UPDATE contacto SET estado = :estado WHERE id = :id");
            $stmt->bindValue(':estado', $nuevo_estado, PDO::PARAM_STR);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            header("Location: dashboard.php?msg=contact_status_updated&tab=contacto");
            exit();

        // --- BLOQUE 2: BORRADO SEGURO DE REGISTROS (CITAS Y MENSAJES) ---
        case 'eliminar':
            $stmt = $db->prepare("DELETE FROM citas WHERE id = :id");
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            header("Location: dashboard.php?msg=deleted&tab=citas");
            exit();

        case 'eliminar_contacto':
            $stmt = $db->prepare("DELETE FROM contacto WHERE id = :id");
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            header("Location: dashboard.php?msg=contact_deleted&tab=contacto");
            exit();

        default:
            header("Location: dashboard.php?error=unknown_action");
            exit();
    }
} catch (PDOException $e) {
    error_log("Error en acciones_cita: " . $e->getMessage());
    header("Location: dashboard.php?error=db_error");
    exit();
} catch (Exception $e) {
    error_log("Error general en acciones_cita: " . $e->getMessage());
    header("Location: dashboard.php?error=general_error");
    exit();
}
