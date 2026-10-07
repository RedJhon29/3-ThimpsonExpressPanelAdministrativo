<!--====================ENCABEZADO====================
VISTA: Panel/index — dashboard con el conteo de usuarios
ARCHIVO: Vistas/Panel/index.php
==================================================-->

<!--=====================DETALLES=====================
QUÉ HACE: pinta las tres tarjetas de usuarios del dashboard
    (total, activos e inactivos) con el partial tarjetaEstadistica.php.
VINCULADO A: lo incluye Controladores/panelController.php con
    $stats ya cargado; las cifras salen de Usuario::contarPorEstado().
SI SE ALTERA: cada clave de $stats debe existir aquí; si se
    renombra en el modelo, la tarjeta sale vacía.
LÍMITES: la tabla de pedidos recientes y el gráfico de distribución
    se quitaron porque mostraban datos inventados; se reconstruyen
    con consultas reales.
FECHA: 2026-10-02 | LUGAR: Ocotal, Nueva Segovia
ESCRITO POR: ING. DENIS MANUEL LÓPEZ MOLINA.
==================================================-->

<!--================CUERPO DEL CÓDIGO=================-->

<?php include VIEW_PATH . '/Plantillas/encabezadoAdmin.php'; ?>
<?php include VIEW_PATH . '/Plantillas/barraLateralAdmin.php'; ?>

<!-- Dashboard Content -->
<div class="row g-3 mb-4">
    <?php
    /*
     * Cada tarjeta se pinta con el partial tarjetaEstadistica.php para no
     * repetir el mismo markup. Las tres salen de Usuario::contarPorEstado(),
     * asi que reflejan el conteo real de la base en cada carga del panel.
     * Son col-md-4 porque son tres: llenan la fila entera sin huecos.
     */
    $tarjetas = [
        [
            'icono' => 'bi-people',
            'color' => 'var(--primary)',
            'valor' => $stats['usuarios_total'],
            'etiqueta' => 'Usuarios',
        ],
        [
            'icono' => 'bi-person-check',
            'color' => 'var(--success)',
            'valor' => $stats['usuarios_activos'],
            'etiqueta' => 'Usuarios Activos',
        ],
        [
            'icono' => 'bi-person-slash',
            'color' => 'var(--muted)',
            'valor' => $stats['usuarios_inactivos'],
            'etiqueta' => 'Usuarios Inactivos',
        ],
    ];

    foreach ($tarjetas as $tarjeta) {
        ?>
        <div class="col-md-4">
            <?php
            $icono = $tarjeta['icono'];
            $color = $tarjeta['color'];
            $valor = $tarjeta['valor'];
            $etiqueta = $tarjeta['etiqueta'];
            include VIEW_PATH . '/Panel/tarjetaEstadistica.php';
            ?>
        </div>
        <?php
    }
    ?>
</div>

<?php include VIEW_PATH . '/Plantillas/pieAdmin.php'; ?>
