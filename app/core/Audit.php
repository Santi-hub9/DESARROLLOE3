<?php
// Auditoría de IA y registro de ejecuciones. Nunca debe romper el flujo principal.
class Audit {
    public static bool $activo = true;
    static function ia(string $accion, string $proveedor, string $modelo, ?int $candidatoId, string $entrada, string $salida, string $estado, int $ms): void {
        if (!self::$activo) return;
        try {
            DB::exec('INSERT INTO auditoria_ia (accion,proveedor,modelo,candidato_id,entrada_hash,resumen_salida,estado,duracion_ms) VALUES (?,?,?,?,?,?,?,?)',
                [$accion, $proveedor, $modelo, $candidatoId, hash('sha256', $entrada), mb_substr($salida, 0, 480), $estado, $ms]);
        } catch (Throwable $e) { error_log('Audit::ia ' . $e->getMessage()); }
    }
    static function log(?int $tareaId, string $flujo, string $estado, $entrada, string $resultado, int $ms): void {
        if (!self::$activo) return;
        try {
            DB::exec('INSERT INTO ejecuciones_log (tarea_id,flujo,estado,entrada,resultado,duracion_ms) VALUES (?,?,?,?,?,?)',
                [$tareaId, $flujo, $estado, is_string($entrada) ? $entrada : json_encode($entrada, JSON_UNESCAPED_UNICODE), mb_substr($resultado, 0, 2000), $ms]);
        } catch (Throwable $e) { error_log('Audit::log ' . $e->getMessage()); }
    }
}
