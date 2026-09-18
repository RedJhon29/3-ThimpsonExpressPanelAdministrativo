<?php
class soporteController {
    public function index() {
        $pageTitle = 'Soporte';
        $activeMenu = 'support';
        $tickets = Soporte::all();
        include VIEW_PATH . '/Soporte/index.php';
    }
}
