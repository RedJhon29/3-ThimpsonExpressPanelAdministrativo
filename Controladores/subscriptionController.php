<?php
class subscriptionController {
    public function index() {
        $pageTitle = 'Suscripciones';
        $activeMenu = 'subscriptions';
        $subscriptions = Subscription::all();
        include VIEW_PATH . '/Suscripciones/index.php';
    }
}
