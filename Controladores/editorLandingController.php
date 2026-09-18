<?php
class editorLandingController {
    public function index() {
        $pageTitle = 'Editor de Landing';
        $activeMenu = 'landing-editor';
        include VIEW_PATH . '/LandingEditor/index.php';
    }
}
