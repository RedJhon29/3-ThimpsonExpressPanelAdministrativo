<?php
class notificationController {
    public function index() {
        $pageTitle = 'Notificaciones';
        $activeMenu = 'notifications';
        $notifications = Notification::all();
        $rules = Notification::getRules();
        include VIEW_PATH . '/Notificaciones/index.php';
    }
}
