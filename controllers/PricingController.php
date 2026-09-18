<?php
class PricingController {
    public function index() {
        $pageTitle = 'Reglas de Pricing';
        $activeMenu = 'pricing';
        $rules = Pricing::getRules();
        include VIEW_PATH . '/pricing/index.php';
    }
}
