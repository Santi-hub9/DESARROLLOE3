<?php $charts = true; ?>
<div class="d-flex justify-content-between align-items-center mb-3"><h3 class="mb-0"><i class="bi bi-speedometer2"></i> Dashboard administrativo</h3>
 <small class="text-muted">Actualización automática cada 10 s · <span id="ult">—</span></small></div>
<div class="row g-3 mb-4">
 <?php foreach ([['kpi-total','Total candidatos','people','primary'],['kpi-prom','Promedio puntuación','bar-chart','info'],['kpi-apro','Aprobados (≥80)','check-circle','success'],['kpi-rech','Rechazados (<80)','x-circle','danger'],['kpi-pend','En proceso','hourglass-split','warning']] as [$id,$t,$i,$c]): ?>
 <div class="col-6 col-lg"><div class="card border-0 shadow-sm kpi border-start border-4 border-<?= $c ?>"><div class="card-body">
  <div class="text-muted small"><i class="bi bi-<?= $i ?>"></i> <?= $t ?></div><div class="fs-2 fw-bold" id="<?= $id ?>">0</div></div></div></div>
 <?php endforeach; ?>
</div>
<div class="row g-3 mb-4">
 <div class="col-lg-5"><div class="card border-0 shadow-sm"><div class="card-body"><h6>Distribución de puntajes</h6><canvas id="chDist" height="220"></canvas></div></div></div>
 <div class="col-lg-3"><div class="card border-0 shadow-sm"><div class="card-body"><h6>Aprobados vs rechazados</h6><canvas id="chEstado" height="220"></canvas></div></div></div>
 <div class="col-lg-4"><div class="card border-0 shadow-sm"><div class="card-body"><h6>Postulaciones por día</h6><canvas id="chDia" height="220"></canvas></div></div></div>
</div>
<div class="card border-0 shadow-sm"><div class="card-body"><div class="d-flex justify-content-between"><h6>Top 10 candidatos</h6><a href="<?= url('ranking') ?>" class="small">Ver ranking</a></div>
 <div class="table-responsive"><table class="table align-middle mb-0"><thead><tr><th>#</th><th>Candidato</th><th>Vacante</th><th style="width:25%">Puntaje</th><th>Estado</th><th></th></tr></thead><tbody id="top10"></tbody></table></div></div></div>
