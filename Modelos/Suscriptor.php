<?php
class Suscriptor {
    private static $subscribers = [
        ['id'=>1,'email'=>'carlos@email.com','name'=>'Carlos','subscribed_at'=>'2026-01-15','status'=>'active'],
        ['id'=>2,'email'=>'ana@email.com','name'=>'Ana','subscribed_at'=>'2026-02-20','status'=>'active'],
        ['id'=>3,'email'=>'pedro@email.com','name'=>'Pedro','subscribed_at'=>'2026-03-10','status'=>'active'],
    ];
    public static function all() { return self::$subscribers; }
}
