<?php
class notificacionController {
    public function index() {
        $pageTitle = 'Notificaciones';
        $activeMenu = 'notifications';
        $notifications = Notificacion::all();
        $rules = Notificacion::getRules();
        include VIEW_PATH . '/Notificaciones/index.php';
    }
}
