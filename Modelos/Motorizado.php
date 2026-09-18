<?php
class Motorizado {
    private static $riders = [
        ['id'=>1,'name'=>'María Torres','phone'=>'+505 8800 1111','vehicle'=>'Moto Yamaha FZ','plate'=>'M-12345','rating'=>4.9,'deliveries'=>342,'status'=>'active','current_lat'=>13.6424,'current_lng'=>-86.4864],
        ['id'=>2,'name'=>'Luis Gómez','phone'=>'+505 8800 2222','vehicle'=>'Moto Honda Wave','plate'=>'M-67890','rating'=>4.8,'deliveries'=>218,'status'=>'active','current_lat'=>13.6324,'current_lng'=>-86.4764],
        ['id'=>3,'name'=>'Carlos Peralta','phone'=>'+505 8800 3333','vehicle'=>'Moto Suzuki GN','plate'=>'M-11223','rating'=>4.7,'deliveries'=>156,'status'=>'active','current_lat'=>13.6524,'current_lng'=>-86.5064],
        ['id'=>4,'name'=>'Ana Ríos','phone'=>'+505 8800 4444','vehicle'=>'Bicicleta','plate'=>'N/A','rating'=>4.6,'deliveries'=>98,'status'=>'inactive','current_lat'=>0,'current_lng'=>0],
    ];
    public static function all() { return self::$riders; }
    public static function find($id) {
        foreach (self::$riders as $r) { if ($r['id'] == $id) return $r; }
        return null;
    }
}
