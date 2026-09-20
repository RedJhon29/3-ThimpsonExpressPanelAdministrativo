<?php include VIEW_PATH . '/Plantillas/encabezadoAdmin.php'; ?>
<?php include VIEW_PATH . '/Plantillas/barraLateralAdmin.php'; ?>

<div class="page-header">
    <h1 class="page-title">Finanzas</h1>
    <div class="page-actions">
        <button class="btn btn-primary" onclick="window.location.href='?page=finance&action=report'">
            <i class="bi bi-file-earmark-text"></i> Generar Reporte
        </button>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon"><i class="bi bi-cash-stack" style="color:var(--success);"></i></div>
            <div>
                <div class="stat-value" style="color:var(--success);"><?php echo CURRENCY_SYMBOL; ?><?php echo number_format($stats['revenue_month']); ?></div>
                <div class="stat-label">Ingresos del Mes</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon"><i class="bi bi-cash" style="color:var(--destructive);"></i></div>
            <div>
                <div class="stat-value" style="color:var(--destructive);"><?php echo CURRENCY_SYMBOL; ?><?php echo number_format($stats['expenses_month']); ?></div>
                <div class="stat-label">Gastos del Mes</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon"><i class="bi bi-graph-up" style="color:var(--primary);"></i></div>
            <div>
                <div class="stat-value" style="color:var(--primary);"><?php echo CURRENCY_SYMBOL; ?><?php echo number_format($stats['profit_month']); ?></div>
                <div class="stat-label">Ganancia Neta</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon"><i class="bi bi-file-earmark-check" style="color:var(--info);"></i></div>
            <div>
                <div class="stat-value" style="color:var(--info);"><?php echo $stats['invoices_paid'] + $stats['invoices_pending']; ?></div>
                <div class="stat-label">Facturas Totales</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Facturas Pendientes (<?php echo $stats['invoices_pending']; ?>)</h5>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    <?php 
                    $pendingInvoices = array_filter($invoices, fn($i) => $i['status'] === 'Pendiente' || $i['status'] === 'Vencida');
                    foreach ($pendingInvoices as $inv): 
                    ?>
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fw-medium"><?php echo $inv['id']; ?></div>
                            <small class="text-muted"><?php echo $inv['client']; ?> - <?php echo date('d/m/Y', strtotime($inv['date'])); ?></small>
                        </div>
                        <div class="text-end">
                            <span class="fw-bold" style="font-family:var(--font-mono);"><?php echo CURRENCY_SYMBOL; ?><?php echo number_format($inv['amount']); ?></span>
                            <span class="badge <?php echo $inv['status'] === 'Vencida' ? 'bg-danger' : 'bg-warning'; ?> ms-2"><?php echo $inv['status']; ?></span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Facturas Pagadas (<?php echo $stats['invoices_paid']; ?>)</h5>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    <?php 
                    $paidInvoices = array_filter($invoices, fn($i) => $i['status'] === 'Pagada');
                    foreach (array_slice($paidInvoices, 0, 5) as $inv): 
                    ?>
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fw-medium"><?php echo $inv['id']; ?></div>
                            <small class="text-muted"><?php echo $inv['client']; ?> - <?php echo date('d/m/Y', strtotime($inv['date'])); ?></small>
                        </div>
                        <div class="text-end">
                            <span class="fw-bold text-success" style="font-family:var(--font-mono);"><?php echo CURRENCY_SYMBOL; ?><?php echo number_format($inv['amount']); ?></span>
                            <span class="badge bg-success ms-2">Pagada</span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Todas las Facturas</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table datatable" style="width:100%">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Cliente</th>
                        <th>Monto</th>
                        <th>Estado</th>
                        <th>Fecha</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($invoices as $inv): ?>
                    <tr>
                        <td><strong><?php echo $inv['id']; ?></strong></td>
                        <td><?php echo $inv['client']; ?></td>
                        <td style="font-family:var(--font-mono);"><?php echo CURRENCY_SYMBOL; ?><?php echo number_format($inv['amount']); ?></td>
                        <td>
                            <span class="status-badge status-<?php echo strtolower($inv['status']); ?>">
                                <?php echo $inv['status']; ?>
                            </span>
                        </td>
                        <td class="text-muted"><?php echo date('d/m/Y', strtotime($inv['date'])); ?></td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn btn-sm btn-outline-primary" title="Ver" onclick="viewInvoice('<?php echo $inv['id']; ?>')">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include VIEW_PATH . '/Plantillas/pieAdmin.php'; ?>

<script>
function viewInvoice(id) {
    window.location.href = '?page=finance&action=invoice_detail&id=' + id;
}
</script>

<style>
.status-pendiente { background: #FFF3E0; color: #E65100; }
.status-vencida { background: #FFEBEE; color: #C62828; }
.status-pagada { background: #E8F5E9; color: #2E7D32; }
</style>