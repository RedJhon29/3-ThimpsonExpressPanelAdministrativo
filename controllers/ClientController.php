<?php
class ClientController {
    public function index() {
        $pageTitle = 'Clientes';
        $activeMenu = 'clients';
        $clients = Client::all();
        include VIEW_PATH . '/clients/index.php';
    }
}
