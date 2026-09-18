<?php
class marketplaceController {
    public function index() {
        $pageTitle = 'Marketplace';
        $activeMenu = 'marketplace';
        $businesses = Business::all();
        include VIEW_PATH . '/Marketplace/index.php';
    }
}
