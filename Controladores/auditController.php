<?php
class auditController {
    public function index() {
        $pageTitle = 'Auditoría';
        $activeMenu = 'audit';
        $logs = Audit::all();
        include VIEW_PATH . '/Auditoria/index.php';
    }
}
