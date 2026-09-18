<?php
class servicioController {
    public function index() {
        $pageTitle = 'Servicios';
        $activeMenu = 'services';
        $services = Servicio::all();
        include VIEW_PATH . '/Servicios/index.php';
    }
}
