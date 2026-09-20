<?php include VIEW_PATH . '/Plantillas/encabezadoAdmin.php'; ?>
<?php include VIEW_PATH . '/Plantillas/barraLateralAdmin.php'; ?>

<div class="page-header">
    <h1 class="page-title">Clientes</h1>
    <div class="page-actions">
        <button class="btn btn-primary" onclick="window.location.href='?page=clients&action=create'">
            <i class="bi bi-plus-lg"></i> Nuevo Cliente
        </button>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon"><i class="bi bi-people" style="color:var(--primary);"></i></div>
            <div>
                <div class="stat-value" style="color:var(--primary);"><?php echo count($clients); ?></div>
                <div class="stat-label">Total Clientes</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon"><i class="bi bi-person-check" style="color:var(--success);"></i></div>
            <div>
                <div class="stat-value" style="color:var(--success);"><?php echo count(array_filter($clients, fn($c) => $c['status'] === 'active')); ?></div>
                <div class="stat-label">Activos</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon"><i class="bi bi-person-x" style="color:var(--destructive);"></i></div>
            <div>
                <div class="stat-value" style="color:var(--destructive);"><?php echo count(array_filter($clients, fn($c) => $c['status'] === 'inactive')); ?></div>
                <div class="stat-label">Inactivos</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon"><i class="bi bi-receipt" style="color:var(--warning);"></i></div>
            <div>
                <div class="stat-value" style="color:var(--warning);"><?php echo array_sum(array_column($clients, 'orders')); ?></div>
                <div class="stat-label">Total Pedidos</div>
            </div>
        </div>
    </div>
</div>

<div class="table-container">
    <div class="table-header">
        <h5 class="mb-0">Lista de Clientes</h5>
        <div class="table-search">
            <input type="text" class="form-control" placeholder="Buscar clientes..." id="clientsSearch">
        </div>
    </div>
    <div class="table-responsive">
        <table class="table datatable" style="width:100%">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Teléfono</th>
                    <th>Pedidos</th>
                    <th>Fecha Registro</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($clients as $client): ?>
                <tr>
                    <td><strong><?php echo $client['id']; ?></strong></td>
                    <td><?php echo $client['name']; ?></td>
                    <td><?php echo $client['email']; ?></td>
                    <td><?php echo $client['phone']; ?></td>
                    <td><?php echo number_format($client['orders']); ?></td>
                    <td class="text-muted"><?php echo date('d/m/Y', strtotime($client['joined'])); ?></td>
                    <td>
                        <span class="status-badge <?php echo $client['status'] === 'active' ? 'status-active' : 'status-inactive'; ?>">
                            <?php echo ucfirst($client['status']); ?>
                        </span>
                    </td>
                    <td>
                        <div class="action-buttons">
                            <button class="btn btn-sm btn-outline-primary" title="Ver detalle" onclick="viewClient(<?php echo $client['id']; ?>)">
                                <i class="bi bi-eye"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-secondary" title="Editar" onclick="editClient(<?php echo $client['id']; ?>)">
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
    const searchInput = document.getElementById('clientsSearch');
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

function viewClient(id) {
    window.location.href = '?page=clients&action=detail&id=' + id;
}

function editClient(id) {
    window.location.href = '?page=clients&action=edit&id=' + id;
}
</script>