<?php
session_start();
$page_title = "Mi Perfil";  // <-- AÑADIDO: Define el título de página
require_once 'php/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php?error=login_required");
    exit();
}

$user_id = $_SESSION['user_id'];

// Conexión a BD
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Obtener datos del usuario
$sql_usuario = "SELECT * FROM usuarios WHERE id = ?";
$stmt_usuario = $conn->prepare($sql_usuario);
$stmt_usuario->bind_param("i", $user_id);
$stmt_usuario->execute();
$result_usuario = $stmt_usuario->get_result();
$usuario = $result_usuario->fetch_assoc();

// Obtener citas del usuario
$sql_citas = "SELECT * FROM citas WHERE usuario_id = ? ORDER BY fecha DESC LIMIT 5";
$stmt_citas = $conn->prepare($sql_citas);
$stmt_citas->bind_param("i", $user_id);
$stmt_citas->execute();
$result_citas = $stmt_citas->get_result();

// Obtener últimas compras
$sql_compras = "SELECT v.id, v.fecha, v.total, COUNT(vd.id) as items 
                FROM ventas v 
                LEFT JOIN venta_detalles vd ON v.id = vd.venta_id 
                WHERE v.usuario_id = ? 
                GROUP BY v.id 
                ORDER BY v.fecha DESC 
                LIMIT 5";
$stmt_compras = $conn->prepare($sql_compras);
$stmt_compras->bind_param("i", $user_id);
$stmt_compras->execute();
$result_compras = $stmt_compras->get_result();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil - Puphub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="estilos.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="container py-5">
        <!-- Botón de volver -->
        <p class="mb-4">
            <a href="javascript:history.back()" class="text-secondary small text-decoration-none fw-bold">
                <i class="fas fa-arrow-left me-1"></i> Volver atrás
            </a>
            <span class="mx-2">|</span>
            <a href="index.php" class="text-secondary small text-decoration-none fw-bold">
                <i class="fas fa-home me-1"></i> Ir al Inicio
            </a>
        </p>
        
        <?php if(isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>
        
        <div class="row">
            <div class="col-md-3">
                <div class="card mb-4 shadow-sm">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <div class="avatar-circle bg-primary text-white d-inline-flex align-items-center justify-content-center rounded-circle" 
                                 style="width: 100px; height: 100px; font-size: 2.5rem;">
                                <?php echo strtoupper(substr($usuario['nombre_completo'], 0, 1)); ?>
                            </div>
                        </div>
                        <h4 class="card-title"><?php echo htmlspecialchars($usuario['nombre_completo']); ?></h4>
                        <p class="text-muted mb-2">
                            <i class="fas fa-envelope me-1"></i>
                            <?php echo htmlspecialchars($usuario['email']); ?>
                        </p>
                        <p class="small text-muted">
                            <i class="fas fa-calendar-alt me-1"></i>
                            Miembro desde: <?php echo date('d/m/Y', strtotime($usuario['fecha_registro'])); ?>
                        </p>
                        
                        <div class="d-grid gap-2 mt-3">
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalEditarPerfil">
                                <i class="fas fa-edit me-1"></i> Editar Perfil
                            </button>
                            <a href="productos.php" class="btn btn-success">
                                <i class="fas fa-shopping-cart me-1"></i> Seguir Comprando
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0"><i class="fas fa-tachometer-alt me-2"></i> Mi Actividad</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Citas:</span>
                            <strong><?php echo $result_citas->num_rows; ?></strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Compras:</span>
                            <strong><?php echo $result_compras->num_rows; ?></strong>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-9">
                <div class="card mb-4 shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-calendar-check me-2"></i> Mis Próximas Citas</h5>
                        <a href="servicios/" class="btn btn-sm btn-primary">
                            <i class="fas fa-plus me-1"></i> Nueva Cita
                        </a>
                    </div>
                    <div class="card-body">
                        <?php if($result_citas->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Servicio</th>
                                        <th>Fecha</th>
                                        <th>Hora</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($cita = $result_citas->fetch_assoc()): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo ucfirst($cita['servicio']); ?></strong><br>
                                            <small class="text-muted"><?php echo $cita['tipo_servicio']; ?></small>
                                        </td>
                                        <td><?php echo date('d/m/Y', strtotime($cita['fecha'])); ?></td>
                                        <td><?php echo $cita['hora']; ?></td>
                                        <td>
                                            <?php 
                                            $estados = [
                                                'pendiente' => 'warning',
                                                'confirmada' => 'success', 
                                                'completada' => 'info',
                                                'cancelada' => 'danger'
                                            ];
                                            ?>
                                            <span class="badge bg-<?php echo $estados[$cita['estado']]; ?>">
                                                <?php echo ucfirst($cita['estado']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#modalDetalleCita<?php echo $cita['id']; ?>">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <?php if($cita['estado'] == 'pendiente'): ?>
                                            <button class="btn btn-sm btn-outline-danger" 
                                                    onclick="cancelarCita(<?php echo $cita['id']; ?>)">
                                                <i class="fas fa-times"></i>
                                            </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-calendar fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No tienes citas agendadas.</p>
                            <a href="servicios/" class="btn btn-primary">
                                <i class="fas fa-plus me-1"></i> Agendar mi primera cita
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-shopping-bag me-2"></i> Historial de Compras</h5>
                    </div>
                    <div class="card-body">
                        <?php if($result_compras->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Pedido #</th>
                                        <th>Fecha</th>
                                        <th>Items</th>
                                        <th>Total</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($compra = $result_compras->fetch_assoc()): ?>
                                    <tr>
                                        <td>#<?php echo str_pad($compra['id'], 6, '0', STR_PAD_LEFT); ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($compra['fecha'])); ?></td>
                                        <td><?php echo $compra['items']; ?> productos</td>
                                        <td><strong>S/ <?php echo number_format($compra['total'], 2); ?></strong></td>
                                        <td>
                                            <span class="badge bg-success">Completada</span>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-receipt"></i> Ver Factura
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No tienes compras registradas.</p>
                            <a href="productos.php" class="btn btn-primary">
                                <i class="fas fa-store me-1"></i> Ver Catálogo
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <!-- Modal: Editar Perfil -->
    <div class="modal fade" id="modalEditarPerfil">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-user-edit me-2"></i> Editar Perfil</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form action="php/actualizar_perfil.php" method="POST">
                        <div class="mb-3">
                            <label class="form-label">Nombre Completo</label>
                            <input type="text" class="form-control" name="nombre_completo" 
                                   value="<?php echo htmlspecialchars($usuario['nombre_completo']); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Correo Electrónico</label>
                            <input type="email" class="form-control" name="email" 
                                   value="<?php echo htmlspecialchars($usuario['email']); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Teléfono</label>
                            <input type="tel" class="form-control" name="telefono" 
                                   placeholder="Agregar teléfono">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Cambiar Contraseña (opcional)</label>
                            <input type="password" class="form-control" name="nueva_password" 
                                   placeholder="Nueva contraseña">
                            <input type="password" class="form-control mt-2" name="confirmar_password" 
                                   placeholder="Confirmar nueva contraseña">
                        </div>
                        
                        <div class="text-end">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Guardar Cambios
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script>
    function cancelarCita(cita_id) {
        if (confirm('¿Estás seguro de cancelar esta cita?')) {
            window.location.href = 'php/cancelar_cita.php?id=' + cita_id;
        }
    }
    </script>
    
    <?php 
    $stmt_usuario->close();
    $stmt_citas->close();
    $stmt_compras->close();
    $conn->close();
    ?>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>