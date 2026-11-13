<?php
// Mide el % de aciertos por campo con tus fotos reales.
// Uso: php bin/evaluar_vision.php [carpeta_imagenes] [ground_truth.csv] [--externo]
// ground_truth.csv (con encabezado): archivo,numero_documento,nombres,apellidos
require __DIR__ . '/../config/config.php';
$dir = $argv[1] ?? 'tests/vision/imagenes'; $csv = $argv[2] ?? 'tests/vision/ground_truth.csv';
$externo = in_array('--externo', $argv, true);
if (!is_file($csv)) exit("No existe $csv\n");
$rows = array_map('str_getcsv', file($csv, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES));
$head = array_shift($rows);
$ok = ['numero_documento' => 0, 'nombres' => 0, 'apellidos' => 0]; $n = 0; $salida = [];
foreach ($rows as $r) {
    $g = array_combine($head, $r); $n++;
    $ruta = rtrim($dir, '/') . '/' . $g['archivo'];
    try { $x = VisionService::extraer($ruta, mime_content_type($ruta), $externo); $d = $x['datos']; $prov = $x['proveedor']; $conf = $x['confianza']; }
    catch (Throwable $e) { $d = []; $prov = 'error'; $conf = 0; }
    $res = [
        'numero_documento' => DocMatcher::soloDigitos($d['numero_documento'] ?? '') === DocMatcher::soloDigitos($g['numero_documento']),
        'nombres' => DocMatcher::normalizar($d['nombres'] ?? '') === DocMatcher::normalizar($g['nombres']),
        'apellidos' => DocMatcher::normalizar($d['apellidos'] ?? '') === DocMatcher::normalizar($g['apellidos']),
    ];
    foreach ($res as $k => $v) if ($v) $ok[$k]++;
    $salida[] = [$g['archivo'], $prov, $conf, (int)$res['numero_documento'], (int)$res['nombres'], (int)$res['apellidos']];
    echo "{$g['archivo']}  [$prov]  conf=$conf  " . implode('', array_map(fn($v) => $v ? '✔' : '✘', $res)) . PHP_EOL;
}
echo PHP_EOL . "Imágenes evaluadas: $n" . PHP_EOL;
foreach ($ok as $k => $v) printf("%-18s %d/%d = %.1f%%\n", $k, $v, $n, $n ? 100 * $v / $n : 0);
$f = fopen(__DIR__ . '/../docs/resultados_vision.csv', 'w');
fputcsv($f, ['archivo', 'proveedor', 'confianza', 'numero_ok', 'nombres_ok', 'apellidos_ok']);
foreach ($salida as $s) fputcsv($f, $s);
fclose($f); echo "Detalle guardado en docs/resultados_vision.csv\n";
