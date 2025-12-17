<?php
session_start();
require_once '../config.php';

// Verificar permisos
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] !== 'ADMIN' && $_SESSION['user_role'] !== 'VENDEDOR')) {
    die('Acceso denegado');
}

$producto_id = $_GET['id'] ?? 0;

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Obtener información del producto
$sql = "SELECT * FROM productos WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $producto_id);
$stmt->execute();
$result = $stmt->get_result();
$producto = $result->fetch_assoc();

if (!$producto) {
    die('Producto no encontrado');
}

// Obtener movimientos
$sql = "SELECT m.*, u.nombre_completo as usuario_nombre
        FROM movimientos_stock m
        LEFT JOIN usuarios u ON m.usuario = u.id
        WHERE m.producto_id = ?
        ORDER BY m.fecha_movimiento DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $producto_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Movimientos - <?php echo $producto['nombre']; ?></title>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { margin: 0; padding: 0; }
        }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .header h1 {
            margin: 0;
            font-size: 18px;
        }
        
        .header .subtitle {
            color: #666;
            margin: 5px 0;
        }
        
        .info-box {
            background: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 20px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        
        .entrada { color: #28a745; }
        .salida { color: #dc3545; }
        
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="no-print" style="text-align: right; margin-bottom: 20px;">
        <button onclick="window.print()">Imprimir</button>
        <button onclick="window.close()">Cerrar</button>
    </div>
    
    <div class="header">
        <h1>Puphub - Reporte de Movimientos de Stock</h1>
        <div class="subtitle">Producto: <?php echo $producto['nombre']; ?></div>
        <div class="subtitle">Generado el: <?php echo date('d/m/Y H:i:s'); ?></div>
    </div>
    
    <div class="info-box">
        <strong>Información del Producto:</strong><br>
        ID: #<?php echo $producto['id']; ?> | 
        Categoría: <?php echo $producto['categoria']; ?> | 
        Precio: S/ <?php echo number_format($producto['precio'], 2); ?> | 
        Stock Actual: <?php echo $producto['stock_quantity']; ?> unidades | 
        Stock Mínimo: <?php echo $producto['stock_minimo']; ?>
    </div>
    
    <h3>Historial de Movimientos</h3>
    <table>
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Tipo</th>
                <th>Cantidad</th>
                <th>Stock Anterior</th>
                <th>Stock Nuevo</th>
                <th>Motivo</th>
                <th>Usuario</th>
            </tr>
        </thead>
        <tbody>
            <?php while($mov = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo date('d/m/Y H:i', strtotime($mov['fecha_movimiento'])); ?></td>
                <td class="<?php echo $mov['tipo']; ?>">
                    <?php echo $mov['tipo'] == 'entrada' ? 'Entrada' : 'Salida'; ?>
                </td>
                <td><?php echo $mov['cantidad']; ?></td>
                <td><?php echo $mov['stock_anterior']; ?></td>
                <td><?php echo $mov['stock_nuevo']; ?></td>
                <td><?php echo $mov['motivo']; ?></td>
                <td><?php echo $mov['usuario_nombre'] ?? 'Sistema'; ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    
    <div class="footer">
        <p>Puphub - Sistema de Gestión Veterinaria</p>
        <p>Reporte generado automáticamente por el sistema</p>
    </div>
</body>
</html>
<?php
$conn->close();
?>