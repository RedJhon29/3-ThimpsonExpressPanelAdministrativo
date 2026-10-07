<?php
/*====================ENCABEZADO====================
MODELO: Panel — indicadores del dashboard
ARCHIVO: Modelos/panelModel.php
==================================================*/

/*=====================DETALLES=====================
QUÉ HACE: entrega el conteo real de usuarios por estado
    (total, activos, inactivos) del dashboard.
VINCULADO A: lo llama Controladores/panelController.php
    y los consume Vistas/Panel/index.php en las tarjetas;
    el conteo viene de usuariosModel::contarPorEstado().
SI SE ALTERA: cambian las cifras visibles del dashboard;
    cada clave debe existir en la vista que la pinta.
LÍMITES: solo expone usuarios. Los KPI de pedidos, riders,
    ingresos y tiempo promedio se quitaron porque eran
    números inventados sin tabla que los respaldara.
FECHA: 2026-10-02 | LUGAR: Ocotal, Nueva Segovia
ESCRITO POR: ING. DENIS MANUEL LÓPEZ MOLINA.
==================================================*/

/*================CUERPO DEL CÓDIGO=================*/

class panelModel {
    /**
     * ====================ENCABEZADO====================
     * FUNCIÓN: getStats() | ROL: modelo
     * ==================================================
     * =====================DETALLES=====================
     * QUÉ HACE: entrega los indicadores reales que se pintan en el dashboard.
     * VINCULADO A: lo llama Controladores/panelController.php::index() y cada
     *     clave la lee Vistas/Panel/index.php para una tarjeta de KPI.
     * SI SE ALTERA: quitar o renombrar una clave deja esa tarjeta sin dato;
     *     revisar el total de claves que espera la vista.
     * FECHA: 2026-10-02 | LUGAR: Ocotal, Nueva Segovia
     * ESCRITO POR: ING. DENIS MANUEL LÓPEZ MOLINA.
     * ==================================================
     */
    public static function getStats() {
        // Conteo real de usuarios: alimenta las tarjetas de total,
        // activos e inactivos del dashboard.
        $usuarios = usuariosModel::contarPorEstado();

        return [
            'usuarios_total' => $usuarios['total'],
            'usuarios_activos' => $usuarios['activos'],
            'usuarios_inactivos' => $usuarios['inactivos'],
        ];
    }
}

/*===========FIN DEL FRAGMENTO DE CÓDIGO============*/