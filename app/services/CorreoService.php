<?php
// AUTOMATIZACIÓN 2 (antes: workflow n8n "Correo")
class CorreoService {
    const SYSTEM = 'Eres el equipo de Recursos Humanos de TALENTIA. Escribe en español, tono profesional y humano, sin inventar datos.
Responde SOLO un JSON válido: {"asunto":"","cuerpo_html":""}
El cuerpo es HTML simple (<p>, <ul>, <li>, <strong>) y termina firmado por "Equipo de Talento Humano - TALENTIA".';
    const INSTRUCCION = [
        'preseleccion' => 'Redacta un correo cálido de PRESELECCIÓN: felicita al candidato, menciona 2 de sus fortalezas e indica que Recursos Humanos lo contactará pronto para agendar una entrevista.',
        'agradecimiento' => 'Redacta un correo empático y respetuoso de AGRADECIMIENTO: indica que por ahora no continúa en el proceso, sugiere 1 o 2 mejoras basadas en las debilidades e invítalo a postular de nuevo. NO menciones ninguna puntuación numérica.',
    ];

    static function ejecutar(array $p): array {
        $hid = (int)($p['hoja_vida_id'] ?? 0);
        $d = DB::one('SELECT c.id AS candidato_id, c.nombres, c.email, v.titulo AS vacante, a.puntuacion, a.fortalezas, a.debilidades
                      FROM analisis_ia a JOIN hojas_vida h ON h.id=a.hoja_vida_id JOIN candidatos c ON c.id=h.candidato_id JOIN vacantes v ON v.id=c.vacante_id
                      WHERE a.hoja_vida_id=? ORDER BY a.id DESC LIMIT 1', [$hid]);
        if (!$d) throw new RuntimeException('Aún no existe el análisis de esta hoja de vida');
        $tipo = Decision::tipoCorreo((int)$d['puntuacion'], UMBRAL_PRESELECCION);

        $fila = DB::one('SELECT * FROM correos_enviados WHERE hoja_vida_id=? AND tipo=? ORDER BY id DESC LIMIT 1', [$hid, $tipo]);
        if ($fila && $fila['estado_envio'] === 'enviado') return ['omitido' => 'el correo ya fue enviado'];   // idempotente

        if (!$fila) {   // paso 1: la IA redacta y se guarda como pendiente (si el SMTP falla no se vuelve a gastar IA)
            $user = self::INSTRUCCION[$tipo] . "\n\nCandidato: {$d['nombres']}\nVacante: {$d['vacante']}\nFortalezas: " . implode('; ', json_decode($d['fortalezas'], true) ?: [])
                  . "\nDebilidades: " . implode('; ', json_decode($d['debilidades'], true) ?: []);
            $r = LLM::generar(self::SYSTEM, $user, ['accion' => 'redactar_correo', 'candidato_id' => (int)$d['candidato_id']]);
            $c = AiJson::correo($r['datos']);
            $id = (int)DB::exec("INSERT INTO correos_enviados (candidato_id,hoja_vida_id,tipo,destinatario,asunto,cuerpo,estado_envio) VALUES (?,?,?,?,?,?, 'pendiente')",
                [$d['candidato_id'], $hid, $tipo, $d['email'], $c['asunto'], $c['cuerpo']]);
            $fila = DB::one('SELECT * FROM correos_enviados WHERE id=?', [$id]);
        }
        return self::enviarFila($fila);
    }

    // Notificación de documento validado/rechazado (plantilla fija: no se envían datos personales a la IA)
    static function documento(array $p): array {
        $doc = DB::one('SELECT d.id, d.estado, c.id AS cid, c.nombres, c.email FROM documentos_identidad d JOIN candidatos c ON c.id=d.candidato_id WHERE d.id=?', [(int)($p['documento_id'] ?? 0)]);
        if (!$doc || !in_array($doc['estado'], ['validado', 'rechazado'], true)) throw new InvalidArgumentException('Documento sin decisión final');
        $tipo = 'documento_' . $doc['estado'];
        $fila = DB::one('SELECT * FROM correos_enviados WHERE candidato_id=? AND tipo=? AND estado_envio="enviado" ORDER BY id DESC LIMIT 1', [$doc['cid'], $tipo]);
        if ($fila) return ['omitido' => 'ya notificado'];
        $n = e($doc['nombres']);
        [$asunto, $cuerpo] = $doc['estado'] === 'validado'
            ? ['Tu documento fue verificado', "<p>Hola $n,</p><p>Verificamos tu documento de identidad con éxito.</p><p>Equipo de Talento Humano - TALENTIA</p>"]
            : ['No pudimos verificar tu documento', "<p>Hola $n,</p><p>No pudimos verificar tu documento. Por favor sube una foto más clara o comunícate con Recursos Humanos.</p><p>Equipo de Talento Humano - TALENTIA</p>"];
        $id = (int)DB::exec("INSERT INTO correos_enviados (candidato_id,hoja_vida_id,tipo,destinatario,asunto,cuerpo,estado_envio) VALUES (?,NULL,?,?,?,?, 'pendiente')", [$doc['cid'], $tipo, $doc['email'], $asunto, $cuerpo]);
        return self::enviarFila(DB::one('SELECT * FROM correos_enviados WHERE id=?', [$id]));
    }

    private static function enviarFila(array $fila): array {
        try {
            Mailer::enviar($fila['destinatario'], $fila['asunto'], $fila['cuerpo']);
            DB::run("UPDATE correos_enviados SET estado_envio='enviado', fecha_envio=NOW(), error_mensaje=NULL WHERE id=?", [$fila['id']]);
            return ['correo_id' => (int)$fila['id'], 'tipo' => $fila['tipo'], 'estado' => 'enviado'];
        } catch (Throwable $e) {
            DB::run("UPDATE correos_enviados SET estado_envio='fallido', error_mensaje=? WHERE id=?", [mb_substr($e->getMessage(), 0, 480), $fila['id']]);
            throw $e;   // la cola lo reintentará
        }
    }
}
