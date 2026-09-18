<?php
class SubscriptionController {
    public function index() {
        $pageTitle = 'Suscripciones';
        $activeMenu = 'subscriptions';
        $subscriptions = Subscription::all();
        include VIEW_PATH . '/subscriptions/index.php';
    }
}
