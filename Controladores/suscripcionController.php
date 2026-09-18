<?php
class suscripcionController {
    public function index() {
        $pageTitle = 'Suscripciones';
        $activeMenu = 'subscriptions';
        $subscriptions = Suscripcion::all();
        include VIEW_PATH . '/Suscripciones/index.php';
    }
}
