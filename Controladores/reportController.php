<?php
class reportController {
    public function index() {
        $pageTitle = 'Reportes';
        $activeMenu = 'reports';
        include VIEW_PATH . '/Reportes/index.php';
    }
}
