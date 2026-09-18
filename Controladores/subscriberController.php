<?php
class subscriberController {
    public function index() {
        $pageTitle = 'Suscriptores';
        $activeMenu = 'subscribers';
        $subscribers = Subscriber::all();
        include VIEW_PATH . '/Suscriptores/index.php';
    }
}
