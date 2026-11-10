<?php
/**
 * ============================================================================
 * [BLOQUE: AUTENTICACIÓN - CONTROL DE SESIÓN Y GESTIÓN DE MENSAJES]
 * ============================================================================
 * Inicialización segura de la sesión HTTP mediante session_start(), carga de
 * la capa de conexión y recuperación de estados o mensajes de feedback
 * enviados por redirecciones previas (parámetros GET o variables $_SESSION).
 * ============================================================================
 */
session_start();

require_once 'database/conexion.php';

$mensaje = "";
$tipoMensaje = "";
$redirigir = false;

// 1. Mensajes desde variable de sesión
if (isset($_SESSION['mensaje'])) {
    $mensaje = $_SESSION['mensaje'];
    $tipoMensaje = $_SESSION['tipoMensaje'] ?? 'alert-error';
    unset($_SESSION['mensaje'], $_SESSION['tipoMensaje']);
}

// 2. Mensajes desde parámetros GET
if (empty($mensaje) && isset($_GET['status'])) {
    if ($_GET['status'] === 'success') {
        $mensaje = "✓ ¡Sesión iniciada correctamente!";
        $tipoMensaje = "alert-success";
    } elseif ($_GET['status'] === 'registered') {
        $mensaje = "✓ ¡Cuenta creada con éxito! Por favor inicia sesión.";
        $tipoMensaje = "alert-success";
    }
}
if (empty($mensaje) && isset($_GET['error'])) {
    if ($_GET['error'] === 'invalid_credentials') {
        $mensaje = "✕ Correo o contraseña incorrectos. Inténtalo de nuevo.";
        $tipoMensaje = "alert-error";
    } elseif ($_GET['error'] === 'empty_fields') {
        $mensaje = "✕ Por favor, completa todos los campos requeridos.";
        $tipoMensaje = "alert-error";
    }
}

// ==========================================
// PROCESAR INICIO DE SESIÓN
// ==========================================

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $correo = trim($_POST['correo'] ?? '');
    $password = $_POST['password'] ?? '';

    // Validar campos vacíos
    if (empty($correo) || empty($password)) {
        $mensaje = "✕ Por favor, completa todos los campos requeridos.";
        $tipoMensaje = "alert-error";
    } else {
        // ==========================================
        // BUSCAR USUARIO
        // ==========================================

        $sql = "SELECT * FROM registrar WHERE correo = ? LIMIT 1";

        $stmt = mysqli_prepare($conexion, $sql);

        if (!$stmt) {
            $mensaje = "✕ Error al preparar la consulta: " . mysqli_error($conexion);
            $tipoMensaje = "alert-error";
        } else {
            mysqli_stmt_bind_param($stmt, "s", $correo);
            mysqli_stmt_execute($stmt);
            $resultado = mysqli_stmt_get_result($stmt);

            // ==========================================
            // COMPROBAR USUARIO Y CONTRASEÑA
            // ==========================================

            if (mysqli_num_rows($resultado) === 1) {
                $usuario = mysqli_fetch_assoc($resultado);

                if (password_verify($password, $usuario['password'])) {
                    // Crear sesión
                    $_SESSION['correo'] = $usuario['correo'];

                    if (isset($usuario['id'])) {
                        $_SESSION['id'] = $usuario['id'];
                    }
                    if (isset($usuario['nombre'])) {
                        $_SESSION['nombre'] = $usuario['nombre'];
                    }

                    $nombreUsuario = $usuario['nombre'] ?? '';
                    if (empty($nombreUsuario)) {
                        $partes = explode('@', $correo);
                        $nombreUsuario = ucfirst($partes[0]);
                    }
                    $primerNombre = explode(' ', trim($nombreUsuario))[0];
                    setcookie("focusland_user", $primerNombre, time() + 86400, "/");

                    // Recordar usuario
                    if (isset($_POST['remember'])) {
                        setcookie(
                            "correo",
                            $correo,
                            time() + (86400 * 30),
                            "/"
                        );
                    }

                    $mensaje = "✓ ¡Sesión iniciada correctamente! Redirigiendo...";
                    $tipoMensaje = "alert-success";
                    $redirigir = true;

                } else {
                    $mensaje = "✕ Correo o contraseña incorrectos. Inténtalo de nuevo.";
                    $tipoMensaje = "alert-error";
                }

            } else {
                $mensaje = "✕ Correo o contraseña incorrectos. Inténtalo de nuevo.";
                $tipoMensaje = "alert-error";
            }

            mysqli_stmt_close($stmt);
        }
    }
}

?>

<!DOCTYPE html>

<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Inicio de Sesión - FocusLand</title>

    <?php if ($redirigir): ?>
        <meta http-equiv="refresh" content="2;url=index.html">
    <?php endif; ?>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/menu.css">
    <link rel="stylesheet" href="assets/css/contacto.css">
    <link rel="stylesheet" href="assets/css/login.css">

</head>

<body>

    <?php
    $activePage = 'login';
    if (file_exists("includes/header.php")) {
        include("includes/header.php");
    }
    ?>

    <main class="auth-main">
        <div class="auth-card">
            <div class="auth-header">
                <h1>Bienvenido de nuevo a FocusLand</h1>
                <p>Ingresa tus credenciales para acceder a tu cuenta y gestionar tu proceso</p>
            </div>

            <!-- ==========================================================
                 [BLOQUE: FEEDBACK VISUAL Y NOTIFICACIONES DE ESTADO]
                 Renderizado dinámico de avisos operativos (.alert-success para 
                 inicios de sesión o registros exitosos, .alert-error para 
                 credenciales inválidas o campos incompletos).
                 ========================================================== -->
            <div id="authAlertBox" class="alert-container">
                <?php if (!empty($mensaje)): ?>
                    <div class="alert <?php echo $tipoMensaje; ?> auth-alert <?php echo ($tipoMensaje === 'alert-success' ? 'success' : 'error'); ?>" role="alert">
                        <span class="alert-icon"><?php echo ($tipoMensaje === 'alert-success' ? '✓' : '✕'); ?></span>
                        <span class="alert-text"><?php echo htmlspecialchars($mensaje); ?></span>
                    </div>
                <?php endif; ?>
            </div>

            <!-- ==========================================================
                 [BLOQUE: INPUTS Y CONTROLES DEL FORMULARIO DE ACCESO]
                 Estructura de captura de credenciales (correo sanitizado,
                 campo de contraseña con toggle visual, persistencia de
                 sesión 'Recuérdame' y botón de envío).
                 ========================================================== -->
            <form action="login.php" method="POST" class="auth-form" id="loginForm" novalidate>
                <div class="form-group">
                    <label for="login-email">Correo electrónico</label>
                    <input
                        type="email"
                        id="login-email"
                        name="correo"
                        placeholder="tu@email.com"
                        value="<?php echo htmlspecialchars($_POST['correo'] ?? ($_COOKIE['correo'] ?? '')); ?>"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="login-password">Contraseña</label>
                    <div class="password-toggle">
                        <input
                            type="password"
                            id="login-password"
                            name="password"
                            placeholder="Ingresa tu contraseña"
                            required
                        >
                        <button
                            type="button"
                            class="toggle-pwd-btn"
                            onclick="togglePassword('login-password')"
                            aria-label="Mostrar u ocultar contraseña"
                        >
                            <i class="fa-solid fa-eye" id="eye-login-password"></i>
                        </button>
                    </div>
                </div>

                <div class="auth-options">
                    <div class="checkbox-group">
                        <input
                            type="checkbox"
                            id="remember-me"
                            name="remember"
                        >
                        <label for="remember-me">Recuérdame</label>
                    </div>

                    <a href="recuperar.php" class="forgot-password-link">
                        ¿Olvidaste tu contraseña?
                    </a>
                </div>

                <button type="submit" class="btn-auth-submit">
                    Iniciar Sesión
                </button>

                <div class="auth-footer-text">
                    ¿No tienes una cuenta aún? <a href="registrar.php">Regístrate aquí</a>
                </div>
            </form>
        </div>
    </main>

    <!-- Footer Moderno en 3 Columnas -->
    <footer class="footer-moderno" id="contacto-footer">
        <div class="footer-wrap">
            <div class="footer-grid">
                <!-- Columna 1: Marca y Propósito -->
                <div class="footer-col footer-col-brand">
                    <div class="footer-brand">
                        <img src="assets/imagenes/logo.png" alt="Logo FocusLand" class="footer-logo">
                        <span class="footer-brand-name">FocusLand</span>
                    </div>
                    <p class="footer-bio">
                        Comprometidos con el desarrollo integral, educativo y emocional de niños con necesidades especiales, brindando orientación y herramientas sensibles a cada familia.
                    </p>
                    <div class="footer-social">
                        <a href="https://facebook.com" target="_blank" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                        <a href="https://instagram.com" target="_blank" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                        <a href="https://wa.me" target="_blank" aria-label="WhatsApp"><i class="fab fa-whatsapp"></i></a>
                    </div>
                </div>

                <!-- Columna 2: Navegación Rápida -->
                <div class="footer-col footer-col-nav">
                    <h4 class="footer-title">Navegación</h4>
                    <ul class="footer-links">
                        <li><a href="index.html">Inicio</a></li>
                        <li><a href="quienes-somos.html">Quiénes Somos</a></li>
                        <li><a href="servicios.html">Servicios</a></li>
                        <li><a href="recursos.html">Recursos</a></li>
                        <li><a href="contacto.php">Contacto</a></li>
                    </ul>
                </div>

                <!-- Columna 3: Atención y Contacto -->
                <div class="footer-col footer-col-info">
                    <h4 class="footer-title">Acompañamiento</h4>
                    <div class="footer-contact-list">
                        <div class="footer-contact-item">
                            <i class="fa-solid fa-envelope"></i>
                            <a href="mailto:soporte@focusland.com">soporte@focusland.com</a>
                        </div>
                        <div class="footer-contact-item">
                            <i class="fa-solid fa-clock"></i>
                            <span>Lun - Vie: 8:00 AM - 6:00 PM<br>Sáb: 8:00 AM - 1:00 PM</span>
                        </div>
                        <div class="footer-contact-item">
                            <i class="fa-solid fa-location-dot"></i>
                            <span>Modalidad Virtual & Presencial</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Línea divisoria inferior (Bottom bar) -->
            <div class="footer-bottom">
                <p class="footer-copy">© 2026 FocusLand. Todos los derechos reservados. <a href="admin/login.php" class="admin-discreet-link" title="Acceso Administrativo" aria-label="Acceso Interno">• Acceso Interno</a></p>
                <p class="footer-tagline">Educación Inclusiva y Desarrollo Integral</p>
            </div>
        </div>
    </footer>

    <!-- ==========================================
         JAVASCRIPT
    =========================================== -->
    <script>
        function togglePassword(id) {
            const passwordInput = document.getElementById(id);
            const icon = document.getElementById('eye-' + id);
            if (passwordInput.type === "password") {
                passwordInput.type = "text";
                if (icon) {
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                }
            } else {
                passwordInput.type = "password";
                if (icon) {
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                }
            }
        }
    </script>

    <script src="assets/js/main.js"></script>
    <?php if ($redirigir): ?>
    <script>
        setTimeout(function() {
            window.location.href = 'index.html';
        }, 1800);
    </script>
    <?php endif; ?>
</body>

</html>
