<?php
// dashboard/dashboard_productos.php
session_start();

// HABILITAR ERRORES
error_reporting(E_ALL);
ini_set('display_errors', 1);

// CONFIGURACIÓN DIRECTA (AJUSTA SEGÚN TU ENTORNO)
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'ProyectoVeterinaria');

// VERIFICAR SESIÓN
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php?error=login_required");
    exit();
}

// CONEXIÓN
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// PROCESAR AGREGAR PRODUCTO (POST AL MISMO ARCHIVO)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['agregar_producto'])) {
    $nombre = trim($_POST['nombre']);
    $categoria = trim($_POST['categoria']);
    $descripcion = trim($_POST['descripcion'] ?? '');
    $precio = floatval($_POST['precio']);
    $stock = intval($_POST['stock_quantity']);
    $stock_minimo = intval($_POST['stock_minimo'] ?? 10);
    
    // Insertar en la base de datos
    $sql = "INSERT INTO productos (nombre, categoria, descripcion, precio, stock_quantity, stock_minimo, fecha_actualizacion) 
            VALUES (?, ?, ?, ?, ?, ?, NOW())";
    
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("sssdii", $nombre, $categoria, $descripcion, $precio, $stock, $stock_minimo);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = '✅ Producto agregado correctamente';
            
            // Registrar movimiento de stock inicial
            $producto_id = $conn->insert_id;
            if ($stock > 0) {
                $usuario_nombre = $_SESSION['user_name'] ?? 'Sistema';
                $sql_mov = "INSERT INTO movimientos_stock (producto_id, tipo, cantidad, motivo, usuario, fecha) 
                           VALUES (?, 'ENTRADA', ?, 'Stock inicial', ?, NOW())";
                $stmt_mov = $conn->prepare($sql_mov);
                if ($stmt_mov) {
                    $stmt_mov->bind_param("iis", $producto_id, $stock, $usuario_nombre);
                    $stmt_mov->execute();
                }
            }
        } else {
            $_SESSION['error'] = '❌ Error: ' . $stmt->error;
        }
    }
    
    // Redirigir al mismo archivo
    header("Location: dashboard_productos.php");
    exit();
}

// PROCESAR ELIMINAR PRODUCTO
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $product_id = intval($_GET['id']);
    
    if ($_SESSION['user_role'] === 'ADMIN') {
        $sql = "DELETE FROM productos WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $product_id);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = '✅ Producto eliminado';
        } else {
            $_SESSION['error'] = '❌ Error al eliminar';
        }
    } else {
        $_SESSION['error'] = '❌ Solo ADMIN puede eliminar';
    }
    
    header("Location: dashboard_productos.php");
    exit();
}

// BÚSQUEDA
$busqueda = $_GET['busqueda'] ?? '';
$sql = "SELECT * FROM productos";

if (!empty($busqueda)) {
    $sql .= " WHERE nombre LIKE '%" . $conn->real_escape_string($busqueda) . "%' 
              OR categoria LIKE '%" . $conn->real_escape_string($busqueda) . "%'";
}

$sql .= " ORDER BY id DESC LIMIT 100";
$result = $conn->query($sql);

// ESTADÍSTICAS
$sql_stats = "SELECT 
    COUNT(*) as total,
    SUM(stock_quantity) as total_stock,
    SUM(precio * stock_quantity) as valor_total,
    SUM(CASE WHEN stock_quantity <= stock_minimo THEN 1 ELSE 0 END) as bajo_stock
    FROM productos";
    
$stats_result = $conn->query($sql_stats);
$stats = $stats_result ? $stats_result->fetch_assoc() : [
    'total' => 0, 
    'total_stock' => 0, 
    'valor_total' => 0,
    'bajo_stock' => 0
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Productos - Veterinaria</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: #f8f9fa;
            padding: 20px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .header {
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            color: white;
            padding: 25px;
            border-radius: 15px;
            margin-bottom: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
            transition: transform 0.3s;
            border-left: 5px solid;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .stat-icon {
            font-size: 2.5rem;
            margin-bottom: 15px;
            opacity: 0.8;
        }
        .stat-value {
            font-size: 2.2rem;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .stat-label {
            color: #6c757d;
            font-size: 0.95rem;
        }
        .main-container {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        .product-img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
            border: 2px solid #dee2e6;
        }
        .btn-action {
            width: 36px;
            height: 36px;
            padding: 0;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin: 2px;
        }
        .badge-custom {
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: 500;
        }
        .search-box {
            background: white;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
        }
        .alert-custom {
            border-radius: 10px;
            border: none;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1><i class="fas fa-paw me-2"></i> Gestión de Productos</h1>
                <p class="mb-0">Bienvenido, <strong><?php echo $_SESSION['user_name'] ?? 'Usuario'; ?></strong> 
                (<?php echo $_SESSION['user_role'] ?? 'VENDEDOR'; ?>)</p>
            </div>
            <div>
                <a href="index.php" class="btn btn-light">
                    <i class="fas fa-arrow-left me-1"></i> Volver al Dashboard
                </a>
            </div>
        </div>
    </div>

    <!-- Mensajes -->
    <?php if(isset($_SESSION['success'])): ?>
    <div class="alert alert-success alert-custom alert-dismissible fade show">
        <i class="fas fa-check-circle me-2"></i>
        <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>
    
    <?php if(isset($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-custom alert-dismissible fade show">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <!-- Estadísticas -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stat-card" style="border-left-color: #4361ee;">
                <div class="stat-icon text-primary">
                    <i class="fas fa-box"></i>
                </div>
                <div class="stat-value text-primary">
                    <?php echo $stats['total']; ?>
                </div>
                <div class="stat-label">Total Productos</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card" style="border-left-color: #06d6a0;">
                <div class="stat-icon text-success">
                    <i class="fas fa-cubes"></i>
                </div>
                <div class="stat-value text-success">
                    <?php echo $stats['total_stock']; ?>
                </div>
                <div class="stat-label">Unidades en Stock</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card" style="border-left-color: #ff9e00;">
                <div class="stat-icon text-warning">
                    <i class="fas fa-coins"></i>
                </div>
                <div class="stat-value text-warning">
                    S/ <?php echo number_format($stats['valor_total'], 2); ?>
                </div>
                <div class="stat-label">Valor del Inventario</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card" style="border-left-color: #ef476f;">
                <div class="stat-icon text-danger">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="stat-value text-danger">
                    <?php echo $stats['bajo_stock']; ?>
                </div>
                <div class="stat-label">Productos Bajo Stock</div>
            </div>
        </div>
    </div>

    <!-- Búsqueda y Botón Agregar -->
    <div class="search-box">
        <div class="row align-items-center">
            <div class="col-md-8">
                <form method="GET" class="d-flex gap-2">
                    <input type="text" class="form-control form-control-lg" 
                           name="busqueda" 
                           value="<?php echo htmlspecialchars($busqueda); ?>" 
                           placeholder="Buscar productos por nombre o categoría...">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-search"></i>
                    </button>
                    <?php if(!empty($busqueda)): ?>
                    <a href="dashboard_productos.php" class="btn btn-secondary btn-lg">
                        <i class="fas fa-times"></i>
                    </a>
                    <?php endif; ?>
                </form>
            </div>
            <div class="col-md-4 text-end">
                <button class="btn btn-success btn-lg" data-bs-toggle="modal" data-bs-target="#modalAgregar">
                    <i class="fas fa-plus me-2"></i> Nuevo Producto
                </button>
            </div>
        </div>
    </div>

    <!-- Tabla de Productos -->
    <div class="main-container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3><i class="fas fa-boxes me-2"></i> Lista de Productos</h3>
            <span class="badge bg-primary"><?php echo $result->num_rows; ?> productos</span>
        </div>

        <?php if($result->num_rows > 0): ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Producto</th>
                        <th>Categoría</th>
                        <th>Precio</th>
                        <th>Stock</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($producto = $result->fetch_assoc()): 
                        $estado = '';
                        $badge_class = '';
                        if ($producto['stock_quantity'] == 0) {
                            $estado = 'AGOTADO';
                            $badge_class = 'bg-danger';
                        } elseif ($producto['stock_quantity'] <= $producto['stock_minimo']) {
                            $estado = 'BAJO STOCK';
                            $badge_class = 'bg-warning text-dark';
                        } else {
                            $estado = 'DISPONIBLE';
                            $badge_class = 'bg-success';
                        }
                    ?>
                    <tr>
                        <td><strong>#<?php echo $producto['id']; ?></strong></td>
                        <td>
                            <div class="d-flex align-items-center">
                                <?php if(!empty($producto['imagen']) && file_exists('../imagenes/' . $producto['imagen'])): ?>
                                <img src="../imagenes/<?php echo $producto['imagen']; ?>" 
                                     class="product-img me-3">
                                <?php else: ?>
                                <div class="product-img me-3 bg-light d-flex align-items-center justify-content-center">
                                    <i class="fas fa-box text-secondary"></i>
                                </div>
                                <?php endif; ?>
                                <div>
                                    <strong><?php echo htmlspecialchars($producto['nombre']); ?></strong>
                                    <?php if(!empty($producto['descripcion'])): ?>
                                    <br><small class="text-muted"><?php echo substr($producto['descripcion'], 0, 50); ?>...</small>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-info badge-custom">
                                <i class="fas fa-tag me-1"></i>
                                <?php echo $producto['categoria']; ?>
                            </span>
                        </td>
                        <td>
                            <span class="fw-bold text-success">
                                S/ <?php echo number_format($producto['precio'], 2); ?>
                            </span>
                        </td>
                        <td>
                            <div class="fw-bold"><?php echo $producto['stock_quantity']; ?> und.</div>
                            <small class="text-muted">Mín: <?php echo $producto['stock_minimo']; ?></small>
                        </td>
                        <td>
                            <span class="badge <?php echo $badge_class; ?> badge-custom">
                                <?php echo $estado; ?>
                            </span>
                        </td>
                        <td>
                            <div class="btn-group">
                                <button class="btn btn-outline-primary btn-action"
                                        onclick="editarProducto(<?php echo $producto['id']; ?>)"
                                        title="Editar">
                                    <i class="fas fa-edit"></i>
                                </button>
                                
                                <?php if($_SESSION['user_role'] === 'ADMIN'): ?>
                                <button class="btn btn-outline-danger btn-action"
                                        onclick="eliminarProducto(<?php echo $producto['id']; ?>, '<?php echo addslashes($producto['nombre']); ?>')"
                                        title="Eliminar">
                                    <i class="fas fa-trash"></i>
                                </button>
                                <?php endif; ?>
                                
                                <button class="btn btn-outline-info btn-action"
                                        onclick="verDetalles(<?php echo $producto['id']; ?>)"
                                        title="Detalles">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="text-center py-5">
            <i class="fas fa-box-open fa-4x text-muted mb-4"></i>
            <h4>No hay productos registrados</h4>
            <p class="text-muted mb-4">Comienza agregando tu primer producto</p>
            <button class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#modalAgregar">
                <i class="fas fa-plus me-2"></i> Agregar Primer Producto
            </button>
        </div>
        <?php endif; ?>
    </div>

    <!-- MODAL: Agregar Producto -->
    <div class="modal fade" id="modalAgregar" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i> Nuevo Producto</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <!-- FORMUARIO CORREGIDO: action apunta al MISMO ARCHIVO -->
                    <form method="POST" action="dashboard_productos.php" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nombre del Producto *</label>
                                <input type="text" class="form-control" name="nombre" required 
                                       placeholder="Ej: Dog Chow Adulto 3KG">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Categoría *</label>
                                <select class="form-select" name="categoria" required>
                                    <option value="">Seleccionar...</option>
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
                        
                        <div class="mb-3">
                            <label class="form-label">Descripción</label>
                            <textarea class="form-control" name="descripcion" rows="3" 
                                      placeholder="Descripción del producto..."></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Precio (S/) *</label>
                                <input type="number" class="form-control" name="precio" 
                                       step="0.01" min="0.01" required 
                                       placeholder="0.00">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Stock Inicial *</label>
                                <input type="number" class="form-control" name="stock_quantity" 
                                       min="0" required value="0">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Stock Mínimo</label>
                                <input type="number" class="form-control" name="stock_minimo" 
                                       min="1" value="10">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Imagen (opcional)</label>
                            <input type="file" class="form-control" name="imagen" accept="image/*">
                            <small class="text-muted">Formatos: JPG, PNG, GIF. Max: 2MB</small>
                        </div>
                        
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Los campos marcados con * son obligatorios.
                        </div>
                        
                        <div class="text-end">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                Cancelar
                            </button>
                            <button type="submit" name="agregar_producto" class="btn btn-success">
                                <i class="fas fa-save me-1"></i> Guardar Producto
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
    function eliminarProducto(id, nombre) {
        if (confirm(`¿Eliminar el producto "${nombre}"?\n\nEsta acción no se puede deshacer.`)) {
            window.location.href = `dashboard_productos.php?action=delete&id=${id}`;
        }
    }
    
    function editarProducto(id) {
        alert(`Editar producto ID: ${id}\n\n(Esta funcionalidad estará disponible en la próxima actualización)`);
    }
    
    function verDetalles(id) {
        alert(`Detalles del producto ID: ${id}\n\n(Esta funcionalidad estará disponible en la próxima actualización)`);
    }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
$conn->close();
?>