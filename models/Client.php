<?php
class Client {
    private static $clients = [
        ['id'=>1,'name'=>'Carlos Martínez','email'=>'carlos@email.com','phone'=>'+505 8100 1111','orders'=>15,'joined'=>'2026-01-15','status'=>'active'],
        ['id'=>2,'name'=>'Ana Rodríguez','email'=>'ana@email.com','phone'=>'+505 8100 2222','orders'=>23,'joined'=>'2026-02-20','status'=>'active'],
        ['id'=>3,'name'=>'Pedro López','email'=>'pedro@email.com','phone'=>'+505 8100 3333','orders'=>8,'joined'=>'2026-03-10','status'=>'active'],
        ['id'=>4,'name'=>'Laura Sánchez','email'=>'laura@email.com','phone'=>'+505 8100 4444','orders'=>31,'joined'=>'2025-11-05','status'=>'active'],
        ['id'=>5,'name'=>'Miguel Ángel','email'=>'miguel@email.com','phone'=>'+505 8100 5555','orders'=>5,'joined'=>'2026-06-01','status'=>'inactive'],
    ];
    public static function all() { return self::$clients; }
}
