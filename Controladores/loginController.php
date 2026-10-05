<?php
/*====================ENCABEZADO====================
CONTROLADOR: loginController — autenticación del panel
ARCHIVO: Controladores/loginController.php
==================================================*/

/*=====================DETALLES=====================
QUÉ HACE: gestiona mostrar el login, validar credenciales contra
    PostgreSQL y cerrar la sesión del panel.
VINCULADO A: lo invoca index.php en las rutas /login,
    /login/authenticate y /logout; llama a Modelos/loginModel.php.
SI SE ALTERA: cambia la entrada al panel; revisar el guard de
    sesión de index.php y la lista de rutas públicas.
FECHA: 2026-10-02 | LUGAR: Ocotal, Nueva Segovia
ESCRITO POR: ING. DENIS MANUEL LÓPEZ MOLINA.
==================================================*/

/*================CUERPO DEL CÓDIGO=================*/

class loginController
{
    /**
     * ====================ENCABEZADO====================
     * FUNCIÓN: index() | ROL: controlador
     * ==================================================
     * =====================DETALLES=====================
     * QUÉ HACE: muestra el formulario de login o redirige si ya hay sesión.
     * VINCULADO A: incluye Vistas/Auth/login.php; consume login_error y
     *           login_usuario de la sesión.
     * SI SE ALTERA: cambiar esas claves de sesión deja el error de login
     *           sin mostrar en la vista.
     * FECHA: 2026-10-02 | LUGAR: Ocotal, Nueva Segovia
     * ESCRITO POR: ING. DENIS MANUEL LÓPEZ MOLINA.
     * ==================================================
     */
    public function index(): void
    {
        if (isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/dashboard');
            exit;
        }

        $pageTitle = 'Iniciar Sesión';
        $activeMenu = '';
        $error = $_SESSION['login_error'] ?? null;
        $usuario = $_SESSION['login_usuario'] ?? '';
        unset($_SESSION['login_error'], $_SESSION['login_usuario']);

        include VIEW_PATH . '/Auth/login.php';
    }

    /**
     * ====================ENCABEZADO====================
     * FUNCIÓN: authenticate() | ROL: controlador
     * ==================================================
     * =====================DETALLES=====================
     * QUÉ HACE: valida nick y clave contra PostgreSQL y abre sesión.
     * VINCULADO A: llama a loginModel::buscarPorNickName(),
     *           verificarClave() y registrarUltimoLogin().
     * SI SE ALTERA: cambia la entrada al panel; revisar el guard de
     *           sesión y las rutas públicas en index.php.
     * FECHA: 2026-10-02 | LUGAR: Ocotal, Nueva Segovia
     * ESCRITO POR: ING. DENIS MANUEL LÓPEZ MOLINA.
     * ==================================================
     */
    public function authenticate(): void
    {
        // TODO(2026-Q4): limitar intentos fallidos por IP y sesión para frenar fuerza bruta
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        $nick = trim($_POST['usuario'] ?? '');
        $clave = $_POST['password'] ?? '';

        if ($nick === '' || $clave === '') {
            $_SESSION['login_error'] = 'Usuario y contraseña son requeridos';
            $_SESSION['login_usuario'] = $nick;
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        $usuario = loginModel::buscarPorNickName($nick);

        if ($usuario === null || !loginModel::verificarClave($clave, $usuario['clave_usuario'])) {
            $_SESSION['login_error'] = 'Credenciales inválidas';
            $_SESSION['login_usuario'] = $nick;
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        // El estado solo se consulta con la clave ya validada: no revela si el nick existe
        if ($usuario['estado_usuario'] !== 'activo') {
            $_SESSION['login_error'] = 'Cuenta desactivada. Contacte al administrador.';
            $_SESSION['login_usuario'] = $nick;
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        // Nueva identidad de sesión: evita fijación de sesión
        session_regenerate_id(true);

        $_SESSION['user_id'] = $usuario['id_usuario'];
        $_SESSION['user_name'] = $usuario['descripcion_usuario'];
        $_SESSION['user_nick'] = $usuario['nick_name'];
        $_SESSION['user_role'] = $usuario['tipo_usuario'];

        loginModel::registrarUltimoLogin($usuario['id_usuario']);

        header('Location: ' . BASE_URL . '/dashboard');
        exit;
    }

    /**
     * ====================ENCABEZADO====================
     * FUNCIÓN: logout() | ROL: controlador
     * ==================================================
     * =====================DETALLES=====================
     * QUÉ HACE: vacía la sesión, borra la cookie y la destruye. SIN VÍNCULOS EXTERNOS.
     * FECHA: 2026-10-02 | LUGAR: Ocotal, Nueva Segovia
     * ESCRITO POR: ING. DENIS MANUEL LÓPEZ MOLINA.
     * ==================================================
     */
    public function logout(): void
    {
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

/*===========FIN DEL FRAGMENTO DE CÓDIGO============*/