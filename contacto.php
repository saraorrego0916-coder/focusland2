<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'database/conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $nombre = $_POST['nombre_acudiente'];
    $correo = $_POST['correo'];
    $telefono = $_POST['telefono'];
    $nombre_nino = $_POST['nombre_nino'];
    $edad = $_POST['edad_nino'];
    $motivo = $_POST['motivo_consulta'];
    $mensaje = $_POST['mensaje'];
    $tipo_atencion = $_POST['tipo_atencion'];
    $horario = $_POST['horario_preferido'];

    $sql = "INSERT INTO contacto
    (nombre_acudiente, correo, telefono, nombre_nino, edad_nino, motivo_consulta, mensaje, tipo_atencion, horario_preferido)
    VALUES
    ('$nombre', '$correo', '$telefono', '$nombre_nino', '$edad', '$motivo', '$mensaje', '$tipo_atencion', '$horario')";

    if(mysqli_query($conexion, $sql)){
        echo "<script>
                alert('Solicitud enviada correctamente');
                window.location='contacto.php';
              </script>";
    }else{
        echo "Error: " . mysqli_error($conexion);
    }

}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contáctanos | FocusLand</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/menu.css">
    <link rel="stylesheet" href="assets/css/contacto.css">
</head>
<body>

<?php
$activePage = 'contacto';
if (file_exists("includes/header.php")) {
    include("includes/header.php");
}
?>

<main>
    <!-- Nueva Sección Introductoria de Contacto -->
    <section class="contact-intro">
        <div class="contact-intro-container">
            <span class="contact-badge">
                <i class="fa-solid fa-hand-holding-heart"></i> Canal de Atención & Orientación
            </span>
            <h1 class="contact-intro-title">Estamos Aquí para Acompañarte a Ti y a tu Familia</h1>
            <p class="contact-intro-desc">
                En FocusLand cada mensaje es atendido con total confidencialidad, sensibilidad y respeto por profesionales en pedagogía y psicología infantil. Nuestro compromiso es brindarte un espacio de escucha activa, orientar tus inquietudes sobre el desarrollo del menor y coordinar las herramientas o sesiones de apoyo más adecuadas.
            </p>

            <!-- Tarjetas Rápidas de Información de Contacto -->
            <div class="contact-cards-grid">
                <!-- Tarjeta 1: Canal de Correo -->
                <div class="contact-info-card">
                    <div class="contact-card-icon">
                        <i class="fa-solid fa-envelope-open-text"></i>
                    </div>
                    <div class="contact-card-content">
                        <h3>Canal de Correo</h3>
                        <p>Soporte e inquietudes generales con respuesta oportuna por parte de nuestro equipo especializado.</p>
                        <a href="mailto:soporte@focusland.com" class="contact-card-link">
                            <i class="fa-regular fa-envelope"></i> soporte@focusland.com
                        </a>
                    </div>
                </div>

                <!-- Tarjeta 2: Horarios de Atención -->
                <div class="contact-info-card">
                    <div class="contact-card-icon">
                        <i class="fa-solid fa-clock"></i>
                    </div>
                    <div class="contact-card-content">
                        <h3>Horarios de Atención</h3>
                        <p>Disponibilidad para orientación virtual y presencial adaptada al ritmo y rutina de tu familia.</p>
                        <span class="contact-card-highlight">
                            <i class="fa-regular fa-calendar-check"></i> Lun - Vie: 8:00 AM - 6:00 PM
                        </span>
                    </div>
                </div>

                <!-- Tarjeta 3: Atención Personalizada -->
                <div class="contact-info-card">
                    <div class="contact-card-icon">
                        <i class="fa-solid fa-user-shield"></i>
                    </div>
                    <div class="contact-card-content">
                        <h3>Atención Personalizada</h3>
                        <p>Espacio seguro y libre de juicios para exponer cualquier duda sobre el proceso de aprendizaje del menor.</p>
                        <span class="contact-card-badge">
                            <i class="fa-solid fa-shield-halved"></i> 100% Confidencial
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contenedor / Tarjeta del Formulario de Contacto -->
    <div class="contenedor contact-form-card">
        <div class="contact-form-header">
            <h2>Envíanos un Mensaje de Contacto</h2>
            <p>
                Diligencia la siguiente información y nuestro equipo responderá a tu solicitud con la mayor prontitud.
            </p>
        </div>

        <form id="formulario" method="POST" action="contacto.php">
            <div class="fila">
                <div>
                    <label>Nombre del acudiente</label>
                    <input type="text" name="nombre_acudiente" placeholder="Escribe tu nombre completo" required>
                </div>

                <div>
                    <label>Correo electrónico</label>
                    <input type="email" name="correo" placeholder="correo@ejemplo.com" required>
                </div>
            </div>

            <div class="fila">
                <div>
                    <label>Teléfono / WhatsApp</label>
                    <input type="tel" name="telefono" placeholder="Número de teléfono o WhatsApp" required>
                </div>

                <div>
                    <label>Nombre del niño/niña</label>
                    <input type="text" name="nombre_nino" placeholder="Nombre completo del menor">
                </div>
            </div>

            <div class="fila">
                <div>
                    <label>Edad del menor</label>
                    <input type="number" name="edad_nino" min="1" max="18" placeholder="Edad (años)">
                </div>

                <div>
                    <label>Motivo de la consulta</label>
                    <select name="motivo_consulta" required>
                        <option value="">Seleccione una opción</option>
                        <option value="Dificultades de aprendizaje">Dificultades de aprendizaje</option>
                        <option value="Déficit de atención (TDAH)">Déficit de atención (TDAH)</option>
                        <option value="Apoyo psicológico">Apoyo psicológico</option>
                        <option value="Orientación para padres">Orientación para padres</option>
                        <option value="Terapia de lenguaje">Terapia de lenguaje</option>
                        <option value="Evaluación inicial">Evaluación inicial</option>
                        <option value="Otro">Otro</option>
                    </select>
                </div>
            </div>

            <div class="fila">
                <div>
                    <label>Tipo de atención</label>
                    <select name="tipo_atencion" required>
                        <option value="Virtual">Virtual (100% Online)</option>
                        <option value="Presencial">Presencial</option>
                        <option value="Cualquiera">Cualquiera / Por definir</option>
                    </select>
                </div>

                <div>
                    <label>Horario preferido</label>
                    <select name="horario_preferido" required>
                        <option value="Mañana">Mañana (8:00 AM - 12:00 PM)</option>
                        <option value="Tarde">Tarde (2:00 PM - 6:00 PM)</option>
                        <option value="Noche">Noche / Fin de tarde</option>
                    </select>
                </div>
            </div>

            <div>
                <label>Mensaje / ¿Cómo podemos ayudarte?</label>
                <textarea placeholder="Cuéntanos brevemente sobre las necesidades de tu hijo o el motivo de tu consulta..." name="mensaje" required></textarea>
            </div>

            <div class="checkbox">
                <input type="checkbox" id="tratamiento_datos" required>
                <label for="tratamiento_datos">Acepto el tratamiento de mis datos personales de acuerdo con la política de privacidad y confidencialidad de FocusLand.</label>
            </div>

            <button type="submit" class="btn-submit">Enviar Mensaje de Contacto</button>
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
                    <li><a href="contacto.php" class="active">Contacto</a></li>
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

<script src="assets/js/main.js"></script>
</body>
</html>