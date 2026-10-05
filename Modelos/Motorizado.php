<?php
/*====================ENCABEZADO====================
MODELO: Motorizado — modelo de datos de riders repartidores
ARCHIVO: Modelos/Motorizado.php
==================================================*/

/*=====================DETALLES=====================
QUÉ HACE: expone la lectura de riders (listado completo y
    búsqueda por id) para el dashboard y el tracking.
VINCULADO A: lo llama Controladores/panelController.php y
    consume su listado el mapa de Vistas/Riders/tracking.php.
SI SE ALTERA: cambia la tabla de riders del dashboard y el
    marcador de cada repartidor en el mapa.
FECHA: 2026-10-02 | LUGAR: Ocotal, Nueva Segovia
ESCRITO POR: ING. DENIS MANUEL LÓPEZ MOLINA.
==================================================*/

/*================CUERPO DEL CÓDIGO=================*/

class Motorizado {
    private static $riders = [
        ['id'=>1,'name'=>'María Torres','phone'=>'+505 8800 1111','vehicle'=>'Moto Yamaha FZ','plate'=>'M-12345','rating'=>4.9,'deliveries'=>342,'status'=>'active','current_lat'=>13.6424,'current_lng'=>-86.4864],
        ['id'=>2,'name'=>'Luis Gómez','phone'=>'+505 8800 2222','vehicle'=>'Moto Honda Wave','plate'=>'M-67890','rating'=>4.8,'deliveries'=>218,'status'=>'active','current_lat'=>13.6324,'current_lng'=>-86.4764],
        ['id'=>3,'name'=>'Carlos Peralta','phone'=>'+505 8800 3333','vehicle'=>'Moto Suzuki GN','plate'=>'M-11223','rating'=>4.7,'deliveries'=>156,'status'=>'active','current_lat'=>13.6524,'current_lng'=>-86.5064],
        ['id'=>4,'name'=>'Ana Ríos','phone'=>'+505 8800 4444','vehicle'=>'Bicicleta','plate'=>'N/A','rating'=>4.6,'deliveries'=>98,'status'=>'inactive','current_lat'=>0,'current_lng'=>0],
    ];
    /**
     * ====================ENCABEZADO====================
     * FUNCIÓN: all() | ROL: modelo
     * ==================================================
     * =====================DETALLES=====================
     * QUÉ HACE: expone el listado completo de riders del panel.
     * VINCULADO A: lo llama Controladores/panelController.php::index() y lo
     *     recorre Vistas/Panel/index.php para la tabla de repartidores.
     * SI SE ALTERA: cambia esa tabla y los totales del dashboard.
     * FECHA: 2026-10-02 | LUGAR: Ocotal, Nueva Segovia
     * ESCRITO POR: ING. DENIS MANUEL LÓPEZ MOLINA.
     * ==================================================
     */
    public static function all() { return self::$riders; }
    /**
     * ====================ENCABEZADO====================
     * FUNCIÓN: find() | ROL: modelo
     * ==================================================
     * =====================DETALLES=====================
     * QUÉ HACE: localiza un rider por su id y devuelve null si no existe.
     * VINCULADO A: lo usan la ficha y el tracking de
     *     Vistas/Riders/ para mostrar un solo repartidor.
     * SI SE ALTERA: el null marca "no encontrado"; quien lo llame debe
     *     validarlo antes de leer los campos del rider.
     * FECHA: 2026-10-02 | LUGAR: Ocotal, Nueva Segovia
     * ESCRITO POR: ING. DENIS MANUEL LÓPEZ MOLINA.
     * ==================================================
     */
    public static function find($id) {
        foreach (self::$riders as $r) { if ($r['id'] == $id) return $r; }
        return null;
    }
}

/*===========FIN DEL FRAGMENTO DE CÓDIGO============*/
