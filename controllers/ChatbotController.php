<?php
class ChatbotController {
    public function index() {
        $pageTitle = 'Chatbot';
        $activeMenu = 'chatbot';
        $intents = Chatbot::getIntents();
        include VIEW_PATH . '/chatbot/index.php';
    }
}
