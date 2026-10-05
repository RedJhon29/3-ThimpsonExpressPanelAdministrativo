<!--====================ENCABEZADO====================
PLANTILLA: encabezadoAdmin — apertura del layout del panel
ARCHIVO: Vistas/Plantillas/encabezadoAdmin.php
==================================================-->

<!--=====================DETALLES=====================
QUÉ HACE: abre el documento con el gestor de carga y los
    recursos del panel (CDN con fallback a local).
VINCULADO A: lo incluye el controlador con $pageTitle y
    $activeMenu cargados, antes de barraLateralAdmin.php.
SI SE ALTERA: si cambia el orden o los data-pasos se
    pierde el menú activo y el fallback offline.
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
    <!-- Gestor de carga con fallback: debe ir antes de los recursos gestionados -->
    <script src="<?php echo BASE_URL; ?>/Publico/Recursos/js/gestorPlugins.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=Inter:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap"
          rel="stylesheet"
          data-nombre="Tipografías Google Fonts"
          data-pasos="cdn,local,cdn"
          data-cdn="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=Inter:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap"
          data-local="<?php echo BASE_URL; ?>/Publico/Recursos/fonts/google-fonts.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
          data-nombre="Bootstrap CSS"
          data-pasos="cdn,local,cdn"
          data-cdn="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          data-local="<?php echo BASE_URL; ?>/Publico/Recursos/bootstrap/css/bootstrap.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet"
          data-nombre="Bootstrap Icons CSS"
          data-pasos="cdn,local,cdn"
          data-cdn="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
          data-local="<?php echo BASE_URL; ?>/Publico/Recursos/bootstrap-icons/bootstrap-icons.min.css">
    <link href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" rel="stylesheet"
          data-nombre="Leaflet CSS"
          data-pasos="cdn,local,cdn"
          data-cdn="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
          data-local="<?php echo BASE_URL; ?>/Publico/Recursos/leaflet/leaflet.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet"
          data-nombre="Select2 CSS"
          data-pasos="cdn,local,cdn"
          data-cdn="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css"
          data-local="<?php echo BASE_URL; ?>/Publico/Recursos/select2/css/select2.min.css">
    <link href="<?php echo BASE_URL; ?>/Publico/Recursos/datatables/dataTables.bootstrap5.min.css" rel="stylesheet"
          data-nombre="DataTables CSS"
          data-pasos="local,cdn"
          data-cdn="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css"
          data-local="<?php echo BASE_URL; ?>/Publico/Recursos/datatables/dataTables.bootstrap5.min.css">
    <link href="<?php echo BASE_URL; ?>/Publico/Recursos/sweetalert/sweetalert2.min.css" rel="stylesheet"
          data-nombre="SweetAlert2 CSS"
          data-pasos="local,cdn"
          data-cdn="https://cdn.jsdelivr.net/npm/sweetalert2@11/sweetalert2.min.css"
          data-local="<?php echo BASE_URL; ?>/Publico/Recursos/sweetalert/sweetalert2.min.css">
    <link href="<?php echo BASE_URL; ?>/Publico/Recursos/alertify/alertify.min.css" rel="stylesheet"
          data-nombre="Alertify CSS"
          data-pasos="local,cdn"
          data-cdn="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/alertify.min.css"
          data-local="<?php echo BASE_URL; ?>/Publico/Recursos/alertify/alertify.min.css">
    <!-- admin.css sin data-pasos: sin CDN alternativo y un error espurio
         durante la navegación generaba una alerta de fallo falsa -->
    <link href="<?php echo BASE_URL; ?>/Publico/Recursos/css/admin.css" rel="stylesheet">
</head>
<body class="admin-body">
