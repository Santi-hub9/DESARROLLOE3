<?php
class ApiController extends Controller {
    // PDF de la hoja de vida: solo personal autenticado
    function cv() {
        $this->auth();
        $hv = HojaVida::find((int)($_GET['id'] ?? 0));
        if (!$hv) { http_response_code(404); exit('No encontrado'); }
        $ruta = UPLOAD_DIR . basename($hv['archivo']);
        if (!is_file($ruta)) { http_response_code(404); exit('Archivo no encontrado'); }
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . basename($hv['nombre_original']) . '"');
        readfile($ruta); exit;
    }
    function stats() {
        if (empty($_SESSION['admin'])) $this->json(['error' => 'No autorizado'], 401);
        $this->json(Reporte::todo());
    }
    // Para hosting sin cron: un cron externo gratuito (p. ej. cron-job.org) llama cada minuto a esta URL con el token
    function tick() {
        if (CRON_TOKEN === '' || !hash_equals(CRON_TOKEN, $_GET['token'] ?? '')) $this->json(['error' => 'Token inválido'], 403);
        set_time_limit(280);
        $this->json(['procesadas' => Queue::procesar(3)]);
    }
}
