<?php
class orderController {
    public function index() {
        $pageTitle = 'Gestión de Pedidos';
        $activeMenu = 'orders';
        $orders = Order::all();
        include VIEW_PATH . '/Pedidos/index.php';
    }
    public function detail() {
        $pageTitle = 'Detalle de Pedido';
        $activeMenu = 'orders';
        include VIEW_PATH . '/Pedidos/detail.php';
    }
}
