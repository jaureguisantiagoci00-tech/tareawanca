<?php
require_once 'db_config.php';
require_once 'helpers.php'; 

// Recibir la ID de sesión del cliente (viene por GET en la URL)
$userId = $_GET['user_id'] ?? '';

if (empty($userId)) {
    sendResponse(false, [], "ID de usuario (sesión) faltante.");
}

try {
    // Consulta SQL para obtener los ítems del carrito de ese usuario
    $sql = "SELECT product_id as id, name, price, quantity 
            FROM user_cart 
            WHERE user_id = :userId";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':userId', $userId);
    $stmt->execute();
    
    $cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Devolver la lista de items al JavaScript
    sendResponse(true, $cart_items, "Carrito cargado correctamente.");

} catch (PDOException $e) {
    sendResponse(false, [], "Error de base de datos: " . $e->getMessage());
}
?>