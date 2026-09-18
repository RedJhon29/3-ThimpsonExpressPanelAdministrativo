<?php
class SupportController {
    public function index() {
        $pageTitle = 'Soporte';
        $activeMenu = 'support';
        $tickets = Support::all();
        include VIEW_PATH . '/support/index.php';
    }
}
