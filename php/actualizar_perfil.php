<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    die('No autorizado');
}

$user_id = $_SESSION['user_id'];
$nombre = $_POST['nombre_completo'] ?? '';
$email = $_POST['email'] ?? '';
$telefono = $_POST['telefono'] ?? '';
$nueva_password = $_POST['nueva_password'] ?? '';

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Actualizar datos básicos
$sql = "UPDATE usuarios SET nombre_completo = ?, email = ?, telefono = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sssi", $nombre, $email, $telefono, $user_id);

if ($stmt->execute()) {
    // Actualizar contraseña si se proporcionó
    if (!empty($nueva_password)) {
        $password_hash = password_hash($nueva_password, PASSWORD_DEFAULT);
        $sql_pass = "UPDATE usuarios SET contrasena_hash = ? WHERE id = ?";
        $stmt_pass = $conn->prepare($sql_pass);
        $stmt_pass->bind_param("si", $password_hash, $user_id);
        $stmt_pass->execute();
    }
    
    $_SESSION['success'] = 'Perfil actualizado correctamente';
    $_SESSION['user_name'] = $nombre;
} else {
    $_SESSION['error'] = 'Error al actualizar perfil';
}

$stmt->close();
$conn->close();

header("Location: ../perfil.php");
exit();
?>