<?php
class ReviewController {
    public function index() {
        $pageTitle = 'Reseñas';
        $activeMenu = 'reviews';
        $reviews = Rating::all();
        include VIEW_PATH . '/reviews/index.php';
    }
}
