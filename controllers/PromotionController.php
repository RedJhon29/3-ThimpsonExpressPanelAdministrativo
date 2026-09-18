<?php
class PromotionController {
    public function index() {
        $pageTitle = 'Promociones';
        $activeMenu = 'promotions';
        $promotions = Promotion::all();
        include VIEW_PATH . '/promotions/index.php';
    }
}
