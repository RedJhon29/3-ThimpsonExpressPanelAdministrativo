<?php
class dashboardController {
    public function index() {
        $pageTitle = 'Dashboard';
        $activeMenu = 'dashboard';
        $stats = Dashboard::getStats();
        $recentOrders = Order::all();
        $riders = Rider::all();
        include VIEW_PATH . '/Panel/index.php';
    }
}
