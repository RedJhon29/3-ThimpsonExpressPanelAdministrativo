<?php
class usuarioAdminController {
    public function index() {
        $pageTitle = 'Usuarios Admin';
        $activeMenu = 'admin-users';
        $users = loginModel::all();
        include VIEW_PATH . '/AdminUsuarios/index.php';
    }
}
