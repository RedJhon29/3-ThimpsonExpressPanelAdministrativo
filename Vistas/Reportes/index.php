<?php include VIEW_PATH . '/Plantillas/encabezadoAdmin.php'; ?>
<?php include VIEW_PATH . '/Plantillas/barraLateralAdmin.php'; ?>

<div class="page-header">
    <h1 class="page-title">Reportes</h1>
    <div class="page-actions">
        <button class="btn btn-primary" onclick="generateReport()">
            <i class="bi bi-file-earmark-plus"></i> Generar Reporte
        </button>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center">
                <div class="stat-icon mx-auto"><i class="bi bi-receipt" style="color:var(--primary);"></i></div>
                <div class="stat-value" style="color:var(--primary);">Pedidos</div>
                <div class="stat-label">Reporte de Pedidos</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center">
                <div class="stat-icon mx-auto"><i class="bi bi-cash-stack" style="color:var(--success);"></i></div>
                <div class="stat-value" style="color:var(--success);">Ingresos</div>
                <div class="stat-label">Reporte Financiero</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center">
                <div class="stat-icon mx-auto"><i class="bi bi-person-video3" style="color:var(--info);"></i></div>
                <div class="stat-value" style="color:var(--info);">Riders</div>
                <div class="stat-label">Performance Riders</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center">
                <div class="stat-icon mx-auto"><i class="bi bi-people" style="color:var(--warning);"></i></div>
                <div class="stat-value" style="color:var(--warning);">Clientes</div>
                <div class="stat-label">Actividad Clientes</div>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">Generar Reporte Personalizado</h5>
    </div>
    <div class="card-body">
        <form id="reportForm" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Tipo de Reporte</label>
                <select class="form-select" name="type">
                    <option value="orders">Pedidos</option>
                    <option value="revenue">Ingresos</option>
                    <option value="riders">Riders</option>
                    <option value="clients">Clientes</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Fecha Inicio</label>
                <input type="date" class="form-control" name="start_date" value="<?php echo date('Y-m-01'); ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Fecha Fin</label>
                <input type="date" class="form-control" name="end_date" value="<?php echo date('Y-m-d'); ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Formato</label>
                <select class="form-select" name="format">
                    <option value="pdf">PDF</option>
                    <option value="excel">Excel</option>
                    <option value="csv">CSV</option>
                </select>
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-download"></i> Generar y Descargar
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Reportes Recientes</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table datatable" style="width:100%">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Tipo</th>
                        <th>Período</th>
                        <th>Formato</th>
                        <th>Fecha</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="6" class="text-center text-muted">No hay reportes generados aún</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include VIEW_PATH . '/Plantillas/pieAdmin.php'; ?>

<script>
function generateReport() {
    alert('Generando reporte... (simulado)');
}
</script>