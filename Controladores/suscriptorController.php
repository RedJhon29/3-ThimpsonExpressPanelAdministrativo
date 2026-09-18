<?php
class suscriptorController {
    public function index() {
        $pageTitle = 'Suscriptores';
        $activeMenu = 'subscribers';
        $subscribers = Suscriptor::all();
        include VIEW_PATH . '/Suscriptores/index.php';
    }
}
