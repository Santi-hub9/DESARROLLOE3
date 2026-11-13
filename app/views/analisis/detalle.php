<?php function ul($arr) { echo '<ul class="mb-0">'; foreach ($arr as $i) echo '<li>' . e(is_array($i) ? implode(' - ', $i) : $i) . '</li>'; echo '</ul>'; } ?>
<a href="<?= url('analisis') ?>" class="btn btn-link px-0">&larr; Volver</a>
<div class="card border-0 shadow-sm mb-3"><div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-3">
 <div><h3 class="mb-1"><?= e($a['nombres'] . ' ' . $a['apellidos']) ?></h3>
  <div class="text-muted"><?= e($a['email']) ?> · <?= e($a['telefono']) ?> · <?= e($a['ciudad']) ?></div>
  <div class="mt-2">Vacante: <strong><?= e($a['vacante']) ?></strong> · <?= badge($a['estado']) ?> · Recomendación: <?= badge($a['recomendacion']) ?></div>
  <a class="btn btn-sm btn-outline-secondary mt-2" target="_blank" href="<?= url('api', 'cv', ['id' => $a['hv_id']]) ?>"><i class="bi bi-file-pdf"></i> Ver PDF</a></div>
 <div class="text-center"><div class="score-ring bg-<?= color_puntaje($a['puntuacion']) ?>"><?= $a['puntuacion'] ?></div><small class="text-muted">de 100 · <?= e($a['modelo_ia']) ?></small></div>
</div></div>
<div class="alert alert-info"><strong>Resumen IA:</strong> <?= e($a['resumen']) ?></div>
<div class="row g-3">
 <div class="col-md-6"><div class="card h-100 border-0 shadow-sm"><div class="card-body"><h6><i class="bi bi-briefcase"></i> Experiencia (<?= e($a['anios_experiencia']) ?> años)</h6><p class="mb-0"><?= e($a['experiencia']) ?></p></div></div></div>
 <div class="col-md-6"><div class="card h-100 border-0 shadow-sm"><div class="card-body"><h6><i class="bi bi-tools"></i> Habilidades técnicas</h6><?php foreach (lista($a['habilidades']) as $h): ?><span class="badge text-bg-primary me-1 mb-1"><?= e($h) ?></span><?php endforeach; ?></div></div></div>
 <div class="col-md-4"><div class="card h-100 border-0 shadow-sm"><div class="card-body"><h6><i class="bi bi-mortarboard"></i> Formación</h6><?php ul(lista($a['formacion'])); ?></div></div></div>
 <div class="col-md-4"><div class="card h-100 border-0 shadow-sm"><div class="card-body"><h6><i class="bi bi-patch-check"></i> Certificaciones</h6><?php ul(lista($a['certificaciones'])); ?></div></div></div>
 <div class="col-md-4"><div class="card h-100 border-0 shadow-sm"><div class="card-body"><h6><i class="bi bi-translate"></i> Idiomas</h6><?php ul(lista($a['idiomas'])); ?></div></div></div>
 <div class="col-md-6"><div class="card h-100 border-success shadow-sm"><div class="card-body"><h6 class="text-success"><i class="bi bi-hand-thumbs-up"></i> Fortalezas</h6><?php ul(lista($a['fortalezas'])); ?></div></div></div>
 <div class="col-md-6"><div class="card h-100 border-danger shadow-sm"><div class="card-body"><h6 class="text-danger"><i class="bi bi-hand-thumbs-down"></i> Debilidades</h6><?php ul(lista($a['debilidades'])); ?></div></div></div>
 <div class="col-12"><div class="card border-0 shadow-sm"><div class="card-body"><h6><i class="bi bi-chat-quote"></i> Justificación de la recomendación</h6><p class="mb-0"><?= e($a['justificacion']) ?></p></div></div></div>
 <div class="col-12"><div class="card border-0 shadow-sm"><div class="card-body"><h6><i class="bi bi-envelope"></i> Correos enviados</h6>
  <?php foreach ($correos as $c): ?><div class="border-bottom py-2"><?= badge($c['tipo']) ?> <?= badge($c['estado_envio']) ?> <strong><?= e($c['asunto']) ?></strong> <small class="text-muted"><?= e($c['fecha_envio']) ?></small></div><?php endforeach; if (!$correos) echo '<span class="text-muted">Sin correos.</span>'; ?>
 </div></div></div>
</div>
