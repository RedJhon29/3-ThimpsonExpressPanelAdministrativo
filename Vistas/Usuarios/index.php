<!--====================ENCABEZADO====================
VISTA: Usuarios/index — listado de usuarios del panel
ARCHIVO: Vistas/Usuarios/index.php
==================================================-->

<!--=====================DETALLES=====================
QUÉ HACE: pinta la tabla de usuarios y los modales de alta y
    edición, más los formularios POST de borrado y estado.
    La columna de selección y el botón de borrar solo se
    pintan para usuarios borrables: nunca el propio ni otro
    superadmin, que solo se editan o desactivan.
VINCULADO A: lo incluye Controladores/usuariosController.php
    con $usuarios, $tipos, $mensaje, $errores, $repintados,
    $modalAbierto y $esSuperadmin; la identidad sale de
    usuarioActual() y el token de crearTokenCsrf().
SI SE ALTERA: los action de los formularios deben coincidir
    con $routes de index.php; los name de los inputs con
    validardatos() del controlador.
FECHA: 2026-10-05 | LUGAR: Ocotal, Nueva Segovia
ESCRITO POR: ING. DENIS MANUEL LÓPEZ MOLINA.
==================================================-->

<!--================CUERPO DEL CÓDIGO=================-->

<?php
// Un solo token por carga: todos los formularios de la página lo comparten
$tokenCsrf = crearTokenCsrf();
$usuarioEnSesion = usuarioActual();
$idSesion = $usuarioEnSesion !== null ? (int)$usuarioEnSesion['id_usuario'] : 0;

// Un superadmin nunca se borra: solo se edita o se desactiva.
// Por eso su fila no ofrece checkbox ni botón de borrar.
$esBorrable = static function (array $fila) use ($idSesion): bool {
    return (int)$fila['id_usuario'] !== $idSesion
        && $fila['tipo_usuario'] !== 'superadmin';
};

// La columna de selección solo aparece si queda alguien que se pueda borrar
$borrables = array_values(array_filter($usuarios, $esBorrable));
$haySeleccionables = $esSuperadmin && count($borrables) > 0;

// Valores para el modal de alta: vacíos, o los repintados tras un error
$valoresNuevo = array_merge(
    ['tipo_usuario' => 'operador', 'descripcion_usuario' => '', 'nick_name' => '', 'foto_usuario' => ''],
    $modalAbierto === 'nuevo' ? $repintados : []
);

// Valores para el modal de edición: vacíos, o los repintados tras un error
$valoresEditar = $modalAbierto === 'editar' ? $repintados : [];
?>

<?php include VIEW_PATH . '/Plantillas/encabezadoAdmin.php'; ?>
<?php include VIEW_PATH . '/Plantillas/barraLateralAdmin.php'; ?>

<!-- Usuarios Content -->
<div class="row g-3">
    <div class="col-12">
        <div class="table-container">
            <div class="table-header">
                <h5 class="mb-0">Usuarios</h5>

                <div class="d-flex align-items-center gap-2">
                    <?php if ($haySeleccionables): ?>
                        <!-- Barra de acciones masivas: solo si hay filas que se puedan borrar -->
                        <span class="usuario-seleccion-conteo" id="conteoSeleccion" hidden>
                            <span id="cantidadSeleccionada">0</span> seleccionado(s)
                        </span>
                        <button type="submit"
                                class="btn usuario-borrado-masivo"
                                id="btnBorrarSeleccionados"
                                form="formEliminarVarios"
                                hidden>
                            <i class="bi bi-trash"></i> Eliminar seleccionados
                        </button>
                    <?php endif; ?>

                    <button type="button"
                            class="btn btn-primary-custom usuario-nuevo"
                            id="botonNuevoUsuario"
                            data-bs-toggle="modal"
                            data-bs-target="#modalNuevo">
                        <i class="bi bi-plus-lg"></i> Nuevo Usuario
                    </button>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table datatable" style="width:100%">
                    <thead>
                        <tr>
                            <?php if ($haySeleccionables): ?>
                                <th class="usuario-col-seleccion">
                                    <input type="checkbox"
                                           id="seleccionarTodos"
                                           class="form-check-input usuario-seleccion-total"
                                           aria-label="Seleccionar todos los usuarios">
                                </th>
                            <?php endif; ?>
                            <th>ID</th>
                            <th>Tipo</th>
                            <th>Descripción</th>
                            <th>Nick</th>
                            <th>Foto</th>
                            <th>Último Login</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($usuarios as $u): ?>
                            <?php $idUsuario = (int)$u['id_usuario']; ?>
                            <?php $esElPropio = $idUsuario === $idSesion; ?>
                            <?php $puedeBorrarse = $esBorrable($u); ?>
                            <tr>
                                <?php if ($haySeleccionables): ?>
                                    <!-- La celda siempre existe para no desalinear la tabla;
                                         en la fila propia va vacía porque no se puede borrar -->
                                    <td class="usuario-celda-seleccion">
                                        <?php if ($puedeBorrarse): ?>
                                            <input type="checkbox"
                                                   class="form-check-input usuario-seleccion"
                                                   id="usuario-seleccion-<?php echo $idUsuario; ?>"
                                                   value="<?php echo $idUsuario; ?>"
                                                   aria-label="Seleccionar usuario <?php echo htmlspecialchars($u['nick_name']); ?>">
                                        <?php endif; ?>
                                    </td>
                                <?php endif; ?>
                                <td><?php echo $idUsuario; ?></td>
                                <td><?php echo htmlspecialchars($u['tipo_usuario']); ?></td>
                                <td><?php echo htmlspecialchars($u['descripcion_usuario']); ?></td>
                                <td><?php echo htmlspecialchars($u['nick_name']); ?></td>
                                <td class="usuario-celda-foto">
                                    <?php if (!empty($u['foto_usuario'])): ?>
                                        <a href="<?php echo htmlspecialchars(BASE_URL . '/' . $u['foto_usuario']); ?>"
                                           target="_blank"
                                           rel="noopener noreferrer"
                                           title="Ver foto de <?php echo htmlspecialchars($u['nick_name']); ?>">
                                            <img src="<?php echo htmlspecialchars(BASE_URL . '/' . $u['foto_usuario']); ?>"
                                                 alt="Foto de <?php echo htmlspecialchars($u['nick_name']); ?>"
                                                 class="usuario-foto-imagen">
                                        </a>
                                    <?php else: ?>
                                        <span class="usuario-sin-foto">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    $ultimoLogin = $u['ultimo_login'] ?? null;
                                    echo $ultimoLogin === null || $ultimoLogin === ''
                                        ? 'Nunca'
                                        : htmlspecialchars(substr((string)$ultimoLogin, 0, 16));
                                    ?>
                                </td>
                                <td>
                                    <?php if ($u['estado_usuario'] === 'activo'): ?>
                                        <span class="badge bg-success">Activo</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Inactivo</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-1 usuario-acciones">
                                        <button type="button"
                                                class="btn btn-ghost usuario-accion usuario-accion-editar"
                                                title="Editar"
                                                aria-label="Editar usuario <?php echo htmlspecialchars($u['nick_name']); ?>"
                                                data-bs-toggle="modal"
                                                data-bs-target="#modalEditar"
                                                data-id="<?php echo $idUsuario; ?>"
                                                data-tipo="<?php echo htmlspecialchars($u['tipo_usuario']); ?>"
                                                data-descripcion="<?php echo htmlspecialchars($u['descripcion_usuario']); ?>"
                                                data-nick="<?php echo htmlspecialchars($u['nick_name']); ?>"
                                                data-foto="<?php echo htmlspecialchars($u['foto_usuario'] ?? ''); ?>">
                                            <i class="bi bi-pencil fs-5"></i>
                                        </button>

                                        <button type="submit"
                                                class="btn btn-ghost usuario-accion usuario-accion-eliminar"
                                                form="form-eliminar-<?php echo $idUsuario; ?>"
                                                title="<?php
                                                    echo $esElPropio
                                                        ? 'No podés eliminar tu propio usuario'
                                                        : ($u['tipo_usuario'] === 'superadmin'
                                                            ? 'El superadministrador no se puede eliminar'
                                                            : ($esSuperadmin ? 'Eliminar' : 'Solo el superadministrador puede eliminar'));
                                                ?>"
                                                aria-label="Eliminar usuario <?php echo htmlspecialchars($u['nick_name']); ?>"
                                                style="color:var(--destructive);<?php echo ($esSuperadmin && $puedeBorrarse) ? '' : 'opacity:0.4;cursor:not-allowed;'; ?>"
                                                <?php echo ($esSuperadmin && $puedeBorrarse) ? '' : 'disabled'; ?>>
                                            <i class="bi bi-trash fs-5"></i>
                                        </button>

                                        <button type="submit"
                                                class="btn btn-ghost usuario-accion usuario-accion-estado"
                                                form="form-estado-<?php echo $idUsuario; ?>"
                                                title="<?php echo $u['estado_usuario'] === 'activo' ? 'Desactivar' : 'Activar'; ?>"
                                                aria-label="Cambiar estado de <?php echo htmlspecialchars($u['nick_name']); ?>"
                                                style="color:<?php echo $u['estado_usuario'] === 'activo' ? 'var(--success)' : 'var(--muted)'; ?>;<?php echo $esElPropio ? 'opacity:0.4;cursor:not-allowed;' : ''; ?>"
                                                <?php echo $esElPropio ? 'disabled' : ''; ?>>
                                            <i class="bi <?php echo $u['estado_usuario'] === 'activo' ? 'bi-toggle-on' : 'bi-toggle-off'; ?> fs-5"></i>
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
</div>

<!-- Los formularios viven fuera de la tabla: HTML no permite <form> dentro de <table> -->
<?php if ($haySeleccionables): ?>
    <form id="formEliminarVarios"
          method="POST"
          action="<?php echo BASE_URL; ?>/usuarios/eliminar-varios"
          class="d-none"
          data-confirmar="Se eliminarán los usuarios seleccionados con sus fotos. ¿Continuamos?">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($tokenCsrf); ?>">
    </form>
<?php endif; ?>

<?php foreach ($usuarios as $u): ?>
    <?php $idUsuario = (int)$u['id_usuario']; ?>

    <?php if ($esBorrable($u)): ?>
        <form id="form-eliminar-<?php echo $idUsuario; ?>"
              method="POST"
              action="<?php echo BASE_URL; ?>/usuarios/eliminar"
              class="d-none"
              data-confirmar="¿Eliminar a <strong><?php echo htmlspecialchars($u['nick_name']); ?></strong>? Esta acción no se puede deshacer.">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($tokenCsrf); ?>">
            <input type="hidden" name="id_usuario" value="<?php echo $idUsuario; ?>">
        </form>
    <?php endif; ?>

    <form id="form-estado-<?php echo $idUsuario; ?>"
          method="POST"
          action="<?php echo BASE_URL; ?>/usuarios/toggle-estado"
          class="d-none"
          data-confirmar="<?php echo $u['estado_usuario'] === 'activo' ? 'Desactivar' : 'Activar'; ?> a <strong><?php echo htmlspecialchars($u['nick_name']); ?></strong>?">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($tokenCsrf); ?>">
        <input type="hidden" name="id_usuario" value="<?php echo $idUsuario; ?>">
    </form>
<?php endforeach; ?>

<!-- Modal: Nuevo Usuario -->
<div class="modal fade" id="modalNuevo" tabindex="-1" aria-labelledby="modalNuevoLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST"
                  action="<?php echo BASE_URL; ?>/usuarios/guardar"
                  id="formNuevoUsuario"
                  class="form-usuario form-usuario-nuevo"
                  enctype="multipart/form-data"
                  novalidate>
                <div class="modal-header">
                    <h5 class="modal-title" id="modalNuevoLabel">
                        <i class="bi bi-person-plus"></i> Nuevo Usuario
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <?php
                    $valores = $valoresNuevo;
                    $prefijoId = 'nuevo';
                    $claveOpcional = false;
                    $textoBoton = 'Crear Usuario';
                    include VIEW_PATH . '/Usuarios/modales_usuarios.php';
                    ?>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Editar Usuario -->
<div class="modal fade" id="modalEditar" tabindex="-1" aria-labelledby="modalEditarLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST"
                  action=""
                  id="formEditarUsuario"
                  class="form-usuario form-usuario-editar"
                  enctype="multipart/form-data"
                  novalidate>
                <div class="modal-header">
                    <h5 class="modal-title" id="modalEditarLabel">
                        <i class="bi bi-pencil-square"></i> Editar Usuario
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <?php
                    $valores = $valoresEditar;
                    $prefijoId = 'editar';
                    $claveOpcional = true;
                    $textoBoton = 'Guardar Cambios';
                    include VIEW_PATH . '/Usuarios/modales_usuarios.php';
                    ?>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
/*====================ENCABEZADO====================
FUNCIÓN: conectarAccionesUsuarios() | ROL: vista (JS)
==================================================
=====================DETALLES=====================
QUÉ HACE: pide confirmación SweetAlert antes de enviar los
    formularios de borrado y de cambio de estado, muestra el
    aviso de la última operación, rellena el modal de edición
    y reabre el modal correcto tras un error de validación.
VINCULADO A: usa data-confirmar de los forms, los data-* de
    los botones de editar y window.showToast() de pieAdmin.php.
SI SE ALTERA: si cambian los ids de los modales o los data-*
    de los botones, revisar los selectores de este script.
FECHA: 2026-10-05 | LUGAR: Ocotal, Nueva Segovia
ESCRITO POR: ING. DENIS MANUEL LÓPEZ MOLINA.
==================================================*/
function conectarAccionesUsuarios() {
    var mensaje = <?php echo json_encode($mensaje, JSON_UNESCAPED_UNICODE); ?>;
    var modalAbierto = <?php echo json_encode($modalAbierto, JSON_UNESCAPED_UNICODE); ?>;

    if (mensaje && window.showToast) {
        window.showToast(mensaje.tipo === 'error' ? 'error' : 'success', mensaje.tipo === 'error' ? 'Acción rechazada' : 'Listo', mensaje.texto);
    }

    if (typeof Swal === 'undefined') { return; }

    // Confirmación antes de borrar o cambiar estado
    document.querySelectorAll('form[data-confirmar]').forEach(function (formulario) {
        formulario.addEventListener('submit', function (evento) {
            evento.preventDefault();

            Swal.fire({
                icon: 'warning',
                title: '¿Confirmás la acción?',
                html: formulario.getAttribute('data-confirmar'),
                showCancelButton: true,
                confirmButtonText: 'Sí, continuar',
                cancelButtonText: 'Cancelar',
                reverseButtons: true,
                background: '#131517',
                color: '#fff'
            }).then(function (resultado) {
                if (resultado.isConfirmed) { formulario.submit(); }
            });
        });
    });

    // Rellenar el modal de edición con los datos de la fila.
    // El input de foto es type=file: no admite precargar un valor, por eso
    // solo se muestra la foto que ya tiene guardada el usuario.
    var modalEditar = document.getElementById('modalEditar');
    if (modalEditar) {
        modalEditar.addEventListener('show.bs.modal', function (evento) {
            var boton = evento.relatedTarget;
            if (!boton) { return; }

            var id = boton.getAttribute('data-id');
            var form = document.getElementById('formEditarUsuario');
            form.action = '<?php echo BASE_URL; ?>/usuarios/actualizar/' + id;

            form.querySelector('[name="tipo_usuario"]').value = boton.getAttribute('data-tipo');
            form.querySelector('[name="descripcion_usuario"]').value = boton.getAttribute('data-descripcion');
            form.querySelector('[name="nick_name"]').value = boton.getAttribute('data-nick');
            form.querySelector('[name="clave"]').value = '';
            form.querySelector('[name="foto_usuario"]').value = '';

            var previa = document.getElementById('usuario-editar-foto_usuario-previa');
            if (previa) {
                previa.hidden = !boton.getAttribute('data-foto');
            }
        });
    }

    // Previsualizar la imagen elegida antes de enviarla
    document.querySelectorAll('input.usuario-campo-foto').forEach(function (campo) {
        campo.addEventListener('change', function () {
            // El id del preview comparte prefijo: usuario-nuevo-foto_usuario-previa
            var previa = document.getElementById(campo.id + '-previa');
            if (!previa) { return; }

            var archivo = campo.files && campo.files[0];
            if (!archivo) {
                previa.hidden = true;
                return;
            }

            var lector = new FileReader();
            lector.onload = function (evento) {
                previa.innerHTML = '';
                var imagen = document.createElement('img');
                imagen.src = evento.target.result;
                imagen.alt = 'Vista previa de la foto elegida';
                imagen.className = 'usuario-foto-imagen';
                previa.appendChild(imagen);

                var nota = document.createElement('span');
                nota.className = 'usuario-campo-ayuda d-block mt-1';
                nota.textContent = archivo.name;
                previa.appendChild(nota);

                previa.hidden = false;
            };
            lector.readAsDataURL(archivo);
        });
    });

    // Selección múltiple: marca filas, refleja el total y arma el POST masivo.
    // Solo el superadmin recibe este bloque: el botón no existe si no puede borrar.
    var selectorTodos = document.getElementById('seleccionarTodos');
    var formularioMasivo = document.getElementById('formEliminarVarios');
    var botonMasivo = document.getElementById('btnBorrarSeleccionados');
    var conteo = document.getElementById('conteoSeleccion');

    if (selectorTodos && formularioMasivo && botonMasivo) {
        var marcarTodos = function (marcar) {
            document.querySelectorAll('input.usuario-seleccion').forEach(function (casilla) {
                casilla.checked = marcar;
            });
            actualizarSeleccion();
        };

        var actualizarSeleccion = function () {
            var marcadas = document.querySelectorAll('input.usuario-seleccion:checked');
            var total = document.querySelectorAll('input.usuario-seleccion').length;

            if (conteo) {
                conteo.hidden = marcadas.length === 0;
                document.getElementById('cantidadSeleccionada').textContent = marcadas.length;
            }
            botonMasivo.hidden = marcadas.length === 0;

            // El checkbox de la cabecera solo se marca si están todas
            selectorTodos.checked = total > 0 && marcadas.length === total;
            selectorTodos.indeterminate = marcadas.length > 0 && marcadas.length < total;

            // Un input por id seleccionado. El nombre lleva corchetes para que
            // PHP los junte en un arreglo: sin ellos solo llegaría el último.
            formularioMasivo.querySelectorAll('input[name="id_usuario[]"]').forEach(function (campo) {
                campo.remove();
            });
            marcadas.forEach(function (casilla) {
                var oculto = document.createElement('input');
                oculto.type = 'hidden';
                oculto.name = 'id_usuario[]';
                oculto.value = casilla.value;
                formularioMasivo.appendChild(oculto);
            });
        };

        selectorTodos.addEventListener('change', function () {
            marcarTodos(selectorTodos.checked);
        });

        document.querySelectorAll('input.usuario-seleccion').forEach(function (casilla) {
            casilla.addEventListener('change', actualizarSeleccion);
        });

        actualizarSeleccion();
    }

    // Tras un error de validación, reabrir el modal que falló
    if (modalAbierto === 'nuevo') {
        var m = new bootstrap.Modal(document.getElementById('modalNuevo'));
        m.show();
    } else if (modalAbierto === 'editar') {
        var m2 = new bootstrap.Modal(document.getElementById('modalEditar'));
        m2.show();
    }
}

gestorPlugins.alListo(conectarAccionesUsuarios);
</script>

<?php include VIEW_PATH . '/Plantillas/pieAdmin.php'; ?>
