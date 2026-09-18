<?php
class MarketplaceController {
    public function index() {
        $pageTitle = 'Marketplace';
        $activeMenu = 'marketplace';
        $businesses = Business::all();
        include VIEW_PATH . '/marketplace/index.php';
    }
}
