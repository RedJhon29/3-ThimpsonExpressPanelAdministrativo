<?php include VIEW_PATH . '/Plantillas/encabezadoAdmin.php'; ?>
<?php include VIEW_PATH . '/Plantillas/barraLateralAdmin.php'; ?>

<div class="page-header">
    <h1 class="page-title">Promociones</h1>
    <div class="page-actions">
        <button class="btn btn-primary" onclick="window.location.href='?page=promotions&action=create'">
            <i class="bi bi-plus-lg"></i> Nueva Promoción
        </button>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Promociones Activas</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table datatable" style="width:100%">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Descuento</th>
                        <th>Tipo</th>
                        <th>Fecha Inicio</th>
                        <th>Fecha Fin</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (Promocion::all() as $promo): ?>
                    <tr>
                        <td><strong><?php echo $promo['id']; ?></strong></td>
                        <td><?php echo $promo['name']; ?></td>
                        <td>
                            <?php if ($promo['type'] === 'percentage'): ?>
                                <strong><?php echo $promo['discount']; ?>%</strong>
                            <?php else: ?>
                                <strong><?php echo CURRENCY_SYMBOL; ?><?php echo number_format($promo['discount']); ?></strong>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge <?php echo $promo['type'] === 'percentage' ? 'bg-primary' : 'bg-success'; ?>">
                                <?php echo $promo['type']; ?>
                            </span>
                        </td>
                        <td><?php echo date('d/m/Y', strtotime($promo['start_date'])); ?></td>
                        <td><?php echo date('d/m/Y', strtotime($promo['end_date'])); ?></td>
                        <td>
                            <span class="status-badge <?php echo $promo['status'] === 'active' ? 'status-active' : 'status-inactive'; ?>">
                                <?php echo ucfirst($promo['status']); ?>
                            </span>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn btn-sm btn-outline-primary" title="Editar" onclick="editPromo(<?php echo $promo['id']; ?>)">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger" title="Eliminar" onclick="deletePromo(<?php echo $promo['id']; ?>)">
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
</div>

<div class="card mt-4">
    <div class="card-header">
        <h5 class="mb-0">Crear Nueva Promoción</h5>
    </div>
    <div class="card-body">
        <form id="promoForm" class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Nombre</label>
                <input type="text" class="form-control" name="name" placeholder="ej. 20% en tu primer pedido">
            </div>
            <div class="col-md-3">
                <label class="form-label">Descuento</label>
                <input type="number" class="form-control" name="discount" placeholder="20">
            </div>
            <div class="col-md-3">
                <label class="form-label">Tipo</label>
                <select class="form-select" name="type">
                    <option value="percentage">Porcentaje</option>
                    <option value="fixed_amount">Monto Fijo</option>
                    <option value="free_shipping">Envío Gratis</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Fecha Inicio</label>
                <input type="date" class="form-control" name="start_date" value="<?php echo date('Y-m-d'); ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label">Fecha Fin</label>
                <input type="date" class="form-control" name="end_date" value="<?php echo date('Y-m-d', strtotime('+30 days')); ?>">
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> Crear Promoción
                </button>
            </div>
        </form>
    </div>
</div>

<?php include VIEW_PATH . '/Plantillas/pieAdmin.php'; ?>

<script>
function editPromo(id) {
    window.location.href = '?page=promotions&action=edit&id=' + id;
}

function deletePromo(id) {
    if (confirm('¿Eliminar esta promoción?')) {
        window.location.href = '?page=promotions&action=delete&id=' + id;
    }
}
</script>