<?php
class AdminUserController {
    public function index() {
        $pageTitle = 'Usuarios Admin';
        $activeMenu = 'admin-users';
        $users = AdminUser::all();
        include VIEW_PATH . '/admin-users/index.php';
    }
}
