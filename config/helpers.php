<?php
function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function url($page = 'home', $action = 'index', $q = []) {
    return BASE_URL . '/index.php?' . http_build_query(array_merge(['page' => $page, 'action' => $action], $q));
}
function csrf_field() {
    $_SESSION['csrf'] ??= bin2hex(random_bytes(16));
    return '<input type="hidden" name="csrf" value="' . $_SESSION['csrf'] . '">';
}
function csrf_check() {
    if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) { http_response_code(419); exit('Token CSRF inválido'); }
}
function lista($json) { $a = json_decode((string)$json, true); return is_array($a) ? $a : []; }
function badge($estado) {
    $m = ['preseleccionado'=>'success','no_seleccionado'=>'secondary','en_analisis'=>'warning','cv_recibido'=>'info',
          'registrado'=>'light','error'=>'danger','enviado'=>'success','fallido'=>'danger','pendiente'=>'warning',
          'procesando'=>'warning','analizado'=>'success','contratar'=>'success','entrevistar'=>'primary',
          'en_espera'=>'warning','descartar'=>'danger','preseleccion'=>'success','agradecimiento'=>'secondary'];
    return '<span class="badge text-bg-' . ($m[$estado] ?? 'secondary') . '">' . e(str_replace('_', ' ', $estado)) . '</span>';
}
function color_puntaje($p) { return $p >= 80 ? 'success' : ($p >= 60 ? 'warning' : 'danger'); }
