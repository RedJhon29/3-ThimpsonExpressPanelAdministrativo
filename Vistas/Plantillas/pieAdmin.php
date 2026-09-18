    </main>
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/alertify.min.js"></script>
<script>
// Splash screen
window.addEventListener('load', function() {
    setTimeout(function() {
        var splash = document.getElementById('splashScreen');
        if (splash) { splash.style.opacity = '0'; setTimeout(function() { splash.style.display = 'none'; }, 300); }
    }, 800);
});

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
            search: "Buscar:",
            lengthMenu: "Mostrar _MENU_",
            info: "Mostrando _START_ a _END_ de _TOTAL_",
            paginate: { previous: "Anterior", next: "Siguiente" },
            zeroRecords: "No se encontraron resultados"
        }
    });
}

// SweetAlert helpers
window.showToast = function(type, title, text) {
    Swal.fire({ icon: type, title: title, text: text, timer: 3000, showConfirmButton: false, background: '#131517', color: '#fff' });
};
</script>
</body>
</html>
