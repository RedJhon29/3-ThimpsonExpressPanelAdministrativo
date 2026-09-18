<?php
class Precio {
    private static $rules = [
        ['id'=>1,'service'=>'Mandado','base_price'=>40,'type'=>'per_stop','active'=>true],
        ['id'=>2,'service'=>'Delivery','base_price'=>40,'type'=>'per_stop','active'=>true],
        ['id'=>3,'service'=>'Encomienda','base_price'=>40,'type'=>'per_stop','active'=>true],
        ['id'=>4,'service'=>'Viaje Expreso','base_price'=>0,'type'=>'quote','active'=>true],
    ];
    public static function getRules() { return self::$rules; }
}
