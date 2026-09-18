<?php
class ServiceController {
    public function index() {
        $pageTitle = 'Servicios';
        $activeMenu = 'services';
        $services = Service::all();
        include VIEW_PATH . '/services/index.php';
    }
}
