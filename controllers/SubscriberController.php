<?php
class SubscriberController {
    public function index() {
        $pageTitle = 'Suscriptores';
        $activeMenu = 'subscribers';
        $subscribers = Subscriber::all();
        include VIEW_PATH . '/subscribers/index.php';
    }
}
