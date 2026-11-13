<?php
class DashboardController extends Controller {
    function index() { $this->auth(); $this->view('dashboard/index', ['title' => 'Dashboard', 'k' => Reporte::todo()['kpis']]); }
}
