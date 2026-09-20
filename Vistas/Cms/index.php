<?php include VIEW_PATH . '/Plantillas/encabezadoAdmin.php'; ?>
<?php include VIEW_PATH . '/Plantillas/barraLateralAdmin.php'; ?>

<div class="page-header">
    <h1 class="page-title">Gestor de Contenido (CMS)</h1>
    <div class="page-actions">
        <button class="btn btn-primary" onclick="window.location.href='?page=cms&action=banner_create'">
            <i class="bi bi-image"></i> Nuevo Banner
        </button>
        <button class="btn btn-outline-primary" onclick="window.location.href='?page=cms&action=faq_create'">
            <i class="bi bi-question-circle"></i> Nueva Pregunta
        </button>
    </div>
</div>

<!-- Banners Section -->
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Banners</h5>
    </div>
    <div class="card-body">
        <?php if (empty($banners)): ?>
            <div class="text-center py-4 text-muted">
                <i class="bi bi-image" style="font-size:3rem;"></i>
                <p class="mt-2">No hay banners configurados</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table datatable" style="width:100%">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Título</th>
                            <th>Estado</th>
                            <th>Fecha Inicio</th>
                            <th>Fecha Fin</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($banners as $banner): ?>
                        <tr>
                            <td><strong><?php echo $banner['id']; ?></strong></td>
                            <td><?php echo $banner['title']; ?></td>
                            <td>
                                <span class="status-badge <?php echo $banner['active'] ? 'status-active' : 'status-inactive'; ?>">
                                    <?php echo $banner['active'] ? 'Activo' : 'Inactivo'; ?>
                                </span>
                            </td>
                            <td><?php echo date('d/m/Y', strtotime($banner['start_date'])); ?></td>
                            <td><?php echo date('d/m/Y', strtotime($banner['end_date'])); ?></td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn btn-sm btn-outline-primary" title="Editar" onclick="editBanner(<?php echo $banner['id']; ?>)">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger" title="Eliminar" onclick="deleteBanner(<?php echo $banner['id']; ?>)">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- FAQs Section -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Preguntas Frecuentes (FAQs)</h5>
    </div>
    <div class="card-body">
        <?php if (empty($faqs)): ?>
            <div class="text-center py-4 text-muted">
                <i class="bi bi-question-circle" style="font-size:3rem;"></i>
                <p class="mt-2">No hay preguntas configuradas</p>
            </div>
        <?php else: ?>
            <div class="accordion" id="faqsAccordion">
                <?php foreach ($faqs as $index => $faq): ?>
                <div class="accordion-item">
                    <h2 class="accordion-header" id="heading<?php echo $faq['id']; ?>">
                        <button class="accordion-button <?php echo $index > 0 ? 'collapsed' : ''; ?>" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?php echo $faq['id']; ?>" aria-expanded="<?php echo $index === 0 ? 'true' : 'false'; ?>" aria-controls="collapse<?php echo $faq['id']; ?>">
                            <span class="badge bg-secondary me-2"><?php echo $faq['category']; ?></span>
                            <?php echo $faq['question']; ?>
                        </button>
                    </h2>
                    <div id="collapse<?php echo $faq['id']; ?>" class="accordion-collapse collapse <?php echo $index === 0 ? 'show' : ''; ?>" aria-labelledby="heading<?php echo $faq['id']; ?>" data-bs-parent="#faqsAccordion">
                        <div class="accordion-body">
                            <?php echo $faq['answer']; ?>
                            <div class="mt-3">
                                <button class="btn btn-sm btn-outline-primary" onclick="editFaq(<?php echo $faq['id']; ?>)">
                                    <i class="bi bi-pencil"></i> Editar
                                </button>
                                <button class="btn btn-sm btn-outline-danger" onclick="deleteFaq(<?php echo $faq['id']; ?>)">
                                    <i class="bi bi-trash"></i> Eliminar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include VIEW_PATH . '/Plantillas/pieAdmin.php'; ?>

<script>
function editBanner(id) {
    window.location.href = '?page=cms&action=banner_edit&id=' + id;
}

function deleteBanner(id) {
    if (confirm('¿Eliminar este banner?')) {
        window.location.href = '?page=cms&action=banner_delete&id=' + id;
    }
}

function editFaq(id) {
    window.location.href = '?page=cms&action=faq_edit&id=' + id;
}

function deleteFaq(id) {
    if (confirm('¿Eliminar esta pregunta?')) {
        window.location.href = '?page=cms&action=faq_delete&id=' + id;
    }
}
</script>