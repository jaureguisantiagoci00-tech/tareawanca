<?php
$page_title = "Entrenamiento Canino y Etología";
require_once '../includes/header.php';
?>

<main class="py-5 px-4">
  <p><a href="../index.php#servicios" class="text-secondary small text-decoration-none fw-bold">← Volver a Servicios</a></p>
  
  <div class="row g-5 align-items-center">
    <div class="col-md-6">
      <h1 class="fw-bold display-5 text-azul">Obediencia y Etología Canina</h1>
      
      <p class="lead mt-3">
        El entrenamiento basado en <strong>refuerzo positivo</strong> fortalece el vínculo y corrige comportamientos no deseados de forma <strong>efectiva y amorosa</strong>.
      </p>

      <h4 class="mt-4 fw-bold text-dark">Nuestros Programas:</h4>
      
      <ul class="list-unstyled list-beneficios">
        <li class="fw-bold mb-2">🐶 <strong>Básico:</strong> Órdenes básicas (sentado, quieto, venir).</li>
        <li class="fw-bold mb-2">🧠 <strong>Avanzado:</strong> Corrección de problemas de conducta (ansiedad por separación, ladridos excesivos).</li>
        <li class="fw-bold mb-2">🤝 <strong>Socialización:</strong> Sesiones controladas para cachorros y perros adultos.</li>
      </ul>

      <p class="mt-4 text-muted">
        Un perro entrenado es un perro feliz. Te enseñamos a <strong>comunicarte eficazmente</strong> con tu mejor amigo.
      </p>
      
      <?php if(isset($_SESSION['user_id'])): ?>
        <button class="btn btn-lg btn-primary fw-bold mt-3" data-bs-toggle="modal" data-bs-target="#modalCitaEntrenamiento">
          🚀 Inscribirse a Clases
        </button>
      <?php else: ?>
        <button class="btn btn-lg btn-primary fw-bold mt-3" data-bs-toggle="modal" data-bs-target="#modalIngreso">
          🚀 Inscribirse a Clases
        </button>
      <?php endif; ?>
    </div>
    
    <div class="col-md-6 text-center">
      <img src="../imagenes/entrena.webp" alt="Entrenador trabajando con perro" class="img-fluid rounded-3 shadow">
      <div class="text-muted small mt-3">Nuestros etólogos trabajan con respeto y positividad.</div>
    </div>
  </div>
</main>

<!-- CTA relacionada -->
<div class="bg-success text-white text-center py-4 mt-4">
  <div class="container">
    <h4 class="fw-bold">🐾 ¿Necesitas un lugar seguro para practicar?</h4>
    <p>Nuestra guardería ofrece un ambiente ideal para la socialización y el juego supervisado.</p>
    <a href="servicio-guarderia.php" class="btn btn-light fw-bold mt-2">Ver Guardería</a>
  </div>
</div>

<!-- Modal específico para entrenamiento -->
<?php if(isset($_SESSION['user_id'])): ?>
<div class="modal fade" id="modalCitaEntrenamiento" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title fw-bold">🐕 Inscripción a Entrenamiento</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form action="../php/procesar_cita.php" method="POST">
          <input type="hidden" name="servicio" value="entrenamiento">
          <input type="hidden" name="usuario_id" value="<?php echo $_SESSION['user_id']; ?>">
          
          <div class="mb-3">
            <label class="form-label">Programa de entrenamiento</label>
            <select class="form-select" name="programa" required>
              <option value="">Seleccionar programa</option>
              <option value="basico">Básico (4 semanas - S/. 200)</option>
              <option value="avanzado">Avanzado (6 semanas - S/. 300)</option>
              <option value="socializacion">Socialización (8 sesiones - S/. 150)</option>
              <option value="problemas_conducta">Corrección de Problemas (10 sesiones - S/. 400)</option>
              <option value="personalizado">Programa Personalizado (Consultar precio)</option>
            </select>
          </div>
          
          <div class="mb-3">
            <label class="form-label">Edad de la mascota</label>
            <select class="form-select" name="edad_mascota" required>
              <option value="">Seleccionar</option>
              <option value="cachorro">Cachorro (2-6 meses)</option>
              <option value="joven">Joven (7-18 meses)</option>
              <option value="adulto">Adulto (1.5-7 años)</option>
              <option value="senior">Senior (+7 años)</option>
            </select>
          </div>
          
          <div class="mb-3">
            <label class="form-label">Horario preferido</label>
            <select class="form-select" name="horario" required>
              <option value="">Seleccionar</option>
              <option value="mañana">Mañana (9:00 - 12:00)</option>
              <option value="tarde">Tarde (15:00 - 18:00)</option>
              <option value="sabado">Sábados (10:00 - 13:00)</option>
              <option value="domingo">Domingos (10:00 - 13:00)</option>
            </select>
          </div>
          
          <div class="mb-3">
            <label class="form-label">Problemas de conducta específicos (si aplica)</label>
            <textarea class="form-control" name="problemas_conducta" rows="3" placeholder="Ej: Ladra mucho, destruye cosas, miedoso, agresivo..."></textarea>
          </div>
          
          <div class="mb-3">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="incluye_propietario" id="incluyePropietario" checked>
              <label class="form-check-label" for="incluyePropietario">
                Incluir entrenamiento para propietario
              </label>
            </div>
          </div>
          
          <div class="alert alert-warning">
            <small><i class="fas fa-exclamation-circle"></i> Nota: Requerimos que la mascota tenga al menos 2 meses y esté al día con sus vacunas.</small>
          </div>
          
          <div class="text-end">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-primary fw-bold">Inscribirse</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

<?php require_once '../includes/footer.php'; ?>