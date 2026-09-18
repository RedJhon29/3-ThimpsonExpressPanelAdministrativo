<?php
class Settings {
    private static $settings = [
        'app_name' => 'Thimpson Express',
        'currency' => 'C$',
        'timezone' => 'America/Managua',
        'maintenance_mode' => false,
        'min_delivery_price' => 40,
        'max_delivery_distance' => 50,
    ];
    public static function getAll() { return self::$settings; }
}
