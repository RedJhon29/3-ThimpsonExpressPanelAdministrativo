<?php
class adminUserController {
    public function index() {
        $pageTitle = 'Usuarios Admin';
        $activeMenu = 'admin-users';
        $users = AdminUser::all();
        include VIEW_PATH . '/AdminUsuarios/index.php';
    }
}
