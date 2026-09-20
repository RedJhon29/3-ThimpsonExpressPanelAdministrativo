<?php include VIEW_PATH . '/Plantillas/encabezadoAdmin.php'; ?>
<?php include VIEW_PATH . '/Plantillas/barraLateralAdmin.php'; ?>

<?php
$riderId = $_GET['id'] ?? null;
$rider = $riderId ? Motorizado::find($riderId) : null;
?>

<?php if (!$rider): ?>
    <div class="alert alert-danger">
        <i class="bi bi-exclamation-triangle"></i> Rider no encontrado
    </div>
    <a href="?page=riders" class="btn btn-primary">Volver a la lista</a>
<?php else: ?>
<div class="page-header">
    <h1 class="page-title">Detalle de Rider</h1>
    <div class="page-actions">
        <a href="?page=riders" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
        <button class="btn btn-primary" onclick="editRider(<?php echo $rider['id']; ?>)">
            <i class="bi bi-pencil"></i> Editar
        </button>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-4">
        <div class="card">
            <div class="card-body text-center">
                <div class="avatar avatar-xl mx-auto mb-3">
                    <i class="bi bi-person-video3" style="font-size:2rem;"></i>
                </div>
                <h4 class="mb-1"><?php echo $rider['name']; ?></h4>
                <span class="status-badge <?php echo $rider['status'] === 'active' ? 'status-active' : 'status-inactive'; ?>">
                    <?php echo ucfirst($rider['status']); ?>
                </span>
                <div class="mt-3">
                    <div class="rating-display">
                        <span class="fw-bold fs-4" style="color:var(--warning);"><?php echo $rider['rating']; ?></span>
                        <i class="bi bi-star-fill" style="color:var(--warning);"></i>
                    </div>
                    <small class="text-muted"><?php echo number_format($rider['deliveries']); ?> entregas completadas</small>
                </div>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <h5 class="mb-0">Información de Contacto</h5>
            </div>
            <div class="card-body">
                <div class="row g-2 text-center">
                    <div class="col-6">
                        <div class="stat-value"><?php echo $rider['phone']; ?></div>
                        <div class="stat-label">Teléfono</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <h5 class="mb-0">Vehículo</h5>
            </div>
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-6">
                        <div class="stat-value"><?php echo $rider['vehicle']; ?></div>
                        <div class="stat-label">Tipo</div>
                    </div>
                    <div class="col-6">
                        <div class="stat-value" style="font-family:var(--font-mono);"><?php echo $rider['plate']; ?></div>
                        <div class="stat-label">Placa</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Ubicación Actual</h5>
            </div>
            <div class="card-body">
                <div id="riderMap" style="height:300px;background:var(--bg-tertiary);border-radius:var(--radius);display:flex;align-items:center;justify-content:center;">
                    <div class="text-center text-muted">
                        <i class="bi bi-geo-alt" style="font-size:3rem;"></i>
                        <p class="mt-2">Lat: <?php echo $rider['current_lat']; ?>, Lng: <?php echo $rider['current_lng']; ?></p>
                        <small>Integrar con mapa (Leaflet/Google Maps)</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Historial de Entregas Recientes</h5>
                <a href="?page=orders&rider_id=<?php echo $rider['id']; ?>" class="btn btn-sm btn-outline-primary">Ver todas</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table datatable" style="width:100%">
                        <thead>
                            <tr>
                                <th>Pedido</th>
                                <th>Cliente</th>
                                <th>Estado</th>
                                <th>Fecha</th>
                                <th>Costo</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="5" class="text-center text-muted">No hay entregas recientes en el sistema de prueba</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php include VIEW_PATH . '/Plantillas/pieAdmin.php'; ?>