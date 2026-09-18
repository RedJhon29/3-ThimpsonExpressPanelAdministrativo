<?php
class resenaController {
    public function index() {
        $pageTitle = 'Reseñas';
        $activeMenu = 'reviews';
        $reviews = Calificacion::all();
        include VIEW_PATH . '/Resenas/index.php';
    }
}
