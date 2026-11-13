<?php
class CvController extends Controller {
    function index() {
        $c = Candidato::find($_SESSION['candidato_id'] ?? 0);
        if (!$c) { $this->flash('warning', 'Primero debes registrarte.'); $this->redirect('candidato'); }
        $this->view('cv/index', ['title' => 'Subir hoja de vida', 'c' => $c]);
    }
    function store() {
        csrf_check();
        $c = Candidato::find($_SESSION['candidato_id'] ?? 0);
        if (!$c) $this->redirect('candidato');
        $f = $_FILES['cv'] ?? null;
        $err = null;
        if (!$f || $f['error'] !== UPLOAD_ERR_OK) $err = 'No se recibió el archivo (máx. 5 MB).';
        elseif ($f['size'] > 5 * 1024 * 1024) $err = 'El PDF supera 5 MB.';
        elseif ((new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']) !== 'application/pdf'
               || file_get_contents($f['tmp_name'], false, null, 0, 5) !== '%PDF-') $err = 'Solo se permiten archivos PDF válidos.';
        if ($err) { $this->flash('danger', $err); $this->redirect('cv'); }

        if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0775, true);
        $nombre = bin2hex(random_bytes(16)) . '.pdf';                 // 2) almacenar en servidor local
        move_uploaded_file($f['tmp_name'], UPLOAD_DIR . $nombre);
        $hid = HojaVida::crear($c['id'], $nombre, basename($f['name']), $f['size']);   // 3) registrar en MySQL
        Candidato::estado($c['id'], 'cv_recibido');

        Queue::push('analizar_cv', ['hoja_vida_id' => (int)$hid]);   // el motor propio toma la tarea (worker)
        $this->flash('success', '¡Hoja de vida recibida! Quedó en cola y la IA la analizará en unos minutos. Puedes verificar tu documento en el menú.');
        $this->redirect('cv');
    }
}
