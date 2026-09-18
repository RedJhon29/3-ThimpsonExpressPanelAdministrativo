<?php
class zoneController {
    public function index() {
        $pageTitle = 'Zonas de Cobertura';
        $activeMenu = 'zones';
        $zones = Zone::all();
        include VIEW_PATH . '/Zonas/index.php';
    }
}
