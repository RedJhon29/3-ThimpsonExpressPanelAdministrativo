<?php
class Soporte {
    private static $tickets = [
        ['id'=>1,'client'=>'Carlos Martínez','subject'=>'Pedido no entregado','status'=>'open','priority'=>'high','created'=>'2026-09-18 10:00'],
        ['id'=>2,'client'=>'Ana Rodríguez','subject'=>'Consulta sobre precio','status'=>'closed','priority'=>'low','created'=>'2026-09-17 15:00'],
        ['id'=>3,'client'=>'Pedro López','subject'=>'Error en facturación','status'=>'open','priority'=>'medium','created'=>'2026-09-17 09:00'],
    ];
    public static function all() { return self::$tickets; }
}
