<?php
// Front controller: index.php?page=<modulo>&action=<metodo>
require __DIR__ . '/../config/config.php';
$routes = [
    'home' => 'HomeController', 'candidato' => 'CandidatoController', 'cv' => 'CvController',
    'auth' => 'AuthController', 'dashboard' => 'DashboardController', 'ranking' => 'RankingController',
    'analisis' => 'AnalisisController', 'correos' => 'CorreoController', 'reportes' => 'ReporteController',
    'documentos' => 'DocumentosController', 'automatizacion' => 'AutomatizacionController', 'api' => 'ApiController',
];
$page = $_GET['page'] ?? 'home';
$action = $_GET['action'] ?? 'index';
if (!isset($routes[$page])) { http_response_code(404); exit('Página no encontrada'); }
$ctrl = new $routes[$page]();
if (!preg_match('/^[a-z]+$/i', $action) || !is_callable([$ctrl, $action])) { http_response_code(404); exit('Acción no encontrada'); }
try { $ctrl->$action(); }
catch (PDOException $ex) { error_log($ex->getMessage()); http_response_code(500); echo 'Error de base de datos.'; }
