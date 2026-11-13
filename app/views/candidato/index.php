<div class="row justify-content-center"><div class="col-lg-7">
<div class="card shadow-sm border-0"><div class="card-body p-4">
 <h3 class="mb-3"><i class="bi bi-person-plus"></i> Registro de candidato</h3>
 <form method="post" action="<?= url('candidato', 'store') ?>" class="row g-3">
  <?= csrf_field() ?>
  <div class="col-md-6"><label class="form-label">Nombres *</label><input name="nombres" class="form-control" required maxlength="100"></div>
  <div class="col-md-6"><label class="form-label">Apellidos *</label><input name="apellidos" class="form-control" required maxlength="100"></div>
  <div class="col-md-6"><label class="form-label">Correo electrónico *</label><input type="email" name="email" class="form-control" required maxlength="150"></div>
  <div class="col-md-6"><label class="form-label">Número de documento *</label><input name="documento_identidad" class="form-control" required maxlength="20" inputmode="numeric"></div>
  <div class="col-md-6"><label class="form-label">Teléfono</label><input name="telefono" class="form-control" maxlength="30"></div>
  <div class="col-md-6"><label class="form-label">Ciudad</label><input name="ciudad" class="form-control" maxlength="80"></div>
  <div class="col-12"><label class="form-label">Vacante *</label>
   <select name="vacante_id" class="form-select" required><option value="">Selecciona...</option>
   <?php foreach ($vacantes as $v): ?><option value="<?= $v['id'] ?>"><?= e($v['titulo']) ?></option><?php endforeach; ?></select></div>
  <div class="col-12"><div class="form-check small">
   <input class="form-check-input" type="checkbox" name="autorizacion" id="aut" required>
   <label class="form-check-label" for="aut"><strong>Autorización de tratamiento de datos (Ley 1581 de 2012).</strong>
    Autorizo a TALENTIA a recolectar y usar mis datos personales y mi hoja de vida con la finalidad exclusiva de evaluar mi postulación, incluido su análisis mediante inteligencia artificial.
    Puedo consultar, corregir o solicitar la eliminación de mis datos escribiendo a Recursos Humanos.</label></div></div>
  <div class="col-12"><button class="btn btn-primary w-100">Continuar <i class="bi bi-arrow-right"></i></button></div>
 </form></div></div></div></div>
