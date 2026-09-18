<?php
class AdminUser {
    private static $users = [
        ['id'=>1,'name'=>'Allan Thimpson','email'=>'allan@thimpsonexpress.com','role'=>'Super Admin','status'=>'active','last_login'=>'2026-09-18 08:00'],
        ['id'=>2,'name'=>'Operador 1','email'=>'operador1@thimpsonexpress.com','role'=>'Operador','status'=>'active','last_login'=>'2026-09-17 14:30'],
    ];
    public static function all() { return self::$users; }
}
