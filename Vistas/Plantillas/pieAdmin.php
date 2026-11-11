<!--====================ENCABEZADO====================
PLANTILLA: pieAdmin — cierre del layout del panel
ARCHIVO: Vistas/Plantillas/pieAdmin.php
==================================================-->

<!--=====================DETALLES=====================
QUÉ HACE: cierra main, pinta el pie con la marca y el año, cierra
    el contenedor del panel, carga las librerías por la cadena
    del gestor, inicializa la UI y alerta fallos.
VINCULADO A: lo cierra la última línea de cada vista del panel;
    abre los contenedores Vistas/Plantillas/barraLateralAdmin.php.
SI SE ALTERA: si se rompe el cierre o la cadena de scripts,
    todas las vistas se desbordan o pierden sus librerías. El pie
    va después de </main> para que .admin-content lo empuje al
    fondo con su margin-top:auto.
LÍMITES: solo marca y año, sin enlaces: las rutas de ayuda o
    soporte no existen y serían links que devuelven 404.
FECHA: 2026-10-02 | LUGAR: Ocotal, Nueva Segovia
ESCRITO POR: ING. DENIS MANUEL LÓPEZ MOLINA.
==================================================-->

<!--================CUERPO DEL CÓDIGO=================-->

    </main>

    <footer class="admin-footer">
        &copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars(APP_NAME); ?> &middot; Panel Administrativo
    </footer>
</div>

<!-- Scripts -->
<script>
/*====================ENCABEZADO====================
FUNCIÓN: inicializarPanel() | ROL: plantilla (JS)
==================================================
=====================DETALLES=====================
QUÉ HACE: cablea el sidebar (off-canvas y colapso), Select2,
    DataTables, el toast y la alerta de cierre de sesión.
VINCULADO A: lo ejecuta gestorPlugins.alListo() con la
    cadena de scripts de este mismo pie.
SI SE ALTERA: si cambia el orden de la cadena o un init,
    revisar los guards de librerías ausentes.
FECHA: 2026-10-05 | LUGAR: Ocotal, Nueva Segovia
ESCRITO POR: ING. DENIS MANUEL LÓPEZ MOLINA.
==================================================
*/
function inicializarPanel() {
    // Sidebar toggle
    document.getElementById('toggleSidebar')?.addEventListener('click', function() {
        document.getElementById('adminSidebar').classList.toggle('show');
    });
    document.getElementById('closeSidebar')?.addEventListener('click', function() {
        document.getElementById('adminSidebar').classList.remove('show');
    });

    // Colapso de escritorio: solo iconos, tooltips y estado persistido.
    prepararColapsoSidebar();

    // Abre el modal de Configuraciones. Va con guarda porque el boton
    // existe en todas las vistas pero bootstrap puede no haber cargado.
    document.getElementById('botonConfiguraciones')?.addEventListener('click', function() {
        if (typeof bootstrap === 'undefined') { return; }
        bootstrap.Modal.getOrCreateInstance(document.getElementById('modalConfiguraciones')).show();
    });

    // Select2
    if (typeof $ !== 'undefined' && $.fn.select2) {
        $('.select2').select2({ theme: 'bootstrap-5', width: '100%' });
    }

    // DataTables
    if (typeof $ !== 'undefined' && $.fn.DataTable) {
        $('.datatable').each(function () {
            $(this).DataTable({
                language: {
                    url: '<?php echo BASE_URL; ?>/Publico/Recursos/datatables/i18n/es-ES.json',
                    // El JSON oficial no trae paginate de raíz y el renderer Bootstrap
                    // lee ese texto: sin esto los botones quedan en inglés.
                    paginate: { first: "Primero", last: "Último", next: "Siguiente", previous: "Anterior" }
                },
                // initComplete corre cuando la inicialización terminó, incluida la
                // carga por AJAX de language.url: recién ahí existe el buscador.
                initComplete: function () {
                    etiquetarCamposDataTables();
                }
            });
        });
    }

    // SweetAlert helpers
    window.showToast = function(type, title, text) {
        Swal.fire({ icon: type, title: title, text: text, timer: 3000, showConfirmButton: false, background: '#131517', color: '#fff' });
    };

    prepararCierreSesion();
}

/*====================ENCABEZADO====================
FUNCIÓN: etiquetarCamposDataTables() | ROL: plantilla (JS)
==================================================
=====================DETALLES=====================
QUÉ HACE: pone id y name a los campos que el renderer
    bootstrap5 de DataTables crea sin ellos: el buscador
    (input.form-control) y el selector de registros
    (select.form-select).
VINCULADO A: la invoca el initComplete de cada tabla en esta
    misma plantilla; los nodos ya existen en el DOM.
SI SE ALTERA: si DataTables cambia sus clases, revisar el
    selector o los campos vuelven a quedar sin identificar.
FECHA: 2026-10-05 | LUGAR: Ocotal, Nueva Segovia
ESCRITO POR: ING. DENIS MANUEL LÓPEZ MOLINA.
*/
function etiquetarCamposDataTables() {
    var campos = document.querySelectorAll(
        '.dataTables_wrapper input.form-control, .dataTables_wrapper select.form-select'
    );

    campos.forEach(function (campo, indice) {
        if (!campo.id) { campo.id = 'dtCampo' + indice; }
        if (!campo.name) { campo.name = 'campo' + indice; }
    });
}

/*====================ENCABEZADO====================
FUNCIÓN: prepararColapsoSidebar() | ROL: plantilla (JS)
==================================================
=====================DETALLES=====================
QUÉ HACE: cablea #colapsarSidebar, sincroniza admin-colapsado en
    <body> con localStorage y enciende los tooltips al colapsar.
VINCULADO A: la llama inicializarPanel(); usa .sidebar-link,
    .sidebar-config y .sidebar-etiqueta de barraLateralAdmin.php.
SI SE ALTERA: key y breakpoint deben coincidir con el script
    anti-parpadeo de barraLateralAdmin.php; sin Bootstrap, title nativo.
FECHA: 2026-11-10 | LUGAR: Ocotal, Nueva Segovia
ESCRITO POR: ING. DENIS MANUEL LÓPEZ MOLINA.
*/
function prepararColapsoSidebar() {
    var boton = document.getElementById('colapsarSidebar');
    if (!boton) { return; }

    var CLAVE = 'panel.sidebar.colapsado';
    var cuerpo = document.body;
    var conBootstrap = typeof bootstrap !== 'undefined' && typeof bootstrap.Tooltip === 'function';
    var etiquetas = [];
    var titulosOriginales = [];
    var ariaOriginales = [];
    var tooltips = [];

    // Ítems que pierden su etiqueta al colapsar: los mismos que
    // necesitan tooltip y nombre accesible. El texto sale de
    // .sidebar-etiqueta para no duplicar la etiqueta en el HTML.
    var items = Array.prototype.slice.call(
        document.querySelectorAll('.sidebar-link, .sidebar-config')
    );

    items.forEach(function (item) {
        var etiqueta = item.querySelector('.sidebar-etiqueta');
        var texto = etiqueta ? etiqueta.textContent.trim() : '';
        etiquetas.push(texto);
        titulosOriginales.push(item.getAttribute('title') || '');
        ariaOriginales.push(item.getAttribute('aria-label') || '');
        if (conBootstrap && texto) {
            // container:'body' para que el aside no recorte el tooltip
            // y placement a la derecha, donde está el contenido.
            tooltips.push(new bootstrap.Tooltip(item, {
                title: texto,
                placement: 'right',
                container: 'body'
            }));
        } else {
            tooltips.push(null);
        }
    });

    /*====================ENCABEZADO====================
    FUNCIÓN: leerPreferencia() | interna de prepararColapsoSidebar()
    ==================================================
    =====================DETALLES=====================
    QUÉ HACE: lee la preferencia de colapso guardada; devuelve
        false si el navegador no tiene localStorage.
    */
    function leerPreferencia() {
        try { return localStorage.getItem(CLAVE) === '1'; }
        catch (error) { return false; }
    }

    /*====================ENCABEZADO====================
    FUNCIÓN: aplicarColapso() | interna de prepararColapsoSidebar()
    ==================================================
    =====================DETALLES=====================
    QUÉ HACE: vuelca el estado en la clase de <body>, en los
        atributos del botón, en los tooltips/aria de los ítems y
        en localStorage cuando guardar es true.
    SI SE ALTERA: sin Bootstrap el title nativo reemplaza al
        tooltip estilizado para no dejar ítems mudos.
    */
    function aplicarColapso(colapsar, guardar) {
        cuerpo.classList.toggle('admin-colapsado', colapsar);
        boton.setAttribute('aria-expanded', colapsar ? 'false' : 'true');
        boton.title = colapsar ? 'Expandir menú lateral' : 'Colapsar menú lateral';
        boton.setAttribute('aria-label', boton.title);

        items.forEach(function (item, indice) {
            var texto = etiquetas[indice];
            var tituloOriginal = titulosOriginales[indice];
            var ariaOriginal = ariaOriginales[indice];
            var tooltip = tooltips[indice];

            // Nombre accesible: al colapsar, la etiqueta visible
            // desaparece y el ícono solo no nombra el enlace.
            if (colapsar && texto) {
                item.setAttribute('aria-label', texto);
            } else if (ariaOriginal) {
                item.setAttribute('aria-label', ariaOriginal);
            } else {
                item.removeAttribute('aria-label');
            }

            if (tooltip) {
                if (colapsar) {
                    // Sin title nativo mientras hay tooltip estilizado:
                    // si no, el navegador pinta los dos encima.
                    item.removeAttribute('title');
                    tooltip.enable();
                } else {
                    tooltip.hide();
                    tooltip.disable();
                    if (tituloOriginal) { item.setAttribute('title', tituloOriginal); }
                    else { item.removeAttribute('title'); }
                }
            } else if (colapsar && texto) {
                // Respaldo sin Bootstrap: tooltip nativo del navegador.
                item.setAttribute('title', texto);
            } else if (tituloOriginal) {
                item.setAttribute('title', tituloOriginal);
            } else {
                item.removeAttribute('title');
            }
        });

        // Solo el click del usuario escribe la preferencia: la carga
        // inicial y los cambios de breakpoint aplican, no guardan.
        if (guardar) {
            try { localStorage.setItem(CLAVE, colapsar ? '1' : '0'); }
            catch (error) { /* sin localStorage: queda para esta página */ }
        }
    }

    boton.addEventListener('click', function () {
        aplicarColapso(!cuerpo.classList.contains('admin-colapsado'), true);
    });

    // Estado inicial: respeta lo persistido solo en escritorio, donde
    // el colapso aplica; en móvil manda el off-canvas con etiquetas.
    var esEscritorio = !window.matchMedia || window.matchMedia('(min-width: 992px)').matches;
    aplicarColapso(leerPreferencia() && esEscritorio, false);

    // Al cruzar 992px se re-sincroniza: al entrar a móvil se apagan los
    // tooltips (la etiqueta vuelve a estar visible) y al volver a
    // escritorio reaparece el estado persistido.
    if (window.matchMedia) {
        var pantallaEscritorio = window.matchMedia('(min-width: 992px)');
        pantallaEscritorio.addEventListener('change', function () {
            aplicarColapso(leerPreferencia() && pantallaEscritorio.matches, false);
        });
    }
}

/*====================ENCABEZADO====================
FUNCIÓN: mostrarOverlaySalida() | ROL: plantilla (JS)
==================================================
=====================DETALLES=====================
QUÉ HACE: muestra el overlay de salida con el spinner doble y
    navega a /logout esperando 1200 ms de efecto mínimo.
VINCULADO A: usa #logoutOverlay (barraLateralAdmin.php) y la
    ruta /logout (index.php); no depende de librerías.
SI SE ALTERA: si cambia el id del overlay o la duración 1200,
    ajustar su selector y el tiempo del efecto.
FECHA: 2026-10-05 | LUGAR: Ocotal, Nueva Segovia
ESCRITO POR: ING. DENIS MANUEL LÓPEZ MOLINA.
==================================================
*/
async function mostrarOverlaySalida(destino) {
    const overlay = document.getElementById('logoutOverlay');
    const duracionEfecto = 1200;
    const inicio = performance.now();

    if (overlay) { overlay.classList.add('show'); }

    let urlDestino = destino;
    try {
        const respuesta = await fetch(destino);
        urlDestino = respuesta.url || destino;
    } catch (error) {
        // Sin red: se navega igual a /logout y el GET destruye la sesión
        urlDestino = destino;
    }

    const restante = Math.max(0, duracionEfecto - (performance.now() - inicio));
    await new Promise(function (resolver) { setTimeout(resolver, restante); });
    window.location.href = urlDestino;
}

/*====================ENCABEZADO====================
FUNCIÓN: prepararCierreSesion() | ROL: plantilla (JS)
==================================================
=====================DETALLES=====================
QUÉ HACE: intercepta los dos botones de salida y consulta con
    SweetAlert si el usuario está seguro antes de salir.
VINCULADO A: usa #logoutBtn y .sidebar-logout de
    barraLateralAdmin.php y SweetAlert2 cargado en este pie.
SI SE ALTERA: si cambian esos botones o el texto de la alerta,
    actualizar sus selectores y su mensaje.
FECHA: 2026-10-05 | LUGAR: Ocotal, Nueva Segovia
ESCRITO POR: ING. DENIS MANUEL LÓPEZ MOLINA.
==================================================
*/
function prepararCierreSesion() {
    document.querySelectorAll('#logoutBtn, .sidebar-logout').forEach(function (boton) {
        boton.addEventListener('click', function (evento) {
            evento.preventDefault();
            const overlay = document.getElementById('logoutOverlay');
            if (overlay && overlay.classList.contains('show')) { return; }

            Swal.fire({
                icon: 'question',
                title: '¿Cerrar sesión?',
                text: '¿Estás seguro de que deseas salir del sistema?',
                showCancelButton: true,
                confirmButtonText: 'Sí, salir',
                cancelButtonText: 'Cancelar',
                reverseButtons: true,
                background: '#131517',
                color: '#fff',
                customClass: { confirmButton: 'swal-confirmar-salida' }
            }).then(function (resultado) {
                if (resultado.isConfirmed) {
                    mostrarOverlaySalida(boton.getAttribute('href'));
                }
            });
        });
    });
}

// Cadena de carga: nivel 0 (jQuery) -> nivel 1 (libs) -> nivel 2 (extensión
// de DataTables que depende del nivel anterior). Cada script sigue su secuencia
// data-pasos; un fallo aislado no detiene la cadena y queda en la alerta final.
gestorPlugins.cargarNiveles([
    [
        { nombre: 'jQuery', pasos: 'cdn,local,cdn',
          cdn: 'https://code.jquery.com/jquery-3.7.1.min.js',
          local: '<?php echo BASE_URL; ?>/Publico/Recursos/jquery/jquery-3.7.1.min.js' }
    ],
    [
        { nombre: 'Bootstrap JS', pasos: 'cdn,local,cdn',
          cdn: 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js',
          local: '<?php echo BASE_URL; ?>/Publico/Recursos/bootstrap/js/bootstrap.bundle.min.js' },
        { nombre: 'Leaflet JS', pasos: 'cdn,local,cdn',
          cdn: 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js',
          local: '<?php echo BASE_URL; ?>/Publico/Recursos/leaflet/leaflet.js' },
        { nombre: 'Select2 JS', pasos: 'cdn,local,cdn',
          cdn: 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js',
          local: '<?php echo BASE_URL; ?>/Publico/Recursos/select2/js/select2.min.js' },
        { nombre: 'DataTables JS', pasos: 'local,cdn',
          cdn: 'https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js',
          local: '<?php echo BASE_URL; ?>/Publico/Recursos/datatables/jquery.dataTables.min.js' },
        { nombre: 'Chart.js', pasos: 'cdn,local,cdn',
          cdn: 'https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js',
          local: '<?php echo BASE_URL; ?>/Publico/Recursos/chartjs/chart.umd.min.js' },
        { nombre: 'SweetAlert2 JS', pasos: 'local,cdn',
          cdn: 'https://cdn.jsdelivr.net/npm/sweetalert2@11/sweetalert2.min.js',
          local: '<?php echo BASE_URL; ?>/Publico/Recursos/sweetalert/sweetalert2.min.js' },
        { nombre: 'Alertify JS', pasos: 'local,cdn',
          cdn: 'https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/alertify.min.js',
          local: '<?php echo BASE_URL; ?>/Publico/Recursos/alertify/alertify.min.js' }
    ],
    [
        { nombre: 'DataTables Bootstrap 5', pasos: 'local,cdn',
          cdn: 'https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js',
          local: '<?php echo BASE_URL; ?>/Publico/Recursos/datatables/dataTables.bootstrap5.min.js' }
    ]
]).then(function () {
    gestorPlugins.alListo(inicializarPanel);
    gestorPlugins.reportarFallos();
});
</script>
</body>
</html>
