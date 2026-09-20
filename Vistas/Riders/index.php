<?php include VIEW_PATH . '/Plantillas/encabezadoAdmin.php'; ?>
<?php include VIEW_PATH . '/Plantillas/barraLateralAdmin.php'; ?>

<div class="page-header">
    <h1 class="page-title">Gestión de Riders</h1>
    <div class="page-actions">
        <button class="btn btn-primary" onclick="window.location.href='?page=riders&action=create'">
            <i class="bi bi-plus-lg"></i> Nuevo Rider
        </button>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon"><i class="bi bi-person-video3" style="color:var(--primary);"></i></div>
            <div>
                <div class="stat-value" style="color:var(--primary);"><?php echo count(array_filter($riders, fn($r) => $r['status'] === 'active')); ?></div>
                <div class="stat-label">Activos</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon"><i class="bi bi-person-x" style="color:var(--destructive);"></i></div>
            <div>
                <div class="stat-value" style="color:var(--destructive);"><?php echo count(array_filter($riders, fn($r) => $r['status'] === 'inactive')); ?></div>
                <div class="stat-label">Inactivos</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon"><i class="bi bi-box-seam" style="color:var(--success);"></i></div>
            <div>
                <div class="stat-value" style="color:var(--success);"><?php echo array_sum(array_column($riders, 'deliveries')); ?></div>
                <div class="stat-label">Total Entregas</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon"><i class="bi bi-star" style="color:var(--warning);"></i></div>
            <div>
                <div class="stat-value" style="color:var(--warning);"><?php echo count($riders) > 0 ? number_format(array_sum(array_column($riders, 'rating')) / count($riders), 1) : '0'; ?></div>
                <div class="stat-label">Rating Promedio</div>
            </div>
        </div>
    </div>
</div>

<div class="table-container">
    <div class="table-header">
        <h5 class="mb-0">Lista de Riders</h5>
        <div class="table-search">
            <input type="text" class="form-control" placeholder="Buscar riders..." id="ridersSearch">
        </div>
    </div>
    <div class="table-responsive">
        <table class="table datatable" style="width:100%">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Teléfono</th>
                    <th>Vehículo</th>
                    <th>Placa</th>
                    <th>Rating</th>
                    <th>Entregas</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($riders as $rider): ?>
                <tr>
                    <td><strong><?php echo $rider['id']; ?></strong></td>
                    <td><?php echo $rider['name']; ?></td>
                    <td><?php echo $rider['phone']; ?></td>
                    <td><?php echo $rider['vehicle']; ?></td>
                    <td><?php echo $rider['plate']; ?></td>
                    <td>
                        <span class="fw-bold" style="color:var(--warning);"><?php echo $rider['rating']; ?></span>
                        <i class="bi bi-star-fill" style="color:var(--warning);"></i>
                    </td>
                    <td><?php echo number_format($rider['deliveries']); ?></td>
                    <td>
                        <span class="status-badge <?php echo $rider['status'] === 'active' ? 'status-active' : 'status-inactive'; ?>">
                            <?php echo ucfirst($rider['status']); ?>
                        </span>
                    </td>
                    <td>
                        <div class="action-buttons">
                            <a href="?page=riders&action=detail&id=<?php echo $rider['id']; ?>" class="btn btn-sm btn-outline-primary" title="Ver detalle">
                                <i class="bi bi-eye"></i>
                            </a>
                            <button class="btn btn-sm btn-outline-secondary" title="Editar" onclick="editRider(<?php echo $rider['id']; ?>)">
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
    const searchInput = document.getElementById('ridersSearch');
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

function editRider(id) {
    window.location.href = '?page=riders&action=edit&id=' + id;
}
</script>