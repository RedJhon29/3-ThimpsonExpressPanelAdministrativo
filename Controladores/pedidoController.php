<?php
class pedidoController {
    public function index() {
        $pageTitle = 'Gestión de Pedidos';
        $activeMenu = 'orders';
        $orders = Pedido::all();
        include VIEW_PATH . '/Pedidos/index.php';
    }
    public function detail() {
        $pageTitle = 'Detalle de Pedido';
        $activeMenu = 'orders';
        $orderId = $_GET['id'] ?? null;
        $order = $orderId ? Pedido::findById($orderId) : null;
        include VIEW_PATH . '/Pedidos/detail.php';
    }
}
