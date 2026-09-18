<?php
class gestorContenidoController {
    public function index() {
        $pageTitle = 'CMS';
        $activeMenu = 'cms';
        $banners = GestorContenido::getBanners();
        $faqs = GestorContenido::getFaqs();
        include VIEW_PATH . '/Cms/index.php';
    }
}
