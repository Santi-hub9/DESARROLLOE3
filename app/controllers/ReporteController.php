<?php
class ReporteController extends Controller {
    function index() { $this->auth(); $this->view('reportes/index', ['title' => 'Reportes y estadísticas']); }
    function csv() {
        $this->auth();
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="ranking_talentia_' . date('Ymd') . '.csv"');
        $o = fopen('php://output', 'w'); fwrite($o, "\xEF\xBB\xBF");
        fputcsv($o, ['Posicion', 'Nombres', 'Apellidos', 'Email', 'Vacante', 'Puntuacion', 'Estado']);
        foreach (Reporte::ranking(10000) as $r) fputcsv($o, [$r['posicion'], $r['nombres'], $r['apellidos'], $r['email'], $r['vacante'], $r['puntuacion'], $r['estado']]);
        fclose($o); exit;
    }
}
