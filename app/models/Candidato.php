<?php
class Candidato {
    static function vacantes() { return DB::all('SELECT id,titulo FROM vacantes WHERE activa=1 ORDER BY titulo'); }
    static function find($id) {
        return DB::one('SELECT c.*, v.titulo AS vacante, v.requisitos FROM candidatos c JOIN vacantes v ON v.id=c.vacante_id WHERE c.id=?', [$id]);
    }
    static function porEmail($email) { return DB::one('SELECT id FROM candidatos WHERE email=?', [$email]); }
    static function crear($d) {
        return DB::exec('INSERT INTO candidatos (vacante_id,nombres,apellidos,email,documento_identidad,telefono,ciudad,autorizacion_datos,fecha_autorizacion) VALUES (?,?,?,?,?,?,?,1,NOW())',
            [$d['vacante_id'], $d['nombres'], $d['apellidos'], $d['email'], $d['documento_identidad'], $d['telefono'], $d['ciudad']]);
    }
    static function estado($id, $estado) { DB::exec('UPDATE candidatos SET estado=? WHERE id=?', [$estado, $id]); }
}
