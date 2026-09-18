<?php
class finanzaController {
    public function index() {
        $pageTitle = 'Finanzas';
        $activeMenu = 'finance';
        $stats = Finanza::getStats();
        $invoices = Facturacion::getInvoices();
        include VIEW_PATH . '/Finanzas/index.php';
    }
}
