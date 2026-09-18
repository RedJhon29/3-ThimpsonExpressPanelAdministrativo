<?php
class configuracionController {
    public function index() {
        $pageTitle = 'Configuración';
        $activeMenu = 'settings';
        $settings = Configuracion::getAll();
        include VIEW_PATH . '/Configuracion/index.php';
    }
}
