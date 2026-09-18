<?php
class deviceController {
    public function index() {
        $pageTitle = 'Dispositivos';
        $activeMenu = 'devices';
        $devices = Device::all();
        include VIEW_PATH . '/Dispositivos/index.php';
    }
}
