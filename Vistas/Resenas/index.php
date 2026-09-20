<?php include VIEW_PATH . '/Plantillas/encabezadoAdmin.php'; ?>
<?php include VIEW_PATH . '/Plantillas/barraLateralAdmin.php'; ?>

<div class="page-header">
    <h1 class="page-title">Reseñas</h1>
    <div class="page-actions">
        <button class="btn btn-primary" onclick="window.location.href='?page=reviews&action=create'">
            <i class="bi bi-plus-lg"></i> Nueva Reseña
        </button>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon"><i class="bi bi-star" style="color:var(--warning);"></i></div>
            <div>
                <div class="stat-value" style="color:var(--warning);">4.7</div>
                <div class="stat-label">Promedio General</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon"><i class="bi bi-chat" style="color:var(--primary);"></i></div>
            <div>
                <div class="stat-value" style="color:var(--primary);">0</div>
                <div class="stat-label">Total Reseñas</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon"><i class="bi bi-check-circle" style="color:var(--success);"></i></div>
            <div>
                <div class="stat-value" style="color:var(--success);">0</div>
                <div class="stat-label">Aprobadas</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon"><i class="bi bi-clock" style="color:var(--warning);"></i></div>
            <div>
                <div class="stat-value" style="color:var(--warning);">0</div>
                <div class="stat-label">Pendientes</div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Reseñas Recientes</h5>
    </div>
    <div class="card-body p-0">
        <div class="text-center py-5">
            <i class="bi bi-star" style="font-size:4rem;color:var(--border);"></i>
            <h5 class="mt-3 text-muted">No hay reseñas aún</h5>
            <p class="text-muted">Las reseñas aparecerán aquí cuando los clientes las envíen</p>
        </div>
    </div>
</div>

<?php include VIEW_PATH . '/Plantillas/pieAdmin.php'; ?>