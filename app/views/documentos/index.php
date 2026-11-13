<div class="row justify-content-center"><div class="col-lg-8">
<div class="card shadow-sm border-0 mb-3"><div class="card-body p-4">
 <h3><i class="bi bi-person-vcard"></i> Verificación de documento</h3>
 <p class="text-muted">Hola <strong><?= e($c['nombres']) ?></strong>. Toma una foto clara de tu cédula (por el lado de los datos), bien iluminada y sin reflejos.</p>
 <form method="post" enctype="multipart/form-data" action="<?= url('documentos', 'store') ?>">
  <?= csrf_field() ?>
  <input type="file" name="imagen" accept="image/*" capture="environment" class="form-control form-control-lg mb-3" required>
  <div class="form-check small mb-3"><input class="form-check-input" type="checkbox" name="permitir_externo" id="ext">
   <label class="form-check-label" for="ext">Autorizo que la imagen se procese con un servicio de IA externo (Gemini) para una lectura más precisa. Si no lo marcas, se procesa solo en nuestros servidores.</label></div>
  <button class="btn btn-success w-100"><i class="bi bi-camera"></i> Enviar documento</button>
 </form></div></div>
<?php if ($docs): ?><div class="card border-0 shadow-sm"><div class="card-body"><h6>Mis envíos</h6>
 <table class="table table-sm align-middle mb-0"><thead><tr><th>Fecha</th><th>Estado</th><th></th></tr></thead><tbody>
 <?php foreach ($docs as $d): ?><tr><td><?= e($d['creado_en']) ?></td><td><?= badge($d['estado']) ?></td>
  <td class="small text-muted"><?= $d['estado'] === 'revision_manual' ? 'Un reclutador lo revisará.' : ($d['estado'] === 'rechazado' ? 'Sube una foto más clara.' : '') ?></td></tr><?php endforeach; ?>
 </tbody></table></div></div><?php endif; ?>
</div></div>
