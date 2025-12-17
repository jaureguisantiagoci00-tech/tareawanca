<?php

if (session_status()=== PHP_SESSION_NONE) {
session_start();
}
$base_url = '/veterinaria/';
$page_title = $page_title ?? 'Puphub';

if (!isset($page_title) || empty($page_title)) {
    $page_title = 'Puphub';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo $page_title; ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="<?php echo $base_url; ?>estilos.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
  <div class="container p-0">
    <header class="border-bottom py-3 px-4">
      <div class="row align-items-center">
        <div class="col-6 col-md-3">
          <a href="<?php echo $base_url; ?>index.php">
            <img src="<?php echo $base_url; ?>imagenes/logo.svg" alt="Logo de Puphub" class="img-fluid" style="max-height: 50px;">
          </a>
        </div>

        <nav class="col-md-6 d-none d-md-flex justify-content-center">
          <a href="<?php echo $base_url; ?>index.php" class="col-auto px-3 text-decoration-none">Inicio</a>
          <a href="<?php echo $base_url; ?>acerca_de_nosotros.php" class="col-auto px-3 text-decoration-none">Acerca de Nosotros</a>
          <a href="<?php echo $base_url; ?>index.php#servicios" class="col-auto px-3 text-decoration-none text-primary fw-bolder">Servicios</a>
          <a href="<?php echo $base_url; ?>productos.php" class="col-auto px-3 text-decoration-none">Productos</a>
          <a href="<?php echo $base_url; ?>index.php#contacto" class="col-auto px-3 text-decoration-none">Contáctenos</a>
          
          <!-- ✅ CORREGIDO: dashboard/index.php -->
          <?php if(isset($_SESSION['user_role']) && ($_SESSION['user_role'] === 'ADMIN' || $_SESSION['user_role'] === 'VENDEDOR')): ?>
          <a href="<?php echo $base_url; ?>dashboard/index.php" class="col-auto px-3 text-decoration-none text-warning fw-bold">
            <i class="fas fa-tachometer-alt me-1"></i> Dashboard
          </a>
          <?php endif; ?>
        </nav>

        <div class="col-6 col-md-3 text-end">
          <?php if(isset($_SESSION['user_id'])): ?>
            <div class="dropdown">
              <button class="btn btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                <?php echo isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Mi Cuenta'; ?>
              </button>
              <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="<?php echo $base_url; ?>perfil.php">
                  <i class="fas fa-user-circle me-2"></i>Mi Perfil
                </a></li>
                
                <!-- ✅ CORREGIDO: dashboard/index.php -->
                <?php if(isset($_SESSION['user_role']) && ($_SESSION['user_role'] === 'ADMIN' || $_SESSION['user_role'] === 'VENDEDOR')): ?>
                <li><a class="dropdown-item text-warning" href="<?php echo $base_url; ?>dashboard/index.php">
                  <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                </a></li>
                <?php endif; ?>
                
                <li><a class="dropdown-item" href="<?php echo $base_url; ?>productos.php">
                  <i class="fas fa-shopping-cart me-2"></i>Carrito
                </a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-danger" href="<?php echo $base_url; ?>logout.php">
                  <i class="fas fa-sign-out-alt me-2"></i>Cerrar Sesión
                </a></li>
              </ul>
            </div>
          <?php else: ?>
            <button class="btn btn-primary fw-bold me-2" data-bs-toggle="modal" data-bs-target="#modalIngreso">
              <i class="fas fa-key me-1"></i> Ingresar
            </button>
            <button class="btn btn-success fw-bold" data-bs-toggle="modal" data-bs-target="#modalRegistro">
              <i class="fas fa-user-plus me-1"></i> Registrarse
            </button>
          <?php endif; ?>
        </div>
      </div>
    </header>