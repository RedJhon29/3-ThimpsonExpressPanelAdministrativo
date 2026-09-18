<?php
class ZoneController {
    public function index() {
        $pageTitle = 'Zonas de Cobertura';
        $activeMenu = 'zones';
        $zones = Zone::all();
        include VIEW_PATH . '/zones/index.php';
    }
}
