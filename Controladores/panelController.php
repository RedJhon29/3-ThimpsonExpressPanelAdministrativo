<?php
/*====================ENCABEZADO====================
CONTROLADOR: panelController — dashboard del panel
ARCHIVO: Controladores/panelController.php
==================================================*/

/*=====================DETALLES=====================
QUÉ HACE: arma el dashboard: define título y menú activo,
    consulta KPI, pedidos recientes y riders, y renderiza la vista.
VINCULADO A: lo llama index.php en la ruta /dashboard y llama
    a Panel::getStats(), Pedido::all() y Motorizado::all().
SI SE ALTERA: cambia el dashboard; revisar que las variables
    sigan llegando completas a Vistas/Panel/index.php.
FECHA: 2026-10-02 | LUGAR: Ocotal, Nueva Segovia
ESCRITO POR: ING. DENIS MANUEL LÓPEZ MOLINA.
==================================================*/

/*================CUERPO DEL CÓDIGO=================*/

class panelController {
    /**
     * ====================ENCABEZADO====================
     * FUNCIÓN: index() | ROL: controlador
     * ==================================================
     * =====================DETALLES=====================
     * QUÉ HACE: muestra el dashboard con KPI, pedidos recientes y riders.
     * VINCULADO A: lo llama index.php en la ruta /dashboard; consulta
     *     Modelos/Panel.php::getStats(), Modelos/Pedido.php::all() y
     *     Modelos/Motorizado.php::all(); renderiza Vistas/Panel/index.php.
     * SI SE ALTERA: la vista exige $pageTitle, $activeMenu, $stats,
     *     $recentOrders y $riders; si falta alguna, el dashboard falla.
     * FECHA: 2026-10-02 | LUGAR: Ocotal, Nueva Segovia
     * ESCRITO POR: ING. DENIS MANUEL LÓPEZ MOLINA.
     * ==================================================
     */
    public function index() {
        $pageTitle = 'Dashboard';
        $activeMenu = 'dashboard';
        $stats = Panel::getStats();
        $recentOrders = Pedido::all();
        $riders = Motorizado::all();
        include VIEW_PATH . '/Panel/index.php';
    }
}

/*===========FIN DEL FRAGMENTO DE CÓDIGO============*/
