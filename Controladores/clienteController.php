<?php
class clienteController {
    public function index() {
        $pageTitle = 'Clientes';
        $activeMenu = 'clients';
        $clients = Cliente::all();
        include VIEW_PATH . '/Clientes/index.php';
    }
}
