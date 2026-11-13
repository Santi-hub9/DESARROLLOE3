<?php
// Lee el archivo .env (claves fuera del código). En Docker las variables llegan por env_file.
function cargar_env(string $ruta): void {
    if (!is_file($ruta)) return;
    foreach (file($ruta, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $l) {
        $l = trim($l);
        if ($l === '' || $l[0] === '#' || !str_contains($l, '=')) continue;
        [$k, $v] = explode('=', $l, 2);
        $k = trim($k); $v = trim($v, " \t\"'");
        if (getenv($k) === false) { putenv("$k=$v"); $_ENV[$k] = $v; }
    }
}
function env(string $k, $def = null) { $v = getenv($k); return ($v === false || $v === '') ? $def : $v; }
