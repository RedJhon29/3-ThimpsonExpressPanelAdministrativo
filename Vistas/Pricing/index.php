<?php include VIEW_PATH . '/Plantillas/encabezadoAdmin.php'; ?>
<?php include VIEW_PATH . '/Plantillas/barraLateralAdmin.php'; ?>

<div class="page-header">
    <h1 class="page-title">Precios / Reglas de Tarificación</h1>
    <div class="page-actions">
        <button class="btn btn-primary" onclick="window.location.href='?page=pricing&action=create'">
            <i class="bi bi-plus-lg"></i> Nueva Regla
        </button>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Reglas de Precios</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table datatable" style="width:100%">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Servicio</th>
                        <th>Precio Base</th>
                        <th>Tipo</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (Precio::getRules() as $rule): ?>
                    <tr>
                        <td><strong><?php echo $rule['id']; ?></strong></td>
                        <td><?php echo $rule['service']; ?></td>
                        <td style="font-family:var(--font-mono);">
                            <?php echo $rule['base_price'] > 0 ? CURRENCY_SYMBOL . number_format($rule['base_price']) : 'Por cotización'; ?>
                        </td>
                        <td>
                            <span class="badge <?php echo $rule['type'] === 'per_stop' ? 'bg-primary' : 'bg-info'; ?>">
                                <?php echo $rule['type']; ?>
                            </span>
                        </td>
                        <td>
                            <span class="status-badge <?php echo $rule['active'] ? 'status-active' : 'status-inactive'; ?>">
                                <?php echo $rule['active'] ? 'Activo' : 'Inactivo'; ?>
                            </span>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn btn-sm btn-outline-primary" title="Editar" onclick="editPrice(<?php echo $rule['id']; ?>)">
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
</div>

<div class="card mt-4">
    <div class="card-header">
        <h5 class="mb-0">Configuración Adicional</h5>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <h6>Recargos</h6>
                <div class="list-group">
                    <div class="list-group-item d-flex justify-content-between">
                        <span>Recargo nocturno (8pm - 6am)</span>
                        <strong><?php echo CURRENCY_SYMBOL; ?>20</strong>
                    </div>
                    <div class="list-group-item d-flex justify-content-between">
                        <span>Recargo fin de semana</span>
                        <strong><?php echo CURRENCY_SYMBOL; ?>15</strong>
                    </div>
                    <div class="list-group-item d-flex justify-content-between">
                        <span>Recargo zona extendida</span>
                        <strong><?php echo CURRENCY_SYMBOL; ?>30</strong>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <h6>Descuentos</h6>
                <div class="list-group">
                    <div class="list-group-item d-flex justify-content-between">
                        <span>Cliente frecuente (>10 pedidos/mes)</span>
                        <strong>10%</strong>
                    </div>
                    <div class="list-group-item d-flex justify-content-between">
                        <span>Pago con billetera móvil</span>
                        <strong>5%</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include VIEW_PATH . '/Plantillas/pieAdmin.php'; ?>

<script>
function editPrice(id) {
    window.location.href = '?page=pricing&action=edit&id=' + id;
}
</script>