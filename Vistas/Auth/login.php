<!--====================ENCABEZADO====================
VISTA: Auth/login — pantalla de autenticación del panel
ARCHIVO: Vistas/Auth/login.php
==================================================-->

<!--=====================DETALLES=====================
QUÉ HACE: muestra el formulario de usuario y contraseña con
    spinner de carga y bloqueo de credenciales inválidas.
VINCULADO A: lo renderiza Controladores/loginController.php
    con $error y $usuario; envía a /login/authenticate.
SI SE ALTERA: si cambian los name del formulario hay que
    ajustar authenticate() y su mensaje de error.
FECHA: 2026-10-02 | LUGAR: Ocotal, Nueva Segovia
ESCRITO POR: ING. DENIS MANUEL LÓPEZ MOLINA.
==================================================-->

<!--================CUERPO DEL CÓDIGO=================-->

<!DOCTYPE html>
<html lang="es-NI">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Iniciar Sesión'; ?> — <?php echo APP_NAME; ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=Inter:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?php echo BASE_URL; ?>/Publico/Recursos/css/admin.css" rel="stylesheet">
    <style>
        .login-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--background);
            padding: 20px;
        }
        .login-card {
            width: 100%;
            max-width: 420px;
            background: var(--surface-2);
            border: 1px solid var(--border);
            border-radius: 0;
            box-shadow: 0 20px 40px rgba(0,0,0,0.4);
            overflow: hidden;
        }
        .login-header {
            background: linear-gradient(135deg, var(--surface-3) 0%, var(--surface-2) 100%);
            border-bottom: 1px solid var(--border);
            padding: 32px 24px 24px;
            text-align: center;
        }
        .login-logo {
            width: 64px;
            height: 64px;
            background: var(--primary);
            border-radius: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
            font-family: var(--font-display);
            font-size: 28px;
            font-weight: 700;
            color: var(--primary-foreground);
        }
        .login-title {
            font-family: var(--font-display);
            font-size: 22px;
            font-weight: 600;
            color: var(--foreground);
            margin: 0 0 4px;
        }
        .login-subtitle {
            font-family: var(--font-sans);
            font-size: 14px;
            color: var(--muted);
            margin: 0;
        }
        .login-body {
            padding: 28px 24px;
        }
        .form-label {
            font-family: var(--font-sans);
            font-size: 13px;
            font-weight: 500;
            color: var(--foreground);
            margin-bottom: 8px;
            display: block;
        }
        .form-control {
            width: 100%;
            padding: 12px 14px;
            font-family: var(--font-sans);
            font-size: 15px;
            color: var(--foreground);
            background: var(--surface-1);
            border: 1px solid var(--border);
            border-radius: 0;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(251, 176, 59, 0.15);
        }
        .form-control::placeholder {
            color: var(--muted);
        }
        .input-group {
            position: relative;
        }
        .input-group .form-control {
            padding-right: 48px;
        }
        .toggle-password {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--muted);
            cursor: pointer;
            padding: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .toggle-password:hover {
            color: var(--foreground);
        }
        .btn-login {
            width: 100%;
            padding: 14px 24px;
            font-family: var(--font-display);
            font-size: 15px;
            font-weight: 600;
            color: var(--primary-foreground);
            background: var(--primary);
            border: none;
            border-radius: 0;
            cursor: pointer;
            transition: background-color 0.2s, transform 0.1s;
            margin-top: 8px;
        }
        .btn-login:hover {
            background: var(--primary-hover);
        }
        .btn-login:active {
            transform: scale(0.98);
        }
        .btn-login:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        .alert {
            padding: 12px 16px;
            border-radius: 0;
            font-family: var(--font-sans);
            font-size: 14px;
            margin-bottom: 20px;
        }
        .alert-danger {
            background: rgba(229, 57, 53, 0.15);
            border: 1px solid var(--destructive);
            color: var(--destructive);
        }
        .login-footer {
            padding: 20px 24px;
            background: var(--surface-3);
            border-top: 1px solid var(--border);
            text-align: center;
        }
        .login-footer p {
            margin: 0;
            font-family: var(--font-sans);
            font-size: 13px;
            color: var(--muted);
        }
        .login-footer code {
            font-family: var(--font-mono);
            font-size: 12px;
            background: var(--surface-1);
            padding: 2px 6px;
            border: 1px solid var(--border);
            color: var(--primary);
        }
        </style>
</head>
<body class="login-page">
    <div class="login-card">
        <div class="login-header">
            <div class="login-logo">T</div>
            <h1 class="login-title">Thimpson Express</h1>
            <p class="login-subtitle">Panel Administrativo</p>
        </div>

        <div class="login-body">
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="<?php echo BASE_URL; ?>/login/authenticate" id="loginForm">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(crearTokenCsrf()); ?>">
                <div class="mb-3">
                    <label for="usuario" class="form-label">
                        <i class="bi bi-person me-1"></i> Usuario
                    </label>
                    <input type="text"
                           class="form-control"
                           id="usuario"
                           name="usuario"
                           placeholder="usuario"
                           required
                           autocomplete="username"
                           autofocus
                           value="<?php echo htmlspecialchars($usuario ?? ''); ?>">
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">
                        <i class="bi bi-lock me-1"></i> Contraseña
                    </label>
                    <div class="input-group">
                        <input type="password"
                               class="form-control"
                               id="password"
                               name="password"
                               placeholder="Contraseña"
                               required
                               autocomplete="current-password">
                        <button type="button"
                                class="toggle-password"
                                aria-label="Mostrar/ocultar contraseña"
                                onclick="togglePassword()">
                            <i class="bi bi-eye" id="toggleIcon"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn-login" id="submitBtn">
                    <span class="btn-text">Iniciar Sesión</span>
                    <span class="btn-loading d-none">
                        <span class="spinner-rueda me-2" style="--rueda-size:1.1rem;--rueda-grosor:3px;" role="status"></span>
                        Ingresando...
                    </span>
                </button>
            </form>
        </div>

        <div class="login-footer">
            <p>&copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?>. Todos los derechos reservados.</p>
        </div>
    </div>

    <!-- Overlay de carga: pantalla completa con los arcos y el texto del login -->
    <div class="login-overlay" id="loginOverlay" role="status" aria-live="polite">
        <span class="spinner-doble" aria-hidden="true">
            <span class="doble-arco doble-arco-amarillo"></span>
            <span class="doble-arco doble-arco-blanco"></span>
        </span>
        <span class="login-overlay-texto">Ingresando al sistema</span>
    </div>

    <script>
        /*====================ENCABEZADO====================
        FUNCIÓN: togglePassword() | ROL: vista (JS)
        ==================================================
        =====================DETALLES=====================
        QUÉ HACE: alterna el campo de contraseña entre texto
            visible y oculto, con su ícono de ojo.
        VINCULADO A: lo llama el botón del campo contraseña en
            este mismo archivo; no depende de librerías.
        SI SE ALTERA: si cambian los id password/toggleIcon,
            actualizar el HTML del botón que la dispara.
        FECHA: 2026-10-02 | LUGAR: Ocotal, Nueva Segovia
        ESCRITO POR: ING. DENIS MANUEL LÓPEZ MOLINA.
        ==================================================
        */
        function togglePassword() {
            const input = document.getElementById('password');
            const icon = document.getElementById('toggleIcon');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            }
        }

        /*====================ENCABEZADO====================
        FUNCIÓN: enviarLogin() | ROL: vista (JS)
        ==================================================
        =====================DETALLES=====================
        QUÉ HACE: muestra el overlay de pantalla completa y envía
            el login, pintando la respuesta tras 2 segundos.
        VINCULADO A: la llama el submit de #loginForm y usa
            #loginOverlay de este archivo; no depende de librerías.
        SI SE ALTERA: si cambian los id del form/overlay o el valor
            2000, ajustar sus selectores y la duración del efecto.
        FECHA: 2026-10-05 | LUGAR: Ocotal, Nueva Segovia
        ESCRITO POR: ING. DENIS MANUEL LÓPEZ MOLINA.
        ==================================================
        */
        async function enviarLogin(formulario) {
            const overlay = document.getElementById('loginOverlay');
            const btn = document.getElementById('submitBtn');
            const btnText = btn.querySelector('.btn-text');
            const btnLoading = btn.querySelector('.btn-loading');
            const duracionEfecto = 2000;
            const inicio = performance.now();

            btn.disabled = true;
            btnText.classList.add('d-none');
            btnLoading.classList.remove('d-none');
            overlay.classList.add('show');

            try {
                const respuesta = await fetch(formulario.action, {
                    method: 'POST',
                    body: new FormData(formulario)
                });

                const transcurrido = performance.now() - inicio;
                const restante = Math.max(0, duracionEfecto - transcurrido);
                await new Promise(function (resolver) {
                    setTimeout(resolver, restante);
                });

                // Credenciales inválidas: se pinta la respuesta (ya lleva el
                // mensaje y el usuario) sin hacer otro GET que consuma el flash.
                if (new URL(respuesta.url).pathname === window.location.pathname) {
                    document.open();
                    document.write(await respuesta.text());
                    document.close();
                    return;
                }

                window.location.href = respuesta.url;
            } catch (error) {
                // Sin red o error del servidor: se devuelve al formulario usable
                overlay.classList.remove('show');
                btn.disabled = false;
                btnText.classList.remove('d-none');
                btnLoading.classList.add('d-none');
            }
        }

        document.getElementById('loginForm').addEventListener('submit', function(e) {
            e.preventDefault();
            enviarLogin(this);
        });

    </script>
</body>
</html>