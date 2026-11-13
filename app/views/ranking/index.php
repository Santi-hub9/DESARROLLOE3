<div class="d-flex justify-content-between align-items-center mb-3">
 <h3 class="mb-0"><i class="bi bi-trophy"></i> <?= $todos ? 'Ranking general' : 'Top 10 candidatos' ?></h3>
 <div><a class="btn btn-outline-primary btn-sm" href="<?= $todos ? url('ranking') : url('ranking', 'index', ['todos' => 1]) ?>"><?= $todos ? 'Ver Top 10' : 'Ver todos' ?></a>
 <a class="btn btn-outline-success btn-sm" href="<?= url('reportes', 'csv') ?>"><i class="bi bi-download"></i> CSV</a></div></div>
<div class="card border-0 shadow-sm"><div class="table-responsive"><table class="table table-hover align-middle mb-0">
 <thead class="table-light"><tr><th>#</th><th>Candidato</th><th>Vacante</th><th style="width:25%">Puntaje</th><th>Estado</th><th></th></tr></thead><tbody>
 <?php foreach ($filas as $r): $m = [1 => '🥇', 2 => '🥈', 3 => '🥉']; ?>
 <tr><td class="fw-bold"><?= $m[$r['posicion']] ?? $r['posicion'] ?></td>
  <td><?= e($r['nombres'] . ' ' . $r['apellidos']) ?><br><small class="text-muted"><?= e($r['email']) ?></small></td>
  <td><?= e($r['vacante']) ?></td>
  <td><div class="progress" style="height:20px"><div class="progress-bar bg-<?= color_puntaje($r['puntuacion']) ?>" style="width:<?= $r['puntuacion'] ?>%"><?= $r['puntuacion'] ?></div></div></td>
  <td><?= badge($r['estado']) ?></td>
  <td><a class="btn btn-sm btn-outline-dark" href="<?= url('analisis', 'detalle', ['id' => $r['analisis_id']]) ?>">Detalle</a></td></tr>
 <?php endforeach; if (!$filas): ?><tr><td colspan="6" class="text-center text-muted py-4">Aún no hay candidatos analizados.</td></tr><?php endif; ?>
 </tbody></table></div></div>
