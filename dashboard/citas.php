<?php
session_start();
require_once '../php/config.php';

// Verificar que el usuario esté logueado y sea ADMIN o VENDEDOR
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] !== 'ADMIN' && $_SESSION['user_role'] !== 'VENDEDOR')) {
    header("Location: index.php?error=access_denied");
    exit();
}

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Procesar acciones
$action = $_GET['action'] ?? '';
$cita_id = $_GET['id'] ?? 0;

// Cambiar estado de cita
if ($action == 'change_status' && $cita_id > 0 && isset($_GET['status'])) {
    $status = $_GET['status'];
    $sql = "UPDATE citas SET estado = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $status, $cita_id);
    $stmt->execute();
    header("Location: citas.php");
    exit();
}

// Cancelar cita
if ($action == 'cancel' && $cita_id > 0) {
    $sql = "UPDATE citas SET estado = 'cancelada' WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $cita_id);
    $stmt->execute();
    header("Location: citas.php");
    exit();
}

// Filtrar por fecha y estado
$fecha_filtro = $_GET['fecha'] ?? '';
$estado_filtro = $_GET['estado'] ?? '';

// Construir consulta con filtros
$where_conditions = [];
$params = [];
$types = '';

if ($fecha_filtro) {
    $where_conditions[] = "DATE(c.fecha) = ?";
    $params[] = $fecha_filtro;
    $types .= 's';
}

if ($estado_filtro && $estado_filtro !== 'todos') {
    $where_conditions[] = "c.estado = ?";
    $params[] = $estado_filtro;
    $types .= 's';
}

$where_sql = '';
if (!empty($where_conditions)) {
    $where_sql = 'WHERE ' . implode(' AND ', $where_conditions);
}

// Obtener estadísticas
$estadisticas = [];

// Total citas
$sql_total = "SELECT COUNT(*) as total FROM citas";
$result_total = $conn->query($sql_total);
$estadisticas['total_citas'] = $result_total->fetch_assoc()['total'];

// Citas hoy
$sql_hoy = "SELECT COUNT(*) as total FROM citas WHERE DATE(fecha) = CURDATE()";
$result_hoy = $conn->query($sql_hoy);
$estadisticas['citas_hoy'] = $result_hoy->fetch_assoc()['total'];

// Citas pendientes
$sql_pendientes = "SELECT COUNT(*) as total FROM citas WHERE estado = 'pendiente'";
$result_pendientes = $conn->query($sql_pendientes);
$estadisticas['citas_pendientes'] = $result_pendientes->fetch_assoc()['total'];

// Obtener citas con información de usuario
$sql = "SELECT c.*, u.nombre_completo, u.email, u.telefono 
        FROM citas c 
        JOIN usuarios u ON c.usuario_id = u.id 
        $where_sql 
        ORDER BY c.fecha DESC, c.hora DESC 
        LIMIT 50";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Gestión de Citas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../estilos.css">
    <style>
        .cita-card {
            border-left: 5px solid;
            margin-bottom: 15px;
            transition: all 0.3s;
        }
        .cita-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .cita-pendiente { border-left-color: #ffc107; }
        .cita-confirmada { border-left-color: #198754; }
        .cita-completada { border-left-color: #0dcaf0; }
        .cita-cancelada { border-left-color: #dc3545; }
        .badge-estado {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
        }
        .badge-pendiente { background: #ffc107; color: #000; }
        .badge-confirmada { background: #198754; color: #fff; }
        .badge-completada { background: #0dcaf0; color: #fff; }
        .badge-cancelada { background: #dc3545; color: #fff; }
        .service-icon {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }
        .icon-aseo { background: #e3f2fd; color: #1976d2; }
        .icon-bano { background: #e8f5e9; color: #388e3c; }
        .icon-salud { background: #ffebee; color: #d32f2f; }
        .icon-entrenamiento { background: #f3e5f5; color: #7b1fa2; }
        .icon-guarderia { background: #fff3e0; color: #f57c00; }
    </style>
</head>
<body>
    <div class="navbar-top">
        <h4 class="mb-0"><i class="fas fa-calendar-alt me-2"></i> Gestión de Citas</h4>
        <div class="d-flex gap-2">
            <a href="index.php" class="btn btn-outline-primary">
                <i class="fas fa-arrow-left me-1"></i> Volver al Dashboard
            </a>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalFiltrarCitas">
                <i class="fas fa-filter me-1"></i> Filtrar
            </button>
        </div>
    </div>

    <!-- Estadísticas -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stat-card primary">
                <div class="stat-icon text-primary"><i class="fas fa-calendar"></i></div>
                <div class="stat-value text-primary"><?php echo $estadisticas['total_citas']; ?></div>
                <div class="stat-label">Total Citas</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card warning">
                <div class="stat-icon text-warning"><i class="fas fa-clock"></i></div>
                <div class="stat-value text-warning"><?php echo $estadisticas['citas_pendientes']; ?></div>
                <div class="stat-label">Pendientes</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card success">
                <div class="stat-icon text-success"><i class="fas fa-check-circle"></i></div>
                <div class="stat-value text-success"><?php echo $estadisticas['citas_hoy']; ?></div>
                <div class="stat-label">Citas Hoy</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card info">
                <div class="stat-icon text-info"><i class="fas fa-calendar-check"></i></div>
                <div class="stat-value text-info"><?php echo $estadisticas['total_citas'] - $estadisticas['citas_pendientes']; ?></div>
                <div class="stat-label">Atendidas</div>
            </div>
        </div>
    </div>

    <!-- Tabla de citas -->
    <div class="table-container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="mb-0">Lista de Citas</h5>
            <div>
                <?php if($fecha_filtro): ?>
                <span class="badge bg-info me-2">Fecha: <?php echo $fecha_filtro; ?></span>
                <?php endif; ?>
                <?php if($estado_filtro && $estado_filtro !== 'todos'): ?>
                <span class="badge bg-info">Estado: <?php echo ucfirst($estado_filtro); ?></span>
                <?php endif; ?>
            </div>
        </div>

        <?php if($result->num_rows > 0): ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Cliente</th>
                        <th>Servicio</th>
                        <th>Fecha/Hora</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($cita = $result->fetch_assoc()): 
                        $detalles = json_decode($cita['detalles'] ?? '{}', true);
                        $estado_class = 'badge-' . $cita['estado'];
                        $cita_class = 'cita-' . $cita['estado'];
                    ?>
                    <tr class="<?php echo $cita_class; ?>">
                        <td>#<?php echo $cita['id']; ?></td>
                        <td>
                            <strong><?php echo htmlspecialchars($cita['nombre_completo']); ?></strong><br>
                            <small class="text-muted"><?php echo $cita['email']; ?></small>
                            <?php if($cita['telefono']): ?>
                            <br><small><i class="fas fa-phone"></i> <?php echo $cita['telefono']; ?></small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <?php 
                                $icon_class = 'icon-' . $cita['servicio'];
                                $icon_text = '';
                                switch($cita['servicio']) {
                                    case 'aseo': $icon_text = '🛁'; break;
                                    case 'bano': $icon_text = '🚿'; break;
                                    case 'salud': $icon_text = '🩺'; break;
                                    case 'entrenamiento': $icon_text = '🐕'; break;
                                    case 'guarderia': $icon_text = '🏠'; break;
                                    default: $icon_text = '📅';
                                }
                                ?>
                                <div class="service-icon <?php echo $icon_class; ?> me-2">
                                    <?php echo $icon_text; ?>
                                </div>
                                <div>
                                    <strong><?php echo ucfirst($cita['servicio']); ?></strong>
                                    <?php if(isset($detalles['tipo_servicio'])): ?>
                                    <br><small class="text-muted"><?php echo $detalles['tipo_servicio']; ?></small>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                        <td>
                            <strong><?php echo date('d/m/Y', strtotime($cita['fecha'])); ?></strong><br>
                            <small class="text-muted"><?php echo $cita['hora']; ?></small>
                        </td>
                        <td>
                            <span class="badge-estado <?php echo $estado_class; ?>">
                                <?php echo ucfirst($cita['estado']); ?>
                            </span>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <!-- Ver detalles -->
                                <button class="btn btn-outline-info" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#modalDetalleCita"
                                        onclick="cargarDetalleCita(<?php echo $cita['id']; ?>)">
                                    <i class="fas fa-eye"></i>
                                </button>
                                
                                <!-- Cambiar estado -->
                                <div class="dropdown">
                                    <button class="btn btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <ul class="dropdown-menu">
                                        <?php if($cita['estado'] !== 'confirmada'): ?>
                                        <li><a class="dropdown-item" href="citas.php?action=change_status&id=<?php echo $cita['id']; ?>&status=confirmada">
                                            <i class="fas fa-check text-success me-2"></i> Confirmar
                                        </a></li>
                                        <?php endif; ?>
                                        
                                        <?php if($cita['estado'] !== 'completada'): ?>
                                        <li><a class="dropdown-item" href="citas.php?action=change_status&id=<?php echo $cita['id']; ?>&status=completada">
                                            <i class="fas fa-check-double text-primary me-2"></i> Completar
                                        </a></li>
                                        <?php endif; ?>
                                        
                                        <?php if($cita['estado'] !== 'pendiente'): ?>
                                        <li><a class="dropdown-item" href="citas.php?action=change_status&id=<?php echo $cita['id']; ?>&status=pendiente">
                                            <i class="fas fa-clock text-warning me-2"></i> Poner como Pendiente
                                        </a></li>
                                        <?php endif; ?>
                                    </ul>
                                </div>
                                
                                <!-- Cancelar -->
                                <?php if($cita['estado'] !== 'cancelada'): ?>
                                <button class="btn btn-outline-danger" 
                                        onclick="cancelarCita(<?php echo $cita['id']; ?>, '<?php echo $cita['nombre_completo']; ?>')">
                                    <i class="fas fa-times"></i>
                                </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="text-center py-5">
            <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
            <h5>No hay citas</h5>
            <p class="text-muted">No se encontraron citas con los filtros aplicados.</p>
            <a href="citas.php" class="btn btn-primary">Ver todas las citas</a>
        </div>
        <?php endif; ?>
    </div>

    <!-- MODAL: Filtrar Citas -->
    <div class="modal fade" id="modalFiltrarCitas" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-filter me-2"></i> Filtrar Citas</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form method="GET" action="citas.php">
                        <div class="mb-3">
                            <label class="form-label">Fecha específica</label>
                            <input type="date" class="form-control" name="fecha" value="<?php echo $fecha_filtro; ?>">
                            <small class="text-muted">Dejar vacío para ver todas las fechas</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Estado</label>
                            <select class="form-select" name="estado">
                                <option value="todos" <?php echo (!$estado_filtro || $estado_filtro == 'todos') ? 'selected' : ''; ?>>Todos los estados</option>
                                <option value="pendiente" <?php echo ($estado_filtro == 'pendiente') ? 'selected' : ''; ?>>Pendientes</option>
                                <option value="confirmada" <?php echo ($estado_filtro == 'confirmada') ? 'selected' : ''; ?>>Confirmadas</option>
                                <option value="completada" <?php echo ($estado_filtro == 'completada') ? 'selected' : ''; ?>>Completadas</option>
                                <option value="cancelada" <?php echo ($estado_filtro == 'cancelada') ? 'selected' : ''; ?>>Canceladas</option>
                            </select>
                        </div>
                        
                        <div class="text-end">
                            <a href="citas.php" class="btn btn-outline-secondary">Limpiar Filtros</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search me-1"></i> Aplicar Filtros
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL: Detalle de Cita -->
    <div class="modal fade" id="modalDetalleCita" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title"><i class="fas fa-info-circle me-2"></i> Detalles de la Cita</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="detalleCitaContent">
                        <div class="text-center py-5">
                            <div class="spinner-border" role="status">
                                <span class="visually-hidden">Cargando...</span>
                            </div>
                            <p class="mt-2">Cargando detalles de la cita...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    // Cancelar cita
    function cancelarCita(id, nombre) {
        if (confirm(`¿Cancelar la cita de ${nombre}?\n\nEsta acción no se puede deshacer.`)) {
            window.location.href = `citas.php?action=cancel&id=${id}`;
        }
    }
    
    // Cargar detalles de cita
    function cargarDetalleCita(id) {
        fetch(`../php/dashboard/get_cita.php?id=${id}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const cita = data.cita;
                    const detalles = JSON.parse(cita.detalles || '{}');
                    
                    let html = `
                        <div class="row">
                            <div class="col-md-6">
                                <h5>Información del Cliente</h5>
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <p><strong>Nombre:</strong> ${cita.nombre_completo}</p>
                                        <p><strong>Email:</strong> ${cita.email}</p>
                                        ${cita.telefono ? `<p><strong>Teléfono:</strong> ${cita.telefono}</p>` : ''}
                                        <p><strong>ID Usuario:</strong> #${cita.usuario_id}</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <h5>Detalles de la Cita</h5>
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <p><strong>Servicio:</strong> ${cita.servicio.charAt(0).toUpperCase() + cita.servicio.slice(1)}</p>
                                        <p><strong>Fecha:</strong> ${new Date(cita.fecha).toLocaleDateString('es-ES')}</p>
                                        <p><strong>Hora:</strong> ${cita.hora}</p>
                                        <p><strong>Estado:</strong> 
                                            <span class="badge-estado badge-${cita.estado}">
                                                ${cita.estado.charAt(0).toUpperCase() + cita.estado.slice(1)}
                                            </span>
                                        </p>
                                        <p><strong>Creada:</strong> ${new Date(cita.creado_en).toLocaleString('es-ES')}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <h5>Información Adicional</h5>
                        <div class="card">
                            <div class="card-body">
                    `;
                    
                    // Mostrar detalles específicos del servicio
                    if (Object.keys(detalles).length > 0) {
                        html += '<ul class="list-unstyled">';
                        for (const [key, value] of Object.entries(detalles)) {
                            if (value) {
                                const label = key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                                html += `<li><strong>${label}:</strong> ${value}</li>`;
                            }
                        }
                        html += '</ul>';
                    } else {
                        html += '<p class="text-muted">No hay información adicional.</p>';
                    }
                    
                    // Observaciones
                    if (cita.observaciones) {
                        html += `
                            <hr>
                            <h6>Observaciones:</h6>
                            <p>${cita.observaciones}</p>
                        `;
                    }
                    
                    html += `
                            </div>
                        </div>
                        
                        <div class="mt-3">
                            <a href="citas.php?action=change_status&id=${cita.id}&status=confirmada" class="btn btn-success">
                                <i class="fas fa-check me-1"></i> Confirmar Cita
                            </a>
                            <a href="citas.php?action=change_status&id=${cita.id}&status=completada" class="btn btn-primary">
                                <i class="fas fa-check-double me-1"></i> Marcar como Completada
                            </a>
                            ${cita.estado !== 'cancelada' ? 
                                `<button class="btn btn-danger" onclick="cancelarCita(${cita.id}, '${cita.nombre_completo}')">
                                    <i class="fas fa-times me-1"></i> Cancelar Cita
                                </button>` : ''
                            }
                        </div>
                    `;
                    
                    document.getElementById('detalleCitaContent').innerHTML = html;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('detalleCitaContent').innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i> Error al cargar los detalles de la cita.
                    </div>
                `;
            });
    }
    </script>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
$conn->close();
?>