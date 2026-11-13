<h3 class="mb-3"><i class="bi bi-person-vcard"></i> Documentos de identidad</h3>
<div class="card border-0 shadow-sm"><div class="table-responsive"><table class="table align-middle mb-0">
 <thead class="table-light"><tr><th>Fecha</th><th>Candidato</th><th>Registrado</th><th>Extraído por la IA</th><th>Confianza</th><th>Estado</th><th></th></tr></thead><tbody>
 <?php foreach ($filas as $r): $x = json_decode($r['datos_extraidos'] ?? '[]', true) ?: []; $m = json_decode($r['coincidencias'] ?? '[]', true) ?: []; ?>
 <tr><td class="small"><?= e($r['creado_en']) ?><br><span class="text-muted">IP <?= e($r['ip']) ?></span></td>
  <td><?= e($r['nombres'] . ' ' . $r['apellidos']) ?></td>
  <td class="small">Doc: <?= e($r['documento_identidad']) ?></td>
  <td class="small"><?php if ($x): ?>Doc: <?= e($x['numero_documento'] ?? '—') ?><br><?= e(($x['nombres'] ?? '') . ' ' . ($x['apellidos'] ?? '')) ?><br>
    <span class="text-muted">vía <?= e($r['proveedor']) ?> · nombre <?= e($m['similitud_nombre'] ?? '?') ?></span><?php else: ?>—<?php endif; ?></td>
  <td><?= $r['confianza'] !== null ? e($r['confianza']) : '—' ?></td><td><?= badge($r['estado']) ?></td>
  <td class="text-nowrap"><a class="btn btn-sm btn-outline-secondary" target="_blank" href="<?= url('documentos', 'imagen', ['id' => $r['id']]) ?>">Ver foto</a>
   <?php if ($r['estado'] === 'revision_manual'): ?>
   <form method="post" action="<?= url('documentos', 'decidir') ?>" class="d-inline"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $r['id'] ?>">
    <button name="decision" value="validado" class="btn btn-sm btn-success">Aprobar</button>
    <button name="decision" value="rechazado" class="btn btn-sm btn-danger">Rechazar</button></form><?php endif; ?></td></tr>
 <?php endforeach; if (!$filas): ?><tr><td colspan="7" class="text-center text-muted py-4">Aún no hay documentos.</td></tr><?php endif; ?>
 </tbody></table></div></div>
