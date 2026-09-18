<?php
class dispositivoController {
    public function index() {
        $pageTitle = 'Dispositivos';
        $activeMenu = 'devices';
        $devices = Dispositivo::all();
        include VIEW_PATH . '/Dispositivos/index.php';
    }
}
