<?php
session_start();
$page_title = "Cuidado de la Salud Veterinaria";
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Puphub - <?php echo $page_title; ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../estilos.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

  <!-- Header simplificado -->
  <div class="container p-0">
    <header class="border-bottom py-3 px-4">
      <div class="row align-items-center">
        <div class="col-6 col-md-3">
          <a href="../index.php">
            <img src="../imagenes/logo.svg" alt="Logo de Puphub" class="img-fluid" style="max-height: 50px;">
          </a>
        </div>

        <nav class="col-md-6 d-none d-md-flex justify-content-center">
          <a href="../index.php" class="col-auto px-3 text-decoration-none">Inicio</a>
          <a href="../acerca_de_nosotros.php" class="col-auto px-3 text-decoration-none">Acerca de Nosotros</a>
          <a href="../index.php#servicios" class="col-auto px-3 text-decoration-none text-primary fw-bolder">Servicios</a>
          <a href="../productos.php" class="col-auto px-3 text-decoration-none">Productos</a>
          <a href="../index.php#contacto" class="col-auto px-3 text-decoration-none">Contáctenos</a>
        </nav>

        <div class="col-6 col-md-3 text-end">
          <?php if(isset($_SESSION['user_id'])): ?>
            <!-- Usuario logueado -->
            <div class="dropdown">
              <button class="btn btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                <?php echo isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Mi Cuenta'; ?>
              </button>
              <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="../perfil.php">Mi Perfil</a></li>
                <li><a class="dropdown-item" href="../productos.php">Carrito</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="../php/logout.php">Cerrar Sesión</a></li>
              </ul>
            </div>
          <?php else: ?>
            <!-- Usuario no logueado -->
            <button class="btn btn-primary fw-bold me-2" data-bs-toggle="modal" data-bs-target="#modalIngreso">
              🔑 Ingresar
            </button>
            <button class="btn btn-success fw-bold" data-bs-toggle="modal" data-bs-target="#modalRegistro">
              📝 Registrarse
            </button>
          <?php endif; ?>
        </div>
      </div>
    </header>

<main class="py-5 px-4">
  <p><a href="../index.php#servicios" class="text-secondary small text-decoration-none fw-bold" onclick="history.back(); return false;">← Volver a Servicios</a></p>
  
  <div class="row g-5 align-items-center">
    <div class="col-md-6">
      <h1 class="fw-bold display-5 text-azul">Clínica Veterinaria y Medicina Avanzada</h1>
      
      <p class="lead mt-3">
        Ofrecemos un cuidado integral de la salud, desde chequeos de rutina hasta diagnósticos complejos con <strong>tecnología de punta</strong>.
      </p>

      <h4 class="mt-4 fw-bold text-dark">Nuestra Cobertura Incluye:</h4>
      
      <ul class="list-unstyled list-beneficios">
        <li class="fw-bold mb-2">🩺 <strong>Diagnóstico Avanzado:</strong> Rayos X, Ecografías y Análisis de Laboratorio completos.</li>
        <li class="fw-bold mb-2">💉 <strong>Prevención:</strong> Vacunación, Desparasitación y Planes de Salud Preventivos personalizados.</li>
        <li class="fw-bold mb-2">💊 <strong>Tratamiento:</strong> Cirugías menores, control post-operatorio y Farmacia especializada.</li>
      </ul>

      <p class="mt-4 text-muted">
        La salud de tu mascota es nuestra prioridad. Contamos con veterinarios certificados en diversas especialidades.
      </p>
      
      <?php if(isset($_SESSION['user_id'])): ?>
        <button class="btn btn-lg btn-primary fw-bold mt-3" data-bs-toggle="modal" data-bs-target="#modalCitaVeterinaria">
          💉 Agendar Consulta Veterinaria
        </button>
      <?php else: ?>
        <button class="btn btn-lg btn-primary fw-bold mt-3" data-bs-toggle="modal" data-bs-target="#modalIngreso">
          💉 Agendar Consulta Veterinaria
        </button>
      <?php endif; ?>
    </div>
    
    <div class="col-md-6 text-center">
      <img src="../imagenes/doctora.webp" alt="Veterinario examinando mascota" class="img-fluid rounded-3 shadow">
      <div class="text-muted small mt-3">Tecnología de imagen para diagnósticos precisos y confiables.</div>
    </div>
  </div>
  
  <!-- Servicios adicionales -->
  <div class="row mt-5 g-4">
    <div class="col-md-4">
      <div class="card h-100 border-0 shadow-sm">
        <div class="card-body text-center">
          <div class="display-4 text-primary mb-3">💉</div>
          <h5 class="card-title fw-bold">Vacunación</h5>
          <p class="card-text small">Programas completos de vacunación para perros y gatos.</p>
          <button class="btn btn-outline-primary btn-sm" onclick="alert('Próximamente: Calendario de vacunación')">Ver Calendario</button>
        </div>
      </div>
    </div>
    
    <div class="col-md-4">
      <div class="card h-100 border-0 shadow-sm">
        <div class="card-body text-center">
          <div class="display-4 text-primary mb-3">🩺</div>
          <h5 class="card-title fw-bold">Chequeo General</h5>
          <p class="card-text small">Examen completo de salud y bienestar de tu mascota.</p>
          <button class="btn btn-outline-primary btn-sm" onclick="document.querySelector('#modalCitaVeterinaria').click()">Solicitar</button>
        </div>
      </div>
    </div>
    
    <div class="col-md-4">
      <div class="card h-100 border-0 shadow-sm">
        <div class="card-body text-center">
          <div class="display-4 text-primary mb-3">💊</div>
          <h5 class="card-title fw-bold">Farmacia</h5>
          <p class="card-text small">Medicamentos y productos veterinarios con receta.</p>
          <a href="../productos.php" class="btn btn-outline-primary btn-sm">Ver Productos</a>
        </div>
      </div>
    </div>
  </div>
</main>

<!-- CTA relacionada -->
<div class="bg-danger text-white text-center py-4 mt-4">
  <div class="container">
    <h4 class="fw-bold">💊 ¿Necesitas vitaminas o medicinas?</h4>
    <p>Visita nuestra sección de productos para encontrar todo lo que tu mascota necesita, con la confianza de <strong>Puphub</strong>.</p>
    <a href="../productos.php" class="btn btn-light fw-bold mt-2">Ver Farmacia</a>
  </div>
</div>

<!-- Modal de Ingreso (simplificado) -->
<div class="modal fade" id="modalIngreso" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title fw-bold">🔑 Ingresar a Puphub</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form action="../php/procesar_login.php" method="POST">
          <div class="mb-3">
            <label class="form-label">Correo Electrónico</label>
            <input type="email" class="form-control" name="email" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Contraseña</label>
            <input type="password" class="form-control" name="password" required>
          </div>
          <div class="text-end">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-primary">Iniciar Sesión</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Modal de Registro (simplificado) -->
<div class="modal fade" id="modalRegistro" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title fw-bold">📝 Registrarse en Puphub</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form action="../php/procesar_registro.php" method="POST">
          <div class="mb-3">
            <label class="form-label">Nombre Completo</label>
            <input type="text" class="form-control" name="nombre_completo" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Correo Electrónico</label>
            <input type="email" class="form-control" name="email" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Contraseña</label>
            <input type="password" class="form-control" name="password" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Confirmar Contraseña</label>
            <input type="password" class="form-control" name="password_confirmacion" required>
          </div>
          <div class="text-end">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-success">Crear Cuenta</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Modal específico para consulta veterinaria -->
<?php if(isset($_SESSION['user_id'])): ?>
<div class="modal fade" id="modalCitaVeterinaria" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title fw-bold">🏥 Agendar Consulta Veterinaria</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form action="../php/procesar_cita.php" method="POST">
          <input type="hidden" name="servicio" value="veterinaria">
          <input type="hidden" name="usuario_id" value="<?php echo $_SESSION['user_id']; ?>">
          
          <div class="mb-3">
            <label class="form-label">Tipo de consulta</label>
            <select class="form-select" name="tipo_consulta" required>
              <option value="">Seleccionar</option>
              <option value="consulta_general">Consulta General (S/. 50)</option>
              <option value="chequeo_completo">Chequeo Completo (S/. 80)</option>
              <option value="vacunacion">Vacunación (S/. 40)</option>
              <option value="emergencia">Emergencia (S/. 120)</option>
              <option value="control">Control (S/. 30)</option>
              <option value="especialista">Consulta con Especialista (S/. 100)</option>
            </select>
          </div>
          
          <div class="mb-3">
            <label class="form-label">Fecha deseada</label>
            <input type="date" class="form-control" name="fecha" required min="<?php echo date('Y-m-d'); ?>">
          </div>
          
          <div class="mb-3">
            <label class="form-label">Horario preferido</label>
            <select class="form-select" name="horario" required>
              <option value="">Seleccionar</option>
              <option value="09:00">09:00 AM</option>
              <option value="10:30">10:30 AM</option>
              <option value="12:00">12:00 PM</option>
              <option value="15:00">03:00 PM</option>
              <option value="16:30">04:30 PM</option>
              <option value="18:00">06:00 PM</option>
            </select>
          </div>
          
          <div class="mb-3">
            <label class="form-label">Especie</label>
            <select class="form-select" name="especie" required>
              <option value="">Seleccionar</option>
              <option value="perro">Perro</option>
              <option value="gato">Gato</option>
              <option value="ave">Ave</option>
              <option value="roedor">Roedor</option>
              <option value="reptil">Reptil</option>
              <option value="otro">Otro</option>
            </select>
          </div>
          
          <div class="mb-3">
            <label class="form-label">Motivo de la consulta</label>
            <textarea class="form-control" name="motivo" rows="3" required placeholder="Describa los síntomas o motivo de la consulta..."></textarea>
          </div>
          
          <div class="mb-3">
            <label class="form-label">¿Es primera vez en nuestra clínica?</label>
            <div>
              <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="primera_vez" id="siPrimera" value="si" required>
                <label class="form-check-label" for="siPrimera">Sí</label>
              </div>
              <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="primera_vez" id="noPrimera" value="no">
                <label class="form-check-label" for="noPrimera">No</label>
              </div>
            </div>
          </div>
          
          <div class="alert alert-info">
            <small><i class="fas fa-info-circle"></i> Para emergencias, llame al (51) 966-589-123. Horario de emergencias: 24/7.</small>
          </div>
          
          <div class="text-end">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-primary fw-bold">Agendar Cita</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

<footer class="bg-dark text-white py-4 mt-4"> 
  <div class="container text-center text-md-start">
    <div class="row">
      <div class="col-md-4 mb-3">
        <h6 class="fw-bold">Síguenos en redes sociales</h6>
        <a href="#"><img src="../imagenes/facebook.png" alt="Facebook" class="me-2"></a>
        <a href="#"><img src="../imagenes/insta.png" alt="Instagram"></a>
      </div>

      <div class="col-md-4 mb-3 small">
        <h6 class="fw-bold">Internado y guardería</h6>
        <p class="mb-1 fw-bold">(51) 999 888-862</p>
        <p>Av. Los Ruiseñores 1234<br>Santa Anita - Lima</p>
      </div>

      <div class="col-md-4 mb-3 small">
        <h6 class="fw-bold">Cuidado de la salud (Veterinaria)</h6>
        <p class="mb-1 fw-bold">(51) 966-589-123</p>
        <p>Av. Gallito de las Rocas 567<br>San Borja - Lima</p>
      </div>
    </div>
    <div class="text-center pt-3 border-top mt-3">
      <p class="mb-0 small text-secondary">&copy; <?php echo date('Y'); ?> Puphub. Todos los derechos reservados.</p>
    </div>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Función para redirigir a productos
function irAProductos() {
  window.location.href = '../productos.php';
}

// Función para abrir modal de cita
function abrirCita() {
  <?php if(isset($_SESSION['user_id'])): ?>
    var modal = new bootstrap.Modal(document.getElementById('modalCitaVeterinaria'));
    modal.show();
  <?php else: ?>
    var modal = new bootstrap.Modal(document.getElementById('modalIngreso'));
    modal.show();
  <?php endif; ?>
}

// Agregar evento a botones "Solicitar"
document.addEventListener('DOMContentLoaded', function() {
  // Botón "Ver Calendario"
  document.querySelectorAll('.btn-outline-primary').forEach(btn => {
    if (btn.textContent.includes('Calendario')) {
      btn.onclick = function() { alert('Próximamente: Calendario de vacunación'); };
    }
  });
});
</script>
</body>
</html>