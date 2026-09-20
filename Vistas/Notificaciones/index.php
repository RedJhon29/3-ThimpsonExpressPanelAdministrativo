<?php include VIEW_PATH . '/Plantillas/encabezadoAdmin.php'; ?>
<?php include VIEW_PATH . '/Plantillas/barraLateralAdmin.php'; ?>

<div class="page-header">
    <h1 class="page-title">Notificaciones</h1>
    <div class="page-actions">
        <button class="btn btn-primary" onclick="window.location.href='?page=notifications&action=create'">
            <i class="bi bi-plus-lg"></i> Nueva Notificación
        </button>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Notificaciones Recientes</h5>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    <?php foreach ($notifications as $notif): ?>
                    <a href="#" class="list-group-item list-group-item-action <?php echo !$notif['read'] ? 'bg-light' : ''; ?>">
                        <div class="d-flex align-items-start">
                            <div class="avatar avatar-sm me-3 <?php echo $notif['type'] === 'order_update' ? 'bg-primary' : ($notif['type'] === 'new_rider' ? 'bg-success' : 'bg-info'); ?>">
                                <i class="bi <?php echo $notif['type'] === 'order_update' ? 'bi-box-seam' : ($notif['type'] === 'new_rider' ? 'bi-person-plus' : 'bi-cash'); ?>"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between">
                                    <div class="fw-medium"><?php echo $notif['message']; ?></div>
                                    <small class="text-muted"><?php echo $notif['time']; ?></small>
                                </div>
                                <div class="d-flex gap-2 mt-1">
                                    <span class="badge bg-light text-dark"><?php echo $notif['type']; ?></span>
                                    <?php if (!$notif['read']): ?>
                                    <span class="badge bg-primary">No leída</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Resumen</h5>
            </div>
            <div class="card-body">
                <div class="row g-2 text-center">
                    <div class="col-4">
                        <div class="stat-value"><?php echo count($notifications); ?></div>
                        <div class="stat-label">Total</div>
                    </div>
                    <div class="col-4">
                        <div class="stat-value text-primary"><?php echo count(array_filter($notifications, fn($n) => !$n['read'])); ?></div>
                        <div class="stat-label">No leídas</div>
                    </div>
                    <div class="col-4">
                        <div class="stat-value text-success"><?php echo count(array_filter($notifications, fn($n) => $n['read'])); ?></div>
                        <div class="stat-label">Leídas</div>
                    </div>
                </div>
                <hr>
                <button class="btn btn-outline-secondary w-100" onclick="markAllRead()">
                    <i class="bi bi-check-all"></i> Marcar todas como leídas
                </button>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Reglas de Notificación</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table datatable" style="width:100%">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Evento</th>
                        <th>Acción</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rules as $rule): ?>
                    <tr>
                        <td><strong><?php echo $rule['id']; ?></strong></td>
                        <td><?php echo $rule['event']; ?></td>
                        <td><?php echo $rule['action']; ?></td>
                        <td>
                            <label class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" <?php echo $rule['active'] ? 'checked' : ''; ?> onchange="toggleRule(<?php echo $rule['id']; ?>, this.checked)">
                            </label>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn btn-sm btn-outline-primary" title="Editar" onclick="editRule(<?php echo $rule['id']; ?>)">
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

<?php include VIEW_PATH . '/Plantillas/pieAdmin.php'; ?>

<script>
function markAllRead() {
    alert('Todas las notificaciones marcadas como leídas (simulado)');
}

function toggleRule(id, active) {
    console.log('Regla ' + id + ' ' + (active ? 'activada' : 'desactivada'));
}

function editRule(id) {
    window.location.href = '?page=notifications&action=edit_rule&id=' + id;
}
</script>