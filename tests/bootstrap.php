<?php
define('APP_PATH', __DIR__ . '/../app');
spl_autoload_register(function ($c) {
    foreach (['core', 'services'] as $d) { $f = APP_PATH . "/$d/$c.php"; if (is_file($f)) { require $f; return; } }
});
if (is_file(__DIR__ . '/../vendor/autoload.php')) require __DIR__ . '/../vendor/autoload.php';
function env(string $k, $def = null) { $v = getenv($k); return ($v === false || $v === '') ? $def : $v; }
