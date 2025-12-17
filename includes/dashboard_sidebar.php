<?php
// dashboard_sidebar.php
?>
<div class="sidebar">
    <div class="sidebar-brand">
        <h3><i class="fas fa-paw"></i></h3>
        <h3>Puphub</h3>
        <small>Panel de Control</small>
    </div>
    
    <nav class="nav flex-column">
        <a href="index.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
            <i class="fas fa-tachometer-alt"></i>
            <span>Dashboard</span>
        </a>
        
        <a href="productos.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'productos.php' ? 'active' : ''; ?>">
            <i class="fas fa-box"></i>
            <span>Productos</span>
        </a>
        
        <a href="usuarios.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'usuarios.php' ? 'active' : ''; ?>">
            <i class="fas fa-users"></i>
            <span>Usuarios</span>
        </a>
        
        <a href="citas.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'citas.php' ? 'active' : ''; ?>">
            <i class="fas fa-calendar-check"></i>
            <span>Citas</span>
        </a>
        
        <a href="ventas.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'ventas.php' ? 'active' : ''; ?>">
            <i class="fas fa-shopping-cart"></i>
            <span>Ventas</span>
        </a>
        
        <a href="inventario.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'inventario.php' ? 'active' : ''; ?>">
            <i class="fas fa-warehouse"></i>
            <span>Inventario</span>
        </a>
        
        <?php if ($_SESSION['user_role'] === 'ADMIN'): ?>
        <a href="configuracion.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'configuracion.php' ? 'active' : ''; ?>">
            <i class="fas fa-cog"></i>
            <span>Configuración</span>
        </a>
        <?php endif; ?>
        
        <div class="mt-5 px-3">
            <a href="../perfil.php" class="nav-link">
                <i class="fas fa-user-circle"></i>
                <span>Mi Perfil</span>
            </a>
            <a href="../logout.php" class="nav-link text-danger">
                <i class="fas fa-sign-out-alt"></i>
                <span>Cerrar Sesión</span>
            </a>
        </div>
    </nav>
</div>