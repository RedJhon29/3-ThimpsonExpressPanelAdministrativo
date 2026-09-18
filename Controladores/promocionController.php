<?php
class promocionController {
    public function index() {
        $pageTitle = 'Promociones';
        $activeMenu = 'promotions';
        $promotions = Promocion::all();
        include VIEW_PATH . '/Promociones/index.php';
    }
}
