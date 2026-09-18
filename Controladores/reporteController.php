<?php
class reporteController {
    public function index() {
        $pageTitle = 'Reportes';
        $activeMenu = 'reports';
        include VIEW_PATH . '/Reportes/index.php';
    }
}
