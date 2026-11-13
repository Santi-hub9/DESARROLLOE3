<?php
/**
 * Módulo de visión: extrae datos de una cédula en foto. Orden: modelo multimodal (Ollama local o Gemini SOLO si el
 * candidato autorizó el envío externo) y, si ninguno responde, OCR local Tesseract + reglas.
 */
class VisionService {
    const SYSTEM = 'Eres un extractor de datos de documentos de identidad. Analiza la imagen de una cédula de ciudadanía colombiana y responde SOLO un JSON válido:
{"tipo_documento":"cedula|otro|ilegible","numero_documento":"solo dígitos","nombres":"","apellidos":"","fecha_nacimiento":"YYYY-MM-DD o null",
"confianza":{"numero_documento":0.0-1.0,"nombres":0.0-1.0,"apellidos":0.0-1.0}}
Si un campo no se lee con claridad usa null y confianza 0. No inventes datos.';

    static function extraer(string $ruta, string $mime, bool $permitirExterno, array $ctx = []): array {
        $errores = [];
        try {
            $r = LLM::generar(self::SYSTEM, 'Extrae los datos del documento de la imagen.',
                $ctx + ['accion' => 'verificar_documento', 'redactar' => true, 'permitir_externo' => $permitirExterno], ['path' => $ruta, 'mime' => $mime]);
            return self::resultado($r['proveedor'], $r['modelo'], $r['datos']);
        } catch (Throwable $e) { $errores[] = $e->getMessage(); }

        $t0 = microtime(true);
        $d = TesseractOcr::extraerCedula($ruta);
        if ($d) {
            Audit::ia('verificar_documento', 'tesseract', 'tesseract+reglas', $ctx['candidato_id'] ?? null, $ruta, '[omitido: datos personales]', 'fallback', (int)((microtime(true) - $t0) * 1000));
            return self::resultado('tesseract', 'tesseract+reglas', $d);
        }
        throw new RuntimeException('No fue posible leer el documento. ' . implode(' | ', $errores));
    }

    private static function resultado(string $proveedor, string $modelo, array $d): array {
        $d['numero_documento'] = DocMatcher::soloDigitos($d['numero_documento'] ?? '');
        return ['proveedor' => $proveedor, 'modelo' => $modelo, 'datos' => $d, 'confianza' => Decision::confianzaGlobal((array)($d['confianza'] ?? []))];
    }
}
