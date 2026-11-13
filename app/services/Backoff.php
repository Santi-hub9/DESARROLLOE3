<?php
class Backoff {
    // Espera antes del reintento n: 30 s, 2 min, 10 min, 30 min (y se queda en 30 min)
    static function segundos(int $intento): int { $t = [30, 120, 600, 1800]; return $t[max(0, min($intento - 1, 3))]; }
}
