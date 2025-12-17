<?php
session_start();
require_once '../php/config.php';

// Verificar que el usuario esté logueado y sea ADMIN o VENDEDOR
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] !== 'ADMIN' && $_SESSION['user_role'] !== 'VENDEDOR')) {
    header("Location: index.php?error=access_denied");
    exit();
}

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Filtrar por fecha
$fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-01'); // Primer día del mes
$fecha_fin = $_GET['fecha_fin'] ?? date('Y-m-t'); // Último día del mes
$usuario_id = $_GET['usuario'] ?? '';

// Construir consulta con filtros
$where_conditions = ["v.estado = 'COMPLETADA'"];
$params = [];
$types = '';

if ($fecha_inicio && $fecha_fin) {
    $where_conditions[] = "DATE(v.fecha) BETWEEN ? AND ?";
    $params[] = $fecha_inicio;
    $params[] = $fecha_fin;
    $types .= 'ss';
}

if ($usuario_id && is_numeric($usuario_id)) {
    $where_conditions[] = "v.usuario_id = ?";
    $params[] = $usuario_id;
    $types .= 'i';
}

$where_sql = 'WHERE ' . implode(' AND ', $where_conditions);

// Obtener estadísticas
$estadisticas = [];

// Total ventas del período
$sql_total = "SELECT COUNT(*) as cantidad, COALESCE(SUM(v.total), 0) as monto 
              FROM ventas v 
              $where_sql";
$stmt_total = $conn->prepare($sql_total);
if (!empty($params)) {
    $stmt_total->bind_param($types, ...$params);
}
$stmt_total->execute();
$result_total = $stmt_total->get_result();
$totales = $result_total->fetch_assoc();
$estadisticas['total_ventas'] = $totales['cantidad'];
$estadisticas['monto_total'] = $totales['monto'];

// Ventas promedio
$estadisticas['venta_promedio'] = $estadisticas['total_ventas'] > 0 
    ? $estadisticas['monto_total'] / $estadisticas['total_ventas'] 
    : 0;

// Día con más ventas
$sql_top_day = "SELECT DATE(fecha) as dia, COUNT(*) as cantidad, SUM(total) as monto 
                FROM ventas 
                WHERE estado = 'COMPLETADA' AND DATE(fecha) BETWEEN ? AND ?
                GROUP BY DATE(fecha) 
                ORDER BY monto DESC 
                LIMIT 1";
$stmt_day = $conn->prepare($sql_top_day);
$stmt_day->bind_param("ss", $fecha_inicio, $fecha_fin);
$stmt_day->execute();
$result_day = $stmt_day->get_result();
$estadisticas['mejor_dia'] = $result_day->fetch_assoc();

// Obtener ventas
$sql = "SELECT v.*, u.nombre_completo, u.email 
        FROM ventas v 
        LEFT JOIN usuarios u ON v.usuario_id = u.id 
        $where_sql 
        ORDER BY v.fecha DESC 
        LIMIT 100";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Obtener lista de usuarios para filtro
$sql_usuarios = "SELECT id, nombre_completo FROM usuarios ORDER BY nombre_completo";
$result_usuarios = $conn->query($sql_usuarios);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Historial de Ventas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../estilos.css">
    <style>
        .venta-card {
            border-left: 4px solid #198754;
            transition: all 0.3s;
        }
        .venta-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .venta-cancelada {
            border-left-color: #dc3545;
            opacity: 0.7;
        }
        .badge-venta {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
        }
        .badge-completada { background: #198754; color: white; }
        .badge-cancelada { background: #dc3545; color: white; }
        .monto-venta {
            font-size: 1.2rem;
            font-weight: bold;
        }
        .producto-chip {
            display: inline-block;
            background: #e9ecef;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.8rem;
            margin: 2px;
        }
        .filter-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
    </style>
</head>
<body>
    <div class="navbar-top">
        <h4 class="mb-0"><i class="fas fa-shopping-cart me-2"></i> Historial de Ventas</h4>
        <div class="d-flex gap-2">
            <a href="index.php" class="btn btn-outline-primary">
                <i class="fas fa-arrow-left me-1"></i> Volver al Dashboard
            </a>
            <button class="btn btn-success" onclick="window.print()">
                <i class="fas fa-print me-1"></i> Imprimir Reporte
            </button>
        </div>
    </div>

    <!-- Filtros -->
    <div class="filter-card">
        <h5 class="mb-3"><i class="fas fa-filter me-2"></i> Filtros</h5>
        <form method="GET" action="ventas.php" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Fecha Inicio</label>
                <input type="date" class="form-control" name="fecha_inicio" value="<?php echo $fecha_inicio; ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Fecha Fin</label>
                <input type="date" class="form-control" name="fecha_fin" value="<?php echo $fecha_fin; ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Cliente</label>
                <select class="form-select" name="usuario">
                    <option value="">Todos los clientes</option>
                    <?php while($usuario = $result_usuarios->fetch_assoc()): ?>
                    <option value="<?php echo $usuario['id']; ?>" 
                        <?php echo ($usuario_id == $usuario['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($usuario['nombre_completo']); ?>
                    </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <div class="d-flex gap-2 w-100">
                    <a href="ventas.php" class="btn btn-outline-secondary flex-grow-1">Limpiar</a>
                    <button type="submit" class="btn btn-primary flex-grow-1">
                        <i class="fas fa-search me-1"></i> Filtrar
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Estadísticas -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stat-card primary">
                <div class="stat-icon text-primary"><i class="fas fa-receipt"></i></div>
                <div class="stat-value text-primary"><?php echo $estadisticas['total_ventas']; ?></div>
                <div class="stat-label">Ventas Totales</div>
                <small class="text-muted">Período seleccionado</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card success">
                <div class="stat-icon text-success"><i class="fas fa-money-bill-wave"></i></div>
                <div class="stat-value text-success">S/ <?php echo number_format($estadisticas['monto_total'], 2); ?></div>
                <div class="stat-label">Monto Total</div>
                <small class="text-muted">Ingresos brutos</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card warning">
                <div class="stat-icon text-warning"><i class="fas fa-chart-line"></i></div>
                <div class="stat-value text-warning">S/ <?php echo number_format($estadisticas['venta_promedio'], 2); ?></div>
                <div class="stat-label">Venta Promedio</div>
                <small class="text-muted">Por transacción</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card info">
                <div class="stat-icon text-info"><i class="fas fa-calendar-star"></i></div>
                <div class="stat-value text-info">
                    <?php if($estadisticas['mejor_dia']): ?>
                    S/ <?php echo number_format($estadisticas['mejor_dia']['monto'], 2); ?>
                    <?php else: ?>
                    S/ 0.00
                    <?php endif; ?>
                </div>
                <div class="stat-label">Mejor Día</div>
                <small class="text-muted">
                    <?php echo $estadisticas['mejor_dia']['dia'] ?? 'Sin datos'; ?>
                </small>
            </div>
        </div>
    </div>

    <!-- Tabla de ventas -->
    <div class="table-container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="mb-0">Historial de Ventas</h5>
            <div class="text-muted">
                Mostrando <?php echo $result->num_rows; ?> ventas
                <?php if($fecha_inicio && $fecha_fin): ?>
                • Del <?php echo date('d/m/Y', strtotime($fecha_inicio)); ?> al <?php echo date('d/m/Y', strtotime($fecha_fin)); ?>
                <?php endif; ?>
            </div>
        </div>

        <?php if($result->num_rows > 0): ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>ID Venta</th>
                        <th>Cliente</th>
                        <th>Fecha</th>
                        <th>Productos</th>
                        <th>Total</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($venta = $result->fetch_assoc()): 
                        // Obtener detalles de productos para esta venta
                        $sql_detalles = "SELECT vd.*, p.nombre, p.categoria 
                                        FROM venta_detalles vd 
                                        JOIN productos p ON vd.producto_id = p.id 
                                        WHERE vd.venta_id = ?";
                        $stmt_detalles = $conn->prepare($sql_detalles);
                        $stmt_detalles->bind_param("i", $venta['id']);
                        $stmt_detalles->execute();
                        $detalles_result = $stmt_detalles->get_result();
                        $num_productos = $detalles_result->num_rows;
                    ?>
                    <tr class="venta-card <?php echo $venta['estado'] == 'CANCELADA' ? 'venta-cancelada' : ''; ?>">
                        <td>
                            <strong>#<?php echo str_pad($venta['id'], 6, '0', STR_PAD_LEFT); ?></strong>
                        </td>
                        <td>
                            <?php if($venta['nombre_completo']): ?>
                            <strong><?php echo htmlspecialchars($venta['nombre_completo']); ?></strong><br>
                            <small class="text-muted"><?php echo $venta['email']; ?></small>
                            <?php else: ?>
                            <span class="text-muted">Cliente no registrado</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php echo date('d/m/Y', strtotime($venta['fecha'])); ?><br>
                            <small class="text-muted"><?php echo date('H:i', strtotime($venta['fecha'])); ?></small>
                        </td>
                        <td>
                            <span class="badge bg-secondary"><?php echo $num_productos; ?> productos</span>
                            <div class="mt-1">
                                <?php 
                                $contador = 0;
                                while($detalle = $detalles_result->fetch_assoc() && $contador < 2):
                                    $contador++;
                                ?>
                                    <span class="producto-chip" title="<?php echo htmlspecialchars($detalle['nombre']); ?>">
                                        <?php echo $detalle['cantidad']; ?>x <?php echo substr($detalle['nombre'], 0, 15) . '...'; ?>
                                    </span>
                                <?php endwhile; ?>
                                <?php if($num_productos > 2): ?>
                                <span class="producto-chip">+<?php echo $num_productos - 2; ?> más</span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <span class="monto-venta text-success">S/ <?php echo number_format($venta['total'], 2); ?></span>
                        </td>
                        <td>
                            <span class="badge-venta badge-<?php echo strtolower($venta['estado']); ?>">
                                <?php echo $venta['estado']; ?>
                            </span>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <!-- Ver detalles -->
                                <button class="btn btn-outline-info" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#modalDetalleVenta"
                                        onclick="cargarDetalleVenta(<?php echo $venta['id']; ?>)">
                                    <i class="fas fa-eye"></i>
                                </button>
                                
                                <!-- Factura/Recibo -->
                                <button class="btn btn-outline-primary"
                                        onclick="generarFactura(<?php echo $venta['id']; ?>)">
                                    <i class="fas fa-receipt"></i>
                                </button>
                                
                                <!-- Solo ADMIN puede cancelar -->
                                <?php if($_SESSION['user_role'] === 'ADMIN' && $venta['estado'] == 'COMPLETADA'): ?>
                                <button class="btn btn-outline-danger"
                                        onclick="cancelarVenta(<?php echo $venta['id']; ?>)">
                                    <i class="fas fa-times"></i>
                                </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php 
                    $stmt_detalles->close();
                    endwhile; 
                    ?>
                </tbody>
            </table>
        </div>
        
        <!-- Resumen al final -->
        <div class="mt-4 p-3 bg-light rounded">
            <div class="row text-center">
                <div class="col-md-4">
                    <h5 class="text-primary">S/ <?php echo number_format($estadisticas['monto_total'], 2); ?></h5>
                    <small class="text-muted">TOTAL PERÍODO</small>
                </div>
                <div class="col-md-4">
                    <h5 class="text-success"><?php echo $estadisticas['total_ventas']; ?></h5>
                    <small class="text-muted">TRANSACCIONES</small>
                </div>
                <div class="col-md-4">
                    <h5 class="text-warning">S/ <?php echo number_format($estadisticas['venta_promedio'], 2); ?></h5>
                    <small class="text-muted">PROMEDIO POR VENTA</small>
                </div>
            </div>
        </div>
        
        <?php else: ?>
        <div class="text-center py-5">
            <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
            <h5>No hay ventas registradas</h5>
            <p class="text-muted">No se encontraron ventas con los filtros aplicados.</p>
            <a href="ventas.php" class="btn btn-primary">Ver todas las ventas</a>
        </div>
        <?php endif; ?>
    </div>

    <!-- MODAL: Detalle de Venta -->
    <div class="modal fade" id="modalDetalleVenta" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title"><i class="fas fa-file-invoice me-2"></i> Detalles de Venta</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="detalleVentaContent">
                        <div class="text-center py-5">
                            <div class="spinner-border" role="status">
                                <span class="visually-hidden">Cargando...</span>
                            </div>
                            <p class="mt-2">Cargando detalles de la venta...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    // Cargar detalles de venta
    function cargarDetalleVenta(id) {
        fetch(`../php/dashboard/get_venta.php?id=${id}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const venta = data.venta;
                    const detalles = data.detalles;
                    const usuario = data.usuario;
                    
                    let html = `
                        <div class="invoice-header mb-4">
                            <div class="row">
                                <div class="col-md-6">
                                    <h4>Factura #${venta.id.toString().padStart(6, '0')}</h4>
                                    <p class="text-muted mb-0">Fecha: ${new Date(venta.fecha).toLocaleDateString('es-ES', {
                                        weekday: 'long',
                                        year: 'numeric',
                                        month: 'long',
                                        day: 'numeric',
                                        hour: '2-digit',
                                        minute: '2-digit'
                                    })}</p>
                                </div>
                                <div class="col-md-6 text-end">
                                    <span class="badge-venta badge-${venta.estado.toLowerCase()}">
                                        ${venta.estado}
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0">Información del Cliente</h6>
                                    </div>
                                    <div class="card-body">
                                        ${usuario ? `
                                            <p><strong>Nombre:</strong> ${usuario.nombre_completo}</p>
                                            <p><strong>Email:</strong> ${usuario.email}</p>
                                            ${usuario.telefono ? `<p><strong>Teléfono:</strong> ${usuario.telefono}</p>` : ''}
                                            <p><strong>ID Usuario:</strong> #${usuario.id}</p>
                                        ` : '<p class="text-muted">Cliente no registrado</p>'}
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0">Resumen de Venta</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>Subtotal:</span>
                                            <strong>S/ ${parseFloat(venta.total).toFixed(2)}</strong>
                                        </div>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>IGV (18%):</span>
                                            <strong>S/ ${(parseFloat(venta.total) * 0.18).toFixed(2)}</strong>
                                        </div>
                                        <hr>
                                        <div class="d-flex justify-content-between">
                                            <span class="h5">Total:</span>
                                            <span class="h4 text-success">S/ ${parseFloat(venta.total).toFixed(2)}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <h5 class="mb-3">Productos Vendidos</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>Producto</th>
                                        <th class="text-center">Cantidad</th>
                                        <th class="text-end">Precio Unit.</th>
                                        <th class="text-end">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                    `;
                    
                    // Productos
                    let total_productos = 0;
                    detalles.forEach(detalle => {
                        total_productos += detalle.cantidad;
                        html += `
                            <tr>
                                <td>
                                    <strong>${detalle.nombre}</strong><br>
                                    <small class="text-muted">${detalle.categoria}</small>
                                </td>
                                <td class="text-center">${detalle.cantidad}</td>
                                <td class="text-end">S/ ${parseFloat(detalle.precio_unitario).toFixed(2)}</td>
                                <td class="text-end">S/ ${parseFloat(detalle.subtotal).toFixed(2)}</td>
                            </tr>
                        `;
                    });
                    
                    html += `
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <td colspan="2">
                                            <strong>Total Productos:</strong> ${total_productos}
                                        </td>
                                        <td class="text-end"><strong>Total:</strong></td>
                                        <td class="text-end">
                                            <strong class="text-success">S/ ${parseFloat(venta.total).toFixed(2)}</strong>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        
                        <div class="mt-4 text-end">
                            <button class="btn btn-primary" onclick="imprimirFactura(${venta.id})">
                                <i class="fas fa-print me-1"></i> Imprimir Factura
                            </button>
                            <button class="btn btn-success" onclick="descargarFactura(${venta.id})">
                                <i class="fas fa-download me-1"></i> Descargar PDF
                            </button>
                        </div>
                    `;
                    
                    document.getElementById('detalleVentaContent').innerHTML = html;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('detalleVentaContent').innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i> Error al cargar los detalles de la venta.
                    </div>
                `;
            });
    }
    
    // Funciones adicionales
    function generarFactura(id) {
        window.open(`../php/generar_factura.php?id=${id}`, '_blank');
    }
    
    function cancelarVenta(id) {
        if (confirm('¿Cancelar esta venta?\n\nEsta acción revertirá el stock de productos.')) {
            fetch(`../php/dashboard/cancelar_venta.php?id=${id}`, {
                method: 'POST'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Venta cancelada correctamente');
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            });
        }
    }
    
    function imprimirFactura(id) {
        const ventana = window.open(`../php/generar_factura.php?id=${id}&print=1`, '_blank');
        ventana.onload = function() {
            ventana.print();
        };
    }
    
    function descargarFactura(id) {
        window.location.href = `../php/generar_factura.php?id=${id}&download=1`;
    }
    </script>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
$conn->close();
?>