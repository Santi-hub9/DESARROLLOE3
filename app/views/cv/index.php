<div class="row justify-content-center"><div class="col-lg-7">
<div class="card shadow-sm border-0"><div class="card-body p-4">
 <h3><i class="bi bi-file-earmark-pdf"></i> Sube tu hoja de vida</h3>
 <p class="text-muted">Hola <strong><?= e($c['nombres']) ?></strong>, postulación a <strong><?= e($c['vacante']) ?></strong>.</p>
 <form method="post" enctype="multipart/form-data" action="<?= url('cv', 'store') ?>">
  <?= csrf_field() ?>
  <input type="file" name="cv" accept="application/pdf" class="form-control form-control-lg mb-3" required>
  <div class="form-text mb-3">Solo PDF con texto seleccionable (no escaneado), máximo 5 MB.</div>
  <button class="btn btn-success w-100"><i class="bi bi-cloud-upload"></i> Enviar para análisis</button>
 </form></div></div></div></div>
<div class="text-center mt-3"><a href="<?= url('documentos') ?>" class="btn btn-outline-primary"><i class="bi bi-person-vcard"></i> Siguiente: verificar mi documento</a></div>
