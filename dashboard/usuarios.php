<?php
session_start();
require_once '../php/config.php';

// Solo ADMIN puede gestionar usuarios
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'ADMIN') {
    header("Location: index.php?error=admin_required");
    exit();
}

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Procesar acciones
$action = $_GET['action'] ?? '';
$user_id = $_GET['id'] ?? 0;

// Cambiar estado de usuario
if ($action == 'toggle_status' && $user_id > 0) {
    $sql = "UPDATE usuarios SET activo = NOT activo WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    header("Location: usuarios.php");
    exit();
}

// Cambiar rol
if ($action == 'change_role' && $user_id > 0 && isset($_GET['role'])) {
    $role = $_GET['role'];
    $sql = "UPDATE usuarios SET rol = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $role, $user_id);
    $stmt->execute();
    header("Location: usuarios.php");
    exit();
}

// Obtener usuarios con paginación
$page = $_GET['page'] ?? 1;
$limit = 15;
$offset = ($page - 1) * $limit;

// Contar total
$sql_count = "SELECT COUNT(*) as total FROM usuarios";
$result_count = $conn->query($sql_count);
$total_users = $result_count->fetch_assoc()['total'];
$total_pages = ceil($total_users / $limit);

// Obtener usuarios
$sql = "SELECT * FROM usuarios ORDER BY fecha_registro DESC LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $limit, $offset);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Gestión de Usuarios</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../estilos.css">
    <style>
        .user-avatar {
            width: 50px;
            height: 50px;
            background: #4361ee;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 1.2rem;
        }
        .badge-role {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
        }
        .badge-admin { background: #f72585; color: white; }
        .badge-vendedor { background: #7209b7; color: white; }
        .badge-active { background: #4cc9f0; color: white; }
        .badge-inactive { background: #6c757d; color: white; }
        .action-buttons {
            display: flex;
            gap: 5px;
        }
    </style>
</head>
<body>
    <div class="navbar-top">
        <h4 class="mb-0"><i class="fas fa-users me-2"></i> Gestión de Usuarios</h4>
        <div class="d-flex gap-2">
            <a href="index.php" class="btn btn-outline-primary">
                <i class="fas fa-arrow-left me-1"></i> Volver al Dashboard
            </a>
        </div>
    </div>

    <div class="container-fluid mt-4">
        <!-- Estadísticas -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stat-card primary">
                    <div class="stat-icon text-primary"><i class="fas fa-users"></i></div>
                    <div class="stat-value text-primary"><?php echo $total_users; ?></div>
                    <div class="stat-label">Total Usuarios</div>
                </div>
            </div>
            <div class="col-md-3">
                <?php 
                $sql_admin = "SELECT COUNT(*) as total FROM usuarios WHERE rol = 'ADMIN'";
                $result_admin = $conn->query($sql_admin);
                $total_admin = $result_admin->fetch_assoc()['total'];
                ?>
                <div class="stat-card warning">
                    <div class="stat-icon text-warning"><i class="fas fa-crown"></i></div>
                    <div class="stat-value text-warning"><?php echo $total_admin; ?></div>
                    <div class="stat-label">Administradores</div>
                </div>
            </div>
            <div class="col-md-3">
                <?php 
                $sql_active = "SELECT COUNT(*) as total FROM usuarios WHERE activo = 1";
                $result_active = $conn->query($sql_active);
                $total_active = $result_active->fetch_assoc()['total'];
                ?>
                <div class="stat-card success">
                    <div class="stat-icon text-success"><i class="fas fa-check-circle"></i></div>
                    <div class="stat-value text-success"><?php echo $total_active; ?></div>
                    <div class="stat-label">Usuarios Activos</div>
                </div>
            </div>
            <div class="col-md-3">
                <?php 
                $sql_today = "SELECT COUNT(*) as total FROM usuarios WHERE DATE(fecha_registro) = CURDATE()";
                $result_today = $conn->query($sql_today);
                $total_today = $result_today->fetch_assoc()['total'];
                ?>
                <div class="stat-card info">
                    <div class="stat-icon text-info"><i class="fas fa-user-plus"></i></div>
                    <div class="stat-value text-info"><?php echo $total_today; ?></div>
                    <div class="stat-label">Nuevos Hoy</div>
                </div>
            </div>
        </div>

        <!-- Tabla de usuarios -->
        <div class="table-container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="mb-0">Lista de Usuarios Registrados</h5>
                <div class="d-flex gap-2">
                    <input type="text" class="form-control" placeholder="Buscar usuario..." id="searchUser">
                    <select class="form-select" style="width: auto;" id="filterRole">
                        <option value="">Todos los roles</option>
                        <option value="ADMIN">Administradores</option>
                        <option value="VENDEDOR">Vendedores</option>
                    </select>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover" id="usersTable">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Usuario</th>
                            <th>Correo</th>
                            <th>Rol</th>
                            <th>Estado</th>
                            <th>Registro</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($user = $result->fetch_assoc()): ?>
                        <tr>
                            <td>#<?php echo $user['id']; ?></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="user-avatar me-3">
                                        <?php echo strtoupper(substr($user['nombre_completo'], 0, 1)); ?>
                                    </div>
                                    <div>
                                        <strong><?php echo htmlspecialchars($user['nombre_completo']); ?></strong>
                                        <?php if($user['telefono']): ?>
                                        <div class="text-muted small">
                                            <i class="fas fa-phone"></i> <?php echo $user['telefono']; ?>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <a href="mailto:<?php echo $user['email']; ?>" class="text-decoration-none">
                                    <?php echo $user['email']; ?>
                                </a>
                            </td>
                            <td>
                                <span class="badge-role badge-<?php echo strtolower($user['rol']); ?>">
                                    <?php echo $user['rol']; ?>
                                </span>
                            </td>
                            <td>
                                <?php if($user['activo']): ?>
                                <span class="badge bg-success">Activo</span>
                                <?php else: ?>
                                <span class="badge bg-secondary">Inactivo</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php echo date('d/m/Y', strtotime($user['fecha_registro'])); ?><br>
                                <small class="text-muted"><?php echo date('H:i', strtotime($user['fecha_registro'])); ?></small>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <!-- Cambiar rol -->
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                            <i class="fas fa-user-tag"></i>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item" href="usuarios.php?action=change_role&id=<?php echo $user['id']; ?>&role=ADMIN">
                                                <i class="fas fa-crown me-2"></i> Hacer ADMIN
                                            </a></li>
                                            <li><a class="dropdown-item" href="usuarios.php?action=change_role&id=<?php echo $user['id']; ?>&role=VENDEDOR">
                                                <i class="fas fa-user-tie me-2"></i> Hacer VENDEDOR
                                            </a></li>
                                        </ul>
                                    </div>
                                    
                                    <!-- Activar/Desactivar -->
                                    <?php if($user['activo']): ?>
                                    <a href="usuarios.php?action=toggle_status&id=<?php echo $user['id']; ?>" 
                                       class="btn btn-sm btn-outline-warning"
                                       onclick="return confirm('¿Desactivar este usuario?')">
                                        <i class="fas fa-user-slash"></i>
                                    </a>
                                    <?php else: ?>
                                    <a href="usuarios.php?action=toggle_status&id=<?php echo $user['id']; ?>" 
                                       class="btn btn-sm btn-outline-success"
                                       onclick="return confirm('¿Activar este usuario?')">
                                        <i class="fas fa-user-check"></i>
                                    </a>
                                    <?php endif; ?>
                                    
                                    <!-- Ver detalles -->
                                    <button class="btn btn-sm btn-outline-info"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalDetalleUsuario"
                                            onclick="cargarDetallesUsuario(<?php echo $user['id']; ?>)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            <?php if($total_pages > 1): ?>
            <nav class="mt-4">
                <ul class="pagination justify-content-center">
                    <?php for($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                        <a class="page-link" href="usuarios.php?page=<?php echo $i; ?>">
                            <?php echo $i; ?>
                        </a>
                    </li>
                    <?php endfor; ?>
                </ul>
            </nav>
            <?php endif; ?>
        </div>
    </div>

    <!-- MODAL: Detalles de Usuario -->
    <div class="modal fade" id="modalDetalleUsuario" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title"><i class="fas fa-user-circle me-2"></i> Detalles del Usuario</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="detalleUsuarioContent">
                        <div class="text-center py-5">
                            <div class="spinner-border" role="status">
                                <span class="visually-hidden">Cargando...</span>
                            </div>
                            <p class="mt-2">Cargando detalles del usuario...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    // Búsqueda y filtrado
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchUser');
        const filterRole = document.getElementById('filterRole');
        const table = document.getElementById('usersTable');
        
        function filterTable() {
            const searchText = searchInput.value.toLowerCase();
            const selectedRole = filterRole.value;
            const rows = table.querySelectorAll('tbody tr');
            
            rows.forEach(row => {
                const name = row.cells[1].textContent.toLowerCase();
                const email = row.cells[2].textContent.toLowerCase();
                const role = row.cells[3].textContent;
                const showRow = 
                    (searchText === '' || name.includes(searchText) || email.includes(searchText)) &&
                    (selectedRole === '' || role.includes(selectedRole));
                
                row.style.display = showRow ? '' : 'none';
            });
        }
        
        searchInput.addEventListener('input', filterTable);
        filterRole.addEventListener('change', filterTable);
    });
    
    // Cargar detalles del usuario
    function cargarDetallesUsuario(id) {
        fetch(`../php/dashboard/get_usuario.php?id=${id}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const user = data.usuario;
                    const stats = data.estadisticas;
                    
                    let html = `
                        <div class="row">
                            <div class="col-md-4 text-center">
                                <div class="user-avatar mx-auto" style="width: 100px; height: 100px; font-size: 3rem;">
                                    ${user.nombre_completo.charAt(0).toUpperCase()}
                                </div>
                                <h4 class="mt-3">${user.nombre_completo}</h4>
                                <p class="text-muted">ID: #${user.id}</p>
                            </div>
                            
                            <div class="col-md-8">
                                <div class="row mb-3">
                                    <div class="col-6">
                                        <strong>Email:</strong>
                                        <p>${user.email}</p>
                                    </div>
                                    <div class="col-6">
                                        <strong>Teléfono:</strong>
                                        <p>${user.telefono || 'No registrado'}</p>
                                    </div>
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-6">
                                        <strong>Rol:</strong>
                                        <span class="badge-role badge-${user.rol.toLowerCase()} ms-2">
                                            ${user.rol}
                                        </span>
                                    </div>
                                    <div class="col-6">
                                        <strong>Estado:</strong>
                                        ${user.activo ? 
                                            '<span class="badge bg-success ms-2">Activo</span>' : 
                                            '<span class="badge bg-secondary ms-2">Inactivo</span>'
                                        }
                                    </div>
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-6">
                                        <strong>Fecha de Registro:</strong>
                                        <p>${new Date(user.fecha_registro).toLocaleDateString('es-ES', {
                                            weekday: 'long',
                                            year: 'numeric',
                                            month: 'long',
                                            day: 'numeric',
                                            hour: '2-digit',
                                            minute: '2-digit'
                                        })}</p>
                                    </div>
                                    <div class="col-6">
                                        <strong>Días como miembro:</strong>
                                        <p>${Math.floor((new Date() - new Date(user.fecha_registro)) / (1000 * 60 * 60 * 24))} días</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <h5 class="mt-3"><i class="fas fa-chart-bar me-2"></i> Estadísticas del Usuario</h5>
                        <div class="row mt-3">
                            <div class="col-md-3">
                                <div class="text-center p-3 border rounded">
                                    <div class="h2 text-primary">${stats.total_citas || 0}</div>
                                    <small class="text-muted">Citas Agendadas</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center p-3 border rounded">
                                    <div class="h2 text-success">${stats.total_compras || 0}</div>
                                    <small class="text-muted">Compras Realizadas</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center p-3 border rounded">
                                    <div class="h2 text-warning">S/ ${parseFloat(stats.total_gastado || 0).toFixed(2)}</div>
                                    <small class="text-muted">Total Gastado</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center p-3 border rounded">
                                    <div class="h2 text-info">${stats.ultima_visita || 'Nunca'}</div>
                                    <small class="text-muted">Última Visita</small>
                                </div>
                            </div>
                        </div>
                    `;
                    
                    document.getElementById('detalleUsuarioContent').innerHTML = html;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('detalleUsuarioContent').innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i> Error al cargar los detalles del usuario.
                    </div>
                `;
            });
    }
    </script>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
$conn->close();
?>