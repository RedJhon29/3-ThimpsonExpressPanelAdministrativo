<?php
class Notificacion {
    private static $notifications = [
        ['id'=>1,'type'=>'order_update','message'=>'Pedido TEX-0847 actualizado a En Camino','time'=>'14:35','read'=>false],
        ['id'=>2,'type'=>'new_rider','message'=>'Nuevo rider registrado: Carlos Peralta','time'=>'12:00','read'=>true],
        ['id'=>3,'type'=>'payment','message'=>'Pago recibido de Sabor Criollo - C$25','time'=>'11:30','read'=>true],
    ];
    private static $rules = [
        ['id'=>1,'event'=>'new_order','action'=>'Notificar admin','active'=>true],
        ['id'=>2,'event'=>'order_delivered','action'=>'Notificar al cliente','active'=>true],
        ['id'=>3,'event'=>'new_review','action'=>'Notificar al rider','active'=>false],
    ];
    public static function all() { return self::$notifications; }
    public static function getRules() { return self::$rules; }
}
