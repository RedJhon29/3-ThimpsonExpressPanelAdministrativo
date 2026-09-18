<?php
class Suscripcion {
    private static $subscriptions = [
        ['id'=>1,'client'=>'Sabor Criollo','plan'=>'Pro','cycle'=>'monthly','amount'=>25,'status'=>'active','renewal'=>'2026-10-01'],
        ['id'=>2,'client'=>'Farmacia Divina','plan'=>'Pro','cycle'=>'yearly','amount'=>240,'status'=>'active','renewal'=>'2027-01-01'],
        ['id'=>3,'client'=>'Supermercado Central','plan'=>'Gratis','cycle'=>'free','amount'=>0,'status'=>'active','renewal'=>'N/A'],
    ];
    public static function all() { return self::$subscriptions; }
}
