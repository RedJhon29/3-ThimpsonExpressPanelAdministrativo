<?php include VIEW_PATH . '/Plantillas/encabezadoAdmin.php'; ?>
<?php include VIEW_PATH . '/Plantillas/barraLateralAdmin.php'; ?>

<div class="page-header">
    <h1 class="page-title">Gestión de Pedidos</h1>
    <div class="page-actions">
        <button class="btn btn-primary" onclick="window.location.href='?page=orders&action=create'">
            <i class="bi bi-plus-lg"></i> Nuevo Pedido
        </button>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon"><i class="bi bi-receipt" style="color:var(--primary);"></i></div>
            <div>
                <div class="stat-value" style="color:var(--primary);"><?php echo count(array_filter($orders, fn($o) => $o['status'] === 'PENDING')); ?></div>
                <div class="stat-label">Pendientes</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon"><i class="bi bi-box-seam" style="color:var(--info);"></i></div>
            <div>
                <div class="stat-value" style="color:var(--info);"><?php echo count(array_filter($orders, fn($o) => $o['status'] === 'PICKED_UP')); ?></div>
                <div class="stat-label">Recolectados</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon"><i class="bi bi-truck" style="color:var(--warning);"></i></div>
            <div>
                <div class="stat-value" style="color:var(--warning);"><?php echo count(array_filter($orders, fn($o) => $o['status'] === 'IN_TRANSIT')); ?></div>
                <div class="stat-label">En Camino</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon"><i class="bi bi-check-circle" style="color:var(--success);"></i></div>
            <div>
                <div class="stat-value" style="color:var(--success);"><?php echo count(array_filter($orders, fn($o) => $o['status'] === 'DELIVERED')); ?></div>
                <div class="stat-label">Entregados</div>
            </div>
        </div>
    </div>
</div>

<div class="table-container">
    <div class="table-header">
        <h5 class="mb-0">Lista de Pedidos</h5>
        <div class="table-search">
            <input type="text" class="form-control" placeholder="Buscar pedidos..." id="ordersSearch">
        </div>
    </div>
    <div class="table-responsive">
        <table class="table datatable" style="width:100%">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Cliente</th>
                    <th>Rider</th>
                    <th>Origen</th>
                    <th>Destino</th>
                    <th>Estado</th>
                    <th>Costo</th>
                    <th>Distancia</th>
                    <th>Fecha</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                <tr>
                    <td><strong><?php echo $order['id']; ?></strong></td>
                    <td><?php echo $order['client']; ?></td>
                    <td><?php echo $order['rider'] ?? '<span class="text-muted">Sin asignar</span>'; ?></td>
                    <td><?php echo $order['origin']; ?></td>
                    <td><?php echo $order['destination']; ?></td>
                    <td>
                        <span class="status-badge <?php echo strtolower($order['status']); ?>">
                            <?php echo $order['status']; ?>
                        </span>
                    </td>
                    <td style="font-family:var(--font-mono);"><?php echo CURRENCY_SYMBOL; ?><?php echo number_format($order['cost']); ?></td>
                    <td><?php echo $order['distance']; ?> km</td>
                    <td class="text-muted"><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></td>
                    <td>
                        <div class="action-buttons">
                            <a href="?page=orders&action=detail&id=<?php echo $order['id']; ?>" class="btn btn-sm btn-outline-primary" title="Ver detalle">
                                <i class="bi bi-eye"></i>
                            </a>
                            <button class="btn btn-sm btn-outline-secondary" title="Editar" onclick="editOrder('<?php echo $order['id']; ?>')">
                                <i class="bi bi-pencil"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include VIEW_PATH . '/Plantillas/pieAdmin.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('ordersSearch');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const filter = this.value.toLowerCase();
            const rows = document.querySelectorAll('.datatable tbody tr');
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(filter) ? '' : 'none';
            });
        });
    }
});

function editOrder(id) {
    window.location.href = '?page=orders&action=edit&id=' + id;
}
</script>