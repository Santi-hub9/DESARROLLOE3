<?php
// Uso:  php bin/worker.php          -> procesa lo pendiente y termina (para cron cada minuto)
//       php bin/worker.php --loop   -> se queda corriendo (Docker / servicio)
require __DIR__ . '/../config/config.php';
$loop = in_array('--loop', $argv ?? [], true);
do {
    try {
        $r = Queue::procesar(5);
        if ($r) echo date('c') . ' tareas: ' . json_encode($r) . PHP_EOL;
    } catch (Throwable $e) { fwrite(STDERR, date('c') . ' worker: ' . $e->getMessage() . PHP_EOL); }
    if ($loop) sleep(5);
} while ($loop);
