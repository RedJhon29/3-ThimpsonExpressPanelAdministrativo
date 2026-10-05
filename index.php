<?php
/*====================ENCABEZADO====================
FRONT CONTROLLER: index.php — router y guard de sesión
ARCHIVO: index.php
==================================================*/

/*=====================DETALLES=====================
QUÉ HACE: enruta la URI al controlador indicado en $routes y
    bloquea con 302 las rutas protegidas sin sesión.
VINCULADO A: exige .htaccess (rewrite) y Configuracion/app.php
    (constantes y autoload); llama a los Controladores/*.
SI SE ALTERA: cambia toda la navegación; revisar $routes, las
    rutas públicas y el prefijo del proyecto.
FECHA: 2026-10-02 | LUGAR: Ocotal, Nueva Segovia
ESCRITO POR: ING. DENIS MANUEL LÓPEZ MOLINA.
==================================================*/

/*================CUERPO DEL CÓDIGO=================*/

// Iniciar sesión al principio
session_start();

require_once __DIR__ . '/Configuracion/app.php';

// URI actual
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = rtrim($uri, '/') ?: '/';

// Remover prefijo del proyecto si existe
$projectPrefix = '/3-ThimpsonExpressPanelAdministrativo';
if (strpos($uri, $projectPrefix) === 0) {
    $uri = substr($uri, strlen($projectPrefix)) ?: '/';
}

// Rutas públicas (no requieren autenticación)
$publicRoutes = [
    '/login',
    '/login/authenticate',
    '/logout',
];

// Router: mapear rutas → Controlador@método
$routes = [
    // Auth
    '/login'                 => ['loginController', 'index'],
    '/login/authenticate'    => ['loginController', 'authenticate'],
    '/logout'                => ['loginController', 'logout'],

    // Dashboard
    '/'                      => ['panelController', 'index'],
    '/dashboard'             => ['panelController', 'index'],
];

// Verificar autenticación para rutas protegidas
$isPublicRoute = in_array($uri, $publicRoutes);
if (!$isPublicRoute && !isset($_SESSION['user_id'])) {
    // Guardar la URL original para redirigir después del login
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    header('Location: ' . BASE_URL . '/login');
    exit;
}

// Buscar coincidencia exacta
$controller = null;
$action = null;
$params = [];

if (isset($routes[$uri])) {
    $controller = $routes[$uri][0];
    $action = $routes[$uri][1];
} else {
    // Buscar rutas con parámetros
    foreach ($routes as $route => $handler) {
        $pattern = preg_replace('/\{(\w+)\}/', '(?P<$1>[^/]+)', $route);
        $pattern = '#^' . $pattern . '$#';

        if (preg_match($pattern, $uri, $matches)) {
            $controller = $handler[0];
            $action = $handler[1];
            foreach ($matches as $key => $value) {
                if (is_string($key)) {
                    $params[$key] = $value;
                }
            }
            break;
        }
    }
}

// Si no se encontró ruta, mostrar 404
if ($controller === null) {
    http_response_code(404);
    $pageTitle = '404 - No encontrado';
    $activeMenu = '';
    include VIEW_PATH . '/Plantillas/encabezadoAdmin.php';
    include VIEW_PATH . '/Errores/404.php';
    include VIEW_PATH . '/Plantillas/pieAdmin.php';
    exit;
}

// Cargar controlador y ejecutar acción
$controllerFile = CONTROLLER_PATH . '/' . $controller . '.php';

if (!file_exists($controllerFile)) {
    http_response_code(500);
    echo "Error: Controlador '$controller' no encontrado.";
    exit;
}

require_once $controllerFile;

if (!class_exists($controller)) {
    http_response_code(500);
    echo "Error: Clase '$controller' no definida en '$controllerFile'.";
    exit;
}

$controllerInstance = new $controller();

if (!method_exists($controllerInstance, $action)) {
    http_response_code(500);
    echo "Error: Método '$action' no existe en '$controller'.";
    exit;
}

call_user_func_array([$controllerInstance, $action], $params);

/*===========FIN DEL FRAGMENTO DE CÓDIGO============*/