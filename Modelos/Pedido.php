<?php
/*====================ENCABEZADO====================
MODELO: Pedido — modelo de datos de pedidos del panel
ARCHIVO: Modelos/Pedido.php
==================================================*/

/*=====================DETALLES=====================
QUÉ HACE: expone la lectura de pedidos (listado, folio y
    contadores por estado) para el dashboard del panel.
VINCULADO A: lo llama Controladores/panelController.php y
    Vistas/Panel/index.php consume sus contadores.
SI SE ALTERA: cambia la tabla reciente y los badges de
    estado del dashboard; revisar Vistas/Panel/index.php.
FECHA: 2026-10-02 | LUGAR: Ocotal, Nueva Segovia
ESCRITO POR: ING. DENIS MANUEL LÓPEZ MOLINA.
==================================================*/

/*================CUERPO DEL CÓDIGO=================*/

class Pedido {
    private static $orders = [
        ['id' => 'TEX-2026-0847', 'client' => 'Carlos Martínez', 'rider' => 'María Torres', 'origin' => 'Ocotal Centro', 'destination' => 'Barrio San José', 'status' => 'IN_TRANSIT', 'cost' => 120, 'distance' => 4.8, 'created_at' => '2026-09-18 14:30'],
        ['id' => 'TEX-2026-0851', 'client' => 'Ana Rodríguez', 'rider' => 'Luis Gómez', 'origin' => 'Farmacia Central', 'destination' => 'Residencial Los Pinos', 'status' => 'PICKED_UP', 'cost' => 185, 'distance' => 6.7, 'created_at' => '2026-09-18 13:15'],
        ['id' => 'TEX-2026-0849', 'client' => 'Pedro López', 'rider' => null, 'origin' => 'Supermercado Central', 'destination' => 'Colonia Libertad', 'status' => 'PENDING', 'cost' => 65, 'distance' => 2.8, 'created_at' => '2026-09-18 15:00'],
        ['id' => 'TEX-2026-0845', 'client' => 'Laura Sánchez', 'rider' => 'María Torres', 'origin' => 'Restaurante El Sabor', 'destination' => 'Casa particular', 'status' => 'DELIVERED', 'cost' => 90, 'distance' => 3.2, 'created_at' => '2026-09-18 12:00'],
        ['id' => 'TEX-2026-0843', 'client' => 'Miguel Ángel', 'rider' => 'Luis Gómez', 'origin' => 'Farmacia Divina', 'destination' => 'Barrio Esperanza', 'status' => 'DELIVERED', 'cost' => 75, 'distance' => 2.1, 'created_at' => '2026-09-18 11:30'],
    ];
    /**
     * ====================ENCABEZADO====================
     * FUNCIÓN: all() | ROL: modelo
     * ==================================================
     * =====================DETALLES=====================
     * QUÉ HACE: expone el listado completo de pedidos que consume el dashboard.
     * VINCULADO A: lo llama Controladores/panelController.php::index() y lo
     *     recorre Vistas/Panel/index.php para la tabla de pedidos recientes.
     * SI SE ALTERA: cambia el contenido de esa tabla en el dashboard.
     * FECHA: 2026-10-02 | LUGAR: Ocotal, Nueva Segovia
     * ESCRITO POR: ING. DENIS MANUEL LÓPEZ MOLINA.
     * ==================================================
     */
    public static function all() { return self::$orders; }
    /**
     * ====================ENCABEZADO====================
     * FUNCIÓN: findById() | ROL: modelo
     * ==================================================
     * =====================DETALLES=====================
     * QUÉ HACE: localiza un pedido por su folio y devuelve null si no existe.
     * VINCULADO A: lo llama Vistas/Pedidos/detail.php con el folio de ?id=
     *     para montar la ficha del pedido.
     * SI SE ALTERA: el null significa "no encontrado"; quien lo llame debe
     *     validarlo antes de leer cualquier campo del pedido.
     * FECHA: 2026-10-02 | LUGAR: Ocotal, Nueva Segovia
     * ESCRITO POR: ING. DENIS MANUEL LÓPEZ MOLINA.
     * ==================================================
     */
    public static function findById($id) {
        foreach (self::$orders as $o) { if ($o['id'] === $id) return $o; }
        return null;
    }
    /**
     * ====================ENCABEZADO====================
     * FUNCIÓN: statusCounts() | ROL: modelo
     * ==================================================
     * =====================DETALLES=====================
     * QUÉ HACE: agrupa los pedidos por estado para los contadores del panel.
     * VINCULADO A: lo llama Vistas/Panel/index.php para pintar los badges de
     *     estado (pendientes, en tránsito, entregados) del dashboard.
     * SI SE ALTERA: si se agrega un estado nuevo hay que sumarlo al array de
     *     contadores inicial o ese pedido quedará fuera de todos los totales.
     * FECHA: 2026-10-02 | LUGAR: Ocotal, Nueva Segovia
     * ESCRITO POR: ING. DENIS MANUEL LÓPEZ MOLINA.
     * ==================================================
     */
    public static function statusCounts() {
        $c = ['PENDING'=>0,'PICKED_UP'=>0,'IN_TRANSIT'=>0,'DELIVERED'=>0];
        foreach (self::$orders as $o) { $c[$o['status']]++; }
        return $c;
    }
}

/*===========FIN DEL FRAGMENTO DE CÓDIGO============*/
