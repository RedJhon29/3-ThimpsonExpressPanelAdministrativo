<?php
/*====================ENCABEZADO====================
CONFIGURACIÓN: carga de variables de entorno desde .env
ARCHIVO: Configuracion/entorno.php
==================================================*/

/*=====================DETALLES=====================
QUÉ HACE: lee .env de la raíz y expone cada CLAVE=VALOR
    con getenv() y $_ENV.
VINCULADO A: lo incluye Configuracion/conexion.php.
SI SE ALTERA: sin esta carga, la conexión cae al respaldo
    local y el panel ignora la base remota.
LÍMITES: no expande variables ni soporta multilínea.
FECHA: 2026-10-30 | LUGAR: Ocotal, Nueva Segovia
ESCRITO POR: ING. DENIS MANUEL LÓPEZ MOLINA.
==================================================*/

/*================CUERPO DEL CÓDIGO=================*/

/**
 * ====================ENCABEZADO====================
 * FUNCIÓN: cargarEntorno() | ROL: lector de .env
 * ==================================================
 * =====================DETALLES=====================
 * QUÉ HACE: vuelca cada CLAVE=VALOR del archivo en el
 *           entorno del proceso, sin sobrescribir lo
 *           que ya venga definido desde afuera.
 * VINCULADO A: obtenerConexion() consume DATABASE_URL.
 * SI SE ALTERA: sin esta carga la app usa localhost.
 * FECHA: 2026-10-30 | LUGAR: Ocotal, Nueva Segovia
 * ESCRITO POR: ING. DENIS MANUEL LÓPEZ MOLINA.
 * ==================================================
 */
function cargarEntorno(string $rutaArchivo): void
{
    if (!is_readable($rutaArchivo)) {
        return;
    }

    $lineas = file($rutaArchivo, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    if ($lineas === false) {
        return;
    }

    foreach ($lineas as $linea) {
        $linea = trim($linea);

        // Se ignoran comentarios y líneas sin asignación
        if ($linea === '' || str_starts_with($linea, '#') || !str_contains($linea, '=')) {
            continue;
        }

        [$clave, $valor] = explode('=', $linea, 2);
        $clave = trim($clave);
        $valor = trim($valor);

        // Se quitan las comillas que envuelven el valor completo
        $largo = strlen($valor);
        if ($largo >= 2) {
            $primero = $valor[0];
            $ultimo  = $valor[$largo - 1];
            if (($primero === '"' && $ultimo === '"') || ($primero === "'" && $ultimo === "'")) {
                $valor = substr($valor, 1, -1);
            }
        }

        if ($clave === '' || getenv($clave) !== false) {
            continue;
        }

        putenv($clave . '=' . $valor);
        $_ENV[$clave] = $valor;
    }
}

/*===========FIN DEL FRAGMENTO DE CÓDIGO============*/
