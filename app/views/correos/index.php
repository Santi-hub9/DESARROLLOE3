<h3 class="mb-3"><i class="bi bi-envelope-paper"></i> Historial de correos enviados</h3>
<div class="card border-0 shadow-sm"><div class="table-responsive"><table class="table align-middle mb-0">
 <thead class="table-light"><tr><th>Fecha envío</th><th>Candidato</th><th>Tipo</th><th>Asunto</th><th>Estado</th><th></th></tr></thead><tbody>
 <?php foreach ($filas as $r): ?>
 <tr><td class="small"><?= e($r['fecha_envio'] ?: '—') ?></td><td><?= e($r['nombres'] . ' ' . $r['apellidos']) ?><br><small class="text-muted"><?= e($r['destinatario']) ?></small></td>
  <td><?= badge($r['tipo']) ?></td><td><?= e($r['asunto']) ?></td><td><?= badge($r['estado_envio']) ?><?php if ($r['error_mensaje']): ?><br><small class="text-danger"><?= e($r['error_mensaje']) ?></small><?php endif; ?></td>
  <td><button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#m<?= $r['id'] ?>">Ver</button></td></tr>
 <div class="modal fade" id="m<?= $r['id'] ?>"><div class="modal-dialog modal-lg"><div class="modal-content"><div class="modal-header"><h6 class="modal-title"><?= e($r['asunto']) ?></h6><button class="btn-close" data-bs-dismiss="modal"></button></div>
  <div class="modal-body"><?= strip_tags($r['cuerpo'], '<p><br><strong><em><ul><li><b><i>') ?></div></div></div></div>
 <?php endforeach; if (!$filas): ?><tr><td colspan="6" class="text-center text-muted py-4">Aún no se han enviado correos.</td></tr><?php endif; ?>
 </tbody></table></div></div>
