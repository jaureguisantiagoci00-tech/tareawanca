<?php
session_start();
require_once '../php/config.php';

// Solo ADMIN puede subir facturas externas
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'ADMIN') {
    header("Location: index.php?error=admin_required");
    exit();
}

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Procesar subida de archivo
$mensaje = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['archivo_factura'])) {
    $proveedor = $_POST['proveedor'] ?? '';
    $numero_factura = $_POST['numero_factura'] ?? '';
    $fecha_emision = $_POST['fecha_emision'] ?? '';
    $monto = $_POST['monto'] ?? 0;
    $tipo_gasto = $_POST['tipo_gasto'] ?? '';
    $observaciones = $_POST['observaciones'] ?? '';
    
    // Validar campos
    if (empty($proveedor) || empty($numero_factura) || empty($fecha_emision) || $monto <= 0) {
        $error = 'Complete todos los campos obligatorios';
    } else {
        // Procesar archivo
        $archivo = $_FILES['archivo_factura'];
        $nombre_archivo = $archivo['name'];
        $tipo_archivo = $archivo['type'];
        $temporal = $archivo['tmp_name'];
        $tamano = $archivo['size'];
        
        // Validar tipo de archivo
        $extensiones_permitidas = ['pdf', 'jpg', 'jpeg', 'png', 'gif'];
        $extension = strtolower(pathinfo($nombre_archivo, PATHINFO_EXTENSION));
        
        if (!in_array($extension, $extensiones_permitidas)) {
            $error = 'Solo se permiten archivos PDF, JPG, PNG o GIF';
        } elseif ($tamano > 5 * 1024 * 1024) { // 5MB máximo
            $error = 'El archivo es demasiado grande (máximo 5MB)';
        } else {
            // Crear carpeta si no existe
            $carpeta_facturas = '../uploads/facturas_externas/';
            if (!file_exists($carpeta_facturas)) {
                mkdir($carpeta_facturas, 0777, true);
            }
            
            // Generar nombre único
            $nombre_unico = uniqid() . '_' . date('Ymd') . '.' . $extension;
            $ruta_destino = $carpeta_facturas . $nombre_unico;
            
            if (move_uploaded_file($temporal, $ruta_destino)) {
                // Insertar en base de datos
                $sql = "INSERT INTO facturas_externas 
                       (proveedor, numero_factura, fecha_emision, monto, tipo_gasto, 
                        archivo, observaciones, subido_por, fecha_subida) 
                       VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
                
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sssdsssi", 
                    $proveedor, $numero_factura, $fecha_emision, $monto, 
                    $tipo_gasto, $nombre_unico, $observaciones, $_SESSION['user_id']);
                
                if ($stmt->execute()) {
                    $mensaje = 'Factura subida correctamente';
                } else {
                    $error = 'Error al guardar en la base de datos: ' . $stmt->error;
                    // Eliminar archivo subido
                    unlink($ruta_destino);
                }
                $stmt->close();
            } else {
                $error = 'Error al subir el archivo';
            }
        }
    }
}

// Obtener facturas existentes
$sql = "SELECT fe.*, u.nombre_completo as subido_por_nombre 
        FROM facturas_externas fe 
        LEFT JOIN usuarios u ON fe.subido_por = u.id 
        ORDER BY fe.fecha_subida DESC 
        LIMIT 50";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Facturas Externas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../estilos.css">
    <style>
        .upload-area {
            border: 2px dashed #4361ee;
            border-radius: 10px;
            padding: 40px;
            text-align: center;
            background: #f8f9fa;
            cursor: pointer;
            transition: all 0.3s;
        }
        .upload-area:hover {
            background: #e3f2fd;
            border-color: #3a56d4;
        }
        .upload-area.dragover {
            background: #bbdefb;
            border-color: #1976d2;
        }
        .factura-card {
            border-left: 4px solid;
            transition: all 0.3s;
        }
        .factura-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .tipo-insumo { border-left-color: #4caf50; }
        .tipo-servicio { border-left-color: #2196f3; }
        .tipo-otros { border-left-color: #ff9800; }
        .file-icon {
            font-size: 2rem;
            color: #4361ee;
        }
        .file-size {
            font-size: 0.8rem;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="navbar-top">
        <h4 class="mb-0"><i class="fas fa-file-invoice me-2"></i> Facturas Externas</h4>
        <div class="d-flex gap-2">
            <a href="index.php" class="btn btn-outline-primary">
                <i class="fas fa-arrow-left me-1"></i> Volver al Dashboard
            </a>
        </div>
    </div>

    <div class="container-fluid mt-4">
        <!-- Mensajes -->
        <?php if($mensaje): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle me-2"></i> <?php echo $mensaje; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>
        
        <?php if($error): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-triangle me-2"></i> <?php echo $error; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <!-- Formulario de subida -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-upload me-2"></i> Subir Nueva Factura</h5>
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data" id="formFactura">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Proveedor *</label>
                            <input type="text" class="form-control" name="proveedor" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Número de Factura *</label>
                            <input type="text" class="form-control" name="numero_factura" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Fecha Emisión *</label>
                            <input type="date" class="form-control" name="fecha_emision" required 
                                   max="<?php echo date('Y-m-d'); ?>">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Monto (S/) *</label>
                            <input type="number" class="form-control" name="monto" step="0.01" min="0.01" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Tipo de Gasto *</label>
                            <select class="form-select" name="tipo_gasto" required>
                                <option value="">Seleccionar tipo</option>
                                <option value="insumos">Insumos/Veterinarios</option>
                                <option value="alimentos">Alimentos</option>
                                <option value="servicios">Servicios (Luz/Agua/Internet)</option>
                                <option value="equipos">Equipos y Maquinaria</option>
                                <option value="mantenimiento">Mantenimiento</option>
                                <option value="otros">Otros</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Archivo (PDF/Imagen) *</label>
                            <input type="file" class="form-control" name="archivo_factura" accept=".pdf,.jpg,.jpeg,.png,.gif" required>
                            <small class="text-muted">Máximo 5MB. Formatos: PDF, JPG, PNG, GIF</small>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Observaciones</label>
                        <textarea class="form-control" name="observaciones" rows="3" 
                                  placeholder="Descripción del gasto, productos adquiridos, etc."></textarea>
                    </div>
                    
                    <!-- Área de arrastrar y soltar (opcional) -->
                    <div class="upload-area mb-3" id="dropArea">
                        <div class="file-icon mb-3">
                            <i class="fas fa-cloud-upload-alt"></i>
                        </div>
                        <h5>Arrastra y suelta el archivo aquí</h5>
                        <p class="text-muted">o haz clic para seleccionar</p>
                        <input type="file" id="fileInput" style="display: none;" 
                               name="archivo_factura" accept=".pdf,.jpg,.jpeg,.png,.gif">
                    </div>
                    
                    <div class="text-end">
                        <button type="reset" class="btn btn-secondary">Limpiar</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-upload me-1"></i> Subir Factura
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Lista de facturas -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-list me-2"></i> Facturas Subidas</h5>
            </div>
            <div class="card-body">
                <?php if($result->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Factura</th>
                                <th>Proveedor</th>
                                <th>Fecha</th>
                                <th>Monto</th>
                                <th>Tipo</th>
                                <th>Subido por</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($factura = $result->fetch_assoc()): 
                                $extension = pathinfo($factura['archivo'], PATHINFO_EXTENSION);
                                $icon = $extension == 'pdf' ? 'file-pdf' : 'file-image';
                            ?>
                            <tr class="factura-card tipo-<?php echo $factura['tipo_gasto']; ?>">
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="me-3">
                                            <i class="fas fa-<?php echo $icon; ?> text-primary"></i>
                                        </div>
                                        <div>
                                            <strong><?php echo htmlspecialchars($factura['numero_factura']); ?></strong><br>
                                            <small class="text-muted">
                                                <?php echo strtoupper($extension); ?> • 
                                                <?php echo date('d/m/Y', strtotime($factura['fecha_subida'])); ?>
                                            </small>
                                        </div>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars($factura['proveedor']); ?></td>
                                <td>
                                    <?php echo date('d/m/Y', strtotime($factura['fecha_emision'])); ?><br>
                                    <small class="text-muted">emisión</small>
                                </td>
                                <td>
                                    <strong class="text-success">S/ <?php echo number_format($factura['monto'], 2); ?></strong>
                                </td>
                                <td>
                                    <span class="badge bg-info">
                                        <?php echo ucfirst($factura['tipo_gasto']); ?>
                                    </span>
                                </td>
                                <td><?php echo $factura['subido_por_nombre']; ?></td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="../uploads/facturas_externas/<?php echo $factura['archivo']; ?>" 
                                           target="_blank" class="btn btn-outline-primary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="../uploads/facturas_externas/<?php echo $factura['archivo']; ?>" 
                                           download class="btn btn-outline-success">
                                            <i class="fas fa-download"></i>
                                        </a>
                                        <button class="btn btn-outline-danger" 
                                                onclick="eliminarFactura(<?php echo $factura['id']; ?>, '<?php echo $factura['numero_factura']; ?>')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-file-invoice fa-3x text-muted mb-3"></i>
                    <h5>No hay facturas externas</h5>
                    <p class="text-muted">Sube tu primera factura usando el formulario de arriba.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
    // Drag & Drop
    const dropArea = document.getElementById('dropArea');
    const fileInput = document.getElementById('fileInput');
    
    dropArea.addEventListener('click', () => fileInput.click());
    
    fileInput.addEventListener('change', function() {
        if (this.files.length) {
            updateFileName(this.files[0]);
        }
    });
    
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropArea.addEventListener(eventName, preventDefaults, false);
    });
    
    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }
    
    ['dragenter', 'dragover'].forEach(eventName => {
        dropArea.addEventListener(eventName, highlight, false);
    });
    
    ['dragleave', 'drop'].forEach(eventName => {
        dropArea.addEventListener(eventName, unhighlight, false);
    });
    
    function highlight() {
        dropArea.classList.add('dragover');
    }
    
    function unhighlight() {
        dropArea.classList.remove('dragover');
    }
    
    dropArea.addEventListener('drop', handleDrop, false);
    
    function handleDrop(e) {
        const dt = e.dataTransfer;
        const files = dt.files;
        fileInput.files = files;
        updateFileName(files[0]);
    }
    
    function updateFileName(file) {
        const fileName = file.name;
        dropArea.innerHTML = `
            <div class="file-icon mb-3">
                <i class="fas fa-file"></i>
            </div>
            <h5>${fileName}</h5>
            <p class="file-size">${formatFileSize(file.size)}</p>
            <small class="text-muted">Haz clic para cambiar</small>
        `;
    }
    
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }
    
    // Eliminar factura
    function eliminarFactura(id, numero) {
        if (confirm(`¿Eliminar la factura ${numero}?\n\nEsta acción no se puede deshacer.`)) {
            fetch(`../php/dashboard/eliminar_factura.php?id=${id}`, {
                method: 'DELETE'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Factura eliminada correctamente');
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            });
        }
    }
    </script>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
$conn->close();
?>