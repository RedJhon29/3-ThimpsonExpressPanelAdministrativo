<?php include VIEW_PATH . '/Plantillas/encabezadoAdmin.php'; ?>
<?php include VIEW_PATH . '/Plantillas/barraLateralAdmin.php'; ?>

<div class="page-header">
    <h1 class="page-title">Conversaciones</h1>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon"><i class="bi bi-chat" style="color:var(--primary);"></i></div>
            <div>
                <div class="stat-value" style="color:var(--primary);"><?php echo count(Conversacion::all()); ?></div>
                <div class="stat-label">Conversaciones</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon"><i class="bi bi-chat-dots" style="color:var(--success);"></i></div>
            <div>
                <div class="stat-value" style="color:var(--success);"><?php echo count(array_filter(Conversacion::all(), fn($c) => $c['status'] === 'open')); ?></div>
                <div class="stat-label">Abiertas</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon"><i class="bi bi-chat-x" style="color:var(--destructive);"></i></div>
            <div>
                <div class="stat-value" style="color:var(--destructive);"><?php echo count(array_filter(Conversacion::all(), fn($c) => $c['status'] === 'closed')); ?></div>
                <div class="stat-label">Cerradas</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon"><i class="bi bi-bell" style="color:var(--warning);"></i></div>
            <div>
                <div class="stat-value" style="color:var(--warning);"><?php echo array_sum(array_column(Conversacion::all(), 'unread')); ?></div>
                <div class="stat-label">No Leídos</div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="list-group list-group-flush">
            <?php foreach (Conversacion::all() as $conv): ?>
            <a href="?page=conversations&action=detail&id=<?php echo $conv['id']; ?>" class="list-group-item list-group-item-action <?php echo $conv['unread'] > 0 ? 'fw-bold bg-light' : ''; ?>">
                <div class="d-flex align-items-center">
                    <div class="avatar avatar-md me-3">
                        <i class="bi bi-person"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between">
                            <div class="fw-medium"><?php echo $conv['client']; ?></div>
                            <small class="text-muted"><?php echo $conv['time']; ?></small>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted text-truncate" style="max-width:400px;"><?php echo $conv['last_message']; ?></small>
                            <div class="d-flex gap-2">
                                <?php if ($conv['unread'] > 0): ?>
                                <span class="badge bg-primary"><?php echo $conv['unread']; ?> nuevos</span>
                                <?php endif; ?>
                                <span class="badge <?php echo $conv['status'] === 'open' ? 'bg-success' : 'bg-secondary'; ?>"><?php echo $conv['status']; ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php include VIEW_PATH . '/Plantillas/pieAdmin.php'; ?>