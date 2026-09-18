<?php
/**
 * Front Controller — Thimpson Express Admin Panel
 */

$baseDir = dirname(__DIR__);
require_once $baseDir . '/config/app.php';

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = rtrim($uri, '/') ?: '/';

// Remover prefijo del proyecto
$projectPrefix = '/3-ThimpsonExpressPanelAdministrativo/php/public';
if (strpos($uri, $projectPrefix) === 0) {
    $uri = substr($uri, strlen($projectPrefix)) ?: '/';
}

// Router: todas las rutas del admin
$routes = [
    // Dashboard
    '/'                         => ['DashboardController', 'index'],
    '/dashboard'                => ['DashboardController', 'index'],

    // Pedidos
    '/orders'                   => ['OrderController', 'index'],
    '/orders/detail'            => ['OrderController', 'detail'],

    // Riders
    '/riders'                   => ['RiderController', 'index'],
    '/riders/detail'            => ['RiderController', 'detail'],
    '/riders/tracking'          => ['RiderController', 'tracking'],

    // Servicios
    '/services'                 => ['ServiceController', 'index'],

    // Marketplace
    '/marketplace'              => ['MarketplaceController', 'index'],

    // CMS
    '/cms'                      => ['CmsController', 'index'],
    '/landing-editor'           => ['LandingEditorController', 'index'],

    // Chatbot
    '/chatbot'                  => ['ChatbotController', 'index'],

    // Clientes
    '/clients'                  => ['ClientController', 'index'],

    // Finanzas
    '/finance'                  => ['FinanceController', 'index'],

    // Conversaciones
    '/conversations'            => ['ConversationController', 'index'],

    // Suscriptores
    '/subscribers'              => ['SubscriberController', 'index'],

    // Reviews
    '/reviews'                  => ['ReviewController', 'index'],

    // Ratings
    '/ratings'                  => ['RatingController', 'index'],

    // Promociones
    '/promotions'               => ['PromotionController', 'index'],

    // Zonas
    '/zones'                    => ['ZoneController', 'index'],

    // Notificaciones
    '/notifications'            => ['NotificationController', 'index'],

    // Reportes
    '/reports'                  => ['ReportController', 'index'],

    // Auditoría
    '/audit'                    => ['AuditController', 'index'],

    // Configuración
    '/settings'                 => ['SettingsController', 'index'],

    // Usuarios Admin
    '/admin-users'              => ['AdminUserController', 'index'],

    // Dispositivos
    '/devices'                  => ['DeviceController', 'index'],

    // Soporte
    '/support'                  => ['SupportController', 'index'],

    // Suscripciones
    '/subscriptions'            => ['SubscriptionController', 'index'],

    // Pricing
    '/pricing'                  => ['PricingController', 'index'],

    // WhatsApp
    '/whatsapp'                 => ['WhatsappController', 'index'],
    '/openwa'                   => ['OpenwaController', 'index'],
];

// Buscar coincidencia exacta
$controller = null;
$action = null;
$params = [];

if (isset($routes[$uri])) {
    $controller = $routes[$uri][0];
    $action = $routes[$uri][1];
} else {
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

if ($controller === null) {
    http_response_code(404);
    $pageTitle = '404 - No encontrado';
    $activeMenu = '';
    include VIEW_PATH . '/layouts/admin-header.php';
    include VIEW_PATH . '/errors/404.php';
    include VIEW_PATH . '/layouts/admin-footer.php';
    exit;
}

$controllerFile = CONTROLLER_PATH . '/' . $controller . '.php';

if (!file_exists($controllerFile)) {
    http_response_code(500);
    echo "Error: Controlador '$controller' no encontrado.";
    exit;
}

require_once $controllerFile;

if (!class_exists($controller)) {
    http_response_code(500);
    echo "Error: Clase '$controller' no definida.";
    exit;
}

$controllerInstance = new $controller();

if (!method_exists($controllerInstance, $action)) {
    http_response_code(500);
    echo "Error: Método '$action' no existe en '$controller'.";
    exit;
}

call_user_func_array([$controllerInstance, $action], $params);
