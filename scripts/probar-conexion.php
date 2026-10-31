<?php
/*====================ENCABEZADO====================
HERRAMIENTA: prueba de conexión a la base configurada
ARCHIVO: scripts/probar-conexion.php
==================================================*/

/*=====================DETALLES=====================
QUÉ HACE: muestra host, base y usuario de DATABASE_URL
    y ejecuta una consulta real contra usuarios.
VINCULADO A: Configuracion/conexion.php y el archivo .env.
SI SE ALTERA: deja de servir como verificación previa.
LÍMITES: nunca imprime la clave ni el contenido de la URL.
FECHA: 2026-10-30 | LUGAR: Ocotal, Nueva Segovia
ESCRITO POR: ING. DENIS MANUEL LÓPEZ MOLINA.
==================================================*/

/*================CUERPO DEL CÓDIGO=================*/

require_once __DIR__ . '/../Configuracion/conexion.php';

$url = getenv('DATABASE_URL');
$origen = 'archivo .env';

if ($url === false || trim($url) === '') {
    $url = 'postgresql://postgres@127.0.0.1:5432/thimpsonexpress?sslmode=disable';
    $origen = 'respaldo local (no hay DATABASE_URL)';
}

$partes = parse_url(trim($url));
$servidor = $partes['host'] ?? 'desconocido';
$base     = ltrim($partes['path'] ?? '', '/');
$usuario  = $partes['user'] ?? 'desconocido';

echo "Origen:   {$origen}\n";
echo "Servidor: {$servidor}\n";
echo "Base:     {$base}\n";
echo "Usuario:  {$usuario}\n";

try {
    $conexion = obtenerConexion();
    $version  = $conexion->query('SELECT version()')->fetchColumn();
    $filas    = $conexion->query('SELECT count(*) FROM usuarios')->fetchColumn();

    echo "Estado:   CONECTADO\n";
    echo "Versión:  {$version}\n";
    echo "Filas:    {$filas}\n";
} catch (Throwable $error) {
    echo "Estado:   ERROR\n";
    echo "Detalle:  {$error->getMessage()}\n";
    exit(1);
}

/*===========FIN DEL FRAGMENTO DE CÓDIGO============*/
