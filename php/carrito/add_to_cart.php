<?php
// DEBE SER LA PRIMERA LÍNEA - SIN ESPACIOS ANTES
session_start();

// Limpiar cualquier buffer de salida
while (ob_get_level()) ob_end_clean();

// Configurar para devolver JSON
header('Content-Type: application/json; charset=utf-8');

// Respuesta de error genérico (por si algo falla)
function sendError($message) {
    echo json_encode([
        'success' => false,
        'message' => $message
    ]);
    exit;
}

// Respuesta de éxito
function sendSuccess($message) {
    echo json_encode([
        'success' => true,
        'message' => $message
    ]);
    exit;
}

try {
    // 1. Verificar método
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        sendError('Método no permitido. Usa POST.');
    }
    
    // 2. Verificar sesión
    if (!isset($_SESSION['user_id'])) {
        sendError('Debes iniciar sesión para agregar al carrito.');
    }
    
    // 3. Obtener datos
    $userId = $_SESSION['user_id'];
    $productId = $_POST['product_id'] ?? '';
    $name = $_POST['name'] ?? '';
    $price = $_POST['price'] ?? '0';
    $quantity = $_POST['quantity'] ?? '1';
    
    // 4. Validaciones
    if (empty($productId)) {
        sendError('Producto no especificado.');
    }
    
    if (!is_numeric($price) || $price <= 0) {
        sendError('Precio inválido.');
    }
    
    if (!is_numeric($quantity) || $quantity <= 0) {
        sendError('Cantidad inválida.');
    }
    
    // 5. Conectar a BD
    require_once 'db_config.php';
    
    // 6. Verificar que el producto existe
    $sqlCheck = "SELECT id, nombre, stock_quantity FROM productos WHERE id = ?";
    $stmtCheck = $pdo->prepare($sqlCheck);
    $stmtCheck->execute([$productId]);
    $producto = $stmtCheck->fetch();
    
    if (!$producto) {
        sendError('Producto no encontrado en la base de datos.');
    }
    
    // 7. Verificar stock (opcional)
    if (isset($producto['stock_quantity']) && $producto['stock_quantity'] < $quantity) {
        sendError('Stock insuficiente. Solo quedan ' . $producto['stock_quantity'] . ' unidades.');
    }
    
    // 8. Insertar o actualizar en carrito
    // Primero verificar si ya existe en el carrito
    $sqlExist = "SELECT id, quantity FROM user_cart WHERE user_id = ? AND product_id = ?";
    $stmtExist = $pdo->prepare($sqlExist);
    $stmtExist->execute([$userId, $productId]);
    $existente = $stmtExist->fetch();
    
    if ($existente) {
        // Actualizar cantidad
        $newQuantity = $existente['quantity'] + $quantity;
        $sqlUpdate = "UPDATE user_cart SET quantity = ?, price = ? WHERE id = ?";
        $stmtUpdate = $pdo->prepare($sqlUpdate);
        $stmtUpdate->execute([$newQuantity, $price, $existente['id']]);
    } else {
        // Insertar nuevo
        $sqlInsert = "INSERT INTO user_cart (user_id, product_id, name, price, quantity) 
                     VALUES (?, ?, ?, ?, ?)";
        $stmtInsert = $pdo->prepare($sqlInsert);
        $stmtInsert->execute([$userId, $productId, $name, $price, $quantity]);
    }
    
    // 9. Éxito
    sendSuccess('Producto "' . $name . '" agregado al carrito.');
    
} catch (PDOException $e) {
    // Error de base de datos
    sendError('Error de base de datos: ' . $e->getMessage());
    
} catch (Exception $e) {
    // Error general
    sendError('Error: ' . $e->getMessage());
}

// NADA después de esto
?>