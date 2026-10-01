<?php
class loginController {
    public function index() {
        // Si ya está logueado, redirigir al dashboard
        if (isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/dashboard');
            exit;
        }

        $pageTitle = 'Iniciar Sesión';
        $activeMenu = '';
        $error = $_SESSION['login_error'] ?? null;
        unset($_SESSION['login_error']);

        include VIEW_PATH . '/Auth/login.php';
    }

    public function authenticate() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            $_SESSION['login_error'] = 'Email y contraseña son requeridos';
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        if (UsuarioAdmin::verifyPassword($email, $password)) {
            $user = UsuarioAdmin::findByEmail($email);

            if ($user['status'] !== 'active') {
                $_SESSION['login_error'] = 'Cuenta desactivada. Contacte al administrador.';
                header('Location: ' . BASE_URL . '/login');
                exit;
            }

            // Regenerar ID de sesión por seguridad
            session_regenerate_id(true);

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];

            UsuarioAdmin::updateLastLogin($user['id']);

            header('Location: ' . BASE_URL . '/dashboard');
            exit;
        } else {
            $_SESSION['login_error'] = 'Credenciales inválidas';
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
    }

    public function logout() {
        // Destruir sesión completamente
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params['path'], $params['domain'],
                $params['secure'], $params['httponly']
            );
        }
        session_destroy();

        header('Location: ' . BASE_URL . '/login');
        exit;
    }
}