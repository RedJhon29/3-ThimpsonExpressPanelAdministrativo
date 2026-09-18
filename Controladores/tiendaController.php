<?php
class tiendaController {
    public function index() {
        $pageTitle = 'Marketplace';
        $activeMenu = 'marketplace';
        $businesses = Negocio::all();
        include VIEW_PATH . '/Marketplace/index.php';
    }
}
