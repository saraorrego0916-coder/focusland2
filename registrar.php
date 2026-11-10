<?php
/**
 * ============================================================================
 * [BLOQUE: REGISTRO DE USUARIOS - CONTROL DE SESIÓN Y FEEDBACK]
 * ============================================================================
 * Inicialización del contexto de sesión PHP con session_start(), carga del
 * módulo de base de datos e inicialización de estados para mensajes de
 * confirmación o alerta hacia el usuario (éxito, campos vacíos o duplicidad).
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
        $mensaje = "✓ ¡Registro completado con éxito! Redirigiendo...";
        $tipoMensaje = "alert-success";
    }
}
if (empty($mensaje) && isset($_GET['error'])) {
    if ($_GET['error'] === 'empty_fields') {
        $mensaje = "✕ Por favor, completa todos los campos requeridos.";
        $tipoMensaje = "alert-error";
    } elseif ($_GET['error'] === 'email_exists') {
        $mensaje = "✕ Este correo electrónico ya está registrado. Intenta iniciar sesión.";
        $tipoMensaje = "alert-error";
    }
}

// ==========================================
// PROCESAR REGISTRO
// ==========================================

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Obtener datos del formulario
    $nombre = trim($_POST['nombre'] ?? '');
    $correo = trim($_POST['correo'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmpassword = $_POST['confirmpassword'] ?? $password;

    // ==========================================
    // VALIDAR CAMPOS VACÍOS
    // ==========================================

    if (
        empty($nombre) ||
        empty($correo) ||
        empty($password)
    ) {

        $mensaje = "✕ Por favor, completa todos los campos requeridos.";
        $tipoMensaje = "alert-error";

    }

    // ==========================================
    // VALIDAR CORREO
    // ==========================================

    elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {

        $mensaje = "✕ El correo electrónico no tiene un formato válido.";
        $tipoMensaje = "alert-error";

    }

    // ==========================================
    // VALIDAR CONTRASEÑAS
    // ==========================================

    elseif ($password !== $confirmpassword) {

        $mensaje = "✕ Las contraseñas no coinciden. Verifícalas e inténtalo de nuevo.";
        $tipoMensaje = "alert-error";

    }

    // ==========================================
    // VALIDAR LONGITUD DE CONTRASEÑA
    // ==========================================

    elseif (strlen($password) < 6) {

        $mensaje = "✕ La contraseña debe tener al menos 6 caracteres.";
        $tipoMensaje = "alert-error";

    }

    // ==========================================
    // CONTINUAR CON EL REGISTRO
    // ==========================================

    else {

        // ==========================================
        // COMPROBAR SI EL CORREO YA EXISTE
        // ==========================================

        $sql = "SELECT correo FROM registrar WHERE correo = ? LIMIT 1";

        $stmt = mysqli_prepare($conexion, $sql);

        if (!$stmt) {

            $mensaje = "✕ Error al preparar la consulta: " . mysqli_error($conexion);
            $tipoMensaje = "alert-error";

        } else {

            mysqli_stmt_bind_param($stmt, "s", $correo);
            mysqli_stmt_execute($stmt);

            $resultado = mysqli_stmt_get_result($stmt);

            // ==========================================
            // CORREO YA REGISTRADO
            // ==========================================

            if (mysqli_num_rows($resultado) > 0) {

                $mensaje = "✕ Este correo electrónico ya está registrado. Intenta iniciar sesión.";
                $tipoMensaje = "alert-error";

                mysqli_stmt_close($stmt);

            } else {

                mysqli_stmt_close($stmt);

                // ==========================================
                // ENCRIPTAR CONTRASEÑA
                // ==========================================

                $passwordEncriptada = password_hash(
                    $password,
                    PASSWORD_DEFAULT
                );

                // ==========================================
                // INSERTAR USUARIO
                // ==========================================

                $sql = "INSERT INTO registrar (nombre, correo, password)
                        VALUES (?, ?, ?)";

                $stmt = mysqli_prepare($conexion, $sql);

                if (!$stmt) {

                    $mensaje = "✕ Error al preparar el registro: " . mysqli_error($conexion);
                    $tipoMensaje = "alert-error";

                } else {

                    mysqli_stmt_bind_param(
                        $stmt,
                        "sss",
                        $nombre,
                        $correo,
                        $passwordEncriptada
                    );

                    if (mysqli_stmt_execute($stmt)) {

                        // ==========================================
                        // REGISTRO CORRECTO
                        // ==========================================

                        $_SESSION['correo'] = $correo;
                        $_SESSION['nombre'] = $nombre;

                        $primerNombre = explode(' ', trim($nombre))[0];
                        setcookie("focusland_user", $primerNombre, time() + 86400, "/");

                        if (isset($stmt)) {
                            mysqli_stmt_close($stmt);
                        }

                        $mensaje = "✓ ¡Registro completado con éxito! Redirigiendo...";
                        $tipoMensaje = "alert-success";
                        $redirigir = true;

                    } else {

                        $mensaje = "✕ Error al registrar la cuenta: " . mysqli_error($conexion);
                        $tipoMensaje = "alert-error";

                        mysqli_stmt_close($stmt);
                    }
                }
            }
        }
    }
}

?>

<!DOCTYPE html>

<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Crear Cuenta - FocusLand</title>

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
                <h1>Únete a la Comunidad FocusLand</h1>
                <p>Crea una cuenta para acceder al acompañamiento y recursos de tu hijo</p>
            </div>

            <!-- ==========================================================
                 [BLOQUE: FEEDBACK VISUAL Y NOTIFICACIONES DE REGISTRO]
                 Contenedor interactivo para mostrar retroalimentación al usuario:
                 .alert-success para altas confirmadas y redirección;
                 .alert-error para advertencias de validación de formato,
                 coincidencia de clave o existencia previa del correo.
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
                 [BLOQUE: INPUTS DEL FORMULARIO DE REGISTRO DE USUARIOS]
                 Campos requeridos para la creación de cuenta en FocusLand:
                 - Nombre completo del tutor / acudiente.
                 - Correo electrónico válido (identificador de acceso).
                 - Contraseña segura (mínimo 6 caracteres) con botón para visualizar.
                 - Botón de envío que activa validaciones frontend y backend.
                 ========================================================== -->
            <form action="registrar.php" method="POST" class="auth-form" id="registerForm" novalidate>
                <div class="form-group">
                    <label for="signup-name">Nombre completo</label>
                    <input
                        type="text"
                        id="signup-name"
                        name="nombre"
                        placeholder="Escribe tu nombre completo"
                        value="<?php echo htmlspecialchars($_POST['nombre'] ?? ''); ?>"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="signup-email">Correo electrónico</label>
                    <input
                        type="email"
                        id="signup-email"
                        name="correo"
                        placeholder="correo@ejemplo.com"
                        value="<?php echo htmlspecialchars($_POST['correo'] ?? ''); ?>"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="signup-password">Contraseña</label>
                    <div class="password-toggle">
                        <input
                            type="password"
                            id="signup-password"
                            name="password"
                            placeholder="Crea una contraseña (mínimo 6 caracteres)"
                            required
                        >
                        <button
                            type="button"
                            class="toggle-pwd-btn"
                            onclick="togglePassword('signup-password')"
                            aria-label="Mostrar u ocultar contraseña"
                        >
                            <i class="fa-solid fa-eye" id="eye-signup-password"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn-auth-submit">
                    Crear Cuenta
                </button>

                <div class="auth-footer-text">
                    ¿Ya tienes cuenta? <a href="login.php">Inicia sesión aquí</a>
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
