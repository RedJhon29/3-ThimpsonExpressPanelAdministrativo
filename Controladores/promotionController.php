<?php
class promotionController {
    public function index() {
        $pageTitle = 'Promociones';
        $activeMenu = 'promotions';
        $promotions = Promotion::all();
        include VIEW_PATH . '/Promociones/index.php';
    }
}
