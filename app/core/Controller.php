<?php
class Controller {
    protected function view($v, $d = []) {
        extract($d);
        $title = $d['title'] ?? 'TALENTIA';
        ob_start(); require APP_PATH . "/views/$v.php"; $content = ob_get_clean();
        require APP_PATH . '/views/layout.php';
    }
    protected function redirect($page, $action = 'index', $q = []) { header('Location: ' . url($page, $action, $q)); exit; }
    // Cualquier personal autenticado (admin o reclutador)
    protected function auth() { $this->rol(['admin', 'reclutador']); }
    // Control de acceso por rol
    protected function rol(array $roles) {
        if (empty($_SESSION['admin'])) $this->redirect('auth', 'login');
        if (!in_array($_SESSION['admin']['rol'] ?? 'admin', $roles, true)) { http_response_code(403); exit('No tienes permisos para esta acción.'); }
    }
    protected function flash($tipo, $msg) { $_SESSION['flash'] = [$tipo, $msg]; }
    protected function json($data, $code = 200) {
        http_response_code($code); header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE); exit;
    }
}
