<?php include VIEW_PATH . '/Plantillas/encabezadoAdmin.php'; ?>
<?php include VIEW_PATH . '/Plantillas/barraLateralAdmin.php'; ?>

<div class="page-header">
    <h1 class="page-title">Suscripciones</h1>
    <div class="page-actions">
        <button class="btn btn-primary" onclick="window.location.href='?page=subscriptions&action=create'">
            <i class="bi bi-plus-lg"></i> Nueva Suscripción
        </button>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon"><i class="bi bi-credit-card" style="color:var(--primary);"></i></div>
            <div>
                <div class="stat-value" style="color:var(--primary);"><?php echo count(Suscripcion::all()); ?></div>
                <div class="stat-label">Total Suscripciones</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon"><i class="bi bi-check-circle" style="color:var(--success);"></i></div>
            <div>
                <div class="stat-value" style="color:var(--success);"><?php echo count(array_filter(Suscripcion::all(), fn($s) => $s['status'] === 'active')); ?></div>
                <div class="stat-label">Activas</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon"><i class="bi bi-cash" style="color:var(--warning);"></i></div>
            <div>
                <div class="stat-value" style="color:var(--warning);"><?php echo CURRENCY_SYMBOL; ?><?php echo number_format(array_sum(array_column(Suscripcion::all(), 'amount'))); ?></div>
                <div class="stat-label">MRR (Mensual)</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon"><i class="bi bi-arrow-repeat" style="color:var(--info);"></i></div>
            <div>
                <div class="stat-value" style="color:var(--info);"><?php echo count(array_filter(Suscripcion::all(), fn($s) => $s['cycle'] === 'yearly')); ?></div>
                <div class="stat-label">Anuales</div>
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
                        <th>Cliente</th>
                        <th>Plan</th>
                        <th>Ciclo</th>
                        <th>Monto</th>
                        <th>Estado</th>
                        <th>Próxima Renovación</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (Suscripcion::all() as $sub): ?>
                    <tr>
                        <td><strong><?php echo $sub['id']; ?></strong></td>
                        <td><?php echo $sub['client']; ?></td>
                        <td><span class="badge bg-primary"><?php echo $sub['plan']; ?></span></td>
                        <td><?php echo $sub['cycle']; ?></td>
                        <td style="font-family:var(--font-mono);"><?php echo CURRENCY_SYMBOL; ?><?php echo number_format($sub['amount']); ?></td>
                        <td>
                            <span class="status-badge <?php echo $sub['status'] === 'active' ? 'status-active' : 'status-inactive'; ?>">
                                <?php echo ucfirst($sub['status']); ?>
                            </span>
                        </td>
                        <td class="text-muted"><?php echo $sub['renewal'] !== 'N/A' ? date('d/m/Y', strtotime($sub['renewal'])) : 'N/A'; ?></td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn btn-sm btn-outline-primary" title="Ver" onclick="viewSub(<?php echo $sub['id']; ?>)">
                                    <i class="bi bi-eye"></i>
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