<?php
class panelController {
    public function index() {
        $pageTitle = 'Dashboard';
        $activeMenu = 'dashboard';
        $stats = Panel::getStats();
        $recentOrders = Pedido::all();
        $riders = Motorizado::all();
        include VIEW_PATH . '/Panel/index.php';
    }
}
