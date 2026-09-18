<?php
class ReportController {
    public function index() {
        $pageTitle = 'Reportes';
        $activeMenu = 'reports';
        include VIEW_PATH . '/reports/index.php';
    }
}
