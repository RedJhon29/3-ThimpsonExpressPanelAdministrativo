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
    <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/alertify.min.css" rel="stylesheet">
    <link href="<?php echo BASE_URL; ?>/Publico/Recursos/css/admin.css" rel="stylesheet">
</head>
<body class="admin-body">

<!-- Splash Screen -->
<div id="splashScreen" class="splash-screen">
    <div class="splash-content">
        <div style="display:flex;align-items:center;gap:12px;justify-content:center;margin-bottom:16px;">
            <div class="sidebar-logo" style="width:48px;height:48px;font-size:22px;">T</div>
            <div>
                <div style="font-size:20px;font-weight:700;color:#fff;">Thimpson Express</div>
                <div style="font-family:var(--font-mono);font-size:10px;text-transform:uppercase;letter-spacing:0.1em;color:var(--muted);">Panel Administrativo</div>
            </div>
        </div>
        <div class="spinner-border" style="color:var(--primary);width:2rem;height:2rem;" role="status"></div>
    </div>
</div>
