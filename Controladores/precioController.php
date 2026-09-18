<?php
class precioController {
    public function index() {
        $pageTitle = 'Reglas de Pricing';
        $activeMenu = 'pricing';
        $rules = Precio::getRules();
        include VIEW_PATH . '/Pricing/index.php';
    }
}
