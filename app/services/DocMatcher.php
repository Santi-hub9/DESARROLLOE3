<?php
// Compara los datos extraídos del documento con lo que registró el candidato.
class DocMatcher {
    static function normalizar(string $s): string {
        $s = strtr(mb_strtolower($s, 'UTF-8'), ['á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u', 'ñ' => 'n']);
        return trim(preg_replace('/\s+/', ' ', preg_replace('/[^a-z ]+/', ' ', $s)));
    }
    static function soloDigitos($s): string { return preg_replace('/\D+/', '', (string)$s); }
    static function tokens(string $s): array { return array_values(array_filter(explode(' ', self::normalizar($s)), fn($t) => strlen($t) > 1)); }

    // Proporción de palabras del nombre registrado que aparecen en el extraído (tolera 1 error de OCR)
    static function similitudNombre(string $registrado, string $extraido): float {
        $a = self::tokens($registrado); $b = self::tokens($extraido);
        if (!$a || !$b) return 0.0;
        $ok = 0;
        foreach ($a as $t) foreach ($b as $u) { if ($t === $u || (strlen($t) > 3 && levenshtein($t, $u) <= 1)) { $ok++; break; } }
        return round($ok / count($a), 3);
    }

    static function comparar(array $datos, array $cand): array {
        $reg = self::soloDigitos($cand['documento_identidad'] ?? ''); $ext = self::soloDigitos($datos['numero_documento'] ?? '');
        $sim = self::similitudNombre(($cand['nombres'] ?? '') . ' ' . ($cand['apellidos'] ?? ''), ($datos['nombres'] ?? '') . ' ' . ($datos['apellidos'] ?? ''));
        return ['doc_ok' => $reg === '' ? null : ($ext !== '' && $reg === $ext), 'nombre_ok' => $sim >= 0.75, 'similitud_nombre' => $sim];
    }
}
