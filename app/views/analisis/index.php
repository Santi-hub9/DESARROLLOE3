<h3 class="mb-3"><i class="bi bi-cpu"></i> Análisis de IA</h3>
<div class="card border-0 shadow-sm"><div class="table-responsive"><table class="table table-hover align-middle mb-0">
 <thead class="table-light"><tr><th>Fecha</th><th>Candidato</th><th>Vacante</th><th>Puntaje</th><th>Recomendación</th><th>Estado</th><th></th></tr></thead><tbody>
 <?php foreach ($filas as $r): ?>
 <tr><td class="small"><?= e($r['fecha_analisis']) ?></td><td><?= e($r['nombres'] . ' ' . $r['apellidos']) ?></td><td><?= e($r['vacante']) ?></td>
  <td><span class="badge text-bg-<?= color_puntaje($r['puntuacion']) ?> fs-6"><?= $r['puntuacion'] ?></span></td>
  <td><?= badge($r['recomendacion']) ?></td><td><?= badge($r['estado']) ?></td>
  <td><a class="btn btn-sm btn-primary" href="<?= url('analisis', 'detalle', ['id' => $r['id']]) ?>">Ver</a></td></tr>
 <?php endforeach; if (!$filas): ?><tr><td colspan="7" class="text-center text-muted py-4">Sin análisis todavía.</td></tr><?php endif; ?>
 </tbody></table></div></div>
