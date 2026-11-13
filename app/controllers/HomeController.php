<?php
class HomeController extends Controller {
    function index() { $this->view('home/index', ['title' => 'Inicio']); }
}
