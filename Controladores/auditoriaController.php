<?php
class auditoriaController {
    public function index() {
        $pageTitle = 'Auditoría';
        $activeMenu = 'audit';
        $logs = Auditoria::all();
        include VIEW_PATH . '/Auditoria/index.php';
    }
}
