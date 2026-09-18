<?php
class asistenteVirtualController {
    public function index() {
        $pageTitle = 'Chatbot';
        $activeMenu = 'chatbot';
        $intents = AsistenteVirtual::getIntents();
        include VIEW_PATH . '/Chatbot/index.php';
    }
}
