/**
 * App JS — Admin Panel
 */
document.addEventListener('DOMContentLoaded', function () {

    // Select2
    if (typeof $.fn.select2 !== 'undefined') {
        $('.select2').select2({ theme: 'bootstrap-5', width: '100%' });
    }

    // DataTables
    if (typeof $.fn.DataTable !== 'undefined') {
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
    window.showToast = function (type, title, text) {
        Swal.fire({ icon: type, title: title, text: text, timer: 3000, showConfirmButton: false });
    };

    window.showConfirm = function (title, text, callback) {
        Swal.fire({
            title: title, text: text, icon: 'question',
            showCancelButton: true, confirmButtonColor: '#FBB03B', cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí', cancelButtonText: 'Cancelar'
        }).then(function(result) { if (result.isConfirmed && callback) callback(); });
    };

});
