<?php
class CandidatoController extends Controller {
    function index() { $this->view('candidato/index', ['title' => 'Registro de candidatos', 'vacantes' => Candidato::vacantes()]); }
    function store() {
        csrf_check();
        $d = [];
        foreach (['nombres', 'apellidos', 'email', 'documento_identidad', 'telefono', 'ciudad'] as $c) $d[$c] = trim($_POST[$c] ?? '');
        $d['vacante_id'] = (int)($_POST['vacante_id'] ?? 0);
        $d['documento_identidad'] = preg_replace('/\D/', '', $d['documento_identidad']);
        if ($d['nombres'] === '' || $d['apellidos'] === '' || !filter_var($d['email'], FILTER_VALIDATE_EMAIL) || !$d['vacante_id'] || strlen($d['documento_identidad']) < 5) {
            $this->flash('danger', 'Completa nombres, apellidos, documento, un correo válido y la vacante.'); $this->redirect('candidato');
        }
        if (empty($_POST['autorizacion'])) {   // Ley 1581 de 2012: sin autorización no hay tratamiento
            $this->flash('danger', 'Debes autorizar el tratamiento de tus datos personales para continuar.'); $this->redirect('candidato');
        }
        $existe = Candidato::porEmail($d['email']);
        $_SESSION['candidato_id'] = $existe ? $existe['id'] : Candidato::crear($d);
        $this->flash('success', $existe ? 'Ya estabas registrado. Puedes subir una nueva hoja de vida.' : 'Registro exitoso. Ahora sube tu hoja de vida en PDF.');
        $this->redirect('cv');
    }
}
