<div class="d-flex justify-content-between align-items-center mb-3"><h3 class="mb-0"><i class="bi bi-gear-wide-connected"></i> Motor de automatización</h3>
 <form method="post" action="<?= url('automatizacion', 'ejecutar') ?>"><?= csrf_field() ?><button class="btn btn-primary"><i class="bi bi-play-fill"></i> Ejecutar ahora</button></form></div>
<ul class="nav nav-tabs mb-3"><li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#t1">Cola de tareas</button></li>
 <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#t2">Registro de ejecuciones</button></li>
 <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#t3">Auditoría de IA</button></li></ul>
<div class="tab-content">
<div class="tab-pane fade show active" id="t1"><div class="card border-0 shadow-sm"><div class="table-responsive"><table class="table table-sm align-middle mb-0">
 <thead class="table-light"><tr><th>#</th><th>Tipo</th><th>Estado</th><th>Intentos</th><th>Próximo intento</th><th>Último error</th><th></th></tr></thead><tbody>
 <?php foreach ($tareas as $t): ?><tr><td><?= $t['id'] ?></td><td><?= e($t['tipo']) ?></td><td><?= badge($t['estado']) ?></td><td><?= $t['intentos'] ?>/<?= $t['max_intentos'] ?></td>
  <td class="small"><?= e($t['proximo_intento']) ?></td><td class="small text-danger"><?= e($t['ultimo_error']) ?></td>
  <td><?php if ($t['estado'] === 'fallida'): ?><form method="post" action="<?= url('automatizacion', 'reintentar') ?>"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $t['id'] ?>"><button class="btn btn-sm btn-outline-primary">Reintentar</button></form><?php endif; ?></td></tr>
 <?php endforeach; ?></tbody></table></div></div></div>
<div class="tab-pane fade" id="t2"><div class="card border-0 shadow-sm"><div class="table-responsive"><table class="table table-sm mb-0">
 <thead class="table-light"><tr><th>Fecha</th><th>Flujo</th><th>Estado</th><th>Entrada</th><th>Resultado</th><th>ms</th></tr></thead><tbody>
 <?php foreach ($logs as $l): ?><tr><td class="small"><?= e($l['fecha']) ?></td><td><?= e($l['flujo']) ?></td><td><?= badge($l['estado'] === 'ok' ? 'enviado' : ($l['estado'] === 'error' ? 'fallido' : 'pendiente')) ?></td>
  <td class="small"><code><?= e(mb_substr($l['entrada'], 0, 80)) ?></code></td><td class="small"><?= e(mb_substr($l['resultado'], 0, 120)) ?></td><td><?= $l['duracion_ms'] ?></td></tr><?php endforeach; ?></tbody></table></div></div></div>
<div class="tab-pane fade" id="t3"><div class="card border-0 shadow-sm"><div class="table-responsive"><table class="table table-sm mb-0">
 <thead class="table-light"><tr><th>Fecha</th><th>Acción</th><th>Proveedor</th><th>Modelo</th><th>Estado</th><th>Hash de entrada</th><th>ms</th></tr></thead><tbody>
 <?php foreach ($auditoria as $a): ?><tr><td class="small"><?= e($a['fecha']) ?></td><td><?= e($a['accion']) ?></td><td><?= e($a['proveedor']) ?></td><td><?= e($a['modelo']) ?></td>
  <td><?= badge($a['estado'] === 'ok' ? 'enviado' : ($a['estado'] === 'error' ? 'fallido' : 'pendiente')) ?></td><td class="small"><code><?= e(substr($a['entrada_hash'], 0, 12)) ?>…</code></td><td><?= $a['duracion_ms'] ?></td></tr><?php endforeach; ?></tbody></table></div></div></div>
</div>
