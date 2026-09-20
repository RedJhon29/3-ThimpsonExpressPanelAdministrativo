<?php include VIEW_PATH . '/Plantillas/encabezadoAdmin.php'; ?>
<?php include VIEW_PATH . '/Plantillas/barraLateralAdmin.php'; ?>

<?php
$orderId = $_GET['id'] ?? null;
$order = $orderId ? Pedido::findById($orderId) : null;
?>

<?php if (!$order): ?>
    <div class="alert alert-danger">
        <i class="bi bi-exclamation-triangle"></i> Pedido no encontrado
    </div>
    <a href="?page=orders" class="btn btn-primary">Volver a la lista</a>
<?php else: ?>
<div class="page-header">
    <h1 class="page-title">Detalle de Pedido</h1>
    <div class="page-actions">
        <a href="?page=orders" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
        <button class="btn btn-primary" onclick="editOrder('<?php echo $order['id']; ?>')">
            <i class="bi bi-pencil"></i> Editar
        </button>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Información del Pedido</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">ID del Pedido</label>
                        <p class="form-control-plaintext fw-bold"><?php echo $order['id']; ?></p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Estado</label>
                        <p class="form-control-plaintext">
                            <span class="status-badge <?php echo strtolower($order['status']); ?>">
                                <?php echo $order['status']; ?>
                            </span>
                        </p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Cliente</label>
                        <p class="form-control-plaintext"><?php echo $order['client']; ?></p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Rider Asignado</label>
                        <p class="form-control-plaintext"><?php echo $order['rider'] ?? '<span class="text-muted">Sin asignar</span>'; ?></p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Origen</label>
                        <p class="form-control-plaintext"><?php echo $order['origin']; ?></p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Destino</label>
                        <p class="form-control-plaintext"><?php echo $order['destination']; ?></p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Costo</label>
                        <p class="form-control-plaintext fw-bold" style="font-family:var(--font-mono); color:var(--success);">
                            <?php echo CURRENCY_SYMBOL; ?><?php echo number_format($order['cost']); ?>
                        </p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Distancia</label>
                        <p class="form-control-plaintext"><?php echo $order['distance']; ?> km</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Fecha de Creación</label>
                        <p class="form-control-plaintext"><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Acciones Rápidas</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <button class="btn btn-success" onclick="updateStatus('<?php echo $order['id']; ?>', 'PICKED_UP')">
                        <i class="bi bi-box-seam"></i> Marcar como Recolectado
                    </button>
                    <button class="btn btn-warning" onclick="updateStatus('<?php echo $order['id']; ?>', 'IN_TRANSIT')">
                        <i class="bi bi-truck"></i> Marcar en Camino
                    </button>
                    <button class="btn btn-primary" onclick="updateStatus('<?php echo $order['id']; ?>', 'DELIVERED')">
                        <i class="bi bi-check-circle"></i> Marcar como Entregado
                    </button>
                    <button class="btn btn-outline-danger" onclick="cancelOrder('<?php echo $order['id']; ?>')">
                        <i class="bi bi-x-circle"></i> Cancelar Pedido
                    </button>
                </div>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <h5 class="mb-0">Historial de Estados</h5>
            </div>
            <div class="card-body">
                <div class="timeline">
                    <div class="timeline-item">
                        <div class="timeline-marker bg-primary"></div>
                        <div class="timeline-content">
                            <small class="text-muted">Creado</small>
                            <p class="mb-0 fw-medium"><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></p>
                        </div>
                    </div>
                    <?php if (in_array($order['status'], ['PICKED_UP', 'IN_TRANSIT', 'DELIVERED'])): ?>
                    <div class="timeline-item">
                        <div class="timeline-marker bg-info"></div>
                        <div class="timeline-content">
                            <small class="text-muted">Recolectado</small>
                            <p class="mb-0 fw-medium"><?php echo date('d/m/Y H:i', strtotime($order['created_at'] . ' +15 minutes')); ?></p>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php if (in_array($order['status'], ['IN_TRANSIT', 'DELIVERED'])): ?>
                    <div class="timeline-item">
                        <div class="timeline-marker bg-warning"></div>
                        <div class="timeline-content">
                            <small class="text-muted">En Camino</small>
                            <p class="mb-0 fw-medium"><?php echo date('d/m/Y H:i', strtotime($order['created_at'] . ' +30 minutes')); ?></p>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php if ($order['status'] === 'DELIVERED'): ?>
                    <div class="timeline-item">
                        <div class="timeline-marker bg-success"></div>
                        <div class="timeline-content">
                            <small class="text-muted">Entregado</small>
                            <p class="mb-0 fw-medium"><?php echo date('d/m/Y H:i', strtotime($order['created_at'] . ' +1 hour')); ?></p>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php include VIEW_PATH . '/Plantillas/pieAdmin.php'; ?>

<script>
function updateStatus(id, status) {
    if (confirm('¿Confirmar cambio de estado a ' + status + '?')) {
        window.location.href = '?page=orders&action=updateStatus&id=' + id + '&status=' + status;
    }
}

function cancelOrder(id) {
    if (confirm('¿Cancelar este pedido? Esta acción no se puede deshacer.')) {
        window.location.href = '?page=orders&action=cancel&id=' + id;
    }
}

function editOrder(id) {
    window.location.href = '?page=orders&action=edit&id=' + id;
}
</script>

<style>
.timeline {
    position: relative;
    padding-left: 1.5rem;
}
.timeline::before {
    content: '';
    position: absolute;
    left: 0.5rem;
    top: 0;
    bottom: 0;
    width: 2px;
    background: var(--border);
}
.timeline-item {
    position: relative;
    padding-bottom: 1.5rem;
    display: flex;
    gap: 1rem;
}
.timeline-marker {
    width: 1rem;
    height: 1rem;
    border-radius: 50%;
    flex-shrink: 0;
    margin-top: 0.125rem;
}
.timeline-content {
    flex: 1;
}
</style>