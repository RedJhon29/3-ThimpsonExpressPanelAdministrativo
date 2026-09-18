<?php
class Chatbot {
    private static $intents = [
        ['id'=>1,'name'=>'Saludo','patterns'=>['hola','buenos dias','hey'],'response'=>'¡Hola! ¿En qué puedo ayudarte?'],
        ['id'=>2,'name'=>'Precio','patterns'=>['precio','cuanto cuesta','costo'],'response'=>'Nuestros servicios de mandado cuestan C$40 por parada.'],
        ['id'=>3,'name'=>'Rastreo','patterns'=>['rastrear','pedido','seguimiento'],'response'=>'Podés rastrear tu pedido en nuestra página de rastreo.'],
        ['id'=>4,'name'=>'Contacto','patterns'=>['contacto','telefono','whatsapp'],'response'=>'Claro: +505 8415 9112, Tigo: +505 8593 2295.'],
    ];
    public static function getIntents() { return self::$intents; }
}
