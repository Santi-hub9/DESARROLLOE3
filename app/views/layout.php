<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title) ?> | TALENTIA</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link href="<?= BASE_URL ?>/assets/style.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark talentia-nav">
 <div class="container">
  <a class="navbar-brand fw-bold" href="<?= url('home') ?>"><i class="bi bi-person-lines-fill"></i> TALENTIA</a>
  <button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#nav"><span class="navbar-toggler-icon"></span></button>
  <div id="nav" class="collapse navbar-collapse">
   <ul class="navbar-nav me-auto">
    <li class="nav-item"><a class="nav-link" href="<?= url('home') ?>">Inicio</a></li>
    <li class="nav-item"><a class="nav-link" href="<?= url('candidato') ?>">Postularme</a></li>
    <?php if (!empty($_SESSION['candidato_id'])): ?>
    <li class="nav-item"><a class="nav-link" href="<?= url('documentos') ?>">Verificar documento</a></li>
    <?php endif; ?>
    <?php if (!empty($_SESSION['admin'])): ?>
    <li class="nav-item"><a class="nav-link" href="<?= url('dashboard') ?>">Dashboard</a></li>
    <li class="nav-item"><a class="nav-link" href="<?= url('ranking') ?>">Ranking</a></li>
    <li class="nav-item"><a class="nav-link" href="<?= url('analisis') ?>">Análisis IA</a></li>
    <li class="nav-item"><a class="nav-link" href="<?= url('correos') ?>">Correos</a></li>
    <li class="nav-item"><a class="nav-link" href="<?= url('reportes') ?>">Reportes</a></li>
    <li class="nav-item"><a class="nav-link" href="<?= url('documentos', 'lista') ?>">Documentos</a></li>
    <?php if (($_SESSION['admin']['rol'] ?? '') === 'admin'): ?><li class="nav-item"><a class="nav-link" href="<?= url('automatizacion') ?>">Automatización</a></li><?php endif; ?>
    <?php endif; ?>
   </ul>
   <?php if (!empty($_SESSION['admin'])): ?>
     <span class="navbar-text me-3"><?= e($_SESSION['admin']['nombre']) ?></span>
     <a class="btn btn-outline-light btn-sm" href="<?= url('auth', 'salir') ?>">Salir</a>
   <?php else: ?>
     <a class="btn btn-outline-light btn-sm" href="<?= url('auth', 'login') ?>">Administrador</a>
   <?php endif; ?>
  </div>
 </div>
</nav>
<main class="container py-4">
<?php if (!empty($_SESSION['flash'])): [$t, $m] = $_SESSION['flash']; unset($_SESSION['flash']); ?>
 <div class="alert alert-<?= e($t) ?> alert-dismissible fade show"><?= e($m) ?><button class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>
<?= $content ?>
</main>
<footer class="text-center text-muted small py-3">TALENTIA &copy; <?= date('Y') ?> · Proyecto universitario · PHP + MySQL + n8n + IA</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<?php if (!empty($charts)): ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>const STATS_URL = "<?= url('api', 'stats') ?>", ANALISIS_URL = "<?= url('analisis', 'detalle') ?>&id=";</script>
<script src="<?= BASE_URL ?>/assets/js/charts.js"></script>
<?php endif; ?>
</body></html>
