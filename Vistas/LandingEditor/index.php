<?php include VIEW_PATH . '/Plantillas/encabezadoAdmin.php'; ?>
<?php include VIEW_PATH . '/Plantillas/barraLateralAdmin.php'; ?>

<div class="page-header">
    <h1 class="page-title">Editor de Landing Page</h1>
    <div class="page-actions">
        <button class="btn btn-primary" onclick="saveLanding()">
            <i class="bi bi-save"></i> Guardar
        </button>
        <button class="btn btn-outline-secondary" onclick="previewLanding()">
            <i class="bi bi-eye"></i> Vista Previa
        </button>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Editor Visual</h5>
            </div>
            <div class="card-body p-0">
                <div id="landingCanvas" style="min-height:600px;background:white;border:1px solid var(--border);">
                    <div class="p-4">
                        <h3 class="text-center text-muted mt-5">Arrastra componentes aquí</h3>
                        <p class="text-center text-muted">Editor de landing page (simulado)</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Componentes</h5>
            </div>
            <div class="card-body">
                <div class="list-group">
                    <a href="#" class="list-group-item list-group-item-action" draggable="true" ondragstart="drag(event)">
                        <i class="bi bi-layout-text-window me-2"></i> Hero Section
                    </a>
                    <a href="#" class="list-group-item list-group-item-action" draggable="true" ondragstart="drag(event)">
                        <i class="bi bi-grid me-2"></i> Servicios Grid
                    </a>
                    <a href="#" class="list-group-item list-group-item-action" draggable="true" ondragstart="drag(event)">
                        <i class="bi bi-star me-2"></i> Testimonios
                    </a>
                    <a href="#" class="list-group-item list-group-item-action" draggable="true" ondragstart="drag(event)">
                        <i class="bi bi-geo-alt me-2"></i> Mapa Cobertura
                    </a>
                    <a href="#" class="list-group-item list-group-item-action" draggable="true" ondragstart="drag(event)">
                        <i class="bi bi-whatsapp me-2"></i> Botón WhatsApp
                    </a>
                    <a href="#" class="list-group-item list-group-item-action" draggable="true" ondragstart="drag(event)">
                        <i class="bi bi-envelope me-2"></i> Formulario Contacto
                    </a>
                </div>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <h5 class="mb-0">Configuración Global</h5>
            </div>
            <div class="card-body">
                <form id="landingSettings">
                    <div class="mb-3">
                        <label class="form-label">Título Principal</label>
                        <input type="text" class="form-control" value="Thimpson Express - Envíos Rápidos y Seguros">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Subtítulo</label>
                        <textarea class="form-control" rows="2">Servicios de delivery, encomiendas y transporte en todo Nicaragua</textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Color Primario</label>
                        <input type="color" class="form-control form-control-color" value="#0066CC">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Imagen Hero</label>
                        <input type="text" class="form-control" placeholder="URL de imagen" value="/assets/hero.jpg">
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Aplicar Cambios</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include VIEW_PATH . '/Plantillas/pieAdmin.php'; ?>

<script>
function drag(ev) {
    ev.dataTransfer.setData("text", ev.target.innerHTML);
}

function saveLanding() {
    alert('Landing page guardada (simulado)');
}

function previewLanding() {
    window.open('?page=landing_preview', '_blank');
}
</script>