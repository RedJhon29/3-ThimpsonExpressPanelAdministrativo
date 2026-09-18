<?php
class Promocion {
    private static $promotions = [
        ['id'=>1,'name'=>'10% en primer pedido','discount'=>10,'type'=>'percentage','start_date'=>'2026-09-01','end_date'=>'2026-09-30','status'=>'active'],
        ['id'=>2,'name'=>'Envío gratis >C$200','discount'=>0,'type'=>'free_shipping','start_date'=>'2026-09-01','end_date'=>'2026-12-31','status'=>'active'],
    ];
    public static function all() { return self::$promotions; }
}
