<?php
class supportController {
    public function index() {
        $pageTitle = 'Soporte';
        $activeMenu = 'support';
        $tickets = Support::all();
        include VIEW_PATH . '/Soporte/index.php';
    }
}
