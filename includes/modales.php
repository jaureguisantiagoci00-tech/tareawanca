<?php if(!isset($_SESSION['user_id'])): ?>
<!-- MODAL DE INGRESO -->
<div class="modal fade" id="modalIngreso" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">🔑 Ingresar a Puphub</h5>
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

<!-- MODAL DE REGISTRO -->
<div class="modal fade" id="modalRegistro" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">📝 Registrarse en Puphub</h5>
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
<?php endif; ?>