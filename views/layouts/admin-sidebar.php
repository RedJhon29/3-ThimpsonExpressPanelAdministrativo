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
            <a href="<?php echo BASE_URL; ?>/orders" class="sidebar-link <?php echo ($activeMenu ?? '') === 'orders' ? 'active' : ''; ?>">
                <i class="bi bi-receipt"></i> Órdenes
            </a>
            <a href="<?php echo BASE_URL; ?>/services" class="sidebar-link <?php echo ($activeMenu ?? '') === 'services' ? 'active' : ''; ?>">
                <i class="bi bi-box-seam"></i> Servicios
            </a>
            <a href="<?php echo BASE_URL; ?>/riders" class="sidebar-link <?php echo ($activeMenu ?? '') === 'riders' ? 'active' : ''; ?>">
                <i class="bi bi-person-video3"></i> Motorizados
            </a>
        </div>

        <!-- PLATAFORMA -->
        <div class="sidebar-section">
            <span class="sidebar-section-title">Plataforma</span>
            <a href="<?php echo BASE_URL; ?>/marketplace" class="sidebar-link <?php echo ($activeMenu ?? '') === 'marketplace' ? 'active' : ''; ?>">
                <i class="bi bi-shop"></i> Marketplace
            </a>
            <a href="<?php echo BASE_URL; ?>/cms" class="sidebar-link <?php echo ($activeMenu ?? '') === 'cms' ? 'active' : ''; ?>">
                <i class="bi bi-magic"></i> CMS Super-Poderes
            </a>
            <a href="<?php echo BASE_URL; ?>/chatbot" class="sidebar-link <?php echo ($activeMenu ?? '') === 'chatbot' ? 'active' : ''; ?>">
                <i class="bi bi-robot"></i> IA / Asistente
            </a>
        </div>

        <!-- GESTIÓN -->
        <div class="sidebar-section">
            <span class="sidebar-section-title">Gestión</span>
            <a href="<?php echo BASE_URL; ?>/clients" class="sidebar-link <?php echo ($activeMenu ?? '') === 'clients' ? 'active' : ''; ?>">
                <i class="bi bi-people"></i> Clientes
            </a>
            <a href="<?php echo BASE_URL; ?>/finance" class="sidebar-link <?php echo ($activeMenu ?? '') === 'finance' ? 'active' : ''; ?>">
                <i class="bi bi-cash-stack"></i> Finanzas
            </a>
            <a href="<?php echo BASE_URL; ?>/settings" class="sidebar-link <?php echo ($activeMenu ?? '') === 'settings' ? 'active' : ''; ?>">
                <i class="bi bi-gear"></i> Configuración
            </a>
        </div>

        <!-- SISTEMA -->
        <div class="sidebar-section">
            <span class="sidebar-section-title">Sistema</span>
            <a href="<?php echo BASE_URL; ?>/notifications" class="sidebar-link <?php echo ($activeMenu ?? '') === 'notifications' ? 'active' : ''; ?>">
                <i class="bi bi-bell"></i> Notificaciones
            </a>
            <a href="<?php echo BASE_URL; ?>/audit" class="sidebar-link <?php echo ($activeMenu ?? '') === 'audit' ? 'active' : ''; ?>">
                <i class="bi bi-journal-text"></i> Auditoría
            </a>
            <a href="<?php echo BASE_URL; ?>/support" class="sidebar-link <?php echo ($activeMenu ?? '') === 'support' ? 'active' : ''; ?>">
                <i class="bi bi-headset"></i> Soporte
            </a>
        </div>
    </nav>

    <!-- Footer -->
    <div class="sidebar-footer">
        <div class="sidebar-status">
            <span class="sidebar-status-dot"></span>
            Operativo
        </div>
        <a href="#" class="sidebar-logout">
            <i class="bi bi-box-arrow-left"></i> Cerrar Sesión
        </a>
    </div>
</aside>

<!-- Main Content -->
<div class="admin-main" id="adminMain">
    <!-- TopBar -->
    <header class="admin-topbar">
        <div class="d-flex align-items-center gap-3">
            <button class="btn btn-ghost d-lg-none p-1" id="toggleSidebar" style="color:var(--muted);">
                <i class="bi bi-list fs-4"></i>
            </button>
            <div class="topbar-search">
                <i class="bi bi-search"></i>
                <input type="text" placeholder="Buscar órdenes, clientes, motorizados... (Ctrl+K)">
                <span style="font-family:var(--font-mono);font-size:11px;padding:2px 6px;background:var(--surface-3);color:var(--muted);border:1px solid var(--border);">Ctrl+K</span>
            </div>
        </div>
        <div class="d-flex align-items-center gap-3">
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
        </div>
    </header>

    <!-- Page Content -->
    <main class="admin-content">
