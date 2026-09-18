<?php
class LandingEditorController {
    public function index() {
        $pageTitle = 'Editor de Landing';
        $activeMenu = 'landing-editor';
        include VIEW_PATH . '/landing-editor/index.php';
    }
}
