<?php
class AutomatizacionController extends Controller {
    function index() {
        $this->rol(['admin']);
        $this->view('automatizacion/index', ['title' => 'Motor de automatización',
            'tareas' => DB::all('SELECT * FROM cola_tareas ORDER BY id DESC LIMIT 60'),
            'logs' => DB::all('SELECT * FROM ejecuciones_log ORDER BY id DESC LIMIT 60'),
            'auditoria' => DB::all('SELECT * FROM auditoria_ia ORDER BY id DESC LIMIT 60')]);
    }
    function reintentar() { $this->rol(['admin']); csrf_check(); Queue::reintentar((int)($_POST['id'] ?? 0)); $this->flash('success', 'Tarea reprogramada.'); $this->redirect('automatizacion'); }
    function ejecutar() {   // ejecuta el motor a mano (útil en la sustentación)
        $this->rol(['admin']); csrf_check(); set_time_limit(280);
        $r = Queue::procesar(5);
        $this->flash('info', count($r) . ' tarea(s) procesada(s).'); $this->redirect('automatizacion');
    }
}
