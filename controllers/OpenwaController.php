<?php
class OpenwaController {
    public function index() {
        $pageTitle = 'OpenWA';
        $activeMenu = 'openwa';
        $status = Openwa::getStatus();
        include VIEW_PATH . '/openwa/index.php';
    }
}
