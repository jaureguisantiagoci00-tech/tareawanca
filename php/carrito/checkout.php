<?php
require_once 'db_config.php';
require_once 'helpers.php'; 

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(false, "Método no permitido.");
}

session_start();
$userId = $_SESSION['user_id'] ?? '';

if (empty($userId)) {
    sendResponse(false, [], "Debes iniciar sesión para finalizar la compra.");
}

try {
    $pdo->beginTransaction();

    // 1. Obtener items del carrito
    $cartSql = "SELECT product_id, quantity, price FROM user_cart WHERE user_id = :userId";
    $cartStmt = $pdo->prepare($cartSql);
    $cartStmt->bindParam(':userId', $userId);
    $cartStmt->execute();
    $cartItems = $cartStmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($cartItems)) {
        $pdo->rollBack();
        sendResponse(false, [], "Tu carrito está vacío.");
    }
    
    $totalVenta = 0;
    
    // 2. Verificar stock y calcular total
    foreach ($cartItems as $item) {
        $productId = $item['product_id'];
        $quantity = $item['quantity'];
        $price = $item['price'];
        
        // Verificar stock
        $stockSql = "SELECT stock_quantity FROM productos WHERE id = :productId";
        $stockStmt = $pdo->prepare($stockSql);
        $stockStmt->bindParam(':productId', $productId);
        $stockStmt->execute();
        $stock = $stockStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$stock || $stock['stock_quantity'] < $quantity) {
            $pdo->rollBack();
            sendResponse(false, [], "Stock insuficiente para el producto ID: $productId");
        }
        
        $totalVenta += ($price * $quantity);
    }
    
    // 3. Crear registro de venta
    $ventaSql = "INSERT INTO ventas (usuario_id, total, estado) VALUES (:usuario_id, :total, 'COMPLETADA')";
    $ventaStmt = $pdo->prepare($ventaSql);
    $ventaStmt->bindParam(':usuario_id', $userId);
    $ventaStmt->bindParam(':total', $totalVenta);
    $ventaStmt->execute();
    $ventaId = $pdo->lastInsertId();
    
    // 4. Procesar cada item: descontar stock y crear detalle
    foreach ($cartItems as $item) {
        $productId = $item['product_id'];
        $quantity = $item['quantity'];
        $price = $item['price'];
        $subtotal = $price * $quantity;
        
        // Descontar stock
        $updateSql = "UPDATE productos SET stock_quantity = stock_quantity - :quantity WHERE id = :productId";
        $updateStmt = $pdo->prepare($updateSql);
        $updateStmt->bindParam(':quantity', $quantity);
        $updateStmt->bindParam(':productId', $productId);
        $updateStmt->execute();
        
        // Registrar movimiento de stock
        $movimientoSql = "INSERT INTO movimientos_stock (producto_id, tipo, cantidad, motivo, usuario) 
                         VALUES (:producto_id, 'SALIDA', :cantidad, 'VENTA', 'Sistema')";
        $movimientoStmt = $pdo->prepare($movimientoSql);
        $movimientoStmt->bindParam(':producto_id', $productId);
        $movimientoStmt->bindParam(':cantidad', $quantity);
        $movimientoStmt->execute();
        
        // Crear detalle de venta
        $detalleSql = "INSERT INTO venta_detalles (venta_id, producto_id, cantidad, precio_unitario, subtotal) 
                      VALUES (:venta_id, :producto_id, :cantidad, :precio_unitario, :subtotal)";
        $detalleStmt = $pdo->prepare($detalleSql);
        $detalleStmt->bindParam(':venta_id', $ventaId);
        $detalleStmt->bindParam(':producto_id', $productId);
        $detalleStmt->bindParam(':cantidad', $quantity);
        $detalleStmt->bindParam(':precio_unitario', $price);
        $detalleStmt->bindParam(':subtotal', $subtotal);
        $detalleStmt->execute();
    }
    
    // 5. Vaciar carrito
    $deleteSql = "DELETE FROM user_cart WHERE user_id = :userId";
    $deleteStmt = $pdo->prepare($deleteSql);
    $deleteStmt->bindParam(':userId', $userId);
    $deleteStmt->execute();
    
    $pdo->commit();
    sendResponse(true, [], "✅ Compra finalizada. Venta #$ventaId registrada.");

} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    sendResponse(false, [], "Error: " . $e->getMessage());
}
?>