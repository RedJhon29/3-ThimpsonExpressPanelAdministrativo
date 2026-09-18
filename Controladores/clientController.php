<?php
class clientController {
    public function index() {
        $pageTitle = 'Clientes';
        $activeMenu = 'clients';
        $clients = Client::all();
        include VIEW_PATH . '/Clientes/index.php';
    }
}
