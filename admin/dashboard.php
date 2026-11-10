<?php
/**
 * ============================================================================
 * [BLOQUE: GUARDIÁN / MIDDLEWARE DE SEGURIDAD ADMINISTRATIVA]
 * ============================================================================
 * Protección de ruta perimetral: verifica la existencia y autenticidad del token
 * de sesión $_SESSION['admin_auth']. En caso de acceso no autorizado o sesión
 * expirada, interrumpe el flujo y expulsa al usuario hacia login.php.
 * ============================================================================
 */
session_start();

if (!isset($_SESSION['admin_auth']) || $_SESSION['admin_auth'] !== true) {
    header("Location: login.php?error=access_denied");
    exit();
}

require_once '../database/conexion.php';
$db = $pdo ?? $conexion;

// --- BLOQUE: CÁLCULO DE KPIS Y MÉTRICAS OPERATIVAS ---
$totalCitas = 0;
$citasPendientes = 0;
$totalContactos = 0;

try {
    $totalCitas = (int)$db->query("SELECT COUNT(*) FROM citas")->fetchColumn();
    $citasPendientes = (int)$db->query("SELECT COUNT(*) FROM citas WHERE estado = 'Pendiente'")->fetchColumn();
    $totalContactos = (int)$db->query("SELECT COUNT(*) FROM contacto")->fetchColumn();
} catch (Exception $e) {
    error_log("Error calculando KPIs: " . $e->getMessage());
}

// 2. Control de Pestaña Activa
$activeTab = $_GET['tab'] ?? 'citas';
if (!in_array($activeTab, ['citas', 'contacto'])) {
    $activeTab = 'citas';
}

// 3. Notificaciones de Estado por URL
$mensajeAlerta = '';
$tipoAlerta = '';

if (isset($_GET['msg'])) {
    $msg = $_GET['msg'];
    if ($msg === 'status_updated') {
        $mensajeAlerta = "✓ El estado de la cita fue actualizado correctamente.";
        $tipoAlerta = "admin-alert-success";
    } else if ($msg === 'deleted') {
        $mensajeAlerta = "✓ El registro de la cita se eliminó correctamente del sistema.";
        $tipoAlerta = "admin-alert-success";
    } else if ($msg === 'contact_status_updated') {
        $mensajeAlerta = "✓ El estado del mensaje de contacto fue actualizado.";
        $tipoAlerta = "admin-alert-success";
    } else if ($msg === 'contact_deleted') {
        $mensajeAlerta = "✓ El mensaje de contacto fue eliminado exitosamente.";
        $tipoAlerta = "admin-alert-success";
    }
} else if (isset($_GET['error'])) {
    $mensajeAlerta = "✕ Ocurrió un error al procesar la solicitud. Por favor intenta de nuevo.";
    $tipoAlerta = "admin-alert-error";
}

// 4. Cargar Listado de Citas
$listadoCitas = [];
try {
    $stmtCitas = $db->query("SELECT * FROM citas ORDER BY fecha_creacion DESC, id DESC");
    $listadoCitas = $stmtCitas->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log("Error cargando citas: " . $e->getMessage());
}

// 5. Cargar Listado de Contactos
$listadoContactos = [];
try {
    $stmtContactos = $db->query("SELECT * FROM contacto ORDER BY fecha_registro DESC, id DESC");
    $listadoContactos = $stmtContactos->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log("Error cargando contactos: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Administrativo - FocusLand</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>

    <!-- Barra Superior (Admin Topbar) -->
    <header class="admin-topbar">
        <div class="admin-topbar-container">
            <a href="dashboard.php" class="admin-brand">
                <img src="../assets/imagenes/logo.png" alt="Logo FocusLand">
                <span class="admin-brand-name">FocusLand</span>
                <span class="admin-badge">
                    <i class="fa-solid fa-shield-halved"></i> Panel Administrativo
                </span>
            </a>

            <div class="admin-topbar-actions">
                <a href="../index.html" class="btn-admin-action btn-admin-site" target="_blank">
                    <i class="fa-solid fa-arrow-up-right-from-square"></i> Ver Sitio Web
                </a>
                <a href="logout.php" class="btn-admin-action btn-admin-logout" title="Cerrar sesión de administrador">
                    <i class="fa-solid fa-arrow-right-from-bracket"></i> Salir
                </a>
            </div>
        </div>
    </header>

    <main class="admin-main">
        <!-- Encabezado del Dashboard -->
        <div class="admin-header">
            <h1>Gestión Operativa de Familias & Citas</h1>
            <p>Monitoreo en tiempo real de solicitudes de apoyo, estado de agendamientos y mensajes recibidos.</p>
        </div>

        <!-- Alertas de Confirmación / Error -->
        <?php if (!empty($mensajeAlerta)): ?>
            <div class="alert-container">
                <div class="admin-alert <?php echo $tipoAlerta; ?>" role="alert">
                    <span><?php echo htmlspecialchars($mensajeAlerta); ?></span>
                    <button type="button" onclick="this.parentElement.remove()" style="background:none; border:none; color:inherit; cursor:pointer; font-size:1.1rem;">✕</button>
                </div>
            </div>
        <?php endif; ?>

        <!-- ==========================================================
             [BLOQUE: KPIS Y MÉTRICAS SUPERIORES DEL SISTEMA]
             Resumen cuantitativo de alto impacto distribuido en 3 métricas:
             - Total histórico de citas acumuladas.
             - Citas en estado 'Pendiente' que requieren confirmación con familias.
             - Total de solicitudes de contacto e inquietudes recibidas.
             ========================================================== -->
        <section class="admin-kpis">
            <div class="kpi-card">
                <div class="kpi-icon kpi-icon-primary">
                    <i class="fa-solid fa-calendar-days"></i>
                </div>
                <div class="kpi-details">
                    <div class="kpi-number"><?php echo $totalCitas; ?></div>
                    <div class="kpi-title">Total de Citas Registradas</div>
                    <div class="kpi-sub">Historial acumulado en sistema</div>
                </div>
            </div>

            <div class="kpi-card">
                <div class="kpi-icon kpi-icon-warning">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                </div>
                <div class="kpi-details">
                    <div class="kpi-number"><?php echo $citasPendientes; ?></div>
                    <div class="kpi-title">Citas Pendientes</div>
                    <div class="kpi-sub">Requieren confirmación con el acudiente</div>
                </div>
            </div>

            <div class="kpi-card">
                <div class="kpi-icon kpi-icon-success">
                    <i class="fa-solid fa-envelope-open-text"></i>
                </div>
                <div class="kpi-details">
                    <div class="kpi-number"><?php echo $totalContactos; ?></div>
                    <div class="kpi-title">Mensajes de Contacto</div>
                    <div class="kpi-sub">Inquietudes recibidas desde el portal</div>
                </div>
            </div>
        </section>

        <!-- Pestañas de Navegación -->
        <div class="admin-tabs-nav">
            <a href="dashboard.php?tab=citas" class="tab-btn <?php echo ($activeTab === 'citas' ? 'active' : ''); ?>">
                <i class="fa-solid fa-calendar-check"></i> Citas Especializadas
                <span class="tab-count"><?php echo count($listadoCitas); ?></span>
            </a>
            <a href="dashboard.php?tab=contacto" class="tab-btn <?php echo ($activeTab === 'contacto' ? 'active' : ''); ?>">
                <i class="fa-solid fa-inbox"></i> Bandeja de Contacto
                <span class="tab-count"><?php echo count($listadoContactos); ?></span>
            </a>
        </div>

        <?php if ($activeTab === 'citas'): ?>
            <!-- ==========================================================
                 [BLOQUE: LISTADO DINÁMICO Y TABLA CRUD DE CITAS ESPECIALIZADAS]
                 Tabla responsiva con ordenamiento cronológico inverso:
                 - Badges cromáticos de estado (.status-pendiente, .status-confirmada, .status-cancelada).
                 - Enlace directo a WhatsApp para contacto inmediato.
                 - Modales con desglose completo de la cita y notas pedagógicas.
                 - Acciones de cambio de estado en tiempo real y borrado seguro.
                 ========================================================== -->
            <div class="admin-card">
                <div class="card-header-flex">
                    <h2><i class="fa-solid fa-calendar-days" style="color: var(--primary);"></i> Control de Citas Agendadas</h2>
                    <span style="font-size: 0.9rem; color: var(--text-muted);">
                        Mostrando <?php echo count($listadoCitas); ?> registro(s)
                    </span>
                </div>

                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Acudiente</th>
                                <th>Teléfono</th>
                                <th>Menor (Edad)</th>
                                <th>Área / Motivo</th>
                                <th>Atención</th>
                                <th>Horario</th>
                                <th>Estado</th>
                                <th style="text-align: center;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($listadoCitas)): ?>
                                <tr>
                                    <td colspan="9">
                                        <div class="empty-table-state">
                                            <i class="fa-regular fa-calendar-xmark"></i>
                                            <p>No hay citas registradas en este momento.</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($listadoCitas as $cita): ?>
                                    <?php
                                    $estadoClase = 'status-pendiente';
                                    $estadoIcono = 'fa-clock';
                                    if ($cita['estado'] === 'Confirmada') {
                                        $estadoClase = 'status-confirmada';
                                        $estadoIcono = 'fa-check';
                                    } else if ($cita['estado'] === 'Cancelada') {
                                        $estadoClase = 'status-cancelada';
                                        $estadoIcono = 'fa-xmark';
                                    }

                                    $modalidadClase = (stripos($cita['tipo_atencion'], 'virtual') !== false) ? 'virtual' : 'presencial';
                                    $modalidadIcono = ($modalidadClase === 'virtual') ? 'fa-video' : 'fa-building';

                                    // Preparar datos para el modal
                                    $jsonCita = htmlspecialchars(json_encode([
                                        'tipo' => 'cita',
                                        'id' => $cita['id'],
                                        'acudiente' => $cita['nombre_acudiente'],
                                        'correo' => $cita['correo'],
                                        'telefono' => $cita['telefono'],
                                        'menor' => $cita['nombre_nino'],
                                        'edad' => $cita['edad_nino'] . ' años',
                                        'motivo' => $cita['motivo_consulta'],
                                        'modalidad' => $cita['tipo_atencion'],
                                        'horario' => $cita['horario_preferido'],
                                        'estado' => $cita['estado'],
                                        'fecha' => $cita['fecha_creacion'],
                                        'mensaje' => $cita['mensaje'] ?? 'Sin notas o detalles adicionales.'
                                    ]), ENT_QUOTES, 'UTF-8');
                                    ?>
                                    <tr>
                                        <td><strong>#<?php echo $cita['id']; ?></strong></td>
                                        <td class="cell-acudiente">
                                            <strong><?php echo htmlspecialchars($cita['nombre_acudiente']); ?></strong>
                                            <span class="cell-email"><?php echo htmlspecialchars($cita['correo']); ?></span>
                                        </td>
                                        <td>
                                            <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $cita['telefono']); ?>" target="_blank" style="color: var(--primary); text-decoration: none; font-weight: 600;" title="Abrir WhatsApp">
                                                <i class="fa-brands fa-whatsapp" style="color: #25D366;"></i> <?php echo htmlspecialchars($cita['telefono']); ?>
                                            </a>
                                        </td>
                                        <td class="cell-minor">
                                            <?php echo htmlspecialchars($cita['nombre_nino']); ?>
                                            <small><?php echo htmlspecialchars($cita['edad_nino']); ?> años</small>
                                        </td>
                                        <td>
                                            <span style="font-weight: 600; color: var(--text-dark);"><?php echo htmlspecialchars($cita['motivo_consulta']); ?></span>
                                        </td>
                                        <td>
                                            <span class="badge-modalidad <?php echo $modalidadClase; ?>">
                                                <i class="fa-solid <?php echo $modalidadIcono; ?>"></i> <?php echo htmlspecialchars($cita['tipo_atencion']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span style="font-size: 0.88rem; color: #555555;"><?php echo htmlspecialchars($cita['horario_preferido']); ?></span>
                                        </td>
                                        <td>
                                            <!-- Menú Rápido de Cambio de Estado -->
                                            <form action="acciones_cita.php" method="POST" class="select-status-form">
                                                <input type="hidden" name="accion" value="cambiar_estado">
                                                <input type="hidden" name="id" value="<?php echo $cita['id']; ?>">
                                                <select name="nuevo_estado" class="select-status-quick" onchange="this.form.submit()" title="Cambiar estado">
                                                    <option value="Pendiente" <?php echo ($cita['estado'] === 'Pendiente' ? 'selected' : ''); ?>>🟡 Pendiente</option>
                                                    <option value="Confirmada" <?php echo ($cita['estado'] === 'Confirmada' ? 'selected' : ''); ?>>🟢 Confirmada</option>
                                                    <option value="Cancelada" <?php echo ($cita['estado'] === 'Cancelada' ? 'selected' : ''); ?>>🔴 Cancelada</option>
                                                </select>
                                            </form>
                                        </td>
                                        <td>
                                            <div class="table-actions">
                                                <!-- Ver Detalle -->
                                                <button type="button" class="btn-icon-action btn-view-detail" title="Ver información completa" onclick='mostrarDetalle(<?php echo $jsonCita; ?>)'>
                                                    <i class="fa-solid fa-eye"></i>
                                                </button>

                                                <!-- Eliminar Cita -->
                                                <a href="acciones_cita.php?accion=eliminar&id=<?php echo $cita['id']; ?>" class="btn-icon-action btn-delete-row" title="Eliminar registro" onclick="return confirm('¿Deseas eliminar permanentemente esta cita del sistema?');">
                                                    <i class="fa-solid fa-trash-can"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        <?php else: ?>
            <!-- ==========================================================
                 [BLOQUE: BANDEJA DE ENTRADA DE MENSAJES DE CONTACTO]
                 Bandeja de gestión de inquietudes ciudadanas recibidas desde contacto.php:
                 - Visualización de datos de remitente y del menor.
                 - Canales directos de respuesta (mailto y chat directo por WhatsApp).
                 - Badges de estado ('Nuevo' / 'Atendido') y previsualización de texto.
                 - Modales para lectura íntegra del mensaje y opciones de eliminación.
                 ========================================================== -->
            <div class="admin-card">
                <div class="card-header-flex">
                    <h2><i class="fa-solid fa-inbox" style="color: var(--primary);"></i> Bandeja de Solicitudes de Contacto</h2>
                    <span style="font-size: 0.9rem; color: var(--text-muted);">
                        Mostrando <?php echo count($listadoContactos); ?> mensaje(s)
                    </span>
                </div>

                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID / Fecha</th>
                                <th>Remitente</th>
                                <th>Correo</th>
                                <th>Teléfono</th>
                                <th>Asunto / Motivo</th>
                                <th>Mensaje</th>
                                <th>Estado</th>
                                <th style="text-align: center;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($listadoContactos)): ?>
                                <tr>
                                    <td colspan="8">
                                        <div class="empty-table-state">
                                            <i class="fa-regular fa-envelope"></i>
                                            <p>No hay mensajes en la bandeja de contacto.</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($listadoContactos as $contacto): ?>
                                    <?php
                                    $estadoContacto = $contacto['estado'] ?? 'Nuevo';
                                    $contactoClase = ($estadoContacto === 'Atendido') ? 'status-confirmada' : 'status-nuevo';

                                    $jsonContacto = htmlspecialchars(json_encode([
                                        'tipo' => 'contacto',
                                        'id' => $contacto['id'],
                                        'acudiente' => $contacto['nombre_acudiente'],
                                        'correo' => $contacto['correo'],
                                        'telefono' => $contacto['telefono'],
                                        'menor' => $contacto['nombre_nino'],
                                        'edad' => $contacto['edad_nino'] . ' años',
                                        'motivo' => $contacto['motivo_consulta'],
                                        'modalidad' => $contacto['tipo_atencion'] ?? 'No especificada',
                                        'horario' => $contacto['horario_preferido'] ?? 'No especificado',
                                        'estado' => $estadoContacto,
                                        'fecha' => $contacto['fecha_registro'],
                                        'mensaje' => $contacto['mensaje']
                                    ]), ENT_QUOTES, 'UTF-8');
                                    ?>
                                    <tr>
                                        <td>
                                            <strong>#<?php echo $contacto['id']; ?></strong>
                                            <small style="display: block; color: #888888; font-size: 0.78rem;">
                                                <?php echo date('d/m/Y H:i', strtotime($contacto['fecha_registro'])); ?>
                                            </small>
                                        </td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($contacto['nombre_acudiente']); ?></strong>
                                            <?php if (!empty($contacto['nombre_nino'])): ?>
                                                <small style="display: block; color: #666; font-size: 0.8rem;">
                                                    Menor: <?php echo htmlspecialchars($contacto['nombre_nino']); ?> (<?php echo htmlspecialchars($contacto['edad_nino']); ?>a)
                                                </small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="mailto:<?php echo htmlspecialchars($contacto['correo']); ?>" style="color: var(--primary); text-decoration: none;">
                                                <?php echo htmlspecialchars($contacto['correo']); ?>
                                            </a>
                                        </td>
                                        <td>
                                            <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $contacto['telefono']); ?>" target="_blank" style="color: var(--primary); text-decoration: none;" title="Abrir WhatsApp">
                                                <i class="fa-brands fa-whatsapp" style="color: #25D366;"></i> <?php echo htmlspecialchars($contacto['telefono']); ?>
                                            </a>
                                        </td>
                                        <td>
                                            <span style="font-weight: 600; color: var(--text-dark);"><?php echo htmlspecialchars($contacto['motivo_consulta']); ?></span>
                                        </td>
                                        <td style="max-width: 220px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                            <?php echo htmlspecialchars($contacto['mensaje']); ?>
                                        </td>
                                        <td>
                                            <form action="acciones_cita.php" method="POST" class="select-status-form">
                                                <input type="hidden" name="accion" value="cambiar_estado_contacto">
                                                <input type="hidden" name="id" value="<?php echo $contacto['id']; ?>">
                                                <select name="nuevo_estado" class="select-status-quick" onchange="this.form.submit()" title="Cambiar estado del mensaje">
                                                    <option value="Nuevo" <?php echo ($estadoContacto === 'Nuevo' ? 'selected' : ''); ?>>🔵 Nuevo</option>
                                                    <option value="Atendido" <?php echo ($estadoContacto === 'Atendido' ? 'selected' : ''); ?>>🟢 Atendido</option>
                                                </select>
                                            </form>
                                        </td>
                                        <td>
                                            <div class="table-actions">
                                                <button type="button" class="btn-icon-action btn-view-detail" title="Leer mensaje completo" onclick='mostrarDetalle(<?php echo $jsonContacto; ?>)'>
                                                    <i class="fa-solid fa-eye"></i>
                                                </button>

                                                <a href="acciones_cita.php?accion=eliminar_contacto&id=<?php echo $contacto['id']; ?>" class="btn-icon-action btn-delete-row" title="Eliminar mensaje" onclick="return confirm('¿Deseas eliminar este mensaje de contacto?');">
                                                    <i class="fa-solid fa-trash-can"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </main>

    <!-- Modal de Detalles del Registro -->
    <div id="adminModal" class="admin-modal-backdrop" onclick="cerrarModalExterno(event)">
        <div class="admin-modal-card">
            <button type="button" class="admin-modal-close" onclick="cerrarModal()" aria-label="Cerrar ventana">
                <i class="fa-solid fa-xmark"></i>
            </button>
            <h3 class="admin-modal-title" id="modalTitulo">
                <i class="fa-solid fa-circle-info" style="color: var(--primary);"></i> Detalle del Registro
            </h3>

            <div class="admin-modal-grid">
                <div class="admin-detail-field">
                    <span class="admin-detail-label">Nombre del Acudiente</span>
                    <div class="admin-detail-val" id="modalAcudiente">-</div>
                </div>

                <div class="admin-detail-field">
                    <span class="admin-detail-label">Teléfono / WhatsApp</span>
                    <div class="admin-detail-val" id="modalTelefono">-</div>
                </div>

                <div class="admin-detail-field">
                    <span class="admin-detail-label">Correo Electrónico</span>
                    <div class="admin-detail-val" id="modalCorreo">-</div>
                </div>

                <div class="admin-detail-field">
                    <span class="admin-detail-label">Menor y Edad</span>
                    <div class="admin-detail-val" id="modalMenor">-</div>
                </div>

                <div class="admin-detail-field">
                    <span class="admin-detail-label">Área / Motivo</span>
                    <div class="admin-detail-val" id="modalMotivo">-</div>
                </div>

                <div class="admin-detail-field">
                    <span class="admin-detail-label">Modalidad & Horario</span>
                    <div class="admin-detail-val" id="modalModalidad">-</div>
                </div>

                <div class="admin-detail-field">
                    <span class="admin-detail-label">Estado Actual</span>
                    <div class="admin-detail-val" id="modalEstado">-</div>
                </div>

                <div class="admin-detail-field">
                    <span class="admin-detail-label">Fecha de Solicitud</span>
                    <div class="admin-detail-val" id="modalFecha">-</div>
                </div>

                <div class="admin-detail-field full-width">
                    <span class="admin-detail-label">Mensaje o Notas del Acudiente</span>
                    <div class="admin-detail-val">
                        <p id="modalMensaje">-</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pie de Página del Panel -->
    <footer class="admin-footer">
        <p>© 2026 FocusLand — Panel de Gestión y Control Administrativo.</p>
    </footer>

    <script>
        function mostrarDetalle(data) {
            document.getElementById('modalTitulo').innerHTML = (data.tipo === 'cita')
                ? `<i class="fa-solid fa-calendar-check" style="color: var(--primary);"></i> Detalle de Cita #${data.id}`
                : `<i class="fa-solid fa-envelope-open-text" style="color: var(--primary);"></i> Mensaje de Contacto #${data.id}`;

            document.getElementById('modalAcudiente').textContent = data.acudiente || 'No especificado';
            document.getElementById('modalTelefono').textContent = data.telefono || 'No especificado';
            document.getElementById('modalCorreo').textContent = data.correo || 'No especificado';
            document.getElementById('modalMenor').textContent = `${data.menor || 'No especificado'} (${data.edad || ''})`;
            document.getElementById('modalMotivo').textContent = data.motivo || 'No especificado';
            document.getElementById('modalModalidad').textContent = `${data.modalidad || ''} - ${data.horario || ''}`;
            document.getElementById('modalEstado').textContent = data.estado || 'Pendiente';
            document.getElementById('modalFecha').textContent = data.fecha || 'No registrada';
            document.getElementById('modalMensaje').textContent = data.mensaje || 'Sin detalles adicionales.';

            document.getElementById('adminModal').classList.add('show');
            document.body.style.overflow = 'hidden';
        }

        function cerrarModal() {
            document.getElementById('adminModal').classList.remove('show');
            document.body.style.overflow = 'auto';
        }

        function cerrarModalExterno(e) {
            if (e.target.id === 'adminModal') {
                cerrarModal();
            }
        }

        // Cerrar con Escape
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                cerrarModal();
            }
        });
    </script>
</body>
</html>
