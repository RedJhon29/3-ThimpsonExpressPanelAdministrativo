<?php
class Calificacion {
    private static $ratings = [
        ['id'=>1,'rider'=>'María Torres','client'=>'Carlos M.','rating'=>5,'comment'=>'Excelente servicio','date'=>'2026-09-15'],
        ['id'=>2,'rider'=>'María Torres','client'=>'Ana R.','rating'=>5,'comment'=>'Muy profesional','date'=>'2026-09-14'],
        ['id'=>3,'rider'=>'Luis Gómez','client'=>'Pedro L.','rating'=>4,'comment'=>'Buen servicio','date'=>'2026-09-13'],
        ['id'=>4,'rider'=>'María Torres','client'=>'Laura S.','rating'=>5,'comment'=>'Perfecto','date'=>'2026-09-12'],
        ['id'=>5,'rider'=>'Luis Gómez','client'=>'Miguel A.','rating'=>5,'comment'=>'Muy buen servicio','date'=>'2026-09-11'],
    ];
    public static function all() { return self::$ratings; }
    public static function average() {
        $sum = array_sum(array_column(self::$ratings, 'rating'));
        return round($sum / count(self::$ratings), 1);
    }
    public static function breakdown() {
        $b = [5=>0,4=>0,3=>0,2=>0,1=>0];
        foreach (self::$ratings as $r) { $b[$r['rating']]++; }
        return $b;
    }
}
