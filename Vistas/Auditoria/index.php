<?php include VIEW_PATH . '/Plantillas/encabezadoAdmin.php'; ?>
<?php include VIEW_PATH . '/Plantillas/barraLateralAdmin.php'; ?>

<div class="page-header">
    <h1 class="page-title">Auditoría / Logs del Sistema</h1>
    <div class="page-actions">
        <div class="input-group" style="width:300px;">
            <input type="text" class="form-control" placeholder="Filtrar por usuario, acción..." id="auditSearch">
            <button class="btn btn-outline-secondary" type="button" onclick="exportLogs()">
                <i class="bi bi-download"></i>
            </button>
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
                        <th>Usuario</th>
                        <th>Acción</th>
                        <th>Detalles</th>
                        <th>IP</th>
                        <th>Fecha/Hora</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logs as $log): ?>
                    <tr>
                        <td><strong><?php echo $log['id']; ?></strong></td>
                        <td>
                            <span class="badge <?php echo $log['user'] === 'System' ? 'bg-info' : 'bg-primary'; ?>">
                                <?php echo $log['user']; ?>
                            </span>
                        </td>
                        <td><?php echo $log['action']; ?></td>
                        <td><?php echo $log['details']; ?></td>
                        <td style="font-family:var(--font-mono);font-size:0.85rem;"><?php echo $log['ip']; ?></td>
                        <td class="text-muted"><?php echo date('d/m/Y H:i', strtotime($log['time'])); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header">
        <h5 class="mb-0">Estadísticas</h5>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <?php
            $actions = array_column($logs, 'action');
            $users = array_column($logs, 'user');
            $actionCounts = array_count_values($actions);
            $userCounts = array_count_values($users);
            ?>
            <div class="col-md-6">
                <h6>Por Acción</h6>
                <div class="row g-2">
                    <?php foreach ($actionCounts as $action => $count): ?>
                    <div class="col-4">
                        <div class="stat-card text-center py-2">
                            <div class="stat-value"><?php echo $count; ?></div>
                            <div class="stat-label small"><?php echo $action; ?></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="col-md-6">
                <h6>Por Usuario</h6>
                <div class="row g-2">
                    <?php foreach ($userCounts as $user => $count): ?>
                    <div class="col-4">
                        <div class="stat-card text-center py-2">
                            <div class="stat-value"><?php echo $count; ?></div>
                            <div class="stat-label small"><?php echo $user; ?></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include VIEW_PATH . '/Plantillas/pieAdmin.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('auditSearch');
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

function exportLogs() {
    alert('Exportando logs (simulado)');
}
</script>