<?php
class calificacionController {
    public function index() {
        $pageTitle = 'Calificaciones';
        $activeMenu = 'ratings';
        $ratings = Calificacion::all();
        $breakdown = Calificacion::breakdown();
        $average = Calificacion::average();
        include VIEW_PATH . '/Calificaciones/index.php';
    }
}
