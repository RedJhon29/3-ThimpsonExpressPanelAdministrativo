<?php
class landingEditorController {
    public function index() {
        $pageTitle = 'Editor de Landing';
        $activeMenu = 'landing-editor';
        include VIEW_PATH . '/LandingEditor/index.php';
    }
}
