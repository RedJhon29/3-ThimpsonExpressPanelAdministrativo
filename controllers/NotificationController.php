<?php
class NotificationController {
    public function index() {
        $pageTitle = 'Notificaciones';
        $activeMenu = 'notifications';
        $notifications = Notification::all();
        $rules = Notification::getRules();
        include VIEW_PATH . '/notifications/index.php';
    }
}
