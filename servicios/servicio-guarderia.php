<?php
$page_title = "Internado y Guardería Canina";
require_once '../includes/header.php';
?>

<main class="py-5 px-4">
  <p><a href="../index.php#servicios" class="text-secondary small text-decoration-none fw-bold">← Volver a Servicios</a></p>
  
  <div class="row g-5 align-items-center">
    <div class="col-md-6">
      <h1 class="fw-bold display-5 text-azul">Guardería y Hotel para Mascotas</h1>
      
      <p class="lead mt-3">
        Cuando no puedas estar con tu perro, le ofrecemos un hogar lejos de casa con <strong>supervisión 24/7</strong>, juegos y mucho amor.
      </p>

      <h4 class="mt-4 fw-bold text-dark">Nuestros Planes:</h4>
      
      <ul class="list-unstyled list-beneficios">
        <li class="fw-bold mb-2">☀️ <strong>Día Completo:</strong> De 8 am a 6 pm. Incluye 2 paseos y 3 sesiones de juego.</li>
        <li class="fw-bold mb-2">🌙 <strong>Hotel (Noche):</strong> Cuidado nocturno en habitaciones individuales y confortables.</li>
        <li class="fw-bold mb-2">⭐ <strong>Internado de Lujo:</strong> Habitación privada y atención personalizada.</li>
      </ul>

      <p class="mt-4 text-muted">
        Tu perro nunca se sentirá solo. Aseguramos un ambiente seguro y estimulante para su bienestar.
      </p>
      
      <?php if(isset($_SESSION['user_id'])): ?>
        <button class="btn btn-lg btn-primary fw-bold mt-3" data-bs-toggle="modal" data-bs-target="#modalCitaGuarderia">
          📅 Ver Disponibilidad
        </button>
      <?php else: ?>
        <button class="btn btn-lg btn-primary fw-bold mt-3" data-bs-toggle="modal" data-bs-target="#modalIngreso">
          📅 Ver Disponibilidad
        </button>
      <?php endif; ?>
    </div>
    
    <div class="col-md-6 text-center">
      <img src="../imagenes/guarde.jpg" alt="Perros jugando en nuestra guardería" class="img-fluid rounded-3 shadow">
      <div class="text-muted small mt-3">Nuestras instalaciones cuentan con áreas verdes seguras y supervisión constante.</div>
    </div>
  </div>
</main>

<!-- CTA relacionada -->
<div class="bg-info text-white text-center py-4 mt-4">
  <div class="container">
    <h4 class="fw-bold">✈️ ¿Viajas por mucho tiempo?</h4>
    <p>Nuestro hotel es la solución perfecta para estancias largas con seguimiento veterinario.</p>
    <a href="../index.php#contacto" class="btn btn-light fw-bold mt-2">Llamar para Consultar</a>
  </div>
</div>

<!-- Modal específico para guardería -->
<?php if(isset($_SESSION['user_id'])): ?>
<div class="modal fade" id="modalCitaGuarderia" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title fw-bold">🏡 Reservar Guardería</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form action="../php/procesar_cita.php" method="POST">
          <input type="hidden" name="servicio" value="guarderia">
          <input type="hidden" name="usuario_id" value="<?php echo $_SESSION['user_id']; ?>">
          
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Fecha de ingreso</label>
              <input type="date" class="form-control" name="fecha_ingreso" required min="<?php echo date('Y-m-d'); ?>">
            </div>
            
            <div class="col-md-6 mb-3">
              <label class="form-label">Fecha de salida</label>
              <input type="date" class="form-control" name="fecha_salida" required min="<?php echo date('Y-m-d'); ?>">
            </div>
          </div>
          
          <div class="mb-3">
            <label class="form-label">Tipo de estadía</label>
            <select class="form-select" name="tipo_estadia" required>
              <option value="">Seleccionar</option>
              <option value="dia_completo">Día Completo (S/. 30)</option>
              <option value="noche">Hotel (Noche) (S/. 50)</option>
              <option value="internado_lujo">Internado de Lujo (S/. 80)</option>
              <option value="semanal">Paquete Semanal (S/. 300)</option>
            </select>
          </div>
          
          <div class="mb-3">
            <label class="form-label">Número de mascotas</label>
            <input type="number" class="form-control" name="numero_mascotas" min="1" max="3" value="1" required>
          </div>
          
          <div class="mb-3">
            <label class="form-label">Alimentación especial</label>
            <textarea class="form-control" name="alimentacion" rows="2" placeholder="Indica si tu mascota requiere dieta especial..."></textarea>
          </div>
          
          <div class="mb-3">
            <label class="form-label">Medicamentos (si aplica)</label>
            <textarea class="form-control" name="medicamentos" rows="2" placeholder="Horarios y dosis de medicamentos..."></textarea>
          </div>
          
          <div class="alert alert-success">
            <small><i class="fas fa-check-circle"></i> Incluye: Alimentación 2 veces al día, paseos 3 veces, reporte diario con fotos.</small>
          </div>
          
          <div class="text-end">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-primary fw-bold">Solicitar Reserva</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

<?php require_once '../includes/footer.php'; ?>