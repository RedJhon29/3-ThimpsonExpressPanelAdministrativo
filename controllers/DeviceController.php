<?php
class DeviceController {
    public function index() {
        $pageTitle = 'Dispositivos';
        $activeMenu = 'devices';
        $devices = Device::all();
        include VIEW_PATH . '/devices/index.php';
    }
}
