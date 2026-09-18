<?php
/**
 * Configuración general — Thimpson Express Panel Administrativo
 */

// Directorio base
define('BASE_PATH', dirname(__DIR__));
define('VIEW_PATH', BASE_PATH . '/Vistas');
define('CONTROLLER_PATH', BASE_PATH . '/Controladores');
define('MODEL_PATH', BASE_PATH . '/Modelos');

// URL base
define('BASE_URL', '/3-ThimpsonExpressPanelAdministrativo');

// Datos de la empresa
define('APP_NAME', 'Thimpson Express');

// Moneda
define('CURRENCY_SYMBOL', 'C$');

// Autoloader — busca por nombre de clase
spl_autoload_register(function ($class) {
    $modelFile = MODEL_PATH . '/' . $class . '.php';
    $controllerFile = CONTROLLER_PATH . '/' . $class . '.php';

    if (file_exists($modelFile)) {
        require_once $modelFile;
    } elseif (file_exists($controllerFile)) {
        require_once $controllerFile;
    }
});
