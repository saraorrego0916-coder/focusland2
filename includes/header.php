<?php
/**
 * ============================================================================
 * [BLOQUE: CONTROL DE SESIÓN Y DETECCIÓN DE ESTADO DE AUTENTICACIÓN]
 * ============================================================================
 * Verifica e inicia la sesión si no ha sido inicializada previamente.
 * Evalúa las variables de sesión activas ($_SESSION['correo'], ['nombre'],
 * etc.) para determinar si el visitante es un usuario registrado o invitado,
 * y formatea el nombre de pila para el saludo personalizado.
 * ============================================================================
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$activePage = $activePage ?? '';

// Comprobar si hay sesión activa
$is_logged_in = !empty($_SESSION['correo']) || !empty($_SESSION['nombre']) || !empty($_SESSION['usuario']) || !empty($_SESSION['id']);

$usuario_nombre = $_SESSION['nombre'] ?? $_SESSION['usuario_nombre'] ?? $_SESSION['usuario'] ?? '';
if (empty($usuario_nombre) && !empty($_SESSION['correo'])) {
    $partes = explode('@', $_SESSION['correo']);
    $usuario_nombre = ucfirst($partes[0]);
}

$nombreCorto = 'Usuario';
if (!empty($usuario_nombre)) {
    $primerNombre = explode(' ', trim($usuario_nombre))[0];
    $nombreCorto = htmlspecialchars($primerNombre);
}
?>
<header class="navbar">
    <a href="index.html" class="navbar-brand">
        <img src="assets/imagenes/logo.png" alt="Logo FocusLand">
        <span>FocusLand</span>
    </a>

    <button id="menuToggle" class="menu-toggle" aria-label="Abrir menú" aria-expanded="false">
        <i class="fas fa-bars"></i>
    </button>

    <nav class="nav-menu">
        <ul class="nav-links">
            <li><a href="index.html" class="<?php echo ($activePage === 'inicio' ? 'active' : ''); ?>">Inicio</a></li>
            <li><a href="quienes-somos.html" class="<?php echo ($activePage === 'quienes-somos' ? 'active' : ''); ?>">Quiénes Somos</a></li>
            <li><a href="servicios.html" class="<?php echo ($activePage === 'servicios' ? 'active' : ''); ?>">Servicios</a></li>
            <li><a href="recursos.html" class="<?php echo ($activePage === 'recursos' ? 'active' : ''); ?>">Recursos</a></li>
            <li><a href="contacto.php" class="<?php echo ($activePage === 'contacto' ? 'active' : ''); ?>">Contacto</a></li>

            <!-- ==========================================================
                 [BLOQUE: RENDERIZADO CONDICIONAL DEL MENÚ (INVITADO VS AUTENTICADO)]
                 - Estado Autenticado: Muestra badge con nombre del usuario,
                   botón directo para Agendar Cita y botón de Cerrar Sesión.
                 - Estado Invitado: Muestra botón de acceso 'Ingresar' (login).
                 ========================================================== -->
            <?php if ($is_logged_in): ?>
                <li class="nav-auth-group">
                    <div class="user-profile-badge">
                        <i class="fa-solid fa-circle-user user-icon"></i>
                        <span class="user-name">Hola, <?php echo $nombreCorto; ?></span>
                    </div>
                    <a href="cita.php" class="btn-agendar <?php echo ($activePage === 'cita' ? 'active' : ''); ?>">Agendar Cita</a>
                    <a href="logout.php" class="btn-logout" title="Cerrar sesión">
                        <i class="fa-solid fa-arrow-right-from-bracket"></i> Cerrar Sesión
                    </a>
                </li>
            <?php else: ?>
                <li>
                    <a href="login.php" class="btn-ingresar <?php echo ($activePage === 'login' ? 'active' : ''); ?>">Ingresar</a>
                </li>
            <?php endif; ?>
        </ul>
    </nav>
</header>
