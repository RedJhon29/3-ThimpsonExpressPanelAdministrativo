<?php
class WhatsappController {
    public function index() {
        $pageTitle = 'WhatsApp';
        $activeMenu = 'whatsapp';
        $status = Whatsapp::getStatus();
        include VIEW_PATH . '/whatsapp/index.php';
    }
}
