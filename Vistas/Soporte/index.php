<?php include VIEW_PATH . '/Plantillas/encabezadoAdmin.php'; ?>
<?php include VIEW_PATH . '/Plantillas/barraLateralAdmin.php'; ?>

<div class="page-header">
    <h1 class="page-title">Soporte / Tickets</h1>
    <div class="page-actions">
        <button class="btn btn-primary" onclick="window.location.href='?page=support&action=create'">
            <i class="bi bi-plus-lg"></i> Nuevo Ticket
        </button>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon"><i class="bi bi-ticket-perforated" style="color:var(--primary);"></i></div>
            <div>
                <div class="stat-value" style="color:var(--primary);"><?php echo count($tickets); ?></div>
                <div class="stat-label">Total Tickets</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon"><i class="bi bi-folder" style="color:var(--success);"></i></div>
            <div>
                <div class="stat-value" style="color:var(--success);"><?php echo count(array_filter($tickets, fn($t) => $t['status'] === 'open')); ?></div>
                <div class="stat-label">Abiertos</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon"><i class="bi bi-folder-check" style="color:var(--info);"></i></div>
            <div>
                <div class="stat-value" style="color:var(--info);"><?php echo count(array_filter($tickets, fn($t) => $t['status'] === 'closed')); ?></div>
                <div class="stat-label">Cerrados</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon"><i class="bi bi-exclamation-triangle" style="color:var(--destructive);"></i></div>
            <div>
                <div class="stat-value" style="color:var(--destructive);"><?php echo count(array_filter($tickets, fn($t) => $t['priority'] === 'high')); ?></div>
                <div class="stat-label">Alta Prioridad</div>
            </div>
        </div>
    </div>
</div>

<div class="table-container">
    <div class="table-header">
        <h5 class="mb-0">Lista de Tickets</h5>
        <div class="table-search">
            <input type="text" class="form-control" placeholder="Buscar tickets..." id="ticketsSearch">
        </div>
    </div>
    <div class="table-responsive">
        <table class="table datatable" style="width:100%">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Cliente</th>
                    <th>Asunto</th>
                    <th>Estado</th>
                    <th>Prioridad</th>
                    <th>Fecha</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tickets as $ticket): ?>
                <tr>
                    <td><strong>#<?php echo $ticket['id']; ?></strong></td>
                    <td><?php echo $ticket['client']; ?></td>
                    <td><?php echo $ticket['subject']; ?></td>
                    <td>
                        <span class="status-badge <?php echo $ticket['status'] === 'open' ? 'status-active' : 'status-inactive'; ?>">
                            <?php echo ucfirst($ticket['status']); ?>
                        </span>
                    </td>
                    <td>
                        <span class="badge <?php 
                            echo $ticket['priority'] === 'high' ? 'bg-danger' : 
                                ($ticket['priority'] === 'medium' ? 'bg-warning' : 'bg-info'); 
                        ?>">
                            <?php echo ucfirst($ticket['priority']); ?>
                        </span>
                    </td>
                    <td class="text-muted"><?php echo date('d/m/Y H:i', strtotime($ticket['created'])); ?></td>
                    <td>
                        <div class="action-buttons">
                            <button class="btn btn-sm btn-outline-primary" title="Ver/Responder" onclick="viewTicket(<?php echo $ticket['id']; ?>)">
                                <i class="bi bi-eye"></i>
                            </button>
                            <?php if ($ticket['status'] === 'open'): ?>
                            <button class="btn btn-sm btn-outline-success" title="Cerrar" onclick="closeTicket(<?php echo $ticket['id']; ?>)">
                                <i class="bi bi-check-circle"></i>
                            </button>
                            <?php endif; ?>
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
    const searchInput = document.getElementById('ticketsSearch');
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

function viewTicket(id) {
    window.location.href = '?page=support&action=detail&id=' + id;
}

function closeTicket(id) {
    if (confirm('¿Cerrar este ticket?')) {
        window.location.href = '?page=support&action=close&id=' + id;
    }
}
</script>