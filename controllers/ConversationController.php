<?php
class ConversationController {
    public function index() {
        $pageTitle = 'Conversaciones';
        $activeMenu = 'conversations';
        $conversations = Conversation::all();
        include VIEW_PATH . '/conversations/index.php';
    }
}
