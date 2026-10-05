<!--====================ENCABEZADO====================
PLANTILLA: encabezadoAdmin — apertura del layout del panel
ARCHIVO: Vistas/Plantillas/encabezadoAdmin.php
==================================================-->

<!--=====================DETALLES=====================
QUÉ HACE: abre el documento, declara el head con las librerías
    externas y CSS del panel, el splash screen y el body.
VINCULADO A: lo incluye el controlador con $pageTitle y
    $activeMenu ya cargados, antes de barraLateralAdmin.php.
SI SE ALTERA: si cambia el orden o se pierden esas variables,
    se rompen el título del tab y el menú activo del sidebar.
FECHA: 2026-10-02 | LUGAR: Ocotal, Nueva Segovia
ESCRITO POR: ING. DENIS MANUEL LÓPEZ MOLINA.
==================================================-->

<!--================CUERPO DEL CÓDIGO=================-->

<!DOCTYPE html>
<html lang="es-NI">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Admin'; ?> — <?php echo APP_NAME; ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=Inter:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link href="<?php echo BASE_URL; ?>/Publico/Recursos/datatables/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="<?php echo BASE_URL; ?>/Publico/Recursos/sweetalert/sweetalert2.min.css" rel="stylesheet">
    <link href="<?php echo BASE_URL; ?>/Publico/Recursos/alertify/alertify.min.css" rel="stylesheet">
    <link href="<?php echo BASE_URL; ?>/Publico/Recursos/css/admin.css" rel="stylesheet">
</head>
<body class="admin-body">
