<?php

if (session_status()=== PHP_SESSION_NONE) {
session_start();
}
$page_title = "Servicio de Aseo (Grooming)";
require_once '../includes/header.php';
?>

<main class="py-5 px-4">
  <p><a href="../index.php#servicios" class="text-secondary small text-decoration-none fw-bold" onclick="history.back(); return false;">← Volver a Servicios</a></p>
  
  <div class="row g-5 align-items-center">
    <div class="col-md-6 order-md-1">
      <h1 class="fw-bold display-5 text-azul mb-4">🛁 Aseo (Grooming) <strong>Profesional</strong></h1>
      
      <p class="lead fw-normal"> 
        Un pelaje sano es sinónimo de <strong>felicidad y bienestar</strong>. Nuestro servicio de Grooming va <strong>más allá</strong> del corte, garantizando el cuidado experto de la piel y el pelo de tu mascota.
      </p>
      
      <ul class="list-unstyled fw-bold text-dark list-beneficios"> 
        <li>✅ Cortes de raza y personalizados por <strong>estilistas certificados</strong></li>
        <li>✅ Limpieza de oídos, glándulas y <strong>corte de uñas</strong> incluido</li>
        <li>✅ Tratamientos <strong>anti-pulgas y garrapatas</strong> preventivos</li>
        <li>✅ Utilización de productos <strong>hipoalergénicos</strong> de <strong>máxima calidad</strong></li>
      </ul>
      
      <?php if(isset($_SESSION['user_id'])): ?>
        <button class="btn btn-lg btn-primary fw-bold mt-3" data-bs-toggle="modal" data-bs-target="#modalCitaGrooming">
          📅 <strong>Solicitar una Cita Ahora</strong>
        </button>
      <?php else: ?>
        <button class="btn btn-lg btn-primary fw-bold mt-3" data-bs-toggle="modal" data-bs-target="#modalIngreso">
          📅 <strong>Solicitar una Cita Ahora</strong>
        </button>
      <?php endif; ?>
    </div>
    
    <div class="col-md-6 order-md-0 mt-4 mt-md-0">
      <img src="../imagenes/cortep.webp" alt="Grooming profesional para mascotas" class="img-fluid rounded-3 shadow">
      <div class="text-muted small mt-3">Cortes realizados por <strong>expertos</strong> en todas las razas caninas y felinas.</div>
    </div>
  </div>

  <!-- Resto del contenido específico de aseo... -->
  
  
<?php if(isset($_SESSION['user_id'])): ?>
<div class="modal fade" id="modalCitaGrooming" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title">🛁 Solicitar Cita de Aseo</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form action="../php/procesar_cita.php" method="POST">
          <input type="hidden" name="servicio" value="aseo">
          <input type="hidden" name="usuario_id" value="<?php echo $_SESSION['user_id']; ?>">
          
          <div class="mb-3">
            <label class="form-label">Tipo de Servicio *</label>
            <select class="form-select" name="tipo_servicio" required>
              <option value="">Seleccionar servicio</option>
              <option value="corte_basico">Corte Básico (S/. 40)</option>
              <option value="corte_raza">Corte de Raza (S/. 60)</option>
              <option value="baño_completo">Baño Completo + Corte (S/. 80)</option>
              <option value="spa_completo">Spa Completo (S/. 100)</option>
            </select>
          </div>
          
          <div class="mb-3">
            <label class="form-label">Fecha Deseada *</label>
            <input type="date" class="form-control" name="fecha" required 
                   min="<?php echo date('Y-m-d'); ?>">
          </div>
          
          <div class="mb-3">
            <label class="form-label">Hora Preferida *</label>
            <select class="form-select" name="hora" required>
              <option value="">Seleccionar hora</option>
              <option value="09:00">09:00 AM</option>
              <option value="10:30">10:30 AM</option>
              <option value="12:00">12:00 PM</option>
              <option value="15:00">03:00 PM</option>
              <option value="16:30">04:30 PM</option>
              <option value="18:00">06:00 PM</option>
            </select>
          </div>
          
          <div class="mb-3">
            <label class="form-label">Tipo de Mascota *</label>
            <select class="form-select" name="tipo_mascota" required>
              <option value="">Seleccionar</option>
              <option value="perro">Perro</option>
              <option value="gato">Gato</option>
              <option value="otro">Otro</option>
            </select>
          </div>
          
          <div class="mb-3">
            <label class="form-label">Tamaño</label>
            <select class="form-select" name="tamano_mascota">
              <option value="">Seleccionar</option>
              <option value="pequeno">Pequeño (hasta 10kg)</option>
              <option value="mediano">Mediano (11-25kg)</option>
              <option value="grande">Grande (26-40kg)</option>
              <option value="gigante">Gigante (+40kg)</option>
            </select>
          </div>
          
          <div class="mb-3">
            <label class="form-label">Observaciones</label>
            <textarea class="form-control" name="observaciones" rows="3" 
                      placeholder="Especificaciones del corte, alergias, comportamiento especial..."></textarea>
          </div>
          
          <div class="alert alert-info">
            <small><i class="fas fa-info-circle"></i> Todos los servicios incluyen: limpieza de oídos, corte de uñas y cepillado.</small>
          </div>
          
          <div class="text-end">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-primary">
              <i class="fas fa-calendar-check me-1"></i> Solicitar Cita
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>
  

<?php require_once '../includes/footer.php'; ?>