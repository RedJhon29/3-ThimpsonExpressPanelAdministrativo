<?php
class Auditoria {
    private static $logs = [
        ['id'=>1,'user'=>'Admin','action'=>'Login','details'=>'Inicio de sesión exitoso','ip'=>'192.168.1.1','time'=>'2026-09-18 08:00'],
        ['id'=>2,'user'=>'Admin','action'=>'Editar servicio','details'=>'Actualizó precio de Mandado','ip'=>'192.168.1.1','time'=>'2026-09-18 09:15'],
        ['id'=>3,'user'=>'System','action'=>'Backup','details'=>'Backup automático completado','ip'=>'localhost','time'=>'2026-09-18 03:00'],
        ['id'=>4,'user'=>'Admin','action'=>'Aprobar negocio','details'=>'Aprobó Sabor Criollo','ip'=>'192.168.1.1','time'=>'2026-09-17 16:30'],
    ];
    public static function all() { return self::$logs; }
}
