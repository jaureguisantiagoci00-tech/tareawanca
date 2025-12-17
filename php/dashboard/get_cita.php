<?php
session_start();
require_once '../config.php';

// Verificar permisos
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] !== 'ADMIN' && $_SESSION['user_role'] !== 'VENDEDOR')) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
    exit();
}

$cita_id = $_GET['id'] ?? 0;

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Obtener cita con información del usuario
$sql = "SELECT c.*, u.nombre_completo, u.email, u.telefono 
        FROM citas c 
        JOIN usuarios u ON c.usuario_id = u.id 
        WHERE c.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $cita_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Cita no encontrada']);
    exit();
}

$cita = $result->fetch_assoc();

header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'cita' => $cita
]);

$conn->close();
?>