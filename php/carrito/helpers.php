<?php
function sendResponse($success, $data = [], $message = "") {
    // Limpiar cualquier salida previa
    if (ob_get_length()) ob_clean();
    
    // Determinar clave
    $key = is_array($data) && (isset($data[0]) || empty($data)) ? 'items' : 'stock';
    
    header('Content-Type: application/json');
    
    $response = [
        'success' => $success,
        'message' => $message
    ];
    
    // Agregar la clave dinámica
    $response[$key] = $data;

    echo json_encode($response);
    exit; // IMPORTANTE: Terminar ejecución
}
?>