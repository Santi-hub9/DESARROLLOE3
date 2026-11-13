<?php
class CorreoController extends Controller {
    function index() { $this->auth(); $this->view('correos/index', ['title' => 'Historial de correos', 'filas' => Reporte::correos()]); }
}
