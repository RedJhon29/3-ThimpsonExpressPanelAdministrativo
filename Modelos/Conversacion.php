<?php
class Conversacion {
    private static $conversations = [
        ['id'=>1,'client'=>'Carlos Martínez','last_message'=>'¿Cuánto cuesta el delivery?','time'=>'14:30','unread'=>2,'status'=>'open'],
        ['id'=>2,'client'=>'Ana Rodríguez','last_message'=>'Gracias por la entrega','time'=>'13:45','unread'=>0,'status'=>'closed'],
        ['id'=>3,'client'=>'Pedro López','last_message'=>'Necesito rastrear mi pedido','time'=>'12:20','unread'=>1,'status'=>'open'],
    ];
    public static function all() { return self::$conversations; }
}
