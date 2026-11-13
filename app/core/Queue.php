<?php
/**
 * Motor de automatización propio (reemplaza a n8n): cola en MySQL + reintentos con espera creciente + log por ejecución.
 * Lo ejecuta bin/worker.php (cron o bucle) o api&action=tick (cron externo gratuito).
 */
class Queue {
    const HANDLERS = [
        'analizar_cv'         => ['AnalisisCvService', 'ejecutar'],
        'enviar_correo'       => ['CorreoService', 'ejecutar'],
        'actualizar_ranking'  => ['RankingService', 'ejecutar'],
        'verificar_documento' => ['VerificacionDocumentoService', 'ejecutar'],
        'notificar_documento' => ['CorreoService', 'documento'],
    ];

    static function push(string $tipo, array $payload, int $demoraSeg = 0): int {
        return (int)DB::exec('INSERT INTO cola_tareas (tipo,payload,proximo_intento) VALUES (?,?,DATE_ADD(NOW(), INTERVAL ? SECOND))',
            [$tipo, json_encode($payload), $demoraSeg]);
    }

    static function procesar(int $max = 5): array {
        // Recupera tareas "colgadas" (worker caído a mitad de ejecución)
        DB::run("UPDATE cola_tareas SET estado='pendiente' WHERE estado='procesando' AND bloqueada_en < (NOW() - INTERVAL 10 MINUTE)");
        $ids = DB::all("SELECT id FROM cola_tareas WHERE estado='pendiente' AND proximo_intento <= NOW() ORDER BY id LIMIT " . (int)$max);
        $res = [];
        foreach ($ids as $r) { $x = self::ejecutarUna((int)$r['id']); if ($x) $res[] = $x; }
        return $res;
    }

    static function ejecutarUna(int $id): ?array {
        // Reclamo atómico: si otro worker la tomó antes, rowCount() = 0
        if (DB::run("UPDATE cola_tareas SET estado='procesando', bloqueada_en=NOW(), intentos=intentos+1 WHERE id=? AND estado='pendiente'", [$id]) === 0) return null;
        $t = DB::one('SELECT * FROM cola_tareas WHERE id=?', [$id]);
        $payload = json_decode($t['payload'], true) ?: [];
        $cls = null; $t0 = microtime(true);
        try {
            if (!isset(self::HANDLERS[$t['tipo']])) throw new InvalidArgumentException('Tipo de tarea desconocido: ' . $t['tipo']);
            [$cls, $metodo] = self::HANDLERS[$t['tipo']];
            $out = $cls::$metodo($payload);
            DB::run("UPDATE cola_tareas SET estado='completada', ultimo_error=NULL WHERE id=?", [$id]);
            Audit::log($id, $t['tipo'], 'ok', $payload, json_encode($out, JSON_UNESCAPED_UNICODE), (int)((microtime(true) - $t0) * 1000));
            return ['id' => $id, 'estado' => 'completada'];
        } catch (Throwable $e) {
            $msg = mb_substr($e->getMessage(), 0, 480);
            $ms = (int)((microtime(true) - $t0) * 1000);
            $reintentable = !($e instanceof InvalidArgumentException) && (int)$t['intentos'] < (int)$t['max_intentos'];
            if ($reintentable) {
                DB::run("UPDATE cola_tareas SET estado='pendiente', ultimo_error=?, proximo_intento=DATE_ADD(NOW(), INTERVAL ? SECOND) WHERE id=?",
                    [$msg, Backoff::segundos((int)$t['intentos']), $id]);
                Audit::log($id, $t['tipo'], 'reintento', $payload, $msg, $ms);
                return ['id' => $id, 'estado' => 'reintento'];
            }
            DB::run("UPDATE cola_tareas SET estado='fallida', ultimo_error=? WHERE id=?", [$msg, $id]);
            Audit::log($id, $t['tipo'], 'error', $payload, $msg, $ms);
            if ($cls && method_exists($cls, 'alFallar')) { try { $cls::alFallar($payload, $msg); } catch (Throwable $x) { error_log($x->getMessage()); } }
            return ['id' => $id, 'estado' => 'fallida'];
        }
    }

    static function reintentar(int $id): void {
        DB::run("UPDATE cola_tareas SET estado='pendiente', intentos=0, proximo_intento=NOW(), ultimo_error=NULL WHERE id=? AND estado='fallida'", [$id]);
    }
}
