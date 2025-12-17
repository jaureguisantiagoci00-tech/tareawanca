<?php
// dashboard/inventario.php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

// Verifica que config.php exista
$config_path = __DIR__ . '/../php/config.php';
if (!file_exists($config_path)) {
    die("Error: Archivo config.php no encontrado en: $config_path");
}
require_once $config_path;

// Verificar permisos ADMIN o VENDEDOR
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] !== 'ADMIN' && $_SESSION['user_role'] !== 'VENDEDOR')) {
    header("Location: index.php?error=access_denied");
    exit();
}

// Conexión a base de datos
try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        throw new Exception("Error de conexión: " . $conn->connect_error);
    }
    $conn->set_charset("utf8mb4");
} catch (Exception $e) {
    die("Error de conexión a la base de datos: " . $e->getMessage());
}

// Obtener parámetros
$action = $_GET['action'] ?? '';
$producto_id = intval($_GET['id'] ?? 0);

// 1. ACTUALIZAR STOCK MANUALMENTE
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['ajustar_stock'])) {
    $producto_id = intval($_POST['producto_id']);
    $tipo = $_POST['tipo']; // 'entrada' o 'salida'
    $cantidad = intval($_POST['cantidad']);
    $motivo = $_POST['motivo'] ?? '';
    $referencia = $_POST['referencia'] ?? '';
    
    // Validar
    if ($cantidad <= 0) {
        $_SESSION['error'] = 'La cantidad debe ser mayor a cero';
        header("Location: dashboard_inventario.php");
        exit();
    }
    
    // Obtener stock actual
    $sql = "SELECT stock_quantity, nombre FROM productos WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        $_SESSION['error'] = 'Error en la consulta: ' . $conn->error;
        header("Location: dashboard_inventario.php");
        exit();
    }
    
    $stmt->bind_param("i", $producto_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $_SESSION['error'] = 'Producto no encontrado';
        header("Location: dashboard_inventario.php");
        exit();
    }
    
    $producto = $result->fetch_assoc();
    $stock_actual = $producto['stock_quantity'];
    $nombre_producto = $producto['nombre'];
    $nuevo_stock = $stock_actual;
    
    // Calcular nuevo stock
    if ($tipo == 'entrada') {
        $nuevo_stock = $stock_actual + $cantidad;
    } elseif ($tipo == 'salida') {
        if ($stock_actual < $cantidad) {
            $_SESSION['error'] = "Stock insuficiente para {$nombre_producto}. Stock actual: {$stock_actual}";
            header("Location: dashboard_inventario.php");
            exit();
        }
        $nuevo_stock = $stock_actual - $cantidad;
    } else {
        $_SESSION['error'] = 'Tipo de movimiento no válido';
        header("Location: dashboard_inventario.php");
        exit();
    }
    
    // Iniciar transacción
    $conn->begin_transaction();
    
    try {
        // Actualizar stock en productos
        $sql_update = "UPDATE productos SET stock_quantity = ?, fecha_actualizacion = NOW() WHERE id = ?";
        $stmt_update = $conn->prepare($sql_update);
        if (!$stmt_update) {
            throw new Exception("Error preparando update: " . $conn->error);
        }
        $stmt_update->bind_param("ii", $nuevo_stock, $producto_id);
        $stmt_update->execute();
        
        // Registrar movimiento (AJUSTADO A TU ESTRUCTURA DE BD)
        $usuario_nombre = $_SESSION['user_name'] ?? 'Desconocido';
        $tipo_movimiento = strtoupper($tipo) == 'ENTRADA' ? 'ENTRADA' : 'SALIDA';
        
        // Usar la estructura REAL de tu tabla movimientos_stock
        $sql_movimiento = "INSERT INTO movimientos_stock 
                          (producto_id, tipo, cantidad, motivo, usuario, fecha) 
                          VALUES (?, ?, ?, ?, ?, NOW())";
        $stmt_movimiento = $conn->prepare($sql_movimiento);
        if (!$stmt_movimiento) {
            throw new Exception("Error preparando movimiento: " . $conn->error);
        }
        
        $stmt_movimiento->bind_param("isiss", 
            $producto_id, 
            $tipo_movimiento, 
            $cantidad, 
            $motivo, 
            $usuario_nombre
        );
        $stmt_movimiento->execute();
        
        $conn->commit();
        $_SESSION['success'] = "Stock actualizado para {$nombre_producto}: {$stock_actual} → {$nuevo_stock}";
        
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = 'Error al actualizar stock: ' . $e->getMessage();
        error_log("Error en inventario: " . $e->getMessage());
    }
    
    header("Location: dashboard_inventario.php");
    exit();
}

// 2. AGREGAR NUEVO PRODUCTO (AJUSTADO)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['agregar_producto'])) {
    $nombre = trim($_POST['nombre']);
    $categoria = trim($_POST['categoria']);
    $descripcion = trim($_POST['descripcion'] ?? '');
    $precio = floatval($_POST['precio']);
    $stock_initial = intval($_POST['stock_initial'] ?? 0);
    $stock_minimo = intval($_POST['stock_minimo'] ?? 5);
    $costo = floatval($_POST['costo'] ?? 0);
    
    $sql = "INSERT INTO productos 
            (nombre, categoria, descripcion, precio, imagen, stock_quantity, stock_minimo, costo) 
            VALUES (?, ?, ?, ?, 'default.jpg', ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("sssdiii", $nombre, $categoria, $descripcion, $precio, $stock_initial, $stock_minimo, $costo);
        
        if ($stmt->execute()) {
            $producto_id = $conn->insert_id;
            
            // Registrar movimiento inicial si hay stock
            if ($stock_initial > 0) {
                $usuario_nombre = $_SESSION['user_name'] ?? 'Sistema';
                $sql_movimiento = "INSERT INTO movimientos_stock 
                                  (producto_id, tipo, cantidad, motivo, usuario, fecha) 
                                  VALUES (?, 'ENTRADA', ?, 'Stock inicial', ?, NOW())";
                $stmt_movimiento = $conn->prepare($sql_movimiento);
                if ($stmt_movimiento) {
                    $stmt_movimiento->bind_param("iis", $producto_id, $stock_initial, $usuario_nombre);
                    $stmt_movimiento->execute();
                }
            }
            
            $_SESSION['success'] = 'Producto agregado correctamente';
        } else {
            $_SESSION['error'] = 'Error al agregar producto: ' . $stmt->error;
        }
    } else {
        $_SESSION['error'] = 'Error en la consulta: ' . $conn->error;
    }
    
    header("Location: dashboard_inventario.php");
    exit();
}

// 3. ELIMINAR PRODUCTO (solo ADMIN)
if ($action == 'eliminar' && $producto_id > 0 && $_SESSION['user_role'] === 'ADMIN') {
    // Primero verificar si existe
    $sql_check = "SELECT nombre FROM productos WHERE id = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("i", $producto_id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    
    if ($result_check->num_rows > 0) {
        $producto = $result_check->fetch_assoc();
        $nombre_producto = $producto['nombre'];
        
        // Eliminar movimientos primero (por la foreign key)
        $sql_delete_mov = "DELETE FROM movimientos_stock WHERE producto_id = ?";
        $stmt_delete_mov = $conn->prepare($sql_delete_mov);
        $stmt_delete_mov->bind_param("i", $producto_id);
        $stmt_delete_mov->execute();
        
        // Eliminar producto
        $sql = "DELETE FROM productos WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $producto_id);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Producto '{$nombre_producto}' eliminado correctamente";
        } else {
            $_SESSION['error'] = 'Error al eliminar producto: ' . $stmt->error;
        }
    } else {
        $_SESSION['error'] = 'Producto no encontrado';
    }
    
    header("Location: dashboard_inventario.php");
    exit();
}

// Obtener productos con filtros (AJUSTADO)
$filtro_categoria = $_GET['categoria'] ?? '';
$filtro_stock = $_GET['stock'] ?? ''; // 'bajo', 'agotado', 'normal'
$busqueda = $_GET['busqueda'] ?? '';

// Construir consulta
$where_conditions = [];
$params = [];
$types = '';

if (!empty($busqueda)) {
    $where_conditions[] = "(nombre LIKE ? OR categoria LIKE ? OR descripcion LIKE ?)";
    $params[] = "%$busqueda%";
    $params[] = "%$busqueda%";
    $params[] = "%$busqueda%";
    $types .= 'sss';
}

if (!empty($filtro_categoria)) {
    $where_conditions[] = "categoria = ?";
    $params[] = $filtro_categoria;
    $types .= 's';
}

if ($filtro_stock == 'bajo') {
    $where_conditions[] = "stock_quantity <= stock_minimo AND stock_quantity > 0";
} elseif ($filtro_stock == 'agotado') {
    $where_conditions[] = "stock_quantity = 0";
} elseif ($filtro_stock == 'normal') {
    $where_conditions[] = "stock_quantity > stock_minimo";
}

$where_sql = '';
if (!empty($where_conditions)) {
    $where_sql = 'WHERE ' . implode(' AND ', $where_conditions);
}

// Obtener productos
$sql = "SELECT *, 
        (precio * stock_quantity) as valor_total,
        CASE 
            WHEN stock_quantity = 0 THEN 'agotado'
            WHEN stock_quantity <= stock_minimo THEN 'bajo'
            ELSE 'normal'
        END as estado_stock
        FROM productos $where_sql 
        ORDER BY stock_quantity ASC, nombre ASC";
$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

// Obtener categorías únicas
$sql_categorias = "SELECT DISTINCT categoria FROM productos WHERE categoria IS NOT NULL AND categoria != '' ORDER BY categoria";
$result_categorias = $conn->query($sql_categorias);

// Obtener estadísticas
$estadisticas = [];

// Total productos
$sql = "SELECT COUNT(*) as total FROM productos";
$result_total = $conn->query($sql);
$estadisticas['total_productos'] = $result_total->fetch_assoc()['total'];

// Valor total del inventario
$sql = "SELECT SUM(stock_quantity * precio) as valor_total FROM productos";
$result_valor = $conn->query($sql);
$estadisticas['valor_inventario'] = $result_valor->fetch_assoc()['valor_total'] ?? 0;

// Productos con bajo stock
$sql = "SELECT COUNT(*) as total FROM productos WHERE stock_quantity <= stock_minimo AND stock_quantity > 0";
$result_bajo = $conn->query($sql);
$estadisticas['bajo_stock'] = $result_bajo->fetch_assoc()['total'];

// Productos agotados
$sql = "SELECT COUNT(*) as total FROM productos WHERE stock_quantity = 0";
$result_agotado = $conn->query($sql);
$estadisticas['agotados'] = $result_agotado->fetch_assoc()['total'];

// Movimientos recientes (AJUSTADO a tu estructura)
$sql_movimientos = "SELECT m.*, p.nombre as producto_nombre, p.stock_quantity
                    FROM movimientos_stock m
                    JOIN productos p ON m.producto_id = p.id
                    ORDER BY m.fecha DESC
                    LIMIT 10";
$result_movimientos = $conn->query($sql_movimientos);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Control de Inventario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../estilos.css">
    <style>
        .inventory-card {
            border-left: 5px solid;
            transition: all 0.3s;
            margin-bottom: 15px;
        }
        .inventory-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .stock-normal { border-left-color: #28a745; }
        .stock-low { border-left-color: #ffc107; }
        .stock-out { border-left-color: #dc3545; }
        
        .stock-indicator {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 5px;
        }
        .indicator-normal { background: #28a745; }
        .indicator-low { background: #ffc107; }
        .indicator-out { background: #dc3545; }
        
        .badge-stock {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
        }
        .badge-stock-normal { background: #d4edda; color: #155724; }
        .badge-stock-low { background: #fff3cd; color: #856404; }
        .badge-stock-out { background: #f8d7da; color: #721c24; }
        
        .valor-inventario {
            font-size: 1.8rem;
            font-weight: bold;
            color: #4361ee;
        }
        
        .movimiento-entrada { color: #28a745; }
        .movimiento-salida { color: #dc3545; }
        .movimiento-ajuste { color: #6c757d; }
    </style>
</head>
<body>
    <div class="navbar-top">
        <h4 class="mb-0"><i class="fas fa-warehouse me-2"></i> Control de Inventario</h4>
        <div class="d-flex gap-2">
            <a href="index.php" class="btn btn-outline-primary">
                <i class="fas fa-arrow-left me-1"></i> Volver al Dashboard
            </a>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAgregarProducto">
                <i class="fas fa-plus me-1"></i> Nuevo Producto
            </button>
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalAjustarStock">
                <i class="fas fa-exchange-alt me-1"></i> Ajustar Stock
            </button>
        </div>
    </div>

    <!-- Mensajes -->
    <?php if(isset($_SESSION['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show m-3">
        <i class="fas fa-check-circle me-2"></i> <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>
    
    <?php if(isset($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show m-3">
        <i class="fas fa-exclamation-triangle me-2"></i> <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <!-- Estadísticas -->
    <div class="row mb-4 mx-3">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon text-primary"><i class="fas fa-boxes"></i></div>
                <div class="stat-value text-primary"><?php echo $estadisticas['total_productos']; ?></div>
                <div class="stat-label">Total Productos</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon text-success"><i class="fas fa-coins"></i></div>
                <div class="valor-inventario">S/ <?php echo number_format($estadisticas['valor_inventario'], 2); ?></div>
                <div class="stat-label">Valor del Inventario</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon text-warning"><i class="fas fa-exclamation-triangle"></i></div>
                <div class="stat-value text-warning"><?php echo $estadisticas['bajo_stock']; ?></div>
                <div class="stat-label">Bajo Stock</div>
                <small class="text-muted">Requieren atención</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon text-danger"><i class="fas fa-times-circle"></i></div>
                <div class="stat-value text-danger"><?php echo $estadisticas['agotados']; ?></div>
                <div class="stat-label">Agotados</div>
                <small class="text-muted">Sin stock</small>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card mb-4 mx-3">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Buscar producto</label>
                    <input type="text" class="form-control" name="busqueda" value="<?php echo htmlspecialchars($busqueda); ?>" 
                           placeholder="Nombre, categoría o descripción...">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Categoría</label>
                    <select class="form-select" name="categoria">
                        <option value="">Todas las categorías</option>
                        <?php while($categoria = $result_categorias->fetch_assoc()): ?>
                        <option value="<?php echo htmlspecialchars($categoria['categoria']); ?>" 
                                <?php echo ($filtro_categoria == $categoria['categoria']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($categoria['categoria']); ?>
                        </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Estado de Stock</label>
                    <select class="form-select" name="stock">
                        <option value="">Todos</option>
                        <option value="bajo" <?php echo ($filtro_stock == 'bajo') ? 'selected' : ''; ?>>Bajo Stock</option>
                        <option value="agotado" <?php echo ($filtro_stock == 'agotado') ? 'selected' : ''; ?>>Agotados</option>
                        <option value="normal" <?php echo ($filtro_stock == 'normal') ? 'selected' : ''; ?>>Stock Normal</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <div class="d-grid w-100">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search me-1"></i> Filtrar
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabla de Inventario -->
    <div class="card mx-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-boxes me-2"></i> Lista de Productos</h5>
            <div class="small text-muted">
                Mostrando <?php echo $result->num_rows; ?> productos
            </div>
        </div>
        <div class="card-body">
            <?php if($result->num_rows > 0): ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th width="5%">ID</th>
                            <th width="30%">Producto</th>
                            <th width="15%">Categoría</th>
                            <th width="10%">Precio</th>
                            <th width="15%">Stock</th>
                            <th width="10%">Estado</th>
                            <th width="15%">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($producto = $result->fetch_assoc()): 
                            $estado_stock = $producto['estado_stock'];
                            $clase_stock = 'stock-' . $estado_stock;
                            $badge_class = 'badge-stock-' . $estado_stock;
                            $indicator_class = 'indicator-' . $estado_stock;
                            $valor_producto = $producto['precio'] * $producto['stock_quantity'];
                        ?>
                        <tr class="inventory-card <?php echo $clase_stock; ?>">
                            <td>#<?php echo $producto['id']; ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($producto['nombre']); ?></strong>
                                <?php if($producto['descripcion']): ?>
                                <br><small class="text-muted"><?php echo substr(htmlspecialchars($producto['descripcion']), 0, 50); ?>...</small>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($producto['categoria']); ?></td>
                            <td>
                                <strong>S/ <?php echo number_format($producto['precio'], 2); ?></strong>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <span class="stock-indicator <?php echo $indicator_class; ?>"></span>
                                    <div>
                                        <strong><?php echo $producto['stock_quantity']; ?></strong> unidades<br>
                                        <small class="text-muted">Mín: <?php echo $producto['stock_minimo']; ?></small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge-stock <?php echo $badge_class; ?>">
                                    <?php echo ucfirst($estado_stock); ?>
                                </span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary" 
                                            onclick="ajustarStock(<?php echo $producto['id']; ?>, '<?php echo addslashes($producto['nombre']); ?>')">
                                        <i class="fas fa-exchange-alt"></i>
                                    </button>
                                    
                                    <button class="btn btn-outline-info" 
                                            onclick="verMovimientos(<?php echo $producto['id']; ?>)">
                                        <i class="fas fa-history"></i>
                                    </button>
                                    
                                    <button class="btn btn-outline-warning" 
                                            onclick="editarProducto(<?php echo $producto['id']; ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    
                                    <?php if($_SESSION['user_role'] === 'ADMIN'): ?>
                                    <button class="btn btn-outline-danger" 
                                            onclick="eliminarProducto(<?php echo $producto['id']; ?>, '<?php echo addslashes($producto['nombre']); ?>')">
                                        <i class="fas fa-trash"></i>
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
                <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                <h5>No hay productos</h5>
                <p class="text-muted">No se encontraron productos con los filtros aplicados.</p>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAgregarProducto">
                    <i class="fas fa-plus me-1"></i> Agregar Primer Producto
                </button>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Movimientos Recientes -->
    <div class="card mt-4 mx-3 mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-history me-2"></i> Movimientos Recientes</h5>
        </div>
        <div class="card-body">
            <?php if($result_movimientos && $result_movimientos->num_rows > 0): ?>
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Producto</th>
                            <th>Tipo</th>
                            <th>Cantidad</th>
                            <th>Stock Actual</th>
                            <th>Motivo</th>
                            <th>Usuario</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($movimiento = $result_movimientos->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo date('d/m/Y H:i', strtotime($movimiento['fecha'])); ?></td>
                            <td><?php echo htmlspecialchars($movimiento['producto_nombre']); ?></td>
                            <td>
                                <?php if($movimiento['tipo'] == 'ENTRADA'): ?>
                                <span class="movimiento-entrada"><i class="fas fa-arrow-down"></i> Entrada</span>
                                <?php elseif($movimiento['tipo'] == 'SALIDA'): ?>
                                <span class="movimiento-salida"><i class="fas fa-arrow-up"></i> Salida</span>
                                <?php else: ?>
                                <span class="movimiento-ajuste"><i class="fas fa-sync-alt"></i> Ajuste</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo $movimiento['cantidad']; ?></td>
                            <td><?php echo $movimiento['stock_quantity']; ?></td>
                            <td><?php echo htmlspecialchars($movimiento['motivo']); ?></td>
                            <td><?php echo htmlspecialchars($movimiento['usuario']); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="text-center py-3">
                <p class="text-muted">No hay movimientos registrados.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- MODAL: Agregar Producto -->
    <div class="modal fade" id="modalAgregarProducto" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i> Agregar Nuevo Producto</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="dashboard_inventario.php">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nombre del Producto *</label>
                                <input type="text" class="form-control" name="nombre" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Categoría *</label>
                                <select class="form-select" name="categoria" required>
                                    <option value="">Seleccionar categoría</option>
                                    <option value="alimento">Alimento</option>
                                    <option value="snack">Snack</option>
                                    <option value="medicina">Medicina</option>
                                    <option value="accesorio">Accesorio</option>
                                    <option value="juguete">Juguete</option>
                                    <option value="higiene">Higiene</option>
                                    <option value="Otros">Otros</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Precio de Venta (S/) *</label>
                                <input type="number" class="form-control" name="precio" step="0.01" min="0.01" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Costo (S/)</label>
                                <input type="number" class="form-control" name="costo" step="0.01" min="0">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Stock Inicial</label>
                                <input type="number" class="form-control" name="stock_initial" value="0" min="0">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Stock Mínimo</label>
                                <input type="number" class="form-control" name="stock_minimo" value="5" min="1">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Descripción</label>
                            <textarea class="form-control" name="descripcion" rows="3" 
                                      placeholder="Descripción del producto..."></textarea>
                        </div>
                        
                        <div class="text-end">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" name="agregar_producto" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Guardar Producto
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL: Ajustar Stock -->
    <div class="modal fade" id="modalAjustarStock" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-warning text-white">
                    <h5 class="modal-title"><i class="fas fa-exchange-alt me-2"></i> Ajustar Stock</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="dashboard_inventario.php">
                        <div class="mb-3">
                            <label class="form-label">Seleccionar Producto *</label>
                            <select class="form-select" id="selectProducto" name="producto_id" required>
                                <option value="">Seleccionar producto...</option>
                                <?php 
                                // Resetear el puntero del resultado
                                $result->data_seek(0);
                                while($p = $result->fetch_assoc()): ?>
                                <option value="<?php echo $p['id']; ?>" data-stock="<?php echo $p['stock_quantity']; ?>">
                                    <?php echo htmlspecialchars($p['nombre']); ?> (Stock: <?php echo $p['stock_quantity']; ?>)
                                </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Tipo de Movimiento *</label>
                            <select class="form-select" name="tipo" id="tipoMovimiento" required>
                                <option value="entrada">Entrada de Stock (+)</option>
                                <option value="salida">Salida de Stock (-)</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Cantidad *</label>
                            <input type="number" class="form-control" name="cantidad" id="cantidadMovimiento" min="1" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Motivo *</label>
                            <select class="form-select" name="motivo" required>
                                <option value="Compra">Compra a proveedor</option>
                                <option value="Devolución">Devolución de cliente</option>
                                <option value="Ajuste">Ajuste manual</option>
                                <option value="Donación">Donación recibida</option>
                                <option value="Merma">Merma/Desperdicio</option>
                                <option value