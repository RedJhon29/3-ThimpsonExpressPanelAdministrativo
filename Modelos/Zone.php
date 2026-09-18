<?php
class Zone {
    private static $zones = [
        ['id'=>1,'name'=>'Ocotal Centro','lat'=>13.6324,'lng'=>-86.4764,'radius'=>3,'status'=>'active'],
        ['id'=>2,'name'=>'Zona Norte','lat'=>13.65,'lng'=>-86.50,'radius'=>10,'status'=>'active'],
        ['id'=>3,'name'=>'Estelí','lat'=>13.09,'lng'=>-86.35,'radius'=>5,'status'=>'active'],
    ];
    public static function all() { return self::$zones; }
}
