<?php
session_start();
$page_title = "Acerca de Nosotros";
require_once 'includes/header.php';
?>

  <!-- SECCIÓN PRINCIPAL -->
  <div class="py-5">
    <h1 class="display-5 fw-bold text-primary text-center mb-5">Nuestra Misión</h1>

    <div class="row align-items-center mb-5">
      <div class="col-md-6">
        <!-- RUTA CORREGIDA -->
        <img src="imagenes/equipo.jpg" alt="Equipo Puphub" class="img-fluid rounded-3 shadow-lg">
      </div>
      <div class="col-md-6 mt-4 mt-md-0">
        <p class="lead">
          En Puphub, creemos que las mascotas son parte de la familia. Nuestra misión es brindar un entorno seguro, amoroso y profesional para el cuidado integral de perros y gatos, combinando salud, higiene, y bienestar emocional.
        </p>
        <p class="text-muted">
          Desde el primer ladrido hasta el último ronroneo, estamos comprometidos a ser el socio de confianza que tu mascota necesita para vivir una vida plena y feliz.
        </p>
      </div>
    </div>

    <h2 class="fw-bold text-azul text-center mb-4">Nuestros Valores</h2>
    <div class="row text-center g-4">
      <div class="col-md-4 mb-4">
        <i class="fs-1 text-primary">❤️</i>
        <h5 class="fw-bold mt-2">Amor y Dedicación</h5>
        <p class="text-muted small">Tratamos a cada mascota con la misma calidez y atención que le darías tú mismo.</p>
      </div>
      
      <div class="col-md-4 mb-4">
        <i class="fs-1 text-primary">🩺</i>
        <h5 class="fw-bold mt-2">Profesionalismo</h5>
        <p class="text-muted small">Contamos con veterinarios y groomers certificados que garantizan el bienestar físico y emocional.</p>
      </div>
      
      <div class="col-md-4 mb-4">
        <i class="fs-1 text-primary">🤝</i>
        <h5 class="fw-bold mt-2">Transparencia y Confianza</h5>
        <p class="text-muted small">Mantenemos una comunicación abierta y honesta en cada paso del cuidado.</p>
      </div>
    </div>

  </div>

<?php require_once 'includes/footer.php'; ?>