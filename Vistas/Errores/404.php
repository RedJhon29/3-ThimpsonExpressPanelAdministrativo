<!--====================ENCABEZADO====================
VISTA: error 404 — página no encontrada
ARCHIVO: Vistas/Errores/404.php
==================================================-->

<!--=====================DETALLES=====================
QUÉ HACE: muestra el error 404 con un enlace de vuelta al dashboard.
VINCULADO A: lo incluye index.php en la ruta 404, entre
    Vistas/Plantillas/encabezadoAdmin.php y pieAdmin.php.
SI SE ALTERA: revisar que BASE_URL siga disponible (la define
    Configuracion/app.php) antes de tocar el enlace.
FECHA: 2026-10-02 | LUGAR: Ocotal, Nueva Segovia
ESCRITO POR: ING. DENIS MANUEL LÓPEZ MOLINA.
==================================================-->

<!--================CUERPO DEL CÓDIGO=================-->

<!-- Se usa div y no main porque barraLateralAdmin.php ya abrió el
     <main class="admin-content"> que envuelve a toda vista del panel. -->
<div class="container py-5 text-center">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <h1 class="display-1 fw-bold" style="color: var(--primary);">404</h1>
            <h2 class="mb-3">Página no encontrada</h2>
            <p class="text-muted mb-4">La sección que buscás no existe en el panel de administración.</p>
            <a href="<?php echo BASE_URL; ?>/dashboard" class="btn btn-lg btn-primary-custom">
                Volver al Dashboard
            </a>
        </div>
    </div>
</div>
