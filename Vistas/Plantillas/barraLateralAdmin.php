<!--====================ENCABEZADO====================
PLANTILLA: barraLateralAdmin — menú lateral del panel
ARCHIVO: Vistas/Plantillas/barraLateralAdmin.php
==================================================-->

<!--=====================DETALLES=====================
QUÉ HACE: pinta el sidebar con sus categorías y marca el ítem activo
    según $activeMenu; añade el botón de apagado y overlay del topbar.
VINCULADO A: lo incluye el controlador después de
    encabezadoAdmin.php y antes de la vista; lo cierra pieAdmin.php.
SI SE ALTERA: una key nueva de $activeMenu debe existir aquí para
    que se resalte.
LÍMITES: solo hay dos vistas, Dashboard y Usuarios. Las otras
    categorías se conservan como títulos vacíos para cuando existan
    sus rutas: los enlaces que había ahí apuntan a rutas inexistentes.
FECHA: 2026-10-02 | LUGAR: Ocotal, Nueva Segovia
ESCRITO POR: ING. DENIS MANUEL LÓPEZ MOLINA.
==================================================-->

<!--================CUERPO DEL CÓDIGO=================-->

<!-- Sidebar -->
<aside class="admin-sidebar" id="adminSidebar">
    <!-- Logo -->
    <div class="sidebar-header">
        <div class="sidebar-logo">T</div>
        <div>
            <div class="sidebar-brand-text">Thimpson Express</div>
            <div class="sidebar-brand-sub">Panel Admin</div>
        </div>
        <button class="btn btn-sm btn-ghost d-lg-none ms-auto" id="closeSidebar" style="color:var(--muted);">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>

    <!-- Navigation -->
    <nav class="sidebar-nav">
        <!-- OPERACIONES -->
        <div class="sidebar-section">
            <span class="sidebar-section-title">Operaciones</span>
            <a href="<?php echo BASE_URL; ?>/dashboard" class="sidebar-link <?php echo ($activeMenu ?? '') === 'dashboard' ? 'active' : ''; ?>">
                <i class="bi bi-grid-1x2"></i> Dashboard
            </a>
            <a href="<?php echo BASE_URL; ?>/usuarios" class="sidebar-link <?php echo ($activeMenu ?? '') === 'usuarios' ? 'active' : ''; ?>">
                <i class="bi bi-people-fill"></i> Usuarios
            </a>
        </div>

        <!-- PLATAFORMA: categoría sin vistas activas; sus enlaces se
             reagregan cuando existan las rutas reales. -->
        <div class="sidebar-section">
            <span class="sidebar-section-title">Plataforma</span>
        </div>

        <!-- GESTIÓN: categoría sin vistas activas por ahora. -->
        <div class="sidebar-section">
            <span class="sidebar-section-title">Gestión</span>
        </div>

        <!-- SISTEMA: categoría sin vistas activas por ahora. -->
        <div class="sidebar-section">
            <span class="sidebar-section-title">Sistema</span>
        </div>
    </nav>

    <!-- Footer -->
    <div class="sidebar-footer">
        <div class="sidebar-status">
            <span class="sidebar-status-dot"></span>
            Operativo
        </div>
        <a href="<?php echo BASE_URL; ?>/logout" class="sidebar-logout">
            <i class="bi bi-box-arrow-left"></i> Cerrar Sesión
        </a>
    </div>
</aside>

<!-- Main Content -->
<div class="admin-main" id="adminMain">
    <!-- TopBar -->
    <header class="admin-topbar">
        <div class="d-flex align-items-center gap-3 admin-topbar-izq">
            <button class="btn btn-ghost d-lg-none p-1" id="toggleSidebar" style="color:var(--muted);">
                <i class="bi bi-list fs-4"></i>
            </button>
            <div class="topbar-search">
                <i class="bi bi-search"></i>
                <input type="text" id="topbarSearch" name="q" placeholder="Buscar órdenes, clientes, motorizados...">
            </div>
        </div>
        <div class="d-flex align-items-center gap-3 admin-topbar-der">
            <div class="live-indicator d-none d-md-flex">
                <i class="bi bi-lightning-charge-fill" style="color:var(--primary);"></i>
                Cola en vivo
                <span class="live-dot"></span>
            </div>
            <div class="position-relative">
                <i class="bi bi-bell fs-5" style="color:var(--muted);cursor:pointer;"></i>
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size:9px;padding:2px 5px;">3</span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <div class="avatar avatar-md avatar-primary">AT</div>
                <div class="d-none d-md-block">
                    <div style="font-size:14px;font-weight:500;color:var(--foreground);">Allan Thimpson</div>
                    <div style="font-family:var(--font-mono);font-size:10px;text-transform:uppercase;letter-spacing:0.05em;color:var(--muted);">Super Admin</div>
                </div>
            </div>
            <a href="<?php echo BASE_URL; ?>/logout"
               class="topbar-logout"
               id="logoutBtn"
               title="Cerrar sesión"
               aria-label="Cerrar sesión">
                <i class="bi bi-power" aria-hidden="true"></i>
            </a>
        </div>
    </header>

    <!-- Overlay de salida del sistema: mismo spinner doble que el login -->
    <div class="logout-overlay" id="logoutOverlay" role="status" aria-live="polite">
        <span class="spinner-doble" aria-hidden="true">
            <span class="doble-arco doble-arco-amarillo"></span>
            <span class="doble-arco doble-arco-blanco"></span>
        </span>
        <span class="logout-overlay-text">Saliendo del sistema</span>
    </div>

    <!-- Page Content -->
    <main class="admin-content">
