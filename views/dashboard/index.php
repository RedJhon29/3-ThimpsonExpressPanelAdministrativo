<?php include VIEW_PATH . '/layouts/admin-header.php'; ?>
<?php include VIEW_PATH . '/layouts/admin-sidebar.php'; ?>

<!-- Dashboard Content -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon"><i class="bi bi-receipt" style="color:var(--primary);"></i></div>
            <div>
                <div class="stat-value" style="color:var(--primary);"><?php echo $stats['total_orders_today']; ?></div>
                <div class="stat-label">Pedidos Hoy</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon"><i class="bi bi-person-video3" style="color:var(--success);"></i></div>
            <div>
                <div class="stat-value"><?php echo $stats['active_riders']; ?></div>
                <div class="stat-label">Riders Activos</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon"><i class="bi bi-cash-stack" style="color:var(--success);"></i></div>
            <div>
                <div class="stat-value" style="color:var(--success);"><?php echo CURRENCY_SYMBOL; ?><?php echo number_format($stats['revenue_today']); ?></div>
                <div class="stat-label">Ingresos Hoy</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon"><i class="bi bi-clock-history" style="color:var(--destructive);"></i></div>
            <div>
                <div class="stat-value" style="color:var(--destructive);"><?php echo $stats['pending_orders']; ?></div>
                <div class="stat-label">Pendientes</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <!-- Pedidos Recientes -->
    <div class="col-lg-8">
        <div class="table-container">
            <div class="table-header">
                <h5 class="mb-0">Pedidos Recientes</h5>
                <span class="live-indicator"><span class="live-dot"></span> En vivo</span>
            </div>
            <div class="table-responsive">
                <table class="table datatable" style="width:100%">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Cliente</th>
                            <th>Rider</th>
                            <th>Estado</th>
                            <th>Costo</th>
                            <th>Hora</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentOrders as $order): ?>
                        <tr>
                            <td><strong><?php echo $order['id']; ?></strong></td>
                            <td><?php echo $order['client']; ?></td>
                            <td><?php echo $order['rider'] ?? '<span class="text-muted">Sin asignar</span>'; ?></td>
                            <td>
                                <span class="status-badge <?php echo strtolower($order['status']); ?>">
                                    <?php echo $order['status']; ?>
                                </span>
                            </td>
                            <td style="font-family:var(--font-mono);"><?php echo CURRENCY_SYMBOL; ?><?php echo $order['cost']; ?></td>
                            <td class="text-muted"><?php echo date('H:i', strtotime($order['created_at'])); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Stats Panel -->
    <div class="col-lg-4">
        <div class="chart-container mb-3">
            <h5>Distribución de Pedidos</h5>
            <canvas id="ordersChart" height="200"></canvas>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="bi bi-clock" style="color:var(--primary);"></i></div>
            <div>
                <div class="stat-value"><?php echo $stats['avg_delivery_time']; ?></div>
                <div class="stat-label">Tiempo Promedio</div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var statusCounts = <?php echo json_encode(Order::statusCounts()); ?>;
    var ctx = document.getElementById('ordersChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Pendiente', 'Recolectado', 'En Camino', 'Entregado'],
                datasets: [{
                    data: [statusCounts['PENDING'], statusCounts['PICKED_UP'], statusCounts['IN_TRANSIT'], statusCounts['DELIVERED']],
                    backgroundColor: ['#E53935', '#1976D2', '#FBB03B', '#22C55E'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { color: '#A3A3A3', font: { family: 'Inter' } }
                    }
                }
            }
        });
    }
});
</script>

<?php include VIEW_PATH . '/layouts/admin-footer.php'; ?>
