<?php
class Negocio {
    private static $businesses = [
        ['id'=>1,'name'=>'Sabor Criollo','category'=>'Comida','rating'=>4.8,'reviews'=>132,'plan'=>'premium','status'=>'active'],
        ['id'=>2,'name'=>'Farmacia Divina Providencia','category'=>'Farmacias','rating'=>4.9,'reviews'=>87,'plan'=>'premium','status'=>'active'],
        ['id'=>3,'name'=>'Supermercado Central','category'=>'Supermercados','rating'=>4.6,'reviews'=>210,'plan'=>'free','status'=>'active'],
    ];
    public static function all() { return self::$businesses; }
}
