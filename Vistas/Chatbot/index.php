<?php include VIEW_PATH . '/Plantillas/encabezadoAdmin.php'; ?>
<?php include VIEW_PATH . '/Plantillas/barraLateralAdmin.php'; ?>

<div class="page-header">
    <h1 class="page-title">Chatbot / Asistente Virtual</h1>
    <div class="page-actions">
        <button class="btn btn-primary" onclick="window.location.href='?page=chatbot&action=intent_create'">
            <i class="bi bi-plus-lg"></i> Nueva Intención
        </button>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Intenciones Configuradas</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table datatable" style="width:100%">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Patrones (Ejemplos)</th>
                        <th>Respuesta</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($intents as $intent): ?>
                    <tr>
                        <td><strong><?php echo $intent['id']; ?></strong></td>
                        <td><?php echo $intent['name']; ?></td>
                        <td>
                            <span class="badge bg-light text-dark"><?php echo implode('</span> <span class="badge bg-light text-dark">', $intent['patterns']); ?></span>
                        </td>
                        <td class="text-truncate" style="max-width:300px;"><?php echo $intent['response']; ?></td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn btn-sm btn-outline-primary" title="Editar" onclick="editIntent(<?php echo $intent['id']; ?>)">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger" title="Eliminar" onclick="deleteIntent(<?php echo $intent['id']; ?>)">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header">
        <h5 class="mb-0">Probar Chatbot</h5>
    </div>
    <div class="card-body">
        <div class="chat-test-container" style="height:300px;overflow-y:auto;border:1px solid var(--border);border-radius:var(--radius);padding:1rem;background:var(--bg);">
            <div class="chat-message bot-message mb-3">
                <div class="d-flex align-items-start gap-2">
                    <div class="avatar avatar-sm"><i class="bi bi-robot"></i></div>
                    <div>
                        <small class="text-muted">Asistente</small>
                        <p class="mb-0">¡Hola! ¿En qué puedo ayudarte?</p>
                    </div>
                </div>
            </div>
            <div id="chatMessages"></div>
        </div>
        <div class="input-group mt-3">
            <input type="text" class="form-control" id="chatInput" placeholder="Escribe un mensaje...">
            <button class="btn btn-primary" onclick="sendTestMessage()">
                <i class="bi bi-send"></i>
            </button>
        </div>
    </div>
</div>

<?php include VIEW_PATH . '/Plantillas/pieAdmin.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('chatInput').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') sendTestMessage();
    });
});

function sendTestMessage() {
    const input = document.getElementById('chatInput');
    const message = input.value.trim();
    if (!message) return;
    
    const container = document.getElementById('chatMessages');
    
    // Add user message
    container.innerHTML += `
        <div class="chat-message user-message mb-3">
            <div class="d-flex align-items-start gap-2 justify-content-end">
                <div>
                    <small class="text-muted d-block text-end">Tú</small>
                    <p class="mb-0 text-end">${message}</p>
                </div>
            </div>
        </div>
    `;
    
    input.value = '';
    container.scrollTop = container.scrollHeight;
    
    // Simulate bot response
    setTimeout(() => {
        const intents = <?php echo json_encode($intents); ?>;
        let response = 'No entendí tu mensaje. ¿Podés reformularlo?';
        
        for (const intent of intents) {
            for (const pattern of intent.patterns) {
                if (message.toLowerCase().includes(pattern.toLowerCase())) {
                    response = intent.response;
                    break;
                }
            }
        }
        
        container.innerHTML += `
            <div class="chat-message bot-message mb-3">
                <div class="d-flex align-items-start gap-2">
                    <div class="avatar avatar-sm"><i class="bi bi-robot"></i></div>
                    <div>
                        <small class="text-muted">Asistente</small>
                        <p class="mb-0">${response}</p>
                    </div>
                </div>
            </div>
        `;
        container.scrollTop = container.scrollHeight;
    }, 500);
}

function editIntent(id) {
    window.location.href = '?page=chatbot&action=intent_edit&id=' + id;
}

function deleteIntent(id) {
    if (confirm('¿Eliminar esta intención?')) {
        window.location.href = '?page=chatbot&action=intent_delete&id=' + id;
    }
}
</script>