<?php include VIEW_PATH . '/Plantillas/encabezadoAdmin.php'; ?>
<?php include VIEW_PATH . '/Plantillas/barraLateralAdmin.php'; ?>

<div class="page-header">
    <h1 class="page-title">Servicios</h1>
    <div class="page-actions">
        <button class="btn btn-primary" onclick="window.location.href='?page=services&action=create'">
            <i class="bi bi-plus-lg"></i> Nuevo Servicio
        </button>
    </div>
</div>

<div class="table-container">
    <div class="table-header">
        <h5 class="mb-0">Lista de Servicios</h5>
        <div class="table-search">
            <input type="text" class="form-control" placeholder="Buscar servicios..." id="servicesSearch">
        </div>
    </div>
    <div class="table-responsive">
        <table class="table datatable" style="width:100%">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Slug</th>
                    <th>Tipo de Precio</th>
                    <th>Precio Base</th>
                    <th>Cobertura</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($services as $service): ?>
                <tr>
                    <td><strong><?php echo $service['id']; ?></strong></td>
                    <td><?php echo $service['name']; ?></td>
                    <td><?php echo $service['slug']; ?></td>
                    <td>
                        <span class="badge <?php echo $service['price_type'] === 'fixed' ? 'bg-primary' : 'bg-info'; ?>">
                            <?php echo $service['price_type']; ?>
                        </span>
                    </td>
                    <td style="font-family:var(--font-mono);">
                        <?php echo $service['price_base'] > 0 ? CURRENCY_SYMBOL . number_format($service['price_base']) : 'Por cotización'; ?>
                    </td>
                    <td><?php echo $service['coverage']; ?></td>
                    <td>
                        <span class="status-badge <?php echo $service['status'] === 'active' ? 'status-active' : 'status-inactive'; ?>">
                            <?php echo ucfirst($service['status']); ?>
                        </span>
                    </td>
                    <td>
                        <div class="action-buttons">
                            <button class="btn btn-sm btn-outline-primary" title="Editar" onclick="editService(<?php echo $service['id']; ?>)">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger" title="Eliminar" onclick="deleteService(<?php echo $service['id']; ?>)">
                                <i class="bi bi-trash"></i>
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
    const searchInput = document.getElementById('servicesSearch');
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

function editService(id) {
    window.location.href = '?page=services&action=edit&id=' + id;
}

function deleteService(id) {
    if (confirm('¿Eliminar este servicio?')) {
        window.location.href = '?page=services&action=delete&id=' + id;
    }
}
</script>