<?php
/**
 * ============================================================================
 * [BLOQUE: CONTROL DE SESIÓN Y CIERRE SEGURO]
 * ============================================================================
 * Inicializa la sesión actual mediante session_start() para poder acceder
 * a sus variables antes del proceso de invalidación y limpieza.
 * ============================================================================
 */
session_start();

/**
 * ============================================================================
 * [BLOQUE: DESTRUCCIÓN DE SESIÓN, LIMPIEZA DE COOKIES Y REDIRECCIÓN SEGURA]
 * ============================================================================
 * 1. Vacía las variables de sesión en memoria con session_unset().
 * 2. Destruye el archivo / almacenamiento de sesión con session_destroy().
 * 3. Expira la cookie persistente del usuario ('focusland_user').
 * 4. Redirecciona al portal público (index.html) con indicador de éxito.
 * ============================================================================
 */
session_unset();
session_destroy();

// Limpiar cookie de usuario activo si existe
if (isset($_COOKIE['focusland_user'])) {
    setcookie("focusland_user", "", time() - 3600, "/");
}

header("Location: index.html?logout=success");
exit();
?>
