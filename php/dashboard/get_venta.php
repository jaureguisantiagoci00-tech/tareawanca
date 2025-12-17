<?php
session_start();
require_once '../config.php';

// Verificar permisos
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] !== 'ADMIN' && $_SESSION['user_role'] !== 'VENDEDOR')) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
    exit();
}

$venta_id = $_GET['id'] ?? 0;

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Obtener venta
$sql = "SELECT v.*, u.nombre_completo, u.email, u.telefono, u.id as usuario_id 
        FROM ventas v 
        LEFT JOIN usuarios u ON v.usuario_id = u.id 
        WHERE v.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $venta_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Venta no encontrada']);
    exit();
}

$venta = $result->fetch_assoc();

// Obtener detalles de productos
$sql_detalles = "SELECT vd.*, p.nombre, p.categoria 
                 FROM venta_detalles vd 
                 JOIN productos p ON vd.producto_id = p.id 
                 WHERE vd.venta_id = ?";
$stmt_detalles = $conn->prepare($sql_detalles);
$stmt_detalles->bind_param("i", $venta_id);
$stmt_detalles->execute();
$result_detalles = $stmt_detalles->get_result();
$detalles = [];

while($detalle = $result_detalles->fetch_assoc()) {
    $detalles[] = $detalle;
}

// Preparar datos de usuario
$usuario = null;
if ($venta['usuario_id']) {
    $usuario = [
        'id' => $venta['usuario_id'],
        'nombre_completo' => $venta['nombre_completo'],
        'email' => $venta['email'],
        'telefono' => $venta['telefono']
    ];
}

header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'venta' => $venta,
    'detalles' => $detalles,
    'usuario' => $usuario
]);

$conn->close();
?>