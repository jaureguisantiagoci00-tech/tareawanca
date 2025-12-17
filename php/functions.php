<?php
// php/functions.php (agrega estas funciones)

function crearBackupBD($conn, $descripcion = '') {
    $backup_dir = __DIR__ . '/../backups/';
    
    // Crear directorio si no existe
    if (!is_dir($backup_dir)) {
        mkdir($backup_dir, 0755, true);
    }
    
    $backup_file = $backup_dir . 'backup_' . date('Y-m-d_H-i-s') . '.sql';
    $command = "mysqldump --user=" . DB_USER . " --password=" . DB_PASS . 
              " --host=" . DB_HOST . " " . DB_NAME . " > " . $backup_file;
    
    exec($command, $output, $return_var);
    
    if ($return_var === 0) {
        // Registrar en logs
        $sql_log = "INSERT INTO configuracion_backups (archivo, descripcion, tamanio, usuario_id) 
                   VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql_log);
        $tamanio = filesize($backup_file);
        $stmt->bind_param("ssii", basename($backup_file), $descripcion, $tamanio, $_SESSION['user_id']);
        $stmt->execute();
        
        return ['success' => true, 'file' => basename($backup_file)];
    }
    
    return ['success' => false, 'error' => implode("\n", $output)];
}

function limpiarBackupsAntiguos($dias = 30) {
    $backup_dir = __DIR__ . '/../backups/';
    
    if (!is_dir($backup_dir)) {
        return;
    }
    
    $archivos = scandir($backup_dir);
    $limite_tiempo = time() - ($dias * 24 * 60 * 60);
    
    foreach ($archivos as $archivo) {
        if (pathinfo($archivo, PATHINFO_EXTENSION) === 'sql') {
            $ruta_completa = $backup_dir . $archivo;
            if (filemtime($ruta_completa) < $limite_tiempo) {
                unlink($ruta_completa);
            }
        }
    }
}
?>