<?php include VIEW_PATH . '/Plantillas/encabezadoAdmin.php'; ?>
<?php include VIEW_PATH . '/Plantillas/barraLateralAdmin.php'; ?>

<div class="page-header">
    <h1 class="page-title">Suscriptores Newsletter</h1>
    <div class="page-actions">
        <button class="btn btn-primary" onclick="exportSubscribers()">
            <i class="bi bi-download"></i> Exportar
        </button>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon"><i class="bi bi-envelope" style="color:var(--primary);"></i></div>
            <div>
                <div class="stat-value" style="color:var(--primary);"><?php echo count(Suscriptor::all()); ?></div>
                <div class="stat-label">Total Suscriptores</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon"><i class="bi bi-check-circle" style="color:var(--success);"></i></div>
            <div>
                <div class="stat-value" style="color:var(--success);"><?php echo count(array_filter(Suscriptor::all(), fn($s) => $s['status'] === 'active')); ?></div>
                <div class="stat-label">Activos</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon"><i class="bi bi-x-circle" style="color:var(--destructive);"></i></div>
            <div>
                <div class="stat-value" style="color:var(--destructive);"><?php echo count(array_filter(Suscriptor::all(), fn($s) => $s['status'] === 'unsubscribed')); ?></div>
                <div class="stat-label">Darse de baja</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon"><i class="bi bi-calendar" style="color:var(--info);"></i></div>
            <div>
                <div class="stat-value" style="color:var(--info);"><?php echo date('d/m/Y', strtotime(min(array_column(Suscriptor::all(), 'subscribed_at')))); ?></div>
                <div class="stat-label">Primer Suscriptor</div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table datatable" style="width:100%">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Estado</th>
                        <th>Fecha Suscripción</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (Suscriptor::all() as $sub): ?>
                    <tr>
                        <td><strong><?php echo $sub['id']; ?></strong></td>
                        <td><?php echo $sub['name']; ?></td>
                        <td><?php echo $sub['email']; ?></td>
                        <td>
                            <span class="status-badge <?php echo $sub['status'] === 'active' ? 'status-active' : 'status-inactive'; ?>">
                                <?php echo ucfirst($sub['status']); ?>
                            </span>
                        </td>
                        <td class="text-muted"><?php echo date('d/m/Y', strtotime($sub['subscribed_at'])); ?></td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn btn-sm btn-outline-danger" title="Eliminar" onclick="deleteSubscriber(<?php echo $sub['id']; ?>)">
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

<?php include VIEW_PATH . '/Plantillas/pieAdmin.php'; ?>

<script>
function exportSubscribers() {
    alert('Exportando lista de suscriptores (simulado)');
}

function deleteSubscriber(id) {
    if (confirm('¿Eliminar este suscriptor?')) {
        window.location.href = '?page=subscribers&action=delete&id=' + id;
    }
}
</script>