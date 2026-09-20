<?php include VIEW_PATH . '/Plantillas/encabezadoAdmin.php'; ?>
<?php include VIEW_PATH . '/Plantillas/barraLateralAdmin.php'; ?>

<?php $status = Openwa::getStatus(); ?>

<div class="page-header">
    <h1 class="page-title">WhatsApp OpenWA</h1>
    <div class="page-actions">
        <button class="btn btn-primary" onclick="restartInstance()">
            <i class="bi bi-arrow-clockwise"></i> Reiniciar
        </button>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon"><i class="bi bi-whatsapp" style="color:#25D366;"></i></div>
            <div>
                <div class="stat-value" style="color:#25D366;">
                    <span class="badge bg-success"><?php echo ucfirst($status['status']); ?></span>
                </div>
                <div class="stat-label">Estado</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon"><i class="bi bi-phone" style="color:var(--primary);"></i></div>
            <div>
                <div class="stat-value" style="color:var(--primary);"><?php echo $status['phone']; ?></div>
                <div class="stat-label">Número</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon"><i class="bi bi-clock" style="color:var(--info);"></i></div>
            <div>
                <div class="stat-value" style="color:var(--info);"><?php echo $status['uptime']; ?></div>
                <div class="stat-label">Uptime</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon"><i class="bi bi-tag" style="color:var(--warning);"></i></div>
            <div>
                <div class="stat-value" style="color:var(--warning);"><?php echo $status['version']; ?></div>
                <div class="stat-label">Versión</div>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">Estadísticas de Mensajes</h5>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4">
                <div class="stat-card text-center">
                    <div class="stat-icon mx-auto"><i class="bi bi-send" style="color:var(--primary);"></i></div>
                    <div class="stat-value" style="color:var(--primary);"><?php echo number_format($status['messages_sent']); ?></div>
                    <div class="stat-label">Enviados</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card text-center">
                    <div class="stat-icon mx-auto"><i class="bi bi-inbox" style="color:var(--success);"></i></div>
                    <div class="stat-value" style="color:var(--success);"><?php echo number_format($status['messages_received']); ?></div>
                    <div class="stat-label">Recibidos</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card text-center">
                    <div class="stat-icon mx-auto"><i class="bi bi-clock-history" style="color:var(--info);"></i></div>
                    <div class="stat-value" style="color:var(--info);"><?php echo date('d/m/Y H:i', strtotime($status['last_activity'])); ?></div>
                    <div class="stat-label">Última Actividad</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Acciones</h5>
    </div>
    <div class="card-body">
        <div class="d-grid gap-2 d-md-flex">
            <button class="btn btn-outline-primary" onclick="viewQR()">
                <i class="bi bi-qr-code"></i> Ver QR
            </button>
            <button class="btn btn-outline-secondary" onclick="viewLogs()">
                <i class="bi bi-file-text"></i> Ver Logs
            </button>
            <button class="btn btn-outline-secondary" onclick="testMessage()">
                <i class="bi bi-chat"></i> Enviar Prueba
            </button>
        </div>
    </div>
</div>

<?php include VIEW_PATH . '/Plantillas/pieAdmin.php'; ?>

<script>
function restartInstance() {
    if (confirm('¿Reiniciar la instancia de WhatsApp?')) {
        alert('Reiniciando instancia... (simulado)');
    }
}

function viewQR() {
    alert('Mostrando código QR... (simulado)');
}

function viewLogs() {
    alert('Abriendo logs... (simulado)');
}

function testMessage() {
    const phone = prompt('Número de teléfono para prueba:');
    if (phone) alert('Enviando mensaje de prueba a ' + phone + '... (simulado)');
}
</script>