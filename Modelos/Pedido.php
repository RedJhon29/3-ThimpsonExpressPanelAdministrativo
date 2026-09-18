<?php
class Order {
    private static $orders = [
        ['id' => 'TEX-2026-0847', 'client' => 'Carlos Martínez', 'rider' => 'María Torres', 'origin' => 'Ocotal Centro', 'destination' => 'Barrio San José', 'status' => 'IN_TRANSIT', 'cost' => 120, 'distance' => 4.8, 'created_at' => '2026-09-18 14:30'],
        ['id' => 'TEX-2026-0851', 'client' => 'Ana Rodríguez', 'rider' => 'Luis Gómez', 'origin' => 'Farmacia Central', 'destination' => 'Residencial Los Pinos', 'status' => 'PICKED_UP', 'cost' => 185, 'distance' => 6.7, 'created_at' => '2026-09-18 13:15'],
        ['id' => 'TEX-2026-0849', 'client' => 'Pedro López', 'rider' => null, 'origin' => 'Supermercado Central', 'destination' => 'Colonia Libertad', 'status' => 'PENDING', 'cost' => 65, 'distance' => 2.8, 'created_at' => '2026-09-18 15:00'],
        ['id' => 'TEX-2026-0845', 'client' => 'Laura Sánchez', 'rider' => 'María Torres', 'origin' => 'Restaurante El Sabor', 'destination' => 'Casa particular', 'status' => 'DELIVERED', 'cost' => 90, 'distance' => 3.2, 'created_at' => '2026-09-18 12:00'],
        ['id' => 'TEX-2026-0843', 'client' => 'Miguel Ángel', 'rider' => 'Luis Gómez', 'origin' => 'Farmacia Divina', 'destination' => 'Barrio Esperanza', 'status' => 'DELIVERED', 'cost' => 75, 'distance' => 2.1, 'created_at' => '2026-09-18 11:30'],
    ];
    public static function all() { return self::$orders; }
    public static function findById($id) {
        foreach (self::$orders as $o) { if ($o['id'] === $id) return $o; }
        return null;
    }
    public static function statusCounts() {
        $c = ['PENDING'=>0,'PICKED_UP'=>0,'IN_TRANSIT'=>0,'DELIVERED'=>0];
        foreach (self::$orders as $o) { $c[$o['status']]++; }
        return $c;
    }
}
