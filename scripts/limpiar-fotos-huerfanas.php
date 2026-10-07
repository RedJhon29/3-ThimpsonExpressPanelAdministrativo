<?php
/*====================ENCABEZADO====================
SCRIPT: limpieza de carpetas de fotos sin usuario
ARCHIVO: scripts/limpiar-fotos-huerfanas.php
==================================================*/

/*=====================DETALLES=====================
QUÉ HACE: borra las carpetas de Publico/Recursos/uploads/usuarios/
    cuyo id de usuario ya no existe en la base de datos.
VINCULADO A: usa Modelos/almacenFotosModel::limpiarHuerfanos() y
    Configuracion/app.php; pensado para bajas hechas por SQL
    fuera de la aplicación (panel psql, restauraciones).
SI SE ALTERA: solo se ejecuta por CLI; si se expone en web,
    cualquiera podría borrar archivos del servidor.
FECHA: 2026-10-05 | LUGAR: Ocotal, Nueva Segovia
ESCRITO POR: ING. DENIS MANUEL LÓPEZ MOLINA.
==================================================*/

/*================CUERPO DEL CÓDIGO=================*/

// El script borra archivos: solo por línea de comandos, nunca por web
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Este script solo se ejecuta por línea de comandos.');
}

require_once __DIR__ . '/../Configuracion/app.php';

/** @var array<int,int> $eliminados id de usuario => archivos borrados */
$eliminados = almacenFotosModel::limpiarHuerfanos();

if ($eliminados === []) {
    echo "No hay carpetas huerfanas: todo esta en orden.\n";
    exit(0);
}

$totalCarpetas = count($eliminados);
$totalArchivos = array_sum($eliminados);

echo "Se eliminaron $totalCarpetas carpeta(s) huerfana(s) con $totalArchivos archivo(s):\n";

foreach ($eliminados as $idUsuario => $archivos) {
    echo "  - usuario $idUsuario: $archivos archivo(s)\n";
}

echo "\nEspacio liberado del directorio de fotos.\n";