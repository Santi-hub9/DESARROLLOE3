<?php
// Proceso de negocio: verificar el documento del candidato. La decisión la toma la regla Decision::estadoDocumento.
class VerificacionDocumentoService {
    static function ejecutar(array $p): array {
        $id = (int)($p['documento_id'] ?? 0);
        $doc = DB::one('SELECT d.*, c.nombres, c.apellidos, c.documento_identidad FROM documentos_identidad d JOIN candidatos c ON c.id=d.candidato_id WHERE d.id=?', [$id]);
        if (!$doc) throw new InvalidArgumentException("El documento $id no existe");
        if (!in_array($doc['estado'], ['pendiente', 'procesando', 'error'], true)) return ['omitido' => 'ya tiene decisión'];

        DB::run("UPDATE documentos_identidad SET estado='procesando' WHERE id=?", [$id]);
        DB::run("UPDATE candidatos SET documento_verificado='pendiente' WHERE id=?", [$doc['candidato_id']]);
        $ruta = DOCS_DIR . basename($doc['archivo']);
        if (!is_file($ruta)) throw new InvalidArgumentException('La imagen no existe en el servidor');

        $x = VisionService::extraer($ruta, mime_content_type($ruta), (bool)$doc['autorizo_externo'], ['candidato_id' => (int)$doc['candidato_id']]);
        $match = DocMatcher::comparar($x['datos'], $doc);
        $estado = Decision::estadoDocumento($x['confianza'], $match, UMBRAL_CONFIANZA_DOC);

        DB::run('UPDATE documentos_identidad SET estado=?, proveedor=?, datos_extraidos=?, confianza=?, coincidencias=? WHERE id=?',
            [$estado, $x['proveedor'], json_encode($x['datos'], JSON_UNESCAPED_UNICODE), $x['confianza'], json_encode($match), $id]);
        DB::run('UPDATE candidatos SET documento_verificado=? WHERE id=?', [$estado, $doc['candidato_id']]);

        if (in_array($estado, ['validado', 'rechazado'], true)) Queue::push('notificar_documento', ['documento_id' => $id]);   // la visión dispara el proceso
        return ['estado' => $estado, 'confianza' => $x['confianza'], 'proveedor' => $x['proveedor']];   // sin datos personales en el log
    }

    static function alFallar(array $p, string $error): void {
        DB::run("UPDATE documentos_identidad SET estado='revision_manual' WHERE id=?", [(int)($p['documento_id'] ?? 0)]);   // plan B: lo revisa una persona
        DB::run("UPDATE candidatos SET documento_verificado='revision_manual' WHERE id=(SELECT candidato_id FROM documentos_identidad WHERE id=?)", [(int)($p['documento_id'] ?? 0)]);
    }
}
