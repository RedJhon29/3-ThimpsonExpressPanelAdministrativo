<?php
/**
 * Front Controller — Thimpson Express Panel Administrativo
 * Todas las peticiones pasan por aquí vía .htaccess
 */

require_once __DIR__ . '/Configuracion/app.php';

// URI actual
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = rtrim($uri, '/') ?: '/';

// Remover prefijo del proyecto si existe
$projectPrefix = '/3-ThimpsonExpressPanelAdministrativo';
if (strpos($uri, $projectPrefix) === 0) {
    $uri = substr($uri, strlen($projectPrefix)) ?: '/';
}

// Router: mapear rutas → Controlador@método
$routes = [
    // Dashboard
    '/'                         => ['dashboardController', 'index'],
    '/dashboard'                => ['dashboardController', 'index'],

    // Pedidos
    '/orders'                   => ['orderController', 'index'],
    '/orders/detail'            => ['orderController', 'detail'],

    // Riders
    '/riders'                   => ['riderController', 'index'],
    '/riders/detail'            => ['riderController', 'detail'],
    '/riders/tracking'          => ['riderController', 'tracking'],

    // Servicios
    '/services'                 => ['serviceController', 'index'],

    // Marketplace
    '/marketplace'              => ['marketplaceController', 'index'],

    // CMS
    '/cms'                      => ['cmsController', 'index'],
    '/landing-editor'           => ['landingEditorController', 'index'],

    // Chatbot
    '/chatbot'                  => ['chatbotController', 'index'],

    // Clientes
    '/clients'                  => ['clientController', 'index'],

    // Finanzas
    '/finance'                  => ['financeController', 'index'],

    // Conversaciones
    '/conversations'            => ['conversationController', 'index'],

    // Suscriptores
    '/subscribers'              => ['subscriberController', 'index'],

    // Reviews
    '/reviews'                  => ['reviewController', 'index'],

    // Ratings
    '/ratings'                  => ['ratingController', 'index'],

    // Promociones
    '/promotions'               => ['promotionController', 'index'],

    // Zonas
    '/zones'                    => ['zoneController', 'index'],

    // Notificaciones
    '/notifications'            => ['notificationController', 'index'],

    // Reportes
    '/reports'                  => ['reportController', 'index'],

    // Auditoría
    '/audit'                    => ['auditController', 'index'],

    // Configuración
    '/settings'                 => ['settingsController', 'index'],

    // Usuarios Admin
    '/admin-users'              => ['adminUserController', 'index'],

    // Dispositivos
    '/devices'                  => ['deviceController', 'index'],

    // Soporte
    '/support'                  => ['supportController', 'index'],

    // Suscripciones
    '/subscriptions'            => ['subscriptionController', 'index'],

    // Pricing
    '/pricing'                  => ['pricingController', 'index'],

    // WhatsApp
    '/whatsapp'                 => ['whatsappController', 'index'],
    '/openwa'                   => ['openwaController', 'index'],
];

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
    include VIEW_PATH . '/Plantillas/adminHeader.php';
    include VIEW_PATH . '/Errores/404.php';
    include VIEW_PATH . '/Plantillas/adminFooter.php';
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
