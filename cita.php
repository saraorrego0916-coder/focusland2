<?php
/**
 * ============================================================================
 * [BLOQUE: AGENDAMIENTO DE CITAS - CONTROL DE SESIÓN Y FEEDBACK URL]
 * ============================================================================
 * Gestión de la sesión de usuario para precargar datos de acudiente autenticado
 * y recepción de parámetros GET (?status=success o ?error=...) provenientes
 * del controlador procesar_cita.php para emitir notificaciones visuales.
 * ============================================================================
 */
session_start();

$activePage = 'cita';
$mensaje = '';
$tipoMensaje = '';

// Capturar parámetros por URL desde procesar_cita.php
if (isset($_GET['status']) && $_GET['status'] === 'success') {
    $mensaje = "✓ ¡Cita agendada con éxito! La solicitud ha sido registrada correctamente en el sistema y nuestro equipo se comunicará pronto contigo.";
    $tipoMensaje = "alert-success";
} else if (isset($_GET['error'])) {
    $error = $_GET['error'];
    if ($error === 'missing_fields') {
        $mensaje = "✕ Por favor, completa todos los campos requeridos para agendar la cita.";
    } else if ($error === 'db_error') {
        $mensaje = "✕ Ocurrió un error al guardar la cita en la base de datos. Por favor, intenta nuevamente.";
    } else {
        $mensaje = "✕ Ocurrió un inconveniente al procesar la cita. Por favor, intenta nuevamente.";
    }
    $tipoMensaje = "alert-error";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agendar Cita Especializada - FocusLand</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/menu.css">
    <link rel="stylesheet" href="assets/css/contacto.css">
</head>
<body>

<?php
if (file_exists("includes/header.php")) {
    include("includes/header.php");
}
?>

<main>
    <section class="cita-section">
        <div class="cita-grid-2">
            <!-- ==========================================================
                 [BLOQUE: PANEL INFORMATIVO Y PAUTAS DE ATENCIÓN]
                 Sección pedagógica previa a la reserva:
                 - Horarios de atención y disponibilidad semanal.
                 - Modalidades disponibles (Virtual interactiva / Presencial con salas adaptadas).
                 - Duración promedio de la sesión (45 a 60 minutos).
                 - Recomendaciones previas para los padres y el menor.
                 ========================================================== -->
            <div class="cita-info-card">
                <span class="cita-badge">
                    <i class="fa-solid fa-clipboard-list"></i> Información de Atención & Pautas
                </span>
                <h2 class="cita-panel-title">Todo lo que Debes Saber Antes de Agendar</h2>

                <div class="cita-info-blocks">
                    <!-- Bloque 1: Horarios de Atención -->
                    <div class="cita-info-block">
                        <div class="cita-block-icon">
                            <i class="fa-solid fa-clock"></i>
                        </div>
                        <div class="cita-block-content">
                            <h3>Horarios de Atención</h3>
                            <ul class="cita-schedule-list">
                                <li><i class="fa-solid fa-check"></i> <strong>Lunes a Viernes:</strong> 8:00 AM – 12:00 PM | 2:00 PM – 6:00 PM</li>
                                <li><i class="fa-solid fa-check"></i> <strong>Sábados:</strong> 8:30 AM – 1:00 PM</li>
                                <li><i class="fa-solid fa-circle-xmark" style="color: #e74c3c;"></i> <strong>Domingos y Festivos:</strong> Cerrado</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Bloque 2: Modalidades Disponibles -->
                    <div class="cita-info-block">
                        <div class="cita-block-icon">
                            <i class="fa-solid fa-location-dot"></i>
                        </div>
                        <div class="cita-block-content">
                            <h3>Modalidades Disponibles</h3>
                            <p class="cita-modality-item">
                                <strong>Sesión Virtual:</strong> Acompañamiento a distancia mediante videollamada interactiva y segura, ideal para seguimiento en el hogar y orientación familiar.
                            </p>
                            <p class="cita-modality-item">
                                <strong>Sesión Presencial:</strong> Evaluación e intervención directa en nuestras instalaciones con salas adaptadas de estimulación sensorial, psicomotriz y juego terapéutico.
                            </p>
                        </div>
                    </div>

                    <!-- Bloque 3: Duración de la Sesión -->
                    <div class="cita-info-block">
                        <div class="cita-block-icon">
                            <i class="fa-solid fa-stopwatch"></i>
                        </div>
                        <div class="cita-block-content">
                            <h3>Duración de la Sesión</h3>
                            <p>
                                Cada cita tiene una duración aproximada de <strong>45 a 60 minutos</strong> según el área de intervención requerida, asegurando una atención individualizada y enfocada.
                            </p>
                        </div>
                    </div>

                    <!-- Bloque 4: Recomendaciones Previas -->
                    <div class="cita-info-block">
                        <div class="cita-block-icon">
                            <i class="fa-solid fa-lightbulb"></i>
                        </div>
                        <div class="cita-block-content">
                            <h3>Recomendaciones Previas</h3>
                            <p>
                                • Disponer de informes escolares, valoraciones pedagógicas o médicas previas (si las hay).<br>
                                • Procurar que el niño/a haya descansado adecuadamente y se encuentre en un entorno tranquilo y libre de distracciones.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ==========================================================
                 [BLOQUE: FORMULARIO DE RESERVA ESTRUCTURADO (CAMPOS DB)]
                 Captura datos sincronizados con la tabla `citas`:
                 - Acudiente: nombre_acudiente, correo, telefono.
                 - Menor: nombre_nino, edad_nino (validada).
                 - Servicio: motivo_consulta, tipo_atencion, horario_preferido.
                 - Complementarios: fecha_cita, mensaje / observaciones.
                 ========================================================== -->
            <div class="cita-form-card">
                <div class="cita-form-header">
                    <h2>Agendar Cita Especializada</h2>
                    <p>Completa el formulario para reservar la sesión personalizada para tu hijo/a.</p>
                </div>

                <!-- Alertas de Estado -->
                <div id="citaAlertBox" class="alert-container">
                    <?php if (!empty($mensaje)): ?>
                        <div class="alert <?php echo $tipoMensaje; ?> auth-alert <?php echo ($tipoMensaje === 'alert-success' ? 'success' : 'error'); ?>" role="alert">
                            <span class="alert-icon"><?php echo ($tipoMensaje === 'alert-success' ? '✓' : '✕'); ?></span>
                            <span class="alert-text"><?php echo htmlspecialchars($mensaje); ?></span>
                        </div>
                    <?php endif; ?>
                </div>

                <form id="formularioCita" method="POST" action="procesar_cita.php">
                    <div class="fila">
                        <div>
                            <label for="nombre_acudiente">Nombre del acudiente *</label>
                            <input type="text" id="nombre_acudiente" name="nombre_acudiente" placeholder="Escribe tu nombre completo" value="<?php echo htmlspecialchars($_SESSION['nombre'] ?? ($_POST['nombre_acudiente'] ?? '')); ?>" required>
                        </div>

                        <div>
                            <label for="correo">Correo electrónico *</label>
                            <input type="email" id="correo" name="correo" placeholder="ejemplo@correo.com" value="<?php echo htmlspecialchars($_SESSION['correo'] ?? ($_POST['correo'] ?? '')); ?>" required>
                        </div>
                    </div>

                    <div class="fila">
                        <div>
                            <label for="telefono">Teléfono / WhatsApp *</label>
                            <input type="tel" id="telefono" name="telefono" placeholder="Número celular de contacto" value="<?php echo htmlspecialchars($_POST['telefono'] ?? ''); ?>" required>
                        </div>

                        <div>
                            <label for="nombre_nino">Nombre del menor *</label>
                            <input type="text" id="nombre_nino" name="nombre_nino" placeholder="Nombre completo del niño/a" value="<?php echo htmlspecialchars($_POST['nombre_nino'] ?? ''); ?>" required>
                        </div>
                    </div>

                    <div class="fila">
                        <div>
                            <label for="edad_nino">Edad del menor (años) *</label>
                            <input type="number" id="edad_nino" name="edad_nino" min="1" max="18" placeholder="Ej: 7" value="<?php echo htmlspecialchars($_POST['edad_nino'] ?? ''); ?>" required>
                        </div>

                        <div>
                            <label for="motivo_consulta">Área o Servicio de Interés *</label>
                            <select id="motivo_consulta" name="motivo_consulta" required>
                                <option value="">Selecciona un área</option>
                                <option value="Psicología Infantil">Psicología Infantil & Emocional</option>
                                <option value="TDAH / Concentración">TDAH & Concentración</option>
                                <option value="Desarrollo Motriz">Desarrollo Motriz & Estimulación</option>
                                <option value="Lenguaje y Habla">Lenguaje & Comunicación</option>
                                <option value="Escuela para Padres">Escuela para Padres & Crianza Positiva</option>
                                <option value="Evaluación Integral">Evaluación Integral Multidisciplinar</option>
                            </select>
                        </div>
                    </div>

                    <div class="fila">
                        <div>
                            <label for="tipo_atencion">Modalidad de Atención *</label>
                            <select id="tipo_atencion" name="tipo_atencion" required>
                                <option value="">Selecciona modalidad</option>
                                <option value="Virtual">Virtual (Videollamada segura)</option>
                                <option value="Presencial">Presencial (Sede FocusLand)</option>
                            </select>
                        </div>

                        <div>
                            <label for="horario_preferido">Franja Horaria *</label>
                            <select id="horario_preferido" name="horario_preferido" required>
                                <option value="">Selecciona franja</option>
                                <option value="Mañana">Mañana (8:00 AM – 12:00 PM)</option>
                                <option value="Tarde">Tarde (2:00 PM – 6:00 PM)</option>
                            </select>
                        </div>
                    </div>

                    <div class="fila">
                        <div>
                            <label for="fecha_cita">Fecha sugerida para la cita</label>
                            <input type="date" id="fecha_cita" name="fecha_cita" min="<?php echo date('Y-m-d'); ?>" value="<?php echo htmlspecialchars($_POST['fecha_cita'] ?? ''); ?>">
                        </div>

                        <div style="display: flex; flex-direction: column; justify-content: flex-end;">
                            <p style="font-size: 0.83rem; color: #777; margin-bottom: 8px; line-height: 1.4;">
                                <i class="fa-solid fa-circle-info" style="color: #5A4FCF;"></i> La fecha solicitada queda sujeta a confirmación por el equipo pedagógico.
                            </p>
                        </div>
                    </div>

                    <div>
                        <label for="mensaje">Notas o comentarios adicionales (opcional)</label>
                        <textarea id="mensaje" name="mensaje" placeholder="Cuéntanos brevemente sobre las necesidades específicas, avances, dudas o inquietudes sobre el menor..."><?php echo htmlspecialchars($_POST['mensaje'] ?? ''); ?></textarea>
                    </div>

                    <div class="checkbox">
                        <input type="checkbox" id="acepto_datos" required>
                        <label for="acepto_datos" style="font-weight: normal; color: #555555; margin-bottom: 0; cursor: pointer; font-size: 0.9rem;">
                            Acepto el tratamiento de mis datos personales según la política de privacidad de FocusLand.
                        </label>
                    </div>

                    <button type="submit" class="btn-cita-submit">
                        <i class="fa-solid fa-calendar-check"></i> Confirmar Agendamiento
                    </button>
                </form>
            </div>
        </div>
    </section>
</main>

<footer class="footer-moderno">
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

<script src="assets/js/main.js"></script>
</body>
</html>
