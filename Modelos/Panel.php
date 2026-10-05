<?php
/*====================ENCABEZADO====================
MODELO: Panel — indicadores del dashboard
ARCHIVO: Modelos/Panel.php
==================================================*/

/*=====================DETALLES=====================
QUÉ HACE: entrega los KPI del día (pedidos, riders,
    ingresos, entregas y suscripciones) del dashboard.
VINCULADO A: lo llama Controladores/panelController.php
    y los consume Vistas/Panel/index.php en las tarjetas.
SI SE ALTERA: cambian las cifras visibles del dashboard;
    cada clave debe existir en la vista que la pinta.
FECHA: 2026-10-02 | LUGAR: Ocotal, Nueva Segovia
ESCRITO POR: ING. DENIS MANUEL LÓPEZ MOLINA.
==================================================*/

/*================CUERPO DEL CÓDIGO=================*/

class Panel {
    /**
     * ====================ENCABEZADO====================
     * FUNCIÓN: getStats() | ROL: modelo
     * ==================================================
     * =====================DETALLES=====================
     * QUÉ HACE: entrega los indicadores del día que se pintan en el dashboard.
     * VINCULADO A: lo llama Controladores/panelController.php::index() y cada
     *     clave la lee Vistas/Panel/index.php para una tarjeta de KPI.
     * SI SE ALTERA: quitar o renombrar una clave deja esa tarjeta sin dato;
     *     revisar el total de claves que espera la vista.
     * FECHA: 2026-10-02 | LUGAR: Ocotal, Nueva Segovia
     * ESCRITO POR: ING. DENIS MANUEL LÓPEZ MOLINA.
     * ==================================================
     */
    public static function getStats() {
        return [
            'total_orders_today' => 47,
            'active_riders' => 12,
            'revenue_today' => 5840,
            'pending_orders' => 8,
            'delivered_today' => 35,
            'avg_delivery_time' => '28 min',
            'total_clients' => 342,
            'active_subscriptions' => 89,
        ];
    }
}

/*===========FIN DEL FRAGMENTO DE CÓDIGO============*/
