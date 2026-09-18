<?php
class reviewController {
    public function index() {
        $pageTitle = 'Reseñas';
        $activeMenu = 'reviews';
        $reviews = Rating::all();
        include VIEW_PATH . '/Resenas/index.php';
    }
}
