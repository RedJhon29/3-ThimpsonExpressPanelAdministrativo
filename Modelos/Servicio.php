<?php
class Service {
    private static $services = [
        ['id'=>1,'name'=>'Mandado','slug'=>'mandado','price_type'=>'fixed','price_base'=>40,'coverage'=>'Ocotal, Zona Norte','status'=>'active'],
        ['id'=>2,'name'=>'Delivery','slug'=>'delivery','price_type'=>'fixed','price_base'=>40,'coverage'=>'Ocotal, Zona Norte','status'=>'active'],
        ['id'=>3,'name'=>'Encomienda','slug'=>'encomienda','price_type'=>'fixed','price_base'=>40,'coverage'=>'Norte, Central, Pacífico','status'=>'active'],
        ['id'=>4,'name'=>'Viaje Expreso','slug'=>'viaje-expreso','price_type'=>'quote','price_base'=>0,'coverage'=>'Norte, Central, Pacífico','status'=>'active'],
        ['id'=>5,'name'=>'Transporte','slug'=>'transporte','price_type'=>'quote','price_base'=>0,'coverage'=>'Todo el país','status'=>'active'],
        ['id'=>6,'name'=>'Acarreo','slug'=>'acarreo','price_type'=>'quote','price_base'=>0,'coverage'=>'Todo el país','status'=>'active'],
        ['id'=>7,'name'=>'Mudanza','slug'=>'mudanza','price_type'=>'quote','price_base'=>0,'coverage'=>'Todo el país','status'=>'active'],
    ];
    public static function all() { return self::$services; }
}
