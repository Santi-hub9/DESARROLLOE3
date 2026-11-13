<?php
// ===== Configuración general de TALENTIA (los secretos viven en .env) =====
require __DIR__ . '/env.php';
cargar_env(__DIR__ . '/../.env');

define('APP_PATH', __DIR__ . '/../app');
define('UPLOAD_DIR', __DIR__ . '/../storage/cv/');
define('DOCS_DIR', __DIR__ . '/../storage/docs/');
define('BASE_URL', env('BASE_URL', '/talentia/public'));

define('DB_HOST', env('DB_HOST', '127.0.0.1'));
define('DB_NAME', env('DB_NAME', 'talentia'));
define('DB_USER', env('DB_USER', 'root'));
define('DB_PASS', env('DB_PASS', ''));

define('CRON_TOKEN', env('CRON_TOKEN', ''));            // protege api&action=tick
define('UMBRAL_PRESELECCION', (int)env('UMBRAL', 80));
define('UMBRAL_CONFIANZA_DOC', (float)env('UMBRAL_CONFIANZA_DOC', 0.75));

if (PHP_SAPI !== 'cli' && session_status() === PHP_SESSION_NONE) session_start();
if (is_file(__DIR__ . '/../vendor/autoload.php')) require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/helpers.php';

spl_autoload_register(function ($c) {
    foreach (['core', 'services', 'controllers', 'models'] as $d) {
        $f = APP_PATH . "/$d/$c.php";
        if (is_file($f)) { require $f; return; }
    }
});
