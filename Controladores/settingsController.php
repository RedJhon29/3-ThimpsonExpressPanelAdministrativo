<?php
class settingsController {
    public function index() {
        $pageTitle = 'Configuración';
        $activeMenu = 'settings';
        $settings = Settings::getAll();
        include VIEW_PATH . '/Configuracion/index.php';
    }
}
