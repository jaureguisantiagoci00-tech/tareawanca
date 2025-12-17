<?php
require_once 'db_config.php'; 
require_once 'helpers.php'; 

try {
    // CORRECCIÓN: Usar 'productos' en lugar de 'products'
    $sql = "SELECT id as product_id, stock_quantity FROM productos";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    
    $stock_data = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $stock_data[$row['product_id']] = (int)$row['stock_quantity'];
    }

    sendResponse(true, $stock_data, "Stock cargado correctamente.");

} catch (PDOException $e) {
    sendResponse(false, [], "Error al consultar la base de datos: " . $e->getMessage());
}
?>