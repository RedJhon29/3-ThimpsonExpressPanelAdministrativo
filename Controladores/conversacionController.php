<?php
class conversacionController {
    public function index() {
        $pageTitle = 'Conversaciones';
        $activeMenu = 'conversations';
        $conversations = Conversacion::all();
        include VIEW_PATH . '/Conversaciones/index.php';
    }
}
