<?php
class RiderController {
    public function index() {
        $pageTitle = 'Gestión de Riders';
        $activeMenu = 'riders';
        $riders = Rider::all();
        include VIEW_PATH . '/riders/index.php';
    }
    public function detail() {
        $pageTitle = 'Detalle de Rider';
        $activeMenu = 'riders';
        include VIEW_PATH . '/riders/detail.php';
    }
    public function tracking() {
        $pageTitle = 'Tracking en Vivo';
        $activeMenu = 'riders';
        $riders = Rider::all();
        include VIEW_PATH . '/riders/tracking.php';
    }
}
