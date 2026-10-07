<?php
/*====================ENCABEZADO====================
PARTIAL: tarjetaEstadistica.php — tarjeta de indicador del dashboard
ARCHIVO: Vistas/Panel/tarjetaEstadistica.php
==================================================*/

/*=====================DETALLES=====================
QUÉ HACE: pinta una tarjeta KPI con icono, valor y etiqueta.
VINCULADO A: lo incluyen Vistas/Panel/index.php una vez por
    indicador; espera $icono, $color, $valor y $etiqueta.
SI SE ALTERA: si cambia el markup de .stat-card hay que revisar
    las claves de admin.css que lo visten; $color tiñe tanto el
    icono como el valor.
FECHA: 2026-10-07 | LUGAR: Ocotal, Nueva Segovia
ESCRITO POR: ING. DENIS MANUEL LÓPEZ MOLINA.
==================================================*/

/*================CUERPO DEL CÓDIGO=================*/
?>
<div class="stat-card">
    <div class="stat-icon">
        <i class="bi <?php echo htmlspecialchars($icono); ?>" style="color:<?php echo htmlspecialchars($color); ?>;"></i>
    </div>
    <div>
        <div class="stat-value" style="color:<?php echo htmlspecialchars($color); ?>;">
            <?php echo htmlspecialchars((string) $valor); ?>
        </div>
        <div class="stat-label"><?php echo htmlspecialchars($etiqueta); ?></div>
    </div>
</div>