<?php
class Reporte {
    static function ranking($limite = 10) {
        $limite = (int)$limite;
        return DB::all("SELECT r.posicion, r.puntuacion, r.estado, c.id AS candidato_id, c.nombres, c.apellidos, c.email, v.titulo AS vacante,
                        (SELECT MAX(a.id) FROM analisis_ia a WHERE a.hoja_vida_id=r.hoja_vida_id) AS analisis_id
                        FROM ranking r JOIN candidatos c ON c.id=r.candidato_id JOIN vacantes v ON v.id=c.vacante_id
                        ORDER BY r.posicion ASC LIMIT $limite");
    }
    static function correos() {
        return DB::all("SELECT e.*, c.nombres, c.apellidos FROM correos_enviados e JOIN candidatos c ON c.id=e.candidato_id ORDER BY e.id DESC LIMIT 300");
    }
    static function todo() {
        $u = (int)UMBRAL_PRESELECCION;
        $k = DB::one("SELECT COUNT(*) analizados, COALESCE(ROUND(AVG(puntuacion),1),0) promedio, COALESCE(MAX(puntuacion),0) maximo,
                      COALESCE(SUM(puntuacion>=$u),0) aprobados, COALESCE(SUM(puntuacion<$u),0) rechazados FROM v_ultimo_analisis");
        $k['total'] = (int)DB::one('SELECT COUNT(*) c FROM candidatos')['c'];
        $k['pendientes'] = max(0, $k['total'] - (int)$k['analizados']);
        $bins = ['0-20' => 0, '21-40' => 0, '41-60' => 0, '61-80' => 0, '81-100' => 0];
        foreach (DB::all("SELECT CASE WHEN puntuacion<=20 THEN '0-20' WHEN puntuacion<=40 THEN '21-40' WHEN puntuacion<=60 THEN '41-60'
                          WHEN puntuacion<=80 THEN '61-80' ELSE '81-100' END r, COUNT(*) n FROM v_ultimo_analisis GROUP BY r") as $r) $bins[$r['r']] = (int)$r['n'];
        $dias = DB::all("SELECT DATE(creado_en) d, COUNT(*) n FROM candidatos WHERE creado_en >= CURDATE() - INTERVAL 13 DAY GROUP BY d ORDER BY d");
        $prom = DB::all("SELECT DATE(fecha_analisis) d, ROUND(AVG(puntuacion),1) p FROM analisis_ia WHERE fecha_analisis >= CURDATE() - INTERVAL 13 DAY GROUP BY d ORDER BY d");
        $mail = DB::all("SELECT tipo, estado_envio, COUNT(*) n FROM correos_enviados GROUP BY tipo, estado_envio");
        return ['kpis' => $k, 'distribucion' => $bins, 'porDia' => $dias, 'promedioDia' => $prom, 'correos' => $mail, 'top10' => self::ranking(10)];
    }
}
