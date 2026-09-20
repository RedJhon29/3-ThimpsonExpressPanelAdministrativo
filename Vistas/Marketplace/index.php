<?php include VIEW_PATH . '/Plantillas/encabezadoAdmin.php'; ?>
<?php include VIEW_PATH . '/Plantillas/barraLateralAdmin.php'; ?>

<div class="page-header">
    <h1 class="page-title">Marketplace / Negocios</h1>
    <div class="page-actions">
        <button class="btn btn-primary" onclick="window.location.href='?page=marketplace&action=create'">
            <i class="bi bi-plus-lg"></i> Nuevo Negocio
        </button>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon"><i class="bi bi-shop" style="color:var(--primary);"></i></div>
            <div>
                <div class="stat-value" style="color:var(--primary);"><?php echo count(Negocio::all()); ?></div>
                <div class="stat-label">Total Negocios</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon"><i class="bi bi-check-circle" style="color:var(--success);"></i></div>
            <div>
                <div class="stat-value" style="color:var(--success);"><?php echo count(array_filter(Negocio::all(), fn($n) => $n['status'] === 'active')); ?></div>
                <div class="stat-label">Activos</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon"><i class="bi bi-star" style="color:var(--warning);"></i></div>
            <div>
                <div class="stat-value" style="color:var(--warning);"><?php echo number_format(array_sum(array_column(Negocio::all(), 'rating')) / count(Negocio::all()), 1); ?></div>
                <div class="stat-label">Rating Promedio</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon"><i class="bi bi-chat" style="color:var(--info);"></i></div>
            <div>
                <div class="stat-value" style="color:var(--info);"><?php echo array_sum(array_column(Negocio::all(), 'reviews')); ?></div>
                <div class="stat-label">Total Reseñas</div>
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
                        <th>Categoría</th>
                        <th>Rating</th>
                        <th>Reseñas</th>
                        <th>Plan</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (Negocio::all() as $biz): ?>
                    <tr>
                        <td><strong><?php echo $biz['id']; ?></strong></td>
                        <td><?php echo $biz['name']; ?></td>
                        <td><?php echo $biz['category']; ?></td>
                        <td>
                            <span class="fw-bold" style="color:var(--warning);"><?php echo $biz['rating']; ?></span>
                            <i class="bi bi-star-fill" style="color:var(--warning);"></i>
                        </td>
                        <td><?php echo number_format($biz['reviews']); ?></td>
                        <td>
                            <span class="badge <?php echo $biz['plan'] === 'premium' ? 'bg-primary' : 'bg-secondary'; ?>">
                                <?php echo ucfirst($biz['plan']); ?>
                            </span>
                        </td>
                        <td>
                            <span class="status-badge <?php echo $biz['status'] === 'active' ? 'status-active' : 'status-inactive'; ?>">
                                <?php echo ucfirst($biz['status']); ?>
                            </span>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn btn-sm btn-outline-primary" title="Ver" onclick="viewBiz(<?php echo $biz['id']; ?>)">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-secondary" title="Editar" onclick="editBiz(<?php echo $biz['id']; ?>)">
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
function viewBiz(id) {
    window.location.href = '?page=marketplace&action=detail&id=' + id;
}

function editBiz(id) {
    window.location.href = '?page=marketplace&action=edit&id=' + id;
}
</script>