<?php
class OrderController {
    public function index() {
        $pageTitle = 'Gestión de Pedidos';
        $activeMenu = 'orders';
        $orders = Order::all();
        include VIEW_PATH . '/orders/index.php';
    }
    public function detail() {
        $pageTitle = 'Detalle de Pedido';
        $activeMenu = 'orders';
        include VIEW_PATH . '/orders/detail.php';
    }
}
