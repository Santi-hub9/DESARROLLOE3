<?php
class HojaVida {
    static function crear($cid, $archivo, $original, $size) {
        return DB::exec('INSERT INTO hojas_vida (candidato_id,archivo,nombre_original,tamano_bytes) VALUES (?,?,?,?)', [$cid, $archivo, $original, $size]);
    }
    static function find($id) { return DB::one('SELECT * FROM hojas_vida WHERE id=?', [$id]); }
    static function listadoAnalisis() {
        return DB::all("SELECT a.id, a.puntuacion, a.recomendacion, a.fecha_analisis, c.nombres, c.apellidos, c.email, c.estado, v.titulo AS vacante
                        FROM analisis_ia a JOIN hojas_vida h ON h.id=a.hoja_vida_id JOIN candidatos c ON c.id=h.candidato_id
                        JOIN vacantes v ON v.id=c.vacante_id ORDER BY a.id DESC LIMIT 300");
    }
    static function detalle($analisisId) {
        return DB::one("SELECT a.*, h.id AS hv_id, h.nombre_original, h.estado_procesamiento, h.fecha_subida, c.id AS cid, c.nombres, c.apellidos, c.email,
                        c.telefono, c.ciudad, c.estado, v.titulo AS vacante
                        FROM analisis_ia a JOIN hojas_vida h ON h.id=a.hoja_vida_id JOIN candidatos c ON c.id=h.candidato_id
                        JOIN vacantes v ON v.id=c.vacante_id WHERE a.id=?", [$analisisId]);
    }
    static function correosDe($cid) { return DB::all('SELECT * FROM correos_enviados WHERE candidato_id=? ORDER BY id DESC', [$cid]); }
}
