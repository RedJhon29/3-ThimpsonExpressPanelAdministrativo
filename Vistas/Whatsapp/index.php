<?php include VIEW_PATH . '/Plantillas/encabezadoAdmin.php'; ?>
<?php include VIEW_PATH . '/Plantillas/barraLateralAdmin.php'; ?>

<?php $status = Whatsapp::getStatus(); ?>

<div class="page-header">
    <h1 class="page-title">WhatsApp Business API</h1>
    <div class="page-actions">
        <button class="btn btn-primary" onclick="testConnection()">
            <i class="bi bi-wifi"></i> Probar Conexión
        </button>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon"><i class="bi bi-whatsapp" style="color:#25D366;"></i></div>
            <div>
                <div class="stat-value" style="color:#25D366;">
                    <span class="badge bg-success"><?php echo $status['connected'] ? 'Conectado' : 'Desconectado'; ?></span>
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
            <div class="stat-icon"><i class="bi bi-send" style="color:var(--success);"></i></div>
            <div>
                <div class="stat-value" style="color:var(--success);"><?php echo number_format($status['messages_sent']); ?></div>
                <div class="stat-label">Enviados</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon"><i class="bi bi-inbox" style="color:var(--info);"></i></div>
            <div>
                <div class="stat-value" style="color:var(--info);"><?php echo number_format($status['messages_received']); ?></div>
                <div class="stat-label">Recibidos</div>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">Plantillas de Mensajes</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table datatable" style="width:100%">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Categoría</th>
                        <th>Idioma</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>order_confirmation</td>
                        <td>UTILITY</td>
                        <td>es</td>
                        <td><span class="badge bg-success">Aprobada</span></td>
                        <td><button class="btn btn-sm btn-outline-primary">Ver</button></td>
                    </tr>
                    <tr>
                        <td>delivery_update</td>
                        <td>UTILITY</td>
                        <td>es</td>
                        <td><span class="badge bg-success">Aprobada</span></td>
                        <td><button class="btn btn-sm btn-outline-primary">Ver</button></td>
                    </tr>
                    <tr>
                        <td>promotional_offer</td>
                        <td>MARKETING</td>
                        <td>es</td>
                        <td><span class="badge bg-warning">Pendiente</span></td>
                        <td><button class="btn btn-sm btn-outline-primary">Ver</button></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Configuración</h5>
    </div>
    <div class="card-body">
        <form id="waConfigForm" class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Phone Number ID</label>
                <input type="text" class="form-control" placeholder="123456789012345">
            </div>
            <div class="col-md-6">
                <label class="form-label">Business Account ID</label>
                <input type="text" class="form-control" placeholder="123456789012345">
            </div>
            <div class="col-md-6">
                <label class="form-label">Access Token</label>
                <input type="password" class="form-control" placeholder="EAA...">
            </div>
            <div class="col-md-6">
                <label class="form-label">Webhook Verify Token</label>
                <input type="text" class="form-control" placeholder="mi_token_secreto">
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> Guardar Configuración
                </button>
            </div>
        </form>
    </div>
</div>

<?php include VIEW_PATH . '/Plantillas/pieAdmin.php'; ?>

<script>
function testConnection() {
    alert('Probando conexión con WhatsApp API... (simulado)');
}
</script>