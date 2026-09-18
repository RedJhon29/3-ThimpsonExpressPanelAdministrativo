<?php
class chatbotController {
    public function index() {
        $pageTitle = 'Chatbot';
        $activeMenu = 'chatbot';
        $intents = Chatbot::getIntents();
        include VIEW_PATH . '/Chatbot/index.php';
    }
}
