<?php
class whatsappController {
    public function index() {
        $pageTitle = 'WhatsApp';
        $activeMenu = 'whatsapp';
        $status = Whatsapp::getStatus();
        include VIEW_PATH . '/Whatsapp/index.php';
    }
}
