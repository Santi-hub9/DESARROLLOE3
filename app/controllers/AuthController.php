<?php
class AuthController extends Controller {
    function login() { $this->view('auth/login', ['title' => 'Acceso administrador']); }
    function entrar() {
        csrf_check();
        $u = DB::one('SELECT * FROM usuarios WHERE email=?', [trim($_POST['email'] ?? '')]);
        if ($u && password_verify($_POST['password'] ?? '', $u['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['admin'] = ['id' => $u['id'], 'nombre' => $u['nombre'], 'rol' => $u['rol']];
            $this->redirect('dashboard');
        }
        $this->flash('danger', 'Credenciales incorrectas.');
        $this->redirect('auth', 'login');
    }
    function salir() { session_destroy(); header('Location: ' . url('home')); exit; }
}
