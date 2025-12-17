<?php
session_start();
require_once '../php/config.php';

// Verificar que el usuario esté logueado y sea ADMIN o VENDEDOR
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] !== 'ADMIN' && $_SESSION['user_role'] !== 'VENDEDOR')) {
    header("Location: ../index.php?error=access_denied");
    exit();
}

// Conexión a BD
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Obtener estadísticas
$estadisticas = [];

// Total de productos
$sql = "SELECT COUNT(*) as total FROM productos";
$result = $conn->query($sql);
$estadisticas['total_productos'] = $result->fetch_assoc()['total'];

// Total de ventas (últimos 30 días)
$sql = "SELECT COUNT(*) as total, COALESCE(SUM(total), 0) as monto 
        FROM ventas 
        WHERE fecha >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
$result = $conn->query($sql);
$ventas = $result->fetch_assoc();
$estadisticas['ventas_30_dias'] = $ventas['total'];
$estadisticas['monto_ventas'] = $ventas['monto'];

// Productos con bajo stock
$sql = "SELECT COUNT(*) as total FROM productos WHERE stock_quantity <= stock_minimo";
$result = $conn->query($sql);
$estadisticas['bajo_stock'] = $result->fetch_assoc()['total'];

// Total de usuarios
$sql = "SELECT COUNT(*) as total FROM usuarios";
$result = $conn->query($sql);
$estadisticas['total_usuarios'] = $result->fetch_assoc()['total'];

// Citas pendientes
$sql = "SELECT COUNT(*) as total FROM citas WHERE estado = 'pendiente'";
$result = $conn->query($sql);
$estadisticas['citas_pendientes'] = $result->fetch_assoc()['total'];

// Ventas por mes (para el gráfico)
$sql = "SELECT 
            DATE_FORMAT(fecha, '%Y-%m') as mes,
            COUNT(*) as cantidad,
            COALESCE(SUM(total), 0) as monto
        FROM ventas 
        WHERE fecha >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
        GROUP BY DATE_FORMAT(fecha, '%Y-%m')
        ORDER BY mes DESC
        LIMIT 6";
$result_ventas_mes = $conn->query($sql);

// Productos más vendidos
$sql = "SELECT 
            p.nombre,
            p.categoria,
            SUM(vd.cantidad) as total_vendido,
            COALESCE(SUM(vd.subtotal), 0) as total_ventas
        FROM venta_detalles vd
        JOIN productos p ON vd.producto_id = p.id
        GROUP BY vd.producto_id
        ORDER BY total_vendido DESC
        LIMIT 5";
$result_top_productos = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Puphub Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../estilos.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --sidebar-width: 250px;
            --primary-color: #4361ee;
            --secondary-color: #3a0ca3;
            --success-color: #4cc9f0;
            --warning-color: #f72585;
            --info-color: #7209b7;
        }

        .main-content {
            margin-left: 0;
            padding: 20px;
            min-height: 100vh;
        }

        .navbar-top {
            background: white;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            transition: transform 0.3s;
            border-left: 5px solid;
            height: 100%;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-card.primary {
            border-left-color: var(--primary-color);
        }

        .stat-card.success {
            border-left-color: var(--success-color);
        }

        .stat-card.warning {
            border-left-color: var(--warning-color);
        }

        .stat-card.info {
            border-left-color: var(--info-color);
        }

        .stat-icon {
            font-size: 2.5rem;
            opacity: 0.8;
            margin-bottom: 15px;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            margin: 10px 0;
        }

        .stat-label {
            color: #6c757d;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .chart-container {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            height: 100%;
        }

        .table-container {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }

        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }

        .action-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            transition: all 0.3s;
            border: 1px solid #e9ecef;
            text-decoration: none;
            color: inherit;
        }

        .action-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            border-color: var(--primary-color);
        }

        .action-icon {
            font-size: 2rem;
            margin-bottom: 10px;
            color: var(--primary-color);
        }
    </style>
</head>
<body>
    <!-- Top Navbar -->
    <div class="navbar-top">
        <div>
            <h4 class="mb-0"><i class="fas fa-tachometer-alt me-2"></i> Dashboard Administrativo</h4>
            <small class="text-muted">Bienvenido, <?php echo $_SESSION['user_name']; ?> (<?php echo $_SESSION['user_role']; ?>)</small>
        </div>
        <div class="d-flex gap-2">
            <a href="../index.php" class="btn btn-outline-primary">
                <i class="fas fa-home me-1"></i> Ir al Sitio
            </a>
            <a href="../logout.php" class="btn btn-outline-danger">
                <i class="fas fa-sign-out-alt me-1"></i> Cerrar Sesión
            </a>
        </div>
    </div>

    <!-- Estadísticas -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stat-card primary">
                <div class="stat-icon text-primary"><i class="fas fa-box"></i></div>
                <div class="stat-value text-primary"><?php echo $estadisticas['total_productos']; ?></div>
                <div class="stat-label">Productos</div>
                <small class="text-muted"><?php echo $estadisticas['bajo_stock']; ?> con bajo stock</small>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="stat-card success">
                <div class="stat-icon text-success"><i class="fas fa-chart-line"></i></div>
                <div class="stat-value text-success">S/ <?php echo number_format($estadisticas['monto_ventas'], 2); ?></div>
                <div class="stat-label">Ventas (30 días)</div>
                <small class="text-muted"><?php echo $estadisticas['ventas_30_dias']; ?> transacciones</small>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="stat-card warning">
                <div class="stat-icon text-warning"><i class="fas fa-users"></i></div>
                <div class="stat-value text-warning"><?php echo $estadisticas['total_usuarios']; ?></div>
                <div class="stat-label">Usuarios</div>
                <small class="text-muted">Clientes registrados</small>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="stat-card info">
                <div class="stat-icon text-info"><i class="fas fa-calendar-check"></i></div>
                <div class="stat-value text-info"><?php echo $estadisticas['citas_pendientes']; ?></div>
                <div class="stat-label">Citas Pendientes</div>
                <small class="text-muted">Por confirmar</small>
            </div>
        </div>
    </div>

    <!-- Acciones Rápidas -->
    <div class="table-container mb-4">
        <h5 class="mb-3"><i class="fas fa-bolt me-2"></i> Acciones Rápidas</h5>
        <div class="quick-actions">
            <a href="productos.php" class="action-card">
                <div class="action-icon"><i class="fas fa-box"></i></div>
                <h6>Gestionar Productos</h6>
                <p class="text-muted small">Agregar, editar o eliminar productos</p>
            </a>
            
            <a href="usuarios.php" class="action-card">
                <div class="action-icon"><i class="fas fa-users"></i></div>
                <h6>Gestionar Usuarios</h6>
                <p class="text-muted small">Ver y administrar usuarios</p>
            </a>
            
            <a href="citas.php" class="action-card">
                <div class="action-icon"><i class="fas fa-calendar"></i></div>
                <h6>Gestionar Citas</h6>
                <p class="text-muted small">Ver y administrar citas</p>
            </a>
            
            <a href="ventas.php" class="action-card">
                <div class="action-icon"><i class="fas fa-shopping-cart"></i></div>
                <h6>Ver Ventas</h6>
                <p class="text-muted small">Historial de ventas</p>
            </a>
            
            <a href="inventario.php" class="action-card">
                <div class="action-icon"><i class="fas fa-warehouse"></i></div>
                <h6>Control de Inventario</h6>
                <p class="text-muted small">Movimientos de stock</p>
            </a>
            
            <a href="configuracion.php" class="action-card">
                <div class="action-icon"><i class="fas fa-cog"></i></div>
                <h6>Configuración</h6>
                <p class="text-muted small">Ajustes del sistema</p>
            </a>
        </div>
    </div>

    <!-- Gráficos y Tablas -->
    <div class="row">
        <!-- Chart -->
        <div class="col-md-8">
            <div class="chart-container">
                <h5 class="mb-4">Ventas Mensuales</h5>
                <canvas id="salesChart"></canvas>
            </div>
        </div>
        
        <!-- Top Products -->
        <div class="col-md-4">
            <div class="table-container">
                <h5 class="mb-4">Productos Más Vendidos</h5>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Cantidad</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if($result_top_productos && $result_top_productos->num_rows > 0): ?>
                                <?php while($producto = $result_top_productos->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($producto['nombre']); ?></strong><br>
                                        <small class="text-muted"><?php echo $producto['categoria']; ?></small>
                                    </td>
                                    <td><?php echo $producto['total_vendido']; ?></td>
                                    <td>S/ <?php echo number_format($producto['total_ventas'], 2); ?></td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-3">
                                        <i class="fas fa-chart-bar fa-2x mb-2"></i><br>
                                        No hay datos de ventas
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Actividad Reciente -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="table-container">
                <h5 class="mb-4"><i class="fas fa-history me-2"></i> Actividad Reciente</h5>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Usuario</th>
                                <th>Acción</th>
                                <th>Detalles</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><?php echo date('d/m/Y H:i'); ?></td>
                                <td><?php echo $_SESSION['user_name']; ?></td>
                                <td><span class="badge bg-info">Acceso</span></td>
                                <td>Ingreso al Dashboard</td>
                            </tr>
                            <!-- Aquí podrías agregar más registros desde la BD -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
    // Gráfico de ventas
    const ctx = document.getElementById('salesChart').getContext('2d');
    const salesChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun'],
            datasets: [{
                label: 'Ventas (S/)',
                data: [12000, 19000, 15000, 25000, 22000, 30000],
                borderColor: '#4361ee',
                backgroundColor: 'rgba(67, 97, 238, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: true
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'S/ ' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
$conn->close();
?>