<?php
// php/dashboard/get_movimientos.php
session_start();
require_once '../config.php';

// Verificar permisos
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] !== 'ADMIN' && $_SESSION['user_role'] !== 'VENDEDOR')) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
    exit();
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
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Producto no encontrado']);
    exit();
}

// Obtener movimientos
$sql = "SELECT m.*, u.nombre_completo as usuario_nombre
        FROM movimientos_stock m
        LEFT JOIN usuarios u ON m.usuario = u.id
        WHERE m.producto_id = ?
        ORDER BY m.fecha_movimiento DESC
        LIMIT 100";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $producto_id);
$stmt->execute();
$result = $stmt->get_result();
$movimientos = [];

while($mov = $result->fetch_assoc()) {
    $movimientos[] = $mov;
}

header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'producto' => $producto,
    'movimientos' => $movimientos
]);

$conn->close();
?>