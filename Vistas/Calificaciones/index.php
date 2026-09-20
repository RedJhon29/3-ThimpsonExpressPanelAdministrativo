<?php include VIEW_PATH . '/Plantillas/encabezadoAdmin.php'; ?>
<?php include VIEW_PATH . '/Plantillas/barraLateralAdmin.php'; ?>

<div class="page-header">
    <h1 class="page-title">Calificaciones</h1>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon"><i class="bi bi-star" style="color:var(--warning);"></i></div>
            <div>
                <div class="stat-value" style="color:var(--warning); font-size:2.5rem;"><?php echo Calificacion::average(); ?></div>
                <div class="stat-label">Promedio General</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon"><i class="bi bi-ticket-perforated" style="color:var(--primary);"></i></div>
            <div>
                <div class="stat-value" style="color:var(--primary);"><?php echo count(Calificacion::all()); ?></div>
                <div class="stat-label">Total Calificaciones</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon"><i class="bi bi-person-video3" style="color:var(--success);"></i></div>
            <div>
                <div class="stat-value" style="color:var(--success);"><?php echo count(array_unique(array_column(Calificacion::all(), 'rider'))); ?></div>
                <div class="stat-label">Riders Calificados</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon"><i class="bi bi-emoji-smile" style="color:var(--info);"></i></div>
            <div>
                <div class="stat-value" style="color:var(--info);"><?php $b = Calificacion::breakdown(); echo $b[5]; ?></div>
                <div class="stat-label">5 Estrellas</div>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">Distribución de Estrellas</h5>
    </div>
    <div class="card-body">
        <?php $breakdown = Calificacion::breakdown(); $total = count(Calificacion::all()); ?>
        <div class="rating-breakdown">
            <?php for ($i = 5; $i >= 1; $i--): $pct = $total > 0 ? round(($breakdown[$i] / $total) * 100) : 0; ?>
            <div class="rating-row mb-2">
                <div class="rating-label"><?php echo $i; ?> <i class="bi bi-star-fill" style="color:var(--warning);"></i></div>
                <div class="rating-bar-container flex-grow-1 ms-3 me-3">
                    <div class="rating-bar" style="height:8px;background:var(--border);border-radius:4px;overflow:hidden;">
                        <div class="rating-bar-fill" style="width:<?php echo $pct; ?>%;height:100%;background:var(--warning);transition:width 0.3s;"></div>
                    </div>
                </div>
                <div class="rating-count" style="width:40px;"><?php echo $breakdown[$i]; ?> (<?php echo $pct; ?>%)</div>
            </div>
            <?php endfor; ?>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Calificaciones Recientes</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table datatable" style="width:100%">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Rider</th>
                        <th>Cliente</th>
                        <th>Estrellas</th>
                        <th>Comentario</th>
                        <th>Fecha</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (Calificacion::all() as $rating): ?>
                    <tr>
                        <td><strong><?php echo $rating['id']; ?></strong></td>
                        <td><?php echo $rating['rider']; ?></td>
                        <td><?php echo $rating['client']; ?></td>
                        <td>
                            <div class="stars">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="bi bi-star-fill" style="color:<?php echo $i <= $rating['rating'] ? 'var(--warning)' : 'var(--border)'; ?>;"></i>
                                <?php endfor; ?>
                            </div>
                        </td>
                        <td><?php echo $rating['comment']; ?></td>
                        <td class="text-muted"><?php echo date('d/m/Y', strtotime($rating['date'])); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include VIEW_PATH . '/Plantillas/pieAdmin.php'; ?>