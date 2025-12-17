<?php
session_start();
require_once '../config.php';

// Solo ADMIN puede eliminar
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'ADMIN') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
    exit();
}

$factura_id = $_GET['id'] ?? 0;

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Obtener información del archivo
$sql = "SELECT archivo FROM facturas_externas WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $factura_id);
$stmt->execute();
$result = $stmt->get_result();
$factura = $result->fetch_assoc();

if (!$factura) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Factura no encontrada']);
    exit();
}

// Eliminar archivo físico
$ruta_archivo = '../uploads/facturas_externas/' . $factura['archivo'];
if (file_exists($ruta_archivo)) {
    unlink($ruta_archivo);
}

// Eliminar de la BD
$sql_delete = "DELETE FROM facturas_externas WHERE id = ?";
$stmt_delete = $conn->prepare($sql_delete);
$stmt_delete->bind_param("i", $factura_id);
$success = $stmt_delete->execute();

header('Content-Type: application/json');
echo json_encode([
    'success' => $success,
    'message' => $success ? 'Factura eliminada' : 'Error al eliminar'
]);

$conn->close();
?>