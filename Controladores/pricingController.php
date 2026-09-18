<?php
class pricingController {
    public function index() {
        $pageTitle = 'Reglas de Pricing';
        $activeMenu = 'pricing';
        $rules = Pricing::getRules();
        include VIEW_PATH . '/Pricing/index.php';
    }
}
