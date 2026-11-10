<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json');

$is_logged_in = !empty($_SESSION['correo']) || !empty($_SESSION['nombre']) || !empty($_SESSION['usuario']) || !empty($_SESSION['id']);

$nombre = '';
if ($is_logged_in) {
    $usuario_nombre = $_SESSION['nombre'] ?? $_SESSION['usuario_nombre'] ?? $_SESSION['usuario'] ?? '';
    if (empty($usuario_nombre) && !empty($_SESSION['correo'])) {
        $partes = explode('@', $_SESSION['correo']);
        $usuario_nombre = ucfirst($partes[0]);
    }
    $nombre = explode(' ', trim($usuario_nombre))[0];
}

echo json_encode([
    'logged_in' => $is_logged_in,
    'nombre' => $nombre
]);
exit();
?>
