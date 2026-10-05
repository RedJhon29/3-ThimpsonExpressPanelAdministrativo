<!--====================ENCABEZADO====================
PLANTILLA: pieAdmin — cierre del layout del panel
ARCHIVO: Vistas/Plantillas/pieAdmin.php
==================================================-->

<!--=====================DETALLES=====================
QUÉ HACE: cierra main y sidebar, carga las librerías por la
    cadena del gestor, inicializa la UI y alerta fallos.
VINCULADO A: lo cierra la última línea de cada vista del panel;
    abre los contenedores Vistas/Plantillas/barraLateralAdmin.php.
SI SE ALTERA: si se rompe el cierre o la cadena de scripts,
    todas las vistas se desbordan o pierden sus librerías.
FECHA: 2026-10-02 | LUGAR: Ocotal, Nueva Segovia
ESCRITO POR: ING. DENIS MANUEL LÓPEZ MOLINA.
==================================================-->

<!--================CUERPO DEL CÓDIGO=================-->

    </main>
</div>

<!-- Scripts -->
<script>
/*====================ENCABEZADO====================
FUNCIÓN: inicializarPanel() | ROL: plantilla (JS)
==================================================
=====================DETALLES=====================
QUÉ HACE: cablea el sidebar, Select2, DataTables, el toast
    y la alerta de cierre de sesión tras cargar las libs.
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

    // Select2
    if (typeof $ !== 'undefined' && $.fn.select2) {
        $('.select2').select2({ theme: 'bootstrap-5', width: '100%' });
    }

    // DataTables
    if (typeof $ !== 'undefined' && $.fn.DataTable) {
        $('.datatable').DataTable({
            language: {
                url: '<?php echo BASE_URL; ?>/Publico/Recursos/datatables/i18n/es-ES.json',
                // El JSON oficial no trae paginate de raíz y el renderer Bootstrap
                // lee ese texto: sin esto los botones quedan en inglés.
                paginate: { first: "Primero", last: "Último", next: "Siguiente", previous: "Anterior" }
            }
        });
    }

    // SweetAlert helpers
    window.showToast = function(type, title, text) {
        Swal.fire({ icon: type, title: title, text: text, timer: 3000, showConfirmButton: false, background: '#131517', color: '#fff' });
    };

    prepararCierreSesion();
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
