<?php
/*====================ENCABEZADO====================
CONFIGURACIÓN: app.php — bootstrap del panel
ARCHIVO: Configuracion/app.php
==================================================*/

/*=====================DETALLES=====================
QUÉ HACE: define rutas constantes, carga la conexión a la base y
    registra el autoload de Clase.php para Modelos y Controladores.
VINCULADO A: lo incluye index.php en cada petición; depende de
    Configuracion/conexion.php y del esquema de archivos del MVC.
SI SE ALTERA: rompe el arranque del panel si cambian BASE_URL,
    VIEW_PATH o el autoload.
FECHA: 2026-10-02 | LUGAR: Ocotal, Nueva Segovia
ESCRITO POR: ING. DENIS MANUEL LÓPEZ MOLINA.
==================================================*/

/*================CUERPO DEL CÓDIGO=================*/

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

// Define obtenerConexion(): no abre conexión hasta que un modelo la llama
require_once __DIR__ . '/conexion.php';

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

/*===========FIN DEL FRAGMENTO DE CÓDIGO============*/