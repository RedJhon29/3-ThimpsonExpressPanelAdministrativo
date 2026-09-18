<?php
class RatingController {
    public function index() {
        $pageTitle = 'Calificaciones';
        $activeMenu = 'ratings';
        $ratings = Rating::all();
        $breakdown = Rating::breakdown();
        $average = Rating::average();
        include VIEW_PATH . '/ratings/index.php';
    }
}
