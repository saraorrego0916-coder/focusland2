<?php
/**
 * ============================================================================
 * [BLOQUE: CONTROLADOR DE AUTENTICACIÓN ADMINISTRATIVA - BACKEND]
 * ============================================================================
 * Propósito: Validar credenciales de acceso para el panel administrativo privado.
 *            Asegura que la solicitud provenga de POST, coteja contra la clave
 *            maestra institucional y habilita la sesión protegida en $_SESSION.
 * ============================================================================
 */
session_start();

// Verificación obligatoria de método HTTP POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: login.php");
    exit();
}

require_once '../database/conexion.php';
$db = $pdo ?? $conexion;

// Captura y saneamiento preliminar
$usuario  = trim($_POST['usuario'] ?? '');
$password = trim($_POST['password'] ?? '');

if (empty($usuario) || empty($password)) {
    header("Location: login.php?error=invalid_credentials");
    exit();
}

$autenticado = false;

// --- BLOQUE 1: VALIDACIÓN CONTRA CONTRASEÑA MAESTRA INSTITUCIONAL (FocusLand2026*) ---
$credenciales_maestras = [
    'admin@focusland.com' => 'FocusLand2026*',
    'admin'               => 'admin123',
    'administrador'       => 'FocusLand2026*'
];

if (isset($credenciales_maestras[$usuario]) && $credenciales_maestras[$usuario] === $password) {
    $autenticado = true;
}

// --- BLOQUE 2: VERIFICACIÓN COMPLEMENTARIA EN BASE DE DATOS (TABLA REGISTRAR / ROL ADMIN) ---
if (!$autenticado && $db) {
    try {
        // Inspección dinámica de la columna 'rol' en la tabla de usuarios
        $hasRol = false;
        $cols = $db->query("SHOW COLUMNS FROM registrar LIKE 'rol'")->fetchAll();
        if (!empty($cols)) {
            $hasRol = true;
        }

        if ($hasRol) {
            $stmt = $db->prepare("SELECT * FROM registrar WHERE (correo = :u OR nombre = :u) AND rol = 'admin' LIMIT 1");
        } else {
            $stmt = $db->prepare("SELECT * FROM registrar WHERE (correo = :u OR nombre = :u) LIMIT 1");
        }

        $stmt->bindValue(':u', $usuario, PDO::PARAM_STR);
        $stmt->execute();
        $userRow = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($userRow && !empty($userRow['password'])) {
            if (password_verify($password, $userRow['password']) || $password === $userRow['password']) {
                $autenticado = true;
            }
        }
    } catch (Exception $e) {
        error_log("Error en consulta de autenticación admin: " . $e->getMessage());
    }
}

// --- BLOQUE 3: CONCESIÓN DE PRIVILEGIOS DE SESIÓN Y ENTRADA AL DASHBOARD ---
if ($autenticado) {
    $_SESSION['admin_auth'] = true;
    $_SESSION['admin_user'] = $usuario;
    header("Location: dashboard.php");
    exit();
} else {
    header("Location: login.php?error=invalid_credentials");
    exit();
}
