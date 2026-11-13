<?php
class AnalisisController extends Controller {
    function index() { $this->auth(); $this->view('analisis/index', ['title' => 'Análisis de IA', 'filas' => HojaVida::listadoAnalisis()]); }
    function detalle() {
        $this->auth();
        $a = HojaVida::detalle((int)($_GET['id'] ?? 0));
        if (!$a) { http_response_code(404); exit('Análisis no encontrado'); }
        $this->view('analisis/detalle', ['title' => 'Detalle del análisis', 'a' => $a, 'correos' => HojaVida::correosDe($a['cid'])]);
    }
}
