<?php include VIEW_PATH . '/Plantillas/encabezadoAdmin.php'; ?>
<?php include VIEW_PATH . '/Plantillas/barraLateralAdmin.php'; ?>

<div class="page-header">
    <h1 class="page-title">Zonas de Cobertura</h1>
    <div class="page-actions">
        <button class="btn btn-primary" onclick="window.location.href='?page=zones&action=create'">
            <i class="bi bi-plus-lg"></i> Nueva Zona
        </button>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon"><i class="bi bi-geo-alt" style="color:var(--primary);"></i></div>
            <div>
                <div class="stat-value" style="color:var(--primary);"><?php echo count(Zona::all()); ?></div>
                <div class="stat-label">Total Zonas</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon"><i class="bi bi-check-circle" style="color:var(--success);"></i></div>
            <div>
                <div class="stat-value" style="color:var(--success);"><?php echo count(array_filter(Zona::all(), fn($z) => $z['status'] === 'active')); ?></div>
                <div class="stat-label">Activas</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon"><i class="bi bi-x-circle" style="color:var(--destructive);"></i></div>
            <div>
                <div class="stat-value" style="color:var(--destructive);"><?php echo count(array_filter(Zona::all(), fn($z) => $z['status'] === 'inactive')); ?></div>
                <div class="stat-label">Inactivas</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon"><i class="bi bi-arrows-angle-expand" style="color:var(--info);"></i></div>
            <div>
                <div class="stat-value" style="color:var(--info);"><?php echo array_sum(array_column(Zona::all(), 'radius')); ?> km</div>
                <div class="stat-label">Radio Total</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Mapa de Zonas</h5>
            </div>
            <div class="card-body p-0">
                <div id="zonesMap" style="height:400px;background:var(--bg-tertiary);border-radius:var(--radius);display:flex;align-items:center;justify-content:center;">
                    <div class="text-center text-muted">
                        <i class="bi bi-map" style="font-size:3rem;"></i>
                        <p class="mt-2">Mapa de zonas de cobertura</p>
                        <small>Integrar con Leaflet/Google Maps para visualizar círculos</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Lista de Zonas</h5>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    <?php foreach (Zona::all() as $zone): ?>
                    <div class="list-group-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-medium"><?php echo $zone['name']; ?></div>
                                <small class="text-muted">Radio: <?php echo $zone['radius']; ?> km</small>
                            </div>
                            <span class="status-badge <?php echo $zone['status'] === 'active' ? 'status-active' : 'status-inactive'; ?>">
                                <?php echo ucfirst($zone['status']); ?>
                            </span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <h5 class="mb-0">Crear Nueva Zona</h5>
            </div>
            <div class="card-body">
                <form id="zoneForm" class="row g-3">
                    <div class="col-12">
                        <label class="form-label">Nombre</label>
                        <input type="text" class="form-control" name="name" placeholder="ej. Zona Sur">
                    </div>
                    <div class="col-6">
                        <label class="form-label">Latitud</label>
                        <input type="number" step="any" class="form-control" name="lat" placeholder="13.1234">
                    </div>
                    <div class="col-6">
                        <label class="form-label">Longitud</label>
                        <input type="number" step="any" class="form-control" name="lng" placeholder="-86.1234">
                    </div>
                    <div class="col-6">
                        <label class="form-label">Radio (km)</label>
                        <input type="number" class="form-control" name="radius" value="5" min="1" max="100">
                    </div>
                    <div class="col-6">
                        <label class="form-label">Estado</label>
                        <select class="form-select" name="status">
                            <option value="active">Activa</option>
                            <option value="inactive">Inactiva</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-save"></i> Crear Zona
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include VIEW_PATH . '/Plantillas/pieAdmin.php'; ?>

<script>
document.getElementById('zoneForm').addEventListener('submit', function(e) {
    e.preventDefault();
    alert('Zona creada (simulado)');
});
</script>