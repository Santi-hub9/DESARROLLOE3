<?php
class RankingController extends Controller {
    function index() {
        $this->auth();
        $todos = isset($_GET['todos']);
        $this->view('ranking/index', ['title' => 'Ranking', 'filas' => Reporte::ranking($todos ? 1000 : 10), 'todos' => $todos]);
    }
}
