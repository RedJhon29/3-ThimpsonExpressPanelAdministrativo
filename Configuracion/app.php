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

// Define crearTokenCsrf() y verificarTokenCsrf() para los formularios con escritura
require_once __DIR__ . '/seguridad.php';

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

/**
 * ====================ENCABEZADO====================
 * FUNCIÓN: urlAsset() | ROL: helper de plantilla
 * ==================================================
 * =====================DETALLES=====================
 * QUÉ HACE: arma la URL de un recurso local y le agrega ?v= con su
 *     fecha de modificación, para que el navegador no sirva la copia
 *     vieja después de editar el archivo.
 * VINCULADO A: la llaman las plantillas en los <link> y <script> de
 *     admin.css y gestorPlugins.js, los dos archivos que se editan a
 *     mano; los recursos de terceros no la necesitan.
 * SI SE ALTERA: sin el parámetro el problema reaparece en cada cambio
 *     de estilo, y es difícil distinguir caché de un fix que no aplicó.
 * LÍMITES: si el archivo no existe devuelve la URL sin versión, para
 *     que un 404 siga diciendo qué ruta falta y no enmascare el error.
 * FECHA: 2026-10-08 | LUGAR: Ocotal, Nueva Segovia
 * ESCRITO POR: ING. DENIS MANUEL LÓPEZ MOLINA.
 * ==================================================
 */
function urlAsset(string $rutaRelativa): string
{
    $relativa = ltrim($rutaRelativa, '/');
    $url = BASE_URL . '/' . $relativa;
    $archivo = BASE_PATH . '/' . $relativa;

    return is_file($archivo) ? $url . '?v=' . filemtime($archivo) : $url;
}

/*===========FIN DEL FRAGMENTO DE CÓDIGO============*/