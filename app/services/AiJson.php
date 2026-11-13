<?php
// Valida y normaliza lo que devuelve la IA: nunca se guarda una respuesta sin revisar.
class AiJson {
    static function decodificar(string $raw): array {
        $raw = trim(preg_replace('/```(?:json)?/i', '', $raw));
        $a = json_decode($raw, true);
        if (!is_array($a)) throw new InvalidArgumentException('La IA no devolvió un JSON válido');
        return $a;
    }

    static function analisis(array $a): array {
        if (!isset($a['puntuacion']) || !is_numeric($a['puntuacion'])) throw new InvalidArgumentException('Falta la puntuación en la respuesta de la IA');
        $rec = in_array($a['recomendacion'] ?? '', ['contratar', 'entrevistar', 'en_espera', 'descartar'], true) ? $a['recomendacion'] : 'en_espera';
        $lst = fn($k) => json_encode(array_values((array)($a[$k] ?? [])), JSON_UNESCAPED_UNICODE);
        return [
            'puntuacion' => max(0, min(100, (int)round((float)$a['puntuacion']))),
            'resumen' => (string)($a['resumen'] ?? ''),
            'anios_experiencia' => (float)($a['anios_experiencia'] ?? 0),
            'experiencia' => (string)($a['experiencia'] ?? ''),
            'habilidades' => $lst('habilidades_tecnicas'), 'formacion' => $lst('formacion'),
            'certificaciones' => $lst('certificaciones'), 'idiomas' => $lst('idiomas'),
            'fortalezas' => $lst('fortalezas'), 'debilidades' => $lst('debilidades'),
            'recomendacion' => $rec, 'justificacion' => (string)($a['justificacion'] ?? ''),
        ];
    }

    static function correo(array $a): array {
        $asunto = trim((string)($a['asunto'] ?? '')); $html = trim((string)($a['cuerpo_html'] ?? ''));
        if ($asunto === '' || $html === '') throw new InvalidArgumentException('La IA no generó asunto o cuerpo del correo');
        $html = strip_tags($html, '<p><br><strong><em><ul><li><b><i>');
        return ['asunto' => mb_substr($asunto, 0, 200), 'cuerpo' => $html];
    }
}
