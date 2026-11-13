<?php
class DocumentosController extends Controller {
    // ---- Candidato (sesión de candidato) ----
    function index() {
        $c = Candidato::find($_SESSION['candidato_id'] ?? 0);
        if (!$c) { $this->flash('warning', 'Primero debes registrarte.'); $this->redirect('candidato'); }
        $docs = DB::all('SELECT id,tipo,estado,confianza,creado_en FROM documentos_identidad WHERE candidato_id=? ORDER BY id DESC', [$c['id']]);
        $this->view('documentos/index', ['title' => 'Verificar documento', 'c' => $c, 'docs' => $docs]);
    }
    function store() {
        csrf_check();
        $c = Candidato::find($_SESSION['candidato_id'] ?? 0);
        if (!$c) $this->redirect('candidato');
        $f = $_FILES['imagen'] ?? null; $err = null; $ext = null;
        $permitidos = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        if (!$f || $f['error'] !== UPLOAD_ERR_OK) $err = 'No se recibió la imagen (máx. 8 MB).';
        elseif ($f['size'] > 8 * 1024 * 1024) $err = 'La imagen supera 8 MB.';
        else {
            $mime = (new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);
            if (!isset($permitidos[$mime]) || !@getimagesize($f['tmp_name'])) $err = 'Solo se permiten imágenes JPG, PNG o WEBP.'; else $ext = $permitidos[$mime];
        }
        if ($err) { $this->flash('danger', $err); $this->redirect('documentos'); }
        if (!is_dir(DOCS_DIR)) mkdir(DOCS_DIR, 0775, true);
        $nombre = bin2hex(random_bytes(16)) . '.' . $ext;
        move_uploaded_file($f['tmp_name'], DOCS_DIR . $nombre);
        $id = DB::exec('INSERT INTO documentos_identidad (candidato_id,tipo,archivo,subido_por_tipo,subido_por_id,ip,autorizo_externo) VALUES (?,?,?,?,?,?,?)',
            [$c['id'], 'cedula', $nombre, 'candidato', $c['id'], $_SERVER['REMOTE_ADDR'] ?? '', empty($_POST['permitir_externo']) ? 0 : 1]);
        DB::run("UPDATE candidatos SET documento_verificado='pendiente' WHERE id=?", [$c['id']]);
        Queue::push('verificar_documento', ['documento_id' => (int)$id]);
        $this->flash('success', 'Documento recibido. Lo estamos verificando.');
        $this->redirect('documentos');
    }

    // ---- Personal (admin o reclutador) ----
    function lista() {
        $this->auth();
        $filas = DB::all("SELECT d.*, c.nombres, c.apellidos, c.documento_identidad FROM documentos_identidad d JOIN candidatos c ON c.id=d.candidato_id
                          ORDER BY (d.estado='revision_manual') DESC, d.id DESC LIMIT 200");
        $this->view('documentos/lista', ['title' => 'Documentos de identidad', 'filas' => $filas]);
    }
    function imagen() {   // las imágenes solo se sirven con sesión de personal
        $this->auth();
        $d = DB::one('SELECT archivo FROM documentos_identidad WHERE id=?', [(int)($_GET['id'] ?? 0)]);
        $ruta = $d ? DOCS_DIR . basename($d['archivo']) : '';
        if (!$d || !is_file($ruta)) { http_response_code(404); exit('No encontrado'); }
        header('Content-Type: ' . mime_content_type($ruta)); readfile($ruta); exit;
    }
    function decidir() {   // revisión humana cuando la confianza es baja
        $this->auth(); csrf_check();
        $id = (int)($_POST['id'] ?? 0); $dec = $_POST['decision'] ?? '';
        if (!in_array($dec, ['validado', 'rechazado'], true)) { http_response_code(400); exit('Decisión inválida'); }
        $d = DB::one('SELECT candidato_id FROM documentos_identidad WHERE id=?', [$id]);
        if (!$d) { http_response_code(404); exit('No encontrado'); }
        DB::run('UPDATE documentos_identidad SET estado=?, revisado_por=?, fecha_revision=NOW() WHERE id=?', [$dec, $_SESSION['admin']['id'], $id]);
        DB::run('UPDATE candidatos SET documento_verificado=? WHERE id=?', [$dec, $d['candidato_id']]);
        Queue::push('notificar_documento', ['documento_id' => $id]);
        $this->flash('success', "Documento marcado como $dec.");
        $this->redirect('documentos', 'lista');
    }
}
