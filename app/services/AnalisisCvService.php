<?php
// AUTOMATIZACIÓN 1 (antes: workflow n8n "Analizar CV")
class AnalisisCvService {
    const SYSTEM = 'Eres un reclutador senior. Evalúa la hoja de vida adjunta frente a la vacante. Responde SOLO un JSON válido con esta forma:
{"puntuacion":0-100,"resumen":"","anios_experiencia":0,"experiencia":"","habilidades_tecnicas":[],"formacion":[],"certificaciones":[],"idiomas":[{"idioma":"","nivel":""}],"fortalezas":[],"debilidades":[],"recomendacion":"contratar|entrevistar|en_espera|descartar","justificacion":""}
Rúbrica de la puntuación: experiencia relevante 35, habilidades técnicas 30, formación 15, certificaciones 10, idiomas 10.
No inventes datos que no estén en el CV. Responde en español.';

    static function ejecutar(array $p): array {
        $hid = (int)($p['hoja_vida_id'] ?? 0);
        $h = DB::one('SELECT h.*, c.id AS cid, v.titulo, v.requisitos FROM hojas_vida h JOIN candidatos c ON c.id=h.candidato_id JOIN vacantes v ON v.id=c.vacante_id WHERE h.id=?', [$hid]);
        if (!$h) throw new InvalidArgumentException("La hoja de vida $hid no existe");
        if (DB::one('SELECT id FROM analisis_ia WHERE hoja_vida_id=?', [$hid])) return ['omitido' => 'ya estaba analizada'];   // idempotente

        DB::run("UPDATE hojas_vida SET estado_procesamiento='procesando' WHERE id=?", [$hid]);
        DB::run("UPDATE candidatos SET estado='en_analisis' WHERE id=?", [$h['cid']]);
        $ruta = UPLOAD_DIR . basename($h['archivo']);
        if (!is_file($ruta)) throw new InvalidArgumentException('El archivo PDF no existe en el servidor');

        $r = LLM::generar(self::SYSTEM, "VACANTE: {$h['titulo']}\nREQUISITOS: {$h['requisitos']}\n\nAnaliza la hoja de vida adjunta.",
            ['accion' => 'analizar_cv', 'candidato_id' => (int)$h['cid']], ['path' => $ruta, 'mime' => 'application/pdf']);
        $a = AiJson::analisis($r['datos']);

        $pdo = DB::pdo(); $pdo->beginTransaction();
        try {
            DB::exec('INSERT INTO analisis_ia (hoja_vida_id,puntuacion,resumen,anios_experiencia,experiencia,habilidades,formacion,certificaciones,idiomas,fortalezas,debilidades,recomendacion,justificacion,modelo_ia) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)',
                [$hid, $a['puntuacion'], $a['resumen'], $a['anios_experiencia'], $a['experiencia'], $a['habilidades'], $a['formacion'], $a['certificaciones'], $a['idiomas'], $a['fortalezas'], $a['debilidades'], $a['recomendacion'], $a['justificacion'], $r['proveedor'] . ':' . $r['modelo']]);
            DB::run("UPDATE hojas_vida SET estado_procesamiento='analizado' WHERE id=?", [$hid]);
            DB::run('UPDATE candidatos SET estado=? WHERE id=?', [Decision::estadoCandidato($a['puntuacion'], UMBRAL_PRESELECCION), $h['cid']]);
            $pdo->commit();
        } catch (Throwable $e) { $pdo->rollBack(); throw $e; }

        Queue::push('actualizar_ranking', ['hoja_vida_id' => $hid]);   // dispara Automatizaciones 2 y 3
        Queue::push('enviar_correo', ['hoja_vida_id' => $hid]);
        return ['puntuacion' => $a['puntuacion'], 'proveedor' => $r['proveedor']];
    }

    // Se llama cuando se agotan los reintentos
    static function alFallar(array $p, string $error): void {
        $hid = (int)($p['hoja_vida_id'] ?? 0);
        DB::run("UPDATE hojas_vida SET estado_procesamiento='error' WHERE id=?", [$hid]);
        DB::run("UPDATE candidatos SET estado='error' WHERE id=(SELECT candidato_id FROM hojas_vida WHERE id=?)", [$hid]);
    }
}
