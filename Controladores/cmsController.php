<?php
class cmsController {
    public function index() {
        $pageTitle = 'CMS';
        $activeMenu = 'cms';
        $banners = Cms::getBanners();
        $faqs = Cms::getFaqs();
        include VIEW_PATH . '/Cms/index.php';
    }
}
