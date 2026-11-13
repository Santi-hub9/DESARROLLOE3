<?php
// OCR local de código abierto. Los datos nunca salen del servidor. Es el último recurso si no hay modelo de visión.
class TesseractOcr {
    static function disponible(): bool {
        if (!function_exists('shell_exec')) return false;
        $cmd = PHP_OS_FAMILY === 'Windows' ? 'where tesseract 2>NUL' : 'command -v tesseract 2>/dev/null';
        return trim((string)@shell_exec($cmd)) !== '';
    }
    static function extraerCedula(string $ruta): ?array {
        if (!self::disponible()) return null;
        $nulo = PHP_OS_FAMILY === 'Windows' ? '2>NUL' : '2>/dev/null';
        $txt = (string)@shell_exec('tesseract ' . escapeshellarg($ruta) . ' stdout -l spa --psm 6 ' . $nulo);        if (trim($txt) === '') return null;
        $lineas = array_values(array_filter(array_map('trim', preg_split('/\R/', $txt)), fn($l) => $l !== ''));
        $num = null; $ap = null; $no = null;
        foreach ($lineas as $l) {
            if (preg_match('/(\d{1,3}(?:[. ]\d{3}){2,3})/', $l, $m) || preg_match('/\b(\d{7,10})\b/', $l, $m)) { $num = preg_replace('/\D/', '', $m[1]); break; }
        }
        foreach ($lineas as $i => $l) {
            $u = mb_strtoupper($l);
            if ($ap === null && str_contains($u, 'APELLIDOS')) $ap = $lineas[$i + 1] ?? null;
            elseif ($no === null && str_contains($u, 'NOMBRES')) $no = $lineas[$i + 1] ?? null;
        }
        $limpiar = fn($x) => $x === null ? null : trim(preg_replace('/[^A-ZÁÉÍÓÚÑ ]/u', '', mb_strtoupper($x)));
        $ap = $limpiar($ap); $no = $limpiar($no);
        return ['tipo_documento' => 'cedula', 'numero_documento' => $num, 'nombres' => $no, 'apellidos' => $ap,
                'confianza' => ['numero_documento' => $num ? 0.6 : 0, 'nombres' => $no ? 0.6 : 0, 'apellidos' => $ap ? 0.6 : 0]];
    }
}
