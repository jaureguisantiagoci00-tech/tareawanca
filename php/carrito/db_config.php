<?php
// Ajustar rutas para incluir config.php principal
require_once '../config.php';

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // NO mostrar errores aquí
} catch(PDOException $e) {
    // Enviar JSON de error en lugar de die()
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false, 
        'message' => "Error de conexión a la base de datos"
    ]);
    exit;
}
?>