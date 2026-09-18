<?php
class serviceController {
    public function index() {
        $pageTitle = 'Servicios';
        $activeMenu = 'services';
        $services = Service::all();
        include VIEW_PATH . '/Servicios/index.php';
    }
}
