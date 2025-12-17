<?php
session_start();
require_once '../config.php';

// Solo ADMIN puede ver detalles
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'ADMIN') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
    exit();
}

$user_id = $_GET['id'] ?? 0;

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Obtener datos del usuario
$sql = "SELECT * FROM usuarios WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$usuario = $result->fetch_assoc();

// Obtener estadísticas
$estadisticas = [];

// Total de citas
$sql = "SELECT COUNT(*) as total FROM citas WHERE usuario_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$estadisticas['total_citas'] = $result->fetch_assoc()['total'];

// Total de compras
$sql = "SELECT COUNT(*) as total, COALESCE(SUM(total), 0) as total_gastado FROM ventas WHERE usuario_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$compras = $result->fetch_assoc();
$estadisticas['total_compras'] = $compras['total'];
$estadisticas['total_gastado'] = $compras['total_gastado'];

// Última compra
$sql = "SELECT fecha FROM ventas WHERE usuario_id = ? ORDER BY fecha DESC LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$ultima = $result->fetch_assoc();
$estadisticas['ultima_visita'] = $ultima ? date('d/m/Y', strtotime($ultima['fecha'])) : 'Nunca';

header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'usuario' => $usuario,
    'estadisticas' => $estadisticas
]);

$conn->close();
?>