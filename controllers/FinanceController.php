<?php
class FinanceController {
    public function index() {
        $pageTitle = 'Finanzas';
        $activeMenu = 'finance';
        $stats = Finance::getStats();
        $invoices = Billing::getInvoices();
        include VIEW_PATH . '/finance/index.php';
    }
}
