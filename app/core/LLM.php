<?php
/**
 * Cliente de IA con plan B. Prueba los proveedores en el orden de LLM_PROVIDERS (por defecto gemini,ollama).
 * Si uno falla (cuota, red, JSON inválido) pasa al siguiente y deja el rastro en auditoria_ia.
 */
class LLM {
    public static array $fake = [];   // para pruebas unitarias: nombre => callable

    static function generar(string $system, string $user, array $ctx = [], ?array $archivo = null): array {
        $errores = [];
        foreach (self::proveedores($ctx) as $nombre => $fn) {
            $t0 = microtime(true);
            try {
                [$raw, $modelo] = $fn($system, $user, $archivo);
                $datos = AiJson::decodificar($raw);
                Audit::ia($ctx['accion'] ?? 'generar', $nombre, $modelo, $ctx['candidato_id'] ?? null, $system . $user,
                    !empty($ctx['redactar']) ? '[omitido: contiene datos personales]' : $raw, $errores ? 'fallback' : 'ok', self::ms($t0));
                return ['proveedor' => $nombre, 'modelo' => $modelo, 'datos' => $datos];
            } catch (Throwable $e) {
                $errores[] = "$nombre: " . $e->getMessage();
                Audit::ia($ctx['accion'] ?? 'generar', $nombre, '-', $ctx['candidato_id'] ?? null, $system . $user, $e->getMessage(), 'error', self::ms($t0));
            }
        }
        throw new RuntimeException('Todos los proveedores de IA fallaron. ' . ($errores ? implode(' | ', $errores) : 'No hay proveedores configurados.'));
    }

    private static function ms(float $t0): int { return (int)((microtime(true) - $t0) * 1000); }

    private static function proveedores(array $ctx): array {
        if (self::$fake) return self::$fake;
        $out = [];
        foreach (array_map('trim', explode(',', env('LLM_PROVIDERS', 'gemini,ollama'))) as $n) {
            if ($n === 'gemini' && env('GEMINI_API_KEY') && ($ctx['permitir_externo'] ?? true)) $out['gemini'] = [self::class, 'gemini'];
            if ($n === 'ollama' && env('OLLAMA_URL')) $out['ollama'] = [self::class, 'ollama'];
        }
        return $out;
    }

    static function gemini(string $system, string $user, ?array $archivo): array {
        $modelo = env('GEMINI_MODEL', 'gemini-2.5-flash');
        $parts = [['text' => $user]];
        if ($archivo) $parts[] = ['inline_data' => ['mime_type' => $archivo['mime'], 'data' => base64_encode(file_get_contents($archivo['path']))]];
        $body = ['systemInstruction' => ['parts' => [['text' => $system]]], 'contents' => [['role' => 'user', 'parts' => $parts]],
                 'generationConfig' => ['temperature' => 0.2, 'responseMimeType' => 'application/json']];
        [$code, $resp] = Http::postJson("https://generativelanguage.googleapis.com/v1beta/models/$modelo:generateContent",
            ['x-goog-api-key: ' . env('GEMINI_API_KEY')], $body, 120);
        if ($code !== 200) {
            $detalle = json_decode($resp, true)['error']['message'] ?? '';
            throw new RuntimeException("Gemini respondió HTTP $code" . ($code === 429 ? ' (cuota agotada)' : '') . ($detalle ? ': ' . mb_substr($detalle, 0, 200) : ''));
        }        $txt = json_decode($resp, true)['candidates'][0]['content']['parts'][0]['text'] ?? null;
        if (!$txt) throw new RuntimeException('Gemini no devolvió contenido');
        return [$txt, $modelo];
    }

    static function ollama(string $system, string $user, ?array $archivo): array {
        $esImagen = $archivo && str_starts_with($archivo['mime'], 'image/');
        $modelo = $esImagen ? env('OLLAMA_VISION_MODEL', 'llama3.2-vision') : env('OLLAMA_MODEL', 'llama3.1');
        $msg = ['role' => 'user', 'content' => $user];
        if ($archivo && $esImagen) $msg['images'] = [base64_encode(file_get_contents($archivo['path']))];
        elseif ($archivo) {
            $txt = PdfText::extraer($archivo['path']);
            if (trim($txt) === '') throw new RuntimeException('No se pudo extraer texto del PDF para el modelo local');
            $msg['content'] .= "\n\nDOCUMENTO:\n" . mb_substr($txt, 0, 12000);
        }
        [$code, $resp] = Http::postJson(rtrim(env('OLLAMA_URL'), '/') . '/api/chat', [],
            ['model' => $modelo, 'messages' => [['role' => 'system', 'content' => $system], $msg], 'stream' => false, 'format' => 'json', 'options' => ['temperature' => 0.2]], 300);
        if ($code !== 200) throw new RuntimeException("Ollama respondió HTTP $code");
        $txt = json_decode($resp, true)['message']['content'] ?? null;
        if (!$txt) throw new RuntimeException('Ollama no devolvió contenido');
        return [$txt, $modelo];
    }
}
