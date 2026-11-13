<?php $charts = true; ?>
<div class="d-flex justify-content-between align-items-center mb-3"><h3 class="mb-0"><i class="bi bi-graph-up"></i> Reportes y estadísticas</h3>
 <a class="btn btn-success" href="<?= url('reportes', 'csv') ?>"><i class="bi bi-file-earmark-excel"></i> Exportar ranking CSV</a></div>
<div class="row g-3">
 <div class="col-lg-6"><div class="card border-0 shadow-sm"><div class="card-body"><h6>Distribución de puntajes</h6><canvas id="chDist" height="200"></canvas></div></div></div>
 <div class="col-lg-6"><div class="card border-0 shadow-sm"><div class="card-body"><h6>Puntaje promedio por día</h6><canvas id="chPromDia" height="200"></canvas></div></div></div>
 <div class="col-lg-6"><div class="card border-0 shadow-sm"><div class="card-body"><h6>Correos por tipo y estado</h6><canvas id="chCorreos" height="200"></canvas></div></div></div>
 <div class="col-lg-6"><div class="card border-0 shadow-sm"><div class="card-body"><h6>Resumen</h6>
  <ul class="list-group list-group-flush"><li class="list-group-item d-flex justify-content-between">Total candidatos<b id="kpi-total">0</b></li>
  <li class="list-group-item d-flex justify-content-between">Promedio<b id="kpi-prom">0</b></li>
  <li class="list-group-item d-flex justify-content-between">Aprobados<b id="kpi-apro">0</b></li>
  <li class="list-group-item d-flex justify-content-between">Rechazados<b id="kpi-rech">0</b></li>
  <li class="list-group-item d-flex justify-content-between">En proceso<b id="kpi-pend">0</b></li></ul></div></div></div>
</div>
