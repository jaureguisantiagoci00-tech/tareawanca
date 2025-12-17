<?php
$page_title = "Servicio de Baño Rápido y Spa";
require_once '../includes/header.php';
?>

<main class="py-5 px-4">
  <p><a href="../index.php#servicios" class="text-secondary small text-decoration-none fw-bold">← Volver a Servicios</a></p>
  
  <div class="row g-5 align-items-center">
    <div class="col-md-6">
      <h1 class="fw-bold display-5 text-azul">Baño Express y Spa Canino</h1>
      
      <p class="lead mt-3">
        Dale a tu perro la limpieza y frescura que necesita <strong>sin estrés</strong>. Usamos agua tibia y productos especializados para un <strong>brillo óptimo</strong>.
      </p>

      <h4 class="mt-4 fw-bold text-dark">Nuestros Paquetes:</h4>
      
      <ul class="list-unstyled list-beneficios">
        <li class="fw-bold mb-2">🛁 <strong>Baño Rápido:</strong> Champú básico y secado a fondo.</li>
        <li class="fw-bold mb-2">💆 <strong>Spa Antialérgico:</strong> Baño con champú medicado y masaje relajante.</li>
        <li class="fw-bold mb-2">💨 <strong>Tratamiento Anti-Olor:</strong> Desodorización profunda y acondicionador premium.</li>
      </ul>

      <p class="mt-4 text-muted">
        La limpieza regular no solo es estética, sino que <strong>previene</strong> problemas de piel y mejora la calidad de vida de tu mascota.
      </p>
      
      <?php if(isset($_SESSION['user_id'])): ?>
        <button class="btn btn-lg btn-primary fw-bold mt-3" data-bs-toggle="modal" data-bs-target="#modalCitaBano">
          📅 Reservar Hora de Baño
        </button>
      <?php else: ?>
        <button class="btn btn-lg btn-primary fw-bold mt-3" data-bs-toggle="modal" data-bs-target="#modalIngreso">
          📅 Reservar Hora de Baño
        </button>
      <?php endif; ?>
    </div>
    
    <div class="col-md-6">
      <img src="../imagenes/aseoprinci.jpg" alt="Perro siendo bañado en nuestro spa" class="img-fluid rounded-3 shadow">
      <div class="text-muted small mt-3">Equipo profesional y productos de calidad para el cuidado de tu mascota.</div>
    </div>
  </div>
</main>

<!-- CTA relacionada -->
<div class="bg-warning text-dark text-center py-4 mt-4">
  <div class="container">
    <h4 class="fw-bold">✂️ ¿Necesitas un Corte de Pelo?</h4>
    <p>Nuestro servicio de Aseo (<strong>Grooming</strong>) incluye un corte de estilista profesional.</p>
    <a href="servicio-aseo.php" class="btn btn-dark fw-bold mt-2">Ver Servicio de Grooming</a>
  </div>
</div>

<!-- Modal específico para cita de baño -->
<?php if(isset($_SESSION['user_id'])): ?>
<div class="modal fade" id="modalCitaBano" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title fw-bold">🛁 Reservar Hora de Baño</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form action="../php/procesar_cita.php" method="POST">
          <input type="hidden" name="servicio" value="bano">
          <input type="hidden" name="usuario_id" value="<?php echo $_SESSION['user_id']; ?>">
          
          <div class="mb-3">
            <label class="form-label">Fecha deseada</label>
            <input type="date" class="form-control" name="fecha" required min="<?php echo date('Y-m-d'); ?>">
          </div>
          
          <div class="mb-3">
            <label class="form-label">Tipo de Baño</label>
            <select class="form-select" name="tipo_bano" required>
              <option value="">Seleccionar</option>
              <option value="rapido">Baño Rápido (S/. 25)</option>
              <option value="spa_antialergico">Spa Antialérgico (S/. 40)</option>
              <option value="anti_olor">Tratamiento Anti-Olor (S/. 35)</option>
              <option value="completo">Paquete Completo (S/. 50)</option>
            </select>
          </div>
          
          <div class="mb-3">
            <label class="form-label">Tamaño de la mascota</label>
            <select class="form-select" name="tamano_mascota" required>
              <option value="">Seleccionar</option>
              <option value="pequeno">Pequeño (hasta 10kg)</option>
              <option value="mediano">Mediano (11-25kg)</option>
              <option value="grande">Grande (26-40kg)</option>
              <option value="gigante">Gigante (+40kg)</option>
            </select>
          </div>
          
          <div class="mb-3">
            <label class="form-label">Observaciones especiales</label>
            <textarea class="form-control" name="observaciones" rows="3" placeholder="Alergias, comportamiento, necesidades especiales..."></textarea>
          </div>
          
          <div class="alert alert-info">
            <small><i class="fas fa-info-circle"></i> Todos los baños incluyen: secado profesional, cepillado y perfume suave.</small>
          </div>
          
          <div class="text-end">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-primary fw-bold">Reservar Hora</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

<?php require_once '../includes/footer.php'; ?>