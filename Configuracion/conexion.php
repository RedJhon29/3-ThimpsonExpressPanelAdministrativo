<?php
/*====================ENCABEZADO====================
CONFIGURACIÓN: conexión a base de datos PostgreSQL
ARCHIVO: Configuracion/conexion.php
==================================================*/

/*=====================DETALLES=====================
QUÉ HACE: entrega una conexión PDO única y reutilizable a la base
    thimpsonexpress, con errores en excepción.
VINCULADO A: aún sin consumidores; lo incluirán los Modelos con
    require_once y llamarán obtenerConexion().
SI SE ALTERA: cambiar credenciales o DSN deja el panel sin datos.
LÍMITES: credenciales de desarrollo local escritas en el archivo.
FECHA Y HORA: 2026-10-02 15:17
ESCRITO POR: ING. DENIS MANUEL LÓPEZ MOLINA.
==================================================*/

/*================CUERPO DEL CÓDIGO=================*/

define('SERVIDOR_BASE_DATOS', '127.0.0.1');
define('PUERTO_BASE_DATOS', 5432);
define('NOMBRE_BASE_DATOS', 'thimpsonexpress');
define('USUARIO_BASE_DATOS', 'postgres');
define('CLAVE_BASE_DATOS', 'postgres');

/**
 * ====================ENCABEZADO====================
 * FUNCIÓN: obtenerConexion() | ROL: helper de acceso a datos
 * ==================================================
 * =====================DETALLES=====================
 * QUÉ HACE: abre la conexión en el primer llamado y reutiliza la
 *           misma instancia mientras vive la petición PHP.
 * VINCULADO A: exige la extensión pdo_pgsql y las constantes de
 *           este archivo; sin ellas lanza PDOException.
 * SI SE ALTERA: si deja de devolver PDO o de reutilizarla, cada
 *           consumidor tendría que abrir su propia conexión.
 * FECHA Y HORA: 2026-10-02 15:17
 * ESCRITO POR: ING. DENIS MANUEL LÓPEZ MOLINA.
 * ==================================================
 */
function obtenerConexion(): PDO
{
    // static evita reconectar en cada consulta dentro de la misma petición
    static $conexion = null;

    if ($conexion instanceof PDO) {
        return $conexion;
    }

    $conexion = new PDO(
        sprintf(
            'pgsql:host=%s;port=%d;dbname=%s',
            SERVIDOR_BASE_DATOS,
            PUERTO_BASE_DATOS,
            NOMBRE_BASE_DATOS
        ),
        USUARIO_BASE_DATOS,
        CLAVE_BASE_DATOS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );

    return $conexion;
}

/*===========FIN DEL FRAGMENTO DE CÓDIGO============*/
