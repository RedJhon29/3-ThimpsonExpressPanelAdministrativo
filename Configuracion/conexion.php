<?php
/*====================ENCABEZADO====================
CONFIGURACIÓN: conexión a base de datos PostgreSQL
ARCHIVO: Configuracion/conexion.php
==================================================*/

/*=====================DETALLES=====================
QUÉ HACE: entrega una conexión PDO única y reutilizable
    a la base indicada en DATABASE_URL.
VINCULADO A: Configuracion/entorno.php y los Modelos.
SI SE ALTERA: cambiar el DSN deja el panel sin datos.
LÍMITES: sin .env cae a la base local de desarrollo.
FECHA: 2026-10-30 | LUGAR: Ocotal, Nueva Segovia
ESCRITO POR: ING. DENIS MANUEL LÓPEZ MOLINA.
==================================================*/

/*================CUERPO DEL CÓDIGO=================*/

require_once __DIR__ . '/entorno.php';
cargarEntorno(dirname(__DIR__) . '/.env');

/**
 * ====================ENCABEZADO====================
 * FUNCIÓN: obtenerConexion() | ROL: helper de acceso a datos
 * ==================================================
 * =====================DETALLES=====================
 * QUÉ HACE: abre la conexión en el primer llamado y la
 *           reutiliza mientras vive la petición.
 * VINCULADO A: exige pdo_pgsql y DATABASE_URL.
 * SI SE ALTERA: si no devuelve PDO, cada consumidor
 *           tendría que abrir la suya.
 * FECHA: 2026-10-30 | LUGAR: Ocotal, Nueva Segovia
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

    /* ---------- CONEXIÓN REMOTA: NEON ---------- */
    // La cadena completa y la clave viven en .env, que no se versiona.
    $url = getenv('DATABASE_URL');

    /* ---------- CONEXIÓN LOCAL: RESPALDO DE DESARROLLO ---------- */
    // Solo entra si falta el .env: el PostgreSQL local escucha en loopback,
    // sin TLS y sin contraseña, para poder trabajar sin internet.
    if ($url === false || trim($url) === '') {
        $url = 'postgresql://postgres@127.0.0.1:5432/thimpsonexpress?sslmode=disable';
    }
    $url = trim($url);

    $partes = parse_url($url);
    if ($partes === false || !isset($partes['host'])) {
        throw new RuntimeException('DATABASE_URL inválida: no se pudo leer el host.');
    }

    parse_str($partes['query'] ?? '', $consulta);
    // Neon exige TLS; la URL local lo desactiva con sslmode=disable.
    $modoSeguridad = $consulta['sslmode'] ?? 'require';

    $conexion = new PDO(
        sprintf(
            'pgsql:host=%s;port=%d;dbname=%s;sslmode=%s;connect_timeout=10',
            $partes['host'],
            $partes['port'] ?? 5432,
            ltrim($partes['path'] ?? '', '/'),
            $modoSeguridad
        ),
        rawurldecode($partes['user'] ?? ''),
        rawurldecode($partes['pass'] ?? ''),
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );

    return $conexion;
}

/*===========FIN DEL FRAGMENTO DE CÓDIGO============*/
