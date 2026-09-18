<?php
class motorizadoController {
    public function index() {
        $pageTitle = 'Gestión de Riders';
        $activeMenu = 'riders';
        $riders = Motorizado::all();
        include VIEW_PATH . '/Riders/index.php';
    }
    public function detail() {
        $pageTitle = 'Detalle de Rider';
        $activeMenu = 'riders';
        include VIEW_PATH . '/Riders/detail.php';
    }
    public function tracking() {
        $pageTitle = 'Tracking en Vivo';
        $activeMenu = 'riders';
        $riders = Motorizado::all();
        include VIEW_PATH . '/Riders/tracking.php';
    }
}
