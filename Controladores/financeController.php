<?php
class financeController {
    public function index() {
        $pageTitle = 'Finanzas';
        $activeMenu = 'finance';
        $stats = Finance::getStats();
        $invoices = Billing::getInvoices();
        include VIEW_PATH . '/Finanzas/index.php';
    }
}
