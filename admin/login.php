<?php
/**
 * ============================================================================
 * [BLOQUE: ACCESO ADMINISTRATIVO - CONTROL DE SESIÓN Y REDIRECCIÓN PREVENTIVA]
 * ============================================================================
 * Inicializa la sesión con session_start(). Si el administrador ya posee
 * credenciales activas en $_SESSION['admin_auth'], se le redirige de inmediato
 * al dashboard. Captura mensajes de error de autenticación provenientes de auth.php.
 * ============================================================================
 */
session_start();

// Si el administrador ya cuenta con sesión activa, redirigir directo al dashboard
if (isset($_SESSION['admin_auth']) && $_SESSION['admin_auth'] === true) {
    header("Location: dashboard.php");
    exit();
}

$errorMsg = '';
if (isset($_GET['error'])) {
    $err = $_GET['error'];
    if ($err === 'invalid_credentials') {
        $errorMsg = '✕ Credenciales incorrectas. Verifica tu usuario o contraseña de administrador.';
    } else if ($err === 'access_denied') {
        $errorMsg = '✕ Debes iniciar sesión con permisos de administrador para ingresar al panel.';
    } else if ($err === 'logged_out') {
        $errorMsg = '✓ Has cerrado la sesión administrativa de forma segura.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso Administrativo - FocusLand</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <style>
        .admin-login-body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 30px 20px;
            background: var(--bg-main);
        }

        .admin-login-card {
            background: #ffffff;
            border-radius: 20px;
            border: 1.5px solid var(--border-subtle);
            box-shadow: 0 12px 35px rgba(90, 79, 207, 0.12);
            width: 100%;
            max-width: 440px;
            padding: 42px 38px;
            box-sizing: border-box;
            text-align: center;
        }

        .admin-login-logo {
            height: 48px;
            width: auto;
            margin-bottom: 16px;
        }

        .admin-login-badge {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            background: var(--pink-badge-bg);
            color: var(--primary);
            border: 1px solid var(--pink-badge-border);
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            padding: 5px 16px;
            border-radius: 50px;
            margin-bottom: 14px;
        }

        .admin-login-title {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 8px;
        }

        .admin-login-desc {
            font-size: 0.92rem;
            color: var(--text-muted);
            line-height: 1.55;
            margin-bottom: 24px;
        }

        .admin-login-form {
            display: flex;
            flex-direction: column;
            gap: 18px;
            text-align: left;
        }

        .admin-form-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .admin-form-group label {
            font-size: 0.88rem;
            font-weight: 600;
            color: var(--primary);
        }

        .admin-form-group input {
            width: 100%;
            padding: 12px 16px;
            border: 1.5px solid #CDB4F6;
            border-radius: 12px;
            font-size: 0.95rem;
            background: #ffffff;
            color: var(--text-dark);
            box-sizing: border-box;
            outline: none;
            transition: all 0.25s ease;
        }

        .admin-form-group input:focus {
            border-color: var(--secondary);
            box-shadow: 0 0 0 3px rgba(123, 44, 191, 0.15);
        }

        .btn-admin-submit {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: #ffffff;
            border: none;
            padding: 14px;
            font-size: 1rem;
            font-weight: 700;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.25s ease;
            box-shadow: 0 6px 18px rgba(90, 79, 207, 0.28);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: 6px;
        }

        .btn-admin-submit:hover {
            background: linear-gradient(135deg, #4d42b8, #6923a3);
            transform: translateY(-2px);
            box-shadow: 0 8px 22px rgba(90, 79, 207, 0.38);
        }

        .admin-back-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: var(--text-muted);
            text-decoration: none;
            font-size: 0.88rem;
            font-weight: 500;
            margin-top: 22px;
            transition: color 0.2s ease;
        }

        .admin-back-link:hover {
            color: var(--primary);
        }

        .login-alert {
            padding: 12px 16px;
            border-radius: 12px;
            font-size: 0.88rem;
            font-weight: 600;
            margin-bottom: 18px;
            text-align: center;
            line-height: 1.4;
        }

        .login-alert-error {
            background: #FDEDEC;
            border: 1.5px solid #F5B7B1;
            color: #922B21;
        }

        .login-alert-success {
            background: #E8F8F5;
            border: 1.5px solid #A3E4D7;
            color: #117864;
        }
    </style>
</head>
<body class="admin-login-body">

    <div class="admin-login-card">
        <a href="../index.html">
            <img src="../assets/imagenes/logo.png" alt="Logo FocusLand" class="admin-login-logo">
        </a>

        <div>
            <span class="admin-login-badge">
                <i class="fa-solid fa-lock"></i> Gestión Interna FocusLand
            </span>
        </div>

        <h1 class="admin-login-title">Acceso Administrativo</h1>
        <p class="admin-login-desc">Ingresa tus credenciales autorizadas para gestionar citas y mensajes de familias.</p>

        <?php if (!empty($errorMsg)): ?>
            <div class="login-alert <?php echo (strpos($errorMsg, '✓') !== false ? 'login-alert-success' : 'login-alert-error'); ?>" role="alert">
                <?php echo htmlspecialchars($errorMsg); ?>
            </div>
        <?php endif; ?>

        <!-- ==========================================================
             [BLOQUE: FORMULARIO DE ACCESO Y CAPTURA DE CLAVE MAESTRA]
             Formulario de seguridad restringido para el cuerpo directivo.
             Los datos ingresados son enviados a auth.php para ser cotejados
             contra la contraseña maestra única (FocusLand2026*) o cuentas
             administrativas configuradas en la base de datos.
             ========================================================== -->
        <form action="auth.php" method="POST" class="admin-login-form">
            <div class="admin-form-group">
                <label for="admin_usuario">Usuario o Correo de Administrador</label>
                <input type="text" id="admin_usuario" name="usuario" placeholder="admin@focusland.com" required autofocus>
            </div>

            <div class="admin-form-group">
                <label for="admin_password">Contraseña</label>
                <input type="password" id="admin_password" name="password" placeholder="••••••••" required>
            </div>

            <button type="submit" class="btn-admin-submit">
                <i class="fa-solid fa-right-to-bracket"></i> Entrar al Panel
            </button>
        </form>

        <a href="../index.html" class="admin-back-link">
            <i class="fa-solid fa-arrow-left"></i> Volver al sitio público
        </a>
    </div>

</body>
</html>
