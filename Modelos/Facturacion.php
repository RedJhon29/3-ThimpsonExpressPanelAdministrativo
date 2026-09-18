<?php
class Facturacion {
    private static $invoices = [
        ['id'=>'INV-2026-001','client'=>'Sabor Criollo','amount'=>25,'status'=>'Pagada','date'=>'2026-09-01'],
        ['id'=>'INV-2026-002','client'=>'Farmacia Divina','amount'=>25,'status'=>'Pagada','date'=>'2026-09-01'],
        ['id'=>'INV-2026-003','client'=>'Cliente Free','amount'=>0,'status'=>'Pendiente','date'=>'2026-09-01'],
        ['id'=>'INV-2026-004','client'=>'Supermercado Central','amount'=>65,'status'=>'Vencida','date'=>'2026-08-01'],
    ];
    public static function getInvoices() { return self::$invoices; }
}
