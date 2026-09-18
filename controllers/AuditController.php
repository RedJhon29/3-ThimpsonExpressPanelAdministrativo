<?php
class AuditController {
    public function index() {
        $pageTitle = 'Auditoría';
        $activeMenu = 'audit';
        $logs = Audit::all();
        include VIEW_PATH . '/audit/index.php';
    }
}
