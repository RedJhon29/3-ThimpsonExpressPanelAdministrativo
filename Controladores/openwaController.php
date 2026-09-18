<?php
class openwaController {
    public function index() {
        $pageTitle = 'OpenWA';
        $activeMenu = 'openwa';
        $status = Openwa::getStatus();
        include VIEW_PATH . '/Openwa/index.php';
    }
}
