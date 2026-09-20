<?php include VIEW_PATH . '/Plantillas/encabezadoAdmin.php'; ?>
<?php include VIEW_PATH . '/Plantillas/barraLateralAdmin.php'; ?>

<div class="page-header">
    <h1 class="page-title">Configuración</h1>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Configuración General</h5>
            </div>
            <div class="card-body">
                <form id="settingsForm">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nombre de la App</label>
                            <input type="text" class="form-control" name="app_name" value="<?php echo htmlspecialchars($settings['app_name']); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Moneda</label>
                            <input type="text" class="form-control" name="currency" value="<?php echo htmlspecialchars($settings['currency']); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Zona Horaria</label>
                            <input type="text" class="form-control" name="timezone" value="<?php echo htmlspecialchars($settings['timezone']); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Precio Mínimo de Delivery</label>
                            <div class="input-group">
                                <span class="input-group-text"><?php echo CURRENCY_SYMBOL; ?></span>
                                <input type="number" class="form-control" name="min_delivery_price" value="<?php echo $settings['min_delivery_price']; ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Distancia Máxima de Delivery (km)</label>
                            <input type="number" class="form-control" name="max_delivery_distance" value="<?php echo $settings['max_delivery_distance']; ?>">
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch mt-4">
                                <input class="form-check-input" type="checkbox" name="maintenance_mode" id="maintenance_mode" <?php echo $settings['maintenance_mode'] ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="maintenance_mode">Modo Mantenimiento</label>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <h5 class="mb-0">Otras Configuraciones</h5>
            </div>
            <div class="card-body">
                <div class="list-group">
                    <a href="?page=settings&section=integrations" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        <div>
                            <i class="bi bi-gear-wide-connected me-2"></i>
                            <span>Integraciones (WhatsApp, APIs, etc.)</span>
                        </div>
                        <i class="bi bi-chevron-right"></i>
                    </a>
                    <a href="?page=settings&section=appearance" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        <div>
                            <i class="bi bi-palette me-2"></i>
                            <span>Apariencia y Temas</span>
                        </div>
                        <i class="bi bi-chevron-right"></i>
                    </a>
                    <a href="?page=settings&section=security" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        <div>
                            <i class="bi bi-shield-lock me-2"></i>
                            <span>Seguridad y Autenticación</span>
                        </div>
                        <i class="bi class="bi-chevron-right"></i>
                    </a>
                    <a href="?page=settings&section=email" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        <div>
                            <i class="bi bi-envelope me-2"></i>
                            <span>Configuración de Email</span>
                        </div>
                        <i class="bi bi-chevron-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Información del Sistema</h5>
            </div>
            <div class="card-body">
                <div class="row g-2 text-center">
                    <div class="col-6">
                        <div class="stat-value"><?php echo phpversion(); ?></div>
                        <div class="stat-label">PHP Version</div>
                    </div>
                    <div class="col-6">
                        <div class="stat-value">3.0</div>
                        <div class="stat-label">App Version</div>
                    </div>
                </div>
                <hr>
                <div class="row g-2 text-center">
                    <div class="col-6">
                        <div class="stat-value"><?php echo $settings['timezone']; ?></div>
                        <div class="stat-label">Timezone</div>
                    </div>
                    <div class="col-6">
                        <div class="stat-value"><?php echo $settings['maintenance_mode'] ? 'Sí' : 'No'; ?></div>
                        <div class="stat-label">Modo Mantenimiento</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <h5 class="mb-0">Acciones Rápidas</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <button class="btn btn-outline-secondary" onclick="clearCache()">
                        <i class="bi bi-trash"></i> Limpiar Caché
                    </button>
                    <button class="btn btn-outline-secondary" onclick="runBackup()">
                        <i class="bi bi-download"></i> Ejecutar Backup
                    </button>
                    <button class="btn btn-outline-secondary" onclick="viewLogs()">
                        <i class="bi bi-file-text"></i> Ver Logs
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include VIEW_PATH . '/Plantillas/pieAdmin.php'; ?>

<script>
document.getElementById('settingsForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    console.log('Guardando configuración:', Object.fromEntries(formData));
    alert('Configuración guardada (simulado)');
});

function clearCache() {
    if (confirm('¿Limpiar caché del sistema?')) {
        alert('Caché limpiado (simulado)');
    }
}

function runBackup() {
    alert('Backup iniciado (simulado)');
}

function viewLogs() {
    window.location.href = '?page=audit';
}
</script>