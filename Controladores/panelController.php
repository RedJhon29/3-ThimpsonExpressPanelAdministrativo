<?php
/*====================ENCABEZADO====================
CONTROLADOR: panelController — dashboard del panel
ARCHIVO: Controladores/panelController.php
==================================================*/

/*=====================DETALLES=====================
QUÉ HACE: arma el dashboard: define título y menú activo,
    consulta el conteo de usuarios y renderiza la vista.
VINCULADO A: lo llama index.php en la ruta /dashboard y llama
    a Panel::getStats().
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
     * QUÉ HACE: muestra el dashboard con el conteo de usuarios.
     * VINCULADO A: lo llama index.php en la ruta /dashboard; consulta
     *     Modelos/Panel.php::getStats(); renderiza Vistas/Panel/index.php.
     * SI SE ALTERA: la vista exige $pageTitle, $activeMenu y $stats;
     *     si falta alguna, el dashboard falla.
     * LÍMITES: ya no carga pedidos ni riders: el dashboard solo muestra
     *     usuarios. Se reagregan cuando haya consultas reales.
     * FECHA: 2026-10-02 | LUGAR: Ocotal, Nueva Segovia
     * ESCRITO POR: ING. DENIS MANUEL LÓPEZ MOLINA.
     * ==================================================
     */
    public function index() {
        $pageTitle = 'Dashboard';
        $activeMenu = 'dashboard';
        $stats = Panel::getStats();
        include VIEW_PATH . '/Panel/index.php';
    }
}

/*===========FIN DEL FRAGMENTO DE CÓDIGO============*/
