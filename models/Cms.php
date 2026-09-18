<?php
class Cms {
    private static $banners = [
        ['id'=>1,'title'=>'Celebramos con vos!','active'=>true,'start_date'=>'2026-08-01','end_date'=>'2026-08-31'],
    ];
    private static $faqs = [
        ['id'=>1,'question'=>'¿Cómo solicito un servicio?','answer'=>'Podés hacerlo desde nuestra app o por WhatsApp.','category'=>'General'],
        ['id'=>2,'question'=>'¿Cuáles son los horarios?','answer'=>'Lunes a sábado de 7am a 9pm.','category'=>'General'],
        ['id'=>3,'question'=>'¿Cómo pago?','answer'=>'Efectivo, tarjeta o billetera móvil.','category'=>'Pagos'],
        ['id'=>4,'question'=>'¿Puedo rastrear mi pedido?','answer'=>'Sí, desde la página de rastreo con tu código.','category'=>'Pedidos'],
    ];
    public static function getBanners() { return self::$banners; }
    public static function getFaqs() { return self::$faqs; }
}
