<?php
class Dispositivo {
    private static $devices = [
        ['id'=>1,'name'=>'Samsung Galaxy S23','user'=>'María Torres','type'=>'Moto Rider','status'=>'active','last_seen'=>'2026-09-18 14:30'],
        ['id'=>2,'name'=>'iPhone 14','user'=>'Luis Gómez','type'=>'Moto Rider','status'=>'active','last_seen'=>'2026-09-18 13:45'],
    ];
    public static function all() { return self::$devices; }
}
