<?php
class SettingsController {
    public function index() {
        $pageTitle = 'Configuración';
        $activeMenu = 'settings';
        $settings = Settings::getAll();
        include VIEW_PATH . '/settings/index.php';
    }
}
