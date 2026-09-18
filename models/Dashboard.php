<?php
class Dashboard {
    public static function getStats() {
        return [
            'total_orders_today' => 47,
            'active_riders' => 12,
            'revenue_today' => 5840,
            'pending_orders' => 8,
            'delivered_today' => 35,
            'avg_delivery_time' => '28 min',
            'total_clients' => 342,
            'active_subscriptions' => 89,
        ];
    }
}
