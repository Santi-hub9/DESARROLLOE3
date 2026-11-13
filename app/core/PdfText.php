<?php
// Extrae texto de un PDF con pdftotext (poppler). Solo se usa como plan B local.
class PdfText {
    static function extraer(string $ruta): string {
        if (!function_exists('shell_exec')) return '';
        $nulo = PHP_OS_FAMILY === 'Windows' ? '2>NUL' : '2>/dev/null';
        $t = @shell_exec('pdftotext -layout -enc UTF-8 ' . escapeshellarg($ruta) . ' - ' . $nulo);
        return is_string($t) ? $t : '';
    }
}