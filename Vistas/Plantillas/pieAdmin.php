<!--====================ENCABEZADO====================
PLANTILLA: pieAdmin — cierre del layout del panel
ARCHIVO: Vistas/Plantillas/pieAdmin.php
==================================================-->

<!--=====================DETALLES=====================
QUÉ HACE: cierra main y sidebar, carga las librerías del panel,
    define el toast global y el cierre de sesión con confirmación.
VINCULADO A: lo cierra la última línea de cada vista del panel;
    abre los contenedores Vistas/Plantillas/barraLateralAdmin.php.
SI SE ALTERA: si se rompe el cierre, todas las vistas se desbordan;
    revisar el orden encabezado → barra → vista → pie.
FECHA: 2026-10-02 | LUGAR: Ocotal, Nueva Segovia
ESCRITO POR: ING. DENIS MANUEL LÓPEZ MOLINA.
==================================================-->

<!--================CUERPO DEL CÓDIGO=================-->

    </main>
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="<?php echo BASE_URL; ?>/Publico/Recursos/datatables/jquery.dataTables.min.js"></script>
<script src="<?php echo BASE_URL; ?>/Publico/Recursos/datatables/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script src="<?php echo BASE_URL; ?>/Publico/Recursos/sweetalert/sweetalert2.min.js"></script>
<script src="<?php echo BASE_URL; ?>/Publico/Recursos/alertify/alertify.min.js"></script>
<script>
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
prepararCierreSesion();
</script>
</body>
</html>
