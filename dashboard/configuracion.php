<?php
// dashboard/configuracion.php
session_start();

// HABILITAR ERRORES
error_reporting(E_ALL);
ini_set('display_errors', 1);

// VERIFICAR PERMISOS (SOLO ADMIN)
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'ADMIN') {
    header("Location: index.php?error=admin_only");
    exit();
}

// CONFIGURACIÓN DIRECTA
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'ProyectoVeterinaria');

// CONEXIÓN
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// TABLA PARA CONFIGURACIÓN (CREAR SI NO EXISTE)
$sql_create_table = "CREATE TABLE IF NOT EXISTS configuracion_sistema (
    id INT PRIMARY KEY AUTO_INCREMENT,
    clave VARCHAR(100) UNIQUE NOT NULL,
    valor TEXT,
    descripcion VARCHAR(255),
    categoria VARCHAR(50) DEFAULT 'general',
    tipo VARCHAR(20) DEFAULT 'text', -- text, number, email, select, textarea, boolean
    opciones TEXT, -- Para campos select: valor1|Etiqueta1,valor2|Etiqueta2
    actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

$conn->query($sql_create_table);

// INSERTAR CONFIGURACIONES POR DEFECTO SI NO EXISTEN
$configuraciones_default = [
    // GENERAL
    ['sitio_nombre', 'Veterinaria PetCare', 'Nombre del negocio', 'general'],
    ['sitio_email', 'info@veterinaria.com', 'Email de contacto', 'general'],
    ['sitio_telefono', '+51 123 456 789', 'Teléfono principal', 'general'],
    ['sitio_direccion', 'Av. Principal 123, Lima', 'Dirección del local', 'general'],
    ['sitio_facebook', 'https://facebook.com/veterinaria', 'Facebook', 'general'],
    ['sitio_instagram', 'https://instagram.com/veterinaria', 'Instagram', 'general'],
    
    // INVENTARIO
    ['inventario_stock_minimo_default', '10', 'Stock mínimo por defecto', 'inventario'],
    ['inventario_alertas_email', '1', 'Enviar alertas por email', 'inventario'],
    ['inventario_notificar_bajo_stock', '1', 'Notificar bajo stock', 'inventario'],
    
    // VENTAS
    ['ventas_igv', '18', 'Porcentaje de IGV', 'ventas'],
    ['ventas_moneda', 'PEN', 'Símbolo de moneda', 'ventas'],
    ['ventas_moneda_simbolo', 'S/', 'Símbolo de moneda', 'ventas'],
    ['ventas_numero_serie', 'F001', 'Número de serie para facturas', 'ventas'],
    ['ventas_numero_inicial', '1', 'Número inicial para facturas', 'ventas'],
    
    // CITA
    ['citas_horario_inicio', '08:00', 'Hora de inicio de atención', 'citas'],
    ['citas_horario_fin', '18:00', 'Hora de fin de atención', 'citas'],
    ['citas_duracion_default', '30', 'Duración por defecto (minutos)', 'citas'],
    ['citas_max_diarias', '20', 'Máximo de citas por día', 'citas'],
    
    // CORREO
    ['smtp_host', 'smtp.gmail.com', 'Servidor SMTP', 'correo'],
    ['smtp_port', '587', 'Puerto SMTP', 'correo'],
    ['smtp_usuario', 'tuemail@gmail.com', 'Usuario SMTP', 'correo'],
    ['smtp_password', '', 'Contraseña SMTP', 'correo'],
    ['smtp_encryption', 'tls', 'Tipo de encriptación', 'correo'],
    
    // BACKUP
    ['backup_automatico', '0', 'Backup automático', 'backup'],
    ['backup_frecuencia', 'daily', 'Frecuencia de backup', 'backup'],
    ['backup_guardar_dias', '30', 'Días a guardar backups', 'backup'],
    
    // SEGURIDAD
    ['seguridad_intentos_login', '3', 'Intentos máximos de login', 'seguridad'],
    ['seguridad_bloqueo_minutos', '15', 'Minutos de bloqueo', 'seguridad'],
    ['seguridad_requerir_2fa', '0', 'Requerir autenticación en dos pasos', 'seguridad'],
    
    // APARIENCIA
    ['tema_color_primario', '#4361ee', 'Color primario', 'apariencia'],
    ['tema_modo_oscuro', '0', 'Modo oscuro', 'apariencia'],
    ['tema_logo_url', '', 'URL del logo', 'apariencia']
];

foreach ($configuraciones_default as $config) {
    $sql_check = "SELECT id FROM configuracion_sistema WHERE clave = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("s", $config[0]);
    $stmt_check->execute();
    $stmt_check->store_result();
    
    if ($stmt_check->num_rows == 0) {
        $sql_insert = "INSERT INTO configuracion_sistema (clave, valor, descripcion, categoria) 
                      VALUES (?, ?, ?, ?)";
        $stmt_insert = $conn->prepare($sql_insert);
        $stmt_insert->bind_param("ssss", $config[0], $config[1], $config[2], $config[3]);
        $stmt_insert->execute();
    }
}

// PROCESAR ACTUALIZACIÓN DE CONFIGURACIÓN
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['actualizar_config'])) {
        foreach ($_POST['config'] as $clave => $valor) {
            // Sanitizar valor
            $valor = is_array($valor) ? json_encode($valor) : trim($valor);
            
            $sql = "UPDATE configuracion_sistema SET valor = ?, actualizado_en = NOW() 
                    WHERE clave = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $valor, $clave);
            $stmt->execute();
        }
        
        $_SESSION['success'] = '✅ Configuración actualizada correctamente';
        header("Location: configuracion.php");
        exit();
    }
    
    // CAMBIAR CONTRASEÑA ADMIN
    if (isset($_POST['cambiar_password'])) {
        $current_pass = $_POST['current_password'];
        $new_pass = $_POST['new_password'];
        $confirm_pass = $_POST['confirm_password'];
        
        // Verificar contraseña actual
        $sql = "SELECT contrasena_hash FROM usuarios WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        
        if (password_verify($current_pass, $user['contrasena_hash'])) {
            if ($new_pass === $confirm_pass) {
                $new_hash = password_hash($new_pass, PASSWORD_DEFAULT);
                $sql_update = "UPDATE usuarios SET contrasena_hash = ? WHERE id = ?";
                $stmt_update = $conn->prepare($sql_update);
                $stmt_update->bind_param("si", $new_hash, $_SESSION['user_id']);
                
                if ($stmt_update->execute()) {
                    $_SESSION['success'] = '✅ Contraseña actualizada correctamente';
                } else {
                    $_SESSION['error'] = '❌ Error al actualizar contraseña';
                }
            } else {
                $_SESSION['error'] = '❌ Las contraseñas nuevas no coinciden';
            }
        } else {
            $_SESSION['error'] = '❌ Contraseña actual incorrecta';
        }
        
        header("Location: configuracion.php#seguridad");
        exit();
    }
    
    // RESPALDO MANUAL DE BASE DE DATOS
    if (isset($_POST['backup_manual'])) {
        // Crear directorio de backups si no existe
        $backup_dir = __DIR__ . '/../backups/';
        if (!is_dir($backup_dir)) {
            mkdir($backup_dir, 0755, true);
        }
        
        // Nombre del archivo de backup
        $backup_file = $backup_dir . 'backup_' . date('Y-m-d_H-i-s') . '.sql';
        
        // Comando para exportar (MySQL)
        $command = "mysqldump --user=" . DB_USER . " --password=" . DB_PASS . 
                  " --host=" . DB_HOST . " " . DB_NAME . " > " . $backup_file;
        
        // Ejecutar comando
        exec($command, $output, $return_var);
        
        if ($return_var === 0) {
            $_SESSION['success'] = '✅ Backup creado: ' . basename($backup_file);
        } else {
            $_SESSION['error'] = '❌ Error al crear backup';
        }
        
        header("Location: configuracion.php#backup");
        exit();
    }
    
    // RESTAURAR BASE DE DATOS
    if (isset($_POST['restaurar_backup']) && isset($_FILES['backup_file'])) {
        if ($_FILES['backup_file']['error'] === 0) {
            $temp_file = $_FILES['backup_file']['tmp_name'];
            
            // Leer archivo SQL
            $sql_content = file_get_contents($temp_file);
            
            // Ejecutar consultas
            if ($conn->multi_query($sql_content)) {
                do {
                    if ($result = $conn->store_result()) {
                        $result->free();
                    }
                } while ($conn->more_results() && $conn->next_result());
                
                $_SESSION['success'] = '✅ Base de datos restaurada correctamente';
            } else {
                $_SESSION['error'] = '❌ Error al restaurar backup: ' . $conn->error;
            }
        } else {
            $_SESSION['error'] = '❌ Error al subir archivo';
        }
        
        header("Location: configuracion.php#backup");
        exit();
    }
}

// OBTENER CONFIGURACIONES POR CATEGORÍA
$categorias = [
    'general' => ['icon' => 'fas fa-cog', 'nombre' => 'General'],
    'inventario' => ['icon' => 'fas fa-warehouse', 'nombre' => 'Inventario'],
    'ventas' => ['icon' => 'fas fa-shopping-cart', 'nombre' => 'Ventas'],
    'citas' => ['icon' => 'fas fa-calendar-alt', 'nombre' => 'Citas'],
    'correo' => ['icon' => 'fas fa-envelope', 'nombre' => 'Correo'],
    'backup' => ['icon' => 'fas fa-database', 'nombre' => 'Backup'],
    'seguridad' => ['icon' => 'fas fa-shield-alt', 'nombre' => 'Seguridad'],
    'apariencia' => ['icon' => 'fas fa-palette', 'nombre' => 'Apariencia']
];

$configuraciones = [];
foreach ($categorias as $categoria => $info) {
    $sql = "SELECT * FROM configuracion_sistema WHERE categoria = ? ORDER BY clave";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $categoria);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $configuraciones[$categoria] = [];
    while ($row = $result->fetch_assoc()) {
        $configuraciones[$categoria][] = $row;
    }
}

// OBTENER INFORMACIÓN DEL SISTEMA
$php_version = phpversion();
$mysql_version = $conn->server_info;
$sistema_operativo = PHP_OS;
$uso_memoria = round(memory_get_usage() / 1024 / 1024, 2) . ' MB';
$tiempo_ejecucion = round(microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'], 4) . ' seg';

// OBTENER ESTADÍSTICAS
$sql_stats = "SELECT 
    (SELECT COUNT(*) FROM productos) as total_productos,
    (SELECT COUNT(*) FROM usuarios) as total_usuarios,
    (SELECT COUNT(*) FROM citas WHERE DATE(fecha) = CURDATE()) as citas_hoy,
    (SELECT SUM(total) FROM ventas WHERE DATE(fecha) = CURDATE()) as ventas_hoy,
    (SELECT COUNT(*) FROM movimientos_stock WHERE DATE(fecha) = CURDATE()) as movimientos_hoy";

$stats_result = $conn->query($sql_stats);
$stats = $stats_result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración del Sistema - Veterinaria</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3a0ca3;
            --success-color: #06d6a0;
            --warning-color: #ff9e00;
            --danger-color: #ef476f;
            --light-color: #f8f9fa;
            --dark-color: #212529;
        }
        
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
        }
        
        .main-container {
            max-width: 1400px;
            margin: 0 auto;
        }
        
        .header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 30px;
            border-radius: 20px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(67, 97, 238, 0.3);
        }
        
        .config-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            border: none;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .config-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.12);
        }
        
        .config-header {
            display: flex;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .config-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 20px;
            font-size: 24px;
            color: white;
        }
        
        .nav-tabs-custom {
            border-bottom: 2px solid #dee2e6;
        }
        
        .nav-tabs-custom .nav-link {
            border: none;
            color: #6c757d;
            font-weight: 500;
            padding: 12px 25px;
            border-radius: 10px 10px 0 0;
            margin-right: 5px;
            transition: all 0.3s;
        }
        
        .nav-tabs-custom .nav-link:hover {
            background-color: #f8f9fa;
            color: var(--primary-color);
        }
        
        .nav-tabs-custom .nav-link.active {
            background: white;
            color: var(--primary-color);
            border-bottom: 3px solid var(--primary-color);
        }
        
        .form-label-custom {
            font-weight: 600;
            color: #495057;
            margin-bottom: 8px;
        }
        
        .form-control-custom {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 12px 15px;
            transition: all 0.3s;
        }
        
        .form-control-custom:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(67, 97, 238, 0.25);
        }
        
        .btn-primary-custom {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border: none;
            border-radius: 10px;
            padding: 12px 30px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-primary-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(67, 97, 238, 0.4);
        }
        
        .system-info-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
        }
        
        .info-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .info-item:last-child {
            border-bottom: none;
        }
        
        .info-label {
            color: #6c757d;
            font-weight: 500;
        }
        
        .info-value {
            font-weight: 600;
            color: var(--dark-color);
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }
        
        .stat-item {
            background: white;
            padding: 15px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 3px 10px rgba(0,0,0,0.05);
        }
        
        .stat-value {
            font-size: 1.8rem;
            font-weight: bold;
            color: var(--primary-color);
            margin-bottom: 5px;
        }
        
        .stat-label {
            color: #6c757d;
            font-size: 0.9rem;
        }
        
        .backup-list {
            max-height: 300px;
            overflow-y: auto;
        }
        
        .backup-item {
            padding: 12px;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 30px;
        }
        
        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        
        .toggle-slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 34px;
        }
        
        .toggle-slider:before {
            position: absolute;
            content: "";
            height: 22px;
            width: 22px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }
        
        input:checked + .toggle-slider {
            background-color: var(--success-color);
        }
        
        input:checked + .toggle-slider:before {
            transform: translateX(30px);
        }
        
        .tab-content {
            background: white;
            border-radius: 0 15px 15px 15px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }
    </style>
</head>
<body>
    <div class="container-fluid main-container py-4">
        <!-- Header -->
        <div class="header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1><i class="fas fa-sliders-h me-3"></i> Configuración del Sistema</h1>
                    <p class="mb-0">Administra todas las configuraciones de tu veterinaria</p>
                </div>
                <div>
                    <a href="index.php" class="btn btn-light">
                        <i class="fas fa-arrow-left me-2"></i> Volver al Dashboard
                    </a>
                </div>
            </div>
        </div>

        <!-- Mensajes -->
        <div class="row mb-4">
            <div class="col-12">
                <?php if(isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>
                
                <?php if(isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="row">
            <!-- Panel de Navegación -->
            <div class="col-md-3 mb-4">
                <div class="config-card">
                    <h5 class="mb-4"><i class="fas fa-info-circle me-2"></i> Información del Sistema</h5>
                    
                    <div class="system-info-card">
                        <div class="info-item">
                            <span class="info-label">PHP Version:</span>
                            <span class="info-value"><?php echo $php_version; ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">MySQL Version:</span>
                            <span class="info-value"><?php echo $mysql_version; ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Sistema Operativo:</span>
                            <span class="info-value"><?php echo $sistema_operativo; ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Memoria Usada:</span>
                            <span class="info-value"><?php echo $uso_memoria; ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Tiempo Ejecución:</span>
                            <span class="info-value"><?php echo $tiempo_ejecucion; ?></span>
                        </div>
                    </div>
                    
                    <div class="stats-grid">
                        <div class="stat-item">
                            <div class="stat-value"><?php echo $stats['total_productos']; ?></div>
                            <div class="stat-label">Productos</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value"><?php echo $stats['total_usuarios']; ?></div>
                            <div class="stat-label">Usuarios</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value"><?php echo $stats['citas_hoy']; ?></div>
                            <div class="stat-label">Citas Hoy</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value">S/ <?php echo number_format($stats['ventas_hoy'] ?? 0, 2); ?></div>
                            <div class="stat-label">Ventas Hoy</div>
                        </div>
                    </div>
                </div>
                
                <!-- Navegación Lateral -->
                <div class="config-card">
                    <h5 class="mb-4"><i class="fas fa-bars me-2"></i> Categorías</h5>
                    <div class="list-group">
                        <?php foreach($categorias as $cat_id => $cat_info): ?>
                        <a href="#<?php echo $cat_id; ?>" 
                           class="list-group-item list-group-item-action d-flex align-items-center">
                            <div class="me-3" style="color: var(--primary-color);">
                                <i class="<?php echo $cat_info['icon']; ?>"></i>
                            </div>
                            <span><?php echo $cat_info['nombre']; ?></span>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Contenido Principal -->
            <div class="col-md-9">
                <form method="POST" action="configuracion.php">
                    <!-- Tabs Navigation -->
                    <ul class="nav nav-tabs nav-tabs-custom" id="configTabs">
                        <?php foreach($categorias as $cat_id => $cat_info): ?>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $cat_id == 'general' ? 'active' : ''; ?>" 
                               data-bs-toggle="tab" 
                               href="#<?php echo $cat_id; ?>">
                                <i class="<?php echo $cat_info['icon']; ?> me-2"></i>
                                <?php echo $cat_info['nombre']; ?>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>

                    <!-- Tabs Content -->
                    <div class="tab-content" id="configTabsContent">
                        <?php foreach($categorias as $cat_id => $cat_info): ?>
                        <div class="tab-pane fade <?php echo $cat_id == 'general' ? 'show active' : ''; ?>" 
                             id="<?php echo $cat_id; ?>">
                            
                            <div class="config-header">
                                <div class="config-icon">
                                    <i class="<?php echo $cat_info['icon']; ?>"></i>
                                </div>
                                <div>
                                    <h4 class="mb-1"><?php echo $cat_info['nombre']; ?></h4>
                                    <p class="text-muted mb-0">Configura los parámetros de <?php echo strtolower($cat_info['nombre']); ?></p>
                                </div>
                            </div>
                            
                            <div class="row">
                                <?php if(isset($configuraciones[$cat_id]) && count($configuraciones[$cat_id]) > 0): ?>
                                    <?php foreach($configuraciones[$cat_id] as $config): ?>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label-custom">
                                            <?php echo $config['descripcion']; ?>
                                        </label>
                                        
                                        <?php if($config['tipo'] == 'boolean'): ?>
                                        <div class="d-flex align-items-center">
                                            <label class="toggle-switch me-3">
                                                <input type="checkbox" 
                                                       name="config[<?php echo $config['clave']; ?>]" 
                                                       value="1" 
                                                       <?php echo $config['valor'] == '1' ? 'checked' : ''; ?>>
                                                <span class="toggle-slider"></span>
                                            </label>
                                            <span><?php echo $config['valor'] == '1' ? 'Activado' : 'Desactivado'; ?></span>
                                        </div>
                                        
                                        <?php elseif($config['tipo'] == 'select' && !empty($config['opciones'])): ?>
                                        <select class="form-control form-control-custom" 
                                                name="config[<?php echo $config['clave']; ?>]">
                                            <?php 
                                            $opciones = explode(',', $config['opciones']);
                                            foreach($opciones as $opcion):
                                                list($valor, $etiqueta) = explode('|', $opcion);
                                            ?>
                                            <option value="<?php echo trim($valor); ?>" 
                                                    <?php echo trim($config['valor']) == trim($valor) ? 'selected' : ''; ?>>
                                                <?php echo trim($etiqueta); ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                        
                                        <?php elseif($config['tipo'] == 'textarea'): ?>
                                        <textarea class="form-control form-control-custom" 
                                                  name="config[<?php echo $config['clave']; ?>]"
                                                  rows="3"><?php echo htmlspecialchars($config['valor']); ?></textarea>
                                        
                                        <?php else: ?>
                                        <input type="<?php echo $config['tipo']; ?>" 
                                               class="form-control form-control-custom" 
                                               name="config[<?php echo $config['clave']; ?>]"
                                               value="<?php echo htmlspecialchars($config['valor']); ?>"
                                               <?php echo $config['tipo'] == 'number' ? 'step="any"' : ''; ?>>
                                        <?php endif; ?>
                                        
                                        <small class="text-muted">Clave: <code><?php echo $config['clave']; ?></code></small>
                                    </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                <div class="col-12 text-center py-5">
                                    <i class="fas fa-cog fa-3x text-muted mb-3"></i>
                                    <h5>Sin configuraciones</h5>
                                    <p class="text-muted">No hay configuraciones definidas para esta categoría.</p>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Botón de Guardar -->
                    <div class="mt-4 text-end">
                        <button type="submit" name="actualizar_config" class="btn btn-primary-custom btn-lg">
                            <i class="fas fa-save me-2"></i> Guardar Cambios
                        </button>
                    </div>
                </form>
                
                <!-- Sección de Seguridad -->
                <div class="config-card mt-4" id="seguridad">
                    <div class="config-header">
                        <div class="config-icon" style="background: linear-gradient(135deg, #ef476f, #ff9e00);">
                            <i class="fas fa-user-shield"></i>
                        </div>
                        <div>
                            <h4 class="mb-1">Seguridad y Acceso</h4>
                            <p class="text-muted mb-0">Cambia tu contraseña y configura accesos</p>
                        </div>
                    </div>
                    
                    <form method="POST" action="configuracion.php#seguridad">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label-custom">Contraseña Actual *</label>
                                <input type="password" class="form-control form-control-custom" 
                                       name="current_password" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label-custom">Nueva Contraseña *</label>
                                <input type="password" class="form-control form-control-custom" 
                                       name="new_password" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label-custom">Confirmar Contraseña *</label>
                                <input type="password" class="form-control form-control-custom" 
                                       name="confirm_password" required>
                            </div>
                        </div>
                        
                        <div class="text-end">
                            <button type="submit" name="cambiar_password" class="btn btn-primary-custom">
                                <i class="fas fa-key me-2"></i> Cambiar Contraseña
                            </button>
                        </div>
                    </form>
                </div>
                
                <!-- Sección de Backup -->
                <div class="config-card mt-4" id="backup">
                    <div class="config-header">
                        <div class="config-icon" style="background: linear-gradient(135deg, #06d6a0, #118ab2);">
                            <i class="fas fa-database"></i>
                        </div>
                        <div>
                            <h4 class="mb-1">Backup y Restauración</h4>
                            <p class="text-muted mb-0">Respalda y restaura tu base de datos</p>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="config-card">
                                <h5><i class="fas fa-download me-2"></i> Crear Backup</h5>
                                <p class="text-muted">Crea una copia de seguridad de toda la base de datos.</p>
                                
                                <form method="POST" action="configuracion.php#backup">
                                    <div class="mb-3">
                                        <label class="form-label-custom">Descripción (opcional)</label>
                                        <input type="text" class="form-control form-control-custom" 
                                               name="backup_desc" placeholder="Ej: Backup semanal">
                                    </div>
                                    
                                    <button type="submit" name="backup_manual" class="btn btn-success w-100">
                                        <i class="fas fa-save me-2"></i> Crear Backup Manual
                                    </button>
                                </form>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="config-card">
                                <h5><i class="fas fa-upload me-2"></i> Restaurar Backup</h5>
                                <p class="text-muted">Sube un archivo SQL para restaurar la base de datos.</p>
                                
                                <form method="POST" action="configuracion.php#backup" enctype="multipart/form-data">
                                    <div class="mb-3">
                                        <label class="form-label-custom">Seleccionar archivo SQL</label>
                                        <input type="file" class="form-control form-control-custom" 
                                               name="backup_file" accept=".sql" required>
                                        <small class="text-muted">Solo archivos .sql (máx 50MB)</small>
                                    </div>
                                    
                                    <div class="alert alert-warning">
                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                        <strong>Advertencia:</strong> Esta acción sobrescribirá todos los datos actuales.
                                    </div>
                                    
                                    <button type="submit" name="restaurar_backup" class="btn btn-warning w-100"
                                            onclick="return confirm('¿Estás seguro? Esto sobrescribirá TODOS los datos actuales.')">
                                        <i class="fas fa-history me-2"></i> Restaurar Backup
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Lista de Backups -->
                    <div class="mt-4">
                        <h5><i class="fas fa-history me-2"></i> Backups Recientes</h5>
                        
                        <?php
                        $backup_dir = __DIR__ . '/../backups/';
                        if (is_dir($backup_dir)) {
                            $backups = scandir($backup_dir, SCANDIR_SORT_DESCENDING);
                            $backups = array_filter($backups, function($file) {
                                return pathinfo($file, PATHINFO_EXTENSION) === 'sql';
                            });
                            
                            if (count($backups) > 0): ?>
                            <div class="backup-list mt-3">
                                <?php foreach(array_slice($backups, 0, 5) as $backup): 
                                    $filepath = $backup_dir . $backup;
                                    $filesize = filesize($filepath) / 1024 / 1024; // MB
                                    $filetime = filemtime($filepath);
                                ?>
                                <div class="backup-item">
                                    <div>
                                        <strong><?php echo $backup; ?></strong><br>
                                        <small class="text-muted">
                                            <?php echo date('d/m/Y H:i:s', $filetime); ?> • 
                                            <?php echo number_format($filesize, 2); ?> MB
                                        </small>
                                    </div>
                                    <div>
                                        <a href="../backups/<?php echo $backup; ?>" 
                                           class="btn btn-sm btn-outline-primary" download>
                                            <i class="fas fa-download"></i>
                                        </a>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <?php else: ?>
                            <div class="text-center py-3">
                                <p class="text-muted">No hay backups disponibles.</p>
                            </div>
                            <?php endif;
                        } else {
                            echo '<p class="text-muted">Directorio de backups no encontrado.</p>';
                        }
                        ?>
                    </div>
                </div>
                
                <!-- Sección de Logs -->
                <div class="config-card mt-4">
                    <div class="config-header">
                        <div class="config-icon" style="background: linear-gradient(135deg, #6c757d, #495057);">
                            <i class="fas fa-clipboard-list"></i>
                        </div>
                        <div>
                            <h4 class="mb-1">Logs del Sistema</h4>
                            <p class="text-muted mb-0">Registro de actividades del sistema</p>
                        </div>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Usuario</th>
                                    <th>Acción</th>
                                    <th>Detalles</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // Obtener últimos movimientos de stock
                                $sql_logs = "SELECT m.*, u.nombre_completo, p.nombre as producto_nombre
                                            FROM movimientos_stock m
                                            LEFT JOIN usuarios u ON m.usuario = u.nombre_completo
                                            LEFT JOIN productos p ON m.producto_id = p.id
                                            ORDER BY m.fecha DESC LIMIT 10";
                                $result_logs = $conn->query($sql_logs);
                                
                                while($log = $result_logs->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo date('d/m H:i', strtotime($log['fecha'])); ?></td>
                                    <td><?php echo $log['nombre_completo'] ?? 'Sistema'; ?></td>
                                    <td>
                                        <span class="badge <?php echo $log['tipo'] == 'ENTRADA' ? 'bg-success' : 'bg-danger'; ?>">
                                            <?php echo $log['tipo']; ?> STOCK
                                        </span>
                                    </td>
                                    <td>
                                        <?php echo $log['producto_nombre'] ?? 'Producto eliminado'; ?> - 
                                        <?php echo $log['cantidad']; ?> unidades - 
                                        <?php echo $log['motivo']; ?>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Activar tabs al hacer clic en enlaces de navegación lateral
    document.addEventListener('DOMContentLoaded', function() {
        const navLinks = document.querySelectorAll('.list-group-item[href^="#"]');
        const tabLinks = document.querySelectorAll('.nav-link[data-bs-toggle="tab"]');
        
        navLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const targetId = this.getAttribute('href').substring(1);
                
                // Activar el tab correspondiente
                tabLinks.forEach(tabLink => {
                    if (tabLink.getAttribute('href') === '#' + targetId) {
                        const tab = new bootstrap.Tab(tabLink);
                        tab.show();
                    }
                });
                
                // Scroll suave
                document.getElementById(targetId).scrollIntoView({ behavior: 'smooth' });
            });
        });
        
        // Actualizar automáticamente la URL con el hash
        const tabs = document.querySelectorAll('a[data-bs-toggle="tab"]');
        tabs.forEach(tab => {
            tab.addEventListener('shown.bs.tab', function (event) {
                const hash = event.target.getAttribute('href');
                history.pushState(null, null, hash);
            });
        });
        
        // Restaurar tab activo al recargar
        const hash = window.location.hash;
        if (hash) {
            const triggerTab = document.querySelector(`a[href="${hash}"]`);
            if (triggerTab) {
                const tab = new bootstrap.Tab(triggerTab);
                tab.show();
            }
        }
    });
    
    // Validación de contraseña
    document.querySelector('form[name="cambiar_password"]')?.addEventListener('submit', function(e) {
        const newPass = this.querySelector('input[name="new_password"]').value;
        const confirmPass = this.querySelector('input[name="confirm_password"]').value;
        
        if (newPass !== confirmPass) {
            e.preventDefault();
            alert('Las contraseñas nuevas no coinciden.');
            return false;
        }
        
        if (newPass.length < 6) {
            e.preventDefault();
            alert('La contraseña debe tener al menos 6 caracteres.');
            return false;
        }
        
        return true;
    });
    </script>
</body>
</html>

<?php
$conn->close();
?>