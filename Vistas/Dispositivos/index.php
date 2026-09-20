<?php include VIEW_PATH . '/Plantillas/encabezadoAdmin.php'; ?>
<?php include VIEW_PATH . '/Plantillas/barraLateralAdmin.php'; ?>

<div class="page-header">
    <h1 class="page-title">Dispositivos</h1>
    <div class="page-actions">
        <button class="btn btn-primary" onclick="window.location.href='?page=devices&action=create'">
            <i class="bi bi-plus-lg"></i> Nuevo Dispositivo
        </button>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon"><i class="bi bi-phone" style="color:var(--primary);"></i></div>
            <div>
                <div class="stat-value" style="color:var(--primary);"><?php echo count(Dispositivo::all()); ?></div>
                <div class="stat-label">Total Dispositivos</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon"><i class="bi bi-check-circle" style="color:var(--success);"></i></div>
            <div>
                <div class="stat-value" style="color:var(--success);"><?php echo count(array_filter(Dispositivo::all(), fn($d) => $d['status'] === 'active')); ?></div>
                <div class="stat-label">Activos</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon"><i class="bi bi-x-circle" style="color:var(--destructive);"></i></div>
            <div>
                <div class="stat-value" style="color:var(--destructive);"><?php echo count(array_filter(Dispositivo::all(), fn($d) => $d['status'] === 'inactive')); ?></div>
                <div class="stat-label">Inactivos</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon"><i class="bi bi-clock" style="color:var(--warning);"></i></div>
            <div>
                <div class="stat-value" style="color:var(--warning);">--</div>
                <div class="stat-label">Última Conexión</div>
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
                        <th>Usuario</th>
                        <th>Tipo</th>
                        <th>Estado</th>
                        <th>Última Conexión</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (Dispositivo::all() as $device): ?>
                    <tr>
                        <td><strong><?php echo $device['id']; ?></strong></td>
                        <td><?php echo $device['name']; ?></td>
                        <td><?php echo $device['user']; ?></td>
                        <td><?php echo $device['type']; ?></td>
                        <td>
                            <span class="status-badge <?php echo $device['status'] === 'active' ? 'status-active' : 'status-inactive'; ?>">
                                <?php echo ucfirst($device['status']); ?>
                            </span>
                        </td>
                        <td class="text-muted"><?php echo date('d/m/Y H:i', strtotime($device['last_seen'])); ?></td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn btn-sm btn-outline-primary" title="Ver" onclick="viewDevice(<?php echo $device['id']; ?>)">
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