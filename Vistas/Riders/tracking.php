<?php include VIEW_PATH . '/Plantillas/encabezadoAdmin.php'; ?>
<?php include VIEW_PATH . '/Plantillas/barraLateralAdmin.php'; ?>

<div class="page-header">
    <h1 class="page-title">Tracking en Vivo</h1>
    <div class="page-actions">
        <a href="?page=riders" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Mapa de Riders Activos</h5>
            </div>
            <div class="card-body p-0">
                <div id="liveMap" style="height:500px;background:var(--bg-tertiary);">
                    <div class="d-flex align-items-center justify-content-center h-100">
                        <div class="text-center text-muted">
                            <i class="bi bi-map" style="font-size:4rem;"></i>
                            <p class="mt-3">Mapa de tracking en tiempo real</p>
                            <small>Integrar con Leaflet/Google Maps API</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Riders Activos (<?php echo count(array_filter($riders, fn($r) => $r['status'] === 'active')); ?>)</h5>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    <?php foreach ($riders as $rider): ?>
                    <?php if ($rider['status'] === 'active'): ?>
                    <a href="?page=riders&action=detail&id=<?php echo $rider['id']; ?>" class="list-group-item list-group-item-action">
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-sm me-3">
                                <i class="bi bi-person-video3"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="fw-medium"><?php echo $rider['name']; ?></div>
                                <small class="text-muted"><?php echo $rider['vehicle']; ?> - <?php echo $rider['plate']; ?></small>
                            </div>
                            <div class="text-end">
                                <div class="badge bg-success">En línea</div>
                                <small class="text-muted d-block mt-1"><?php echo $rider['rating']; ?> <i class="bi bi-star-fill" style="color:var(--warning);"></i></small>
                            </div>
                        </div>
                    </a>
                    <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <h5 class="mb-0">Controles</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <button class="btn btn-outline-primary" onclick="refreshMap()">
                        <i class="bi bi-arrow-clockwise"></i> Actualizar posiciones
                    </button>
                    <button class="btn btn-outline-secondary" onclick="centerMap()">
                        <i class="bi bi-crosshair"></i> Centrar mapa
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include VIEW_PATH . '/Plantillas/pieAdmin.php'; ?>

<script>
function refreshMap() {
    console.log('Actualizando posiciones de riders...');
    // Aquí se integraría la llamada a la API de tracking
}

function centerMap() {
    console.log('Centrando mapa...');
    // Aquí se centraría el mapa en la posición promedio
}
</script>