<div class="row justify-content-center"><div class="col-md-5"><div class="card shadow-sm border-0"><div class="card-body p-4">
 <h4 class="mb-3"><i class="bi bi-shield-lock"></i> Acceso administrador</h4>
 <form method="post" action="<?= url('auth', 'entrar') ?>"><?= csrf_field() ?>
  <input type="email" name="email" class="form-control mb-3" placeholder="Correo" required>
  <input type="password" name="password" class="form-control mb-3" placeholder="Contraseña" required>
  <button class="btn btn-primary w-100">Ingresar</button>
 </form></div></div></div></div>
