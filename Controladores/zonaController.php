<?php
class zonaController {
    public function index() {
        $pageTitle = 'Zonas de Cobertura';
        $activeMenu = 'zones';
        $zones = Zona::all();
        include VIEW_PATH . '/Zonas/index.php';
    }
}
