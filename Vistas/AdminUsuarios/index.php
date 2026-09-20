<?php include VIEW_PATH . '/Plantillas/encabezadoAdmin.php'; ?>
<?php include VIEW_PATH . '/Plantillas/barraLateralAdmin.php'; ?>

<div class="page-header">
    <h1 class="page-title">Usuarios Administradores</h1>
    <div class="page-actions">
        <button class="btn btn-primary" onclick="window.location.href='?page=admin_users&action=create'">
            <i class="bi bi-plus-lg"></i> Nuevo Usuario
        </button>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon"><i class="bi bi-people" style="color:var(--primary);"></i></div>
            <div>
                <div class="stat-value" style="color:var(--primary);"><?php echo count(UsuarioAdmin::all()); ?></div>
                <div class="stat-label">Total Usuarios</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon"><i class="bi bi-person-check" style="color:var(--success);"></i></div>
            <div>
                <div class="stat-value" style="color:var(--success);"><?php echo count(array_filter(UsuarioAdmin::all(), fn($u) => $u['status'] === 'active')); ?></div>
                <div class="stat-label">Activos</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon"><i class="bi bi-person-badge" style="color:var(--info);"></i></div>
            <div>
                <div class="stat-value" style="color:var(--info);"><?php echo count(array_filter(UsuarioAdmin::all(), fn($u) => $u['role'] === 'Super Admin')); ?></div>
                <div class="stat-label">Super Admins</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon"><i class="bi bi-person-gear" style="color:var(--warning);"></i></div>
            <div>
                <div class="stat-value" style="color:var(--warning);"><?php echo count(array_filter(UsuarioAdmin::all(), fn($u) => $u['role'] === 'Operador')); ?></div>
                <div class="stat-label">Operadores</div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table datatable" style="width:100%">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Rol</th>
                        <th>Estado</th>
                        <th>Último Acceso</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (UsuarioAdmin::all() as $user): ?>
                    <tr>
                        <td><strong><?php echo $user['id']; ?></strong></td>
                        <td><?php echo $user['name']; ?></td>
                        <td><?php echo $user['email']; ?></td>
                        <td>
                            <span class="badge <?php echo $user['role'] === 'Super Admin' ? 'bg-danger' : 'bg-primary'; ?>">
                                <?php echo $user['role']; ?>
                            </span>
                        </td>
                        <td>
                            <span class="status-badge <?php echo $user['status'] === 'active' ? 'status-active' : 'status-inactive'; ?>">
                                <?php echo ucfirst($user['status']); ?>
                            </span>
                        </td>
                        <td class="text-muted"><?php echo date('d/m/Y H:i', strtotime($user['last_login'])); ?></td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn btn-sm btn-outline-primary" title="Editar" onclick="editUser(<?php echo $user['id']; ?>)">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-secondary" title="Cambiar Contraseña" onclick="changePassword(<?php echo $user['id']; ?>)">
                                    <i class="bi bi-key"></i>
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

<?php include VIEW_PATH . '/Plantillas/pieAdmin.php'; ?>

<script>
function editUser(id) {
    window.location.href = '?page=admin_users&action=edit&id=' + id;
}

function changePassword(id) {
    const newPass = prompt('Nueva contraseña para usuario ' + id + ':');
    if (newPass) alert('Contraseña cambiada (simulado)');
}
</script>