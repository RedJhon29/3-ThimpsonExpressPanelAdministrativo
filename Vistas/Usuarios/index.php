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
                                    <?php
                                    // imagenesModel::url() arma la ruta y cae al
                                    // default si el registro no tiene imagen.
                                    $fotoUrl = imagenesModel::url((string)($u['foto_usuario'] ?? ''));
                                    ?>
                                    <a href="<?php echo htmlspecialchars($fotoUrl); ?>"
                                       target="_blank"
                                       rel="noopener noreferrer"
                                       title="Ver foto de <?php echo htmlspecialchars($u['nick_name']); ?>">
                                        <img src="<?php echo htmlspecialchars($fotoUrl); ?>"
                                             alt="Foto de <?php echo htmlspecialchars($u['nick_name']); ?>"
                                             class="usuario-foto-imagen">
                                    </a>
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
                    $fotoPreviaActual = '';   // en el alta no hay foto previa
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
                    // En el alta no hay foto previa. En la edición la foto actual se
                    // inyecta por JS desde data-foto del botón, porque el
                    // modal se renderiza antes de saber a quién se edita.
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
    Los formularios de alta y edición se envían por fetch y
    responden con un aviso suave, sin recargar en caso de fallo.
VINCULADO A: usa data-confirmar de los forms, los data-* de
    los botones de editar y window.showToast() de pieAdmin.php.
SI SE ALTERA: si cambian los ids de los modales, las clases
    .form-usuario o los data-* de los botones, revisar los
    selectores de este script.
FECHA: 2026-10-05 | LUGAR: Ocotal, Nueva Segovia
ESCRITO POR: ING. DENIS MANUEL LÓPEZ MOLINA.
==================================================*/
function conectarAccionesUsuarios() {
    var mensaje = <?php echo json_encode($mensaje, JSON_UNESCAPED_UNICODE); ?>;
    var modalAbierto = <?php echo json_encode($modalAbierto, JSON_UNESCAPED_UNICODE); ?>;
    var esSuperadmin = <?php echo $esSuperadmin ? 'true' : 'false'; ?>;

    // Avisos suaves de resultado. Van centrados como la confirmación de
    // borrado, pero sin botones: se cierran solos. El texto y la
    // sugerencia llegan ya en lenguaje natural desde el controlador.
    // Si la librería no cargó, se cae al toast de Alertify para que el
    // resultado de la acción nunca quede sin avisar.
    var mostrarAviso = function (tipo, texto, sugerencia) {
        if (typeof Swal === 'undefined') {
            if (window.showToast) {
                window.showToast(tipo, tipo === 'error' ? 'Acción rechazada' : 'Listo', texto);
            }
            return;
        }

        Swal.fire({
            icon: tipo === 'error' ? 'error' : 'success',
            title: texto,
            html: sugerencia
                ? '<div style="margin-top:10px;font-size:13.5px;opacity:0.85;line-height:1.45">' + sugerencia + '</div>'
                : '',
            timer: tipo === 'error' ? 6000 : 3000,
            showConfirmButton: false,
            background: '#131517',
            color: '#fff',
            customClass: { popup: 'swal-aviso-usuario' }
        });
    };

    // Resultado de acciones que llegan por POST clásico y recargan la página:
    // eliminar, eliminar varios y cambiar estado.
    if (mensaje) {
        mostrarAviso(mensaje.tipo, mensaje.texto, mensaje.sugerencia);
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

            // La foto actual se pinta desde data-foto del boton que abrio
            // el modal. La nueva arranca oculta hasta que se elija archivo.
            var fotoActual = boton.getAttribute('data-foto');
            var cajaActual = document.getElementById('usuario-editar-foto_usuario-actual');
            var cajaNueva = document.getElementById('usuario-editar-foto_usuario-nueva');

            if (cajaActual) {
                cajaActual.hidden = !fotoActual;
                if (fotoActual) {
                    cajaActual.querySelector('img').src = '<?php echo BASE_URL; ?>/' + fotoActual;
                }
            }

            if (cajaNueva) {
                cajaNueva.hidden = true;
                cajaNueva.querySelector('img').removeAttribute('src');
            }

            // Limpiar el file: si se abre el modal de otro usuario sin
            // elegir nada, no debe quedar el archivo del anterior.
            form.querySelector('[name="foto_usuario"]').value = '';
        });
    }

// =========================================================================
// Validación de la foto elegida, antes de subir nada.
//
// El atributo accept del input solo oculta opciones en el diálogo del
// sistema: con "Todos los archivos" o arrastrando y soltando igual entra
// un PDF. Por eso acá se revisa la firma binaria real del archivo, la
// extensión y el peso, y cada caso falla con su propia alerta.
// =========================================================================

var FOTO_EXTENSIONES_PERMITIDAS = ['png', 'jpg', 'jpeg', 'webp'];
var FOTO_TAMANO_MAXIMO = 2097152;   // 2 MB exactos

/**
 * ====================ENCABEZADO====================
 * FUNCIÓN: obtenerExtension() | ROL: helper JS
 * ==================================================
 * =====================DETALLES=====================
 * QUÉ HACE: saca la extensión en minúsculas del nombre del archivo.
 * VINCULADO A: la invocan revisarArchivoFoto() y la validación de envío.
 * SI SE ALTERA: si devuelve mal, un PDF con extensión en mayúscula
 *     se colaría por el filtro de formatos.
 * FECHA: 2026-10-09 | LUGAR: Ocotal, Nueva Segovia
 * ESCRITO POR: ING. DENIS MANUEL LÓPEZ MOLINA.
 * ==================================================
 */
function obtenerExtension(nombreArchivo) {
    var partes = String(nombreArchivo || '').split('.');
    return partes.length > 1 ? partes.pop().toLowerCase() : '';
}

/**
 * ====================ENCABEZADO====================
 * FUNCIÓN: formatearPeso() | ROL: helper JS
 * ==================================================
 * =====================DETALLES=====================
 * QUÉ HACE: presenta los bytes como MB con coma decimal (es-419).
 * VINCULADO A: la invoca revisarArchivoFoto() para el mensaje de peso.
 * SI SE ALTERA: si no divide entre 1048576, la alerta mostraría bytes crudos.
 * FECHA: 2026-10-09 | LUGAR: Ocotal, Nueva Segovia
 * ESCRITO POR: ING. DENIS MANUEL LÓPEZ MOLINA.
 * ==================================================
 */
function formatearPeso(bytes) {
    return (bytes / (1024 * 1024)).toFixed(2).replace('.', ',') + ' MB';
}

/**
 * ====================ENCABEZADO====================
 * FUNCIÓN: leerFirmaBinaria() | ROL: helper JS
 * ==================================================
 * =====================DETALLES=====================
 * QUÉ HACE: promises con los primeros 12 bytes del archivo, que dicen
 *     qué es realmente y no lo que promete su extensión.
 * VINCULADO A: la invocan revisarArchivoFoto() y la validación de envío;
 *     su equivalente en PHP es leerBytesIniciales() de imagenesModel.
 * SI SE ALTERA: si leyera menos bytes, la firma WEBP quedaría incompleta
 *     y toda imagen .webp sería rechazada.
 * FECHA: 2026-10-09 | LUGAR: Ocotal, Nueva Segovia
 * ESCRITO POR: ING. DENIS MANUEL LÓPEZ MOLINA.
 * ==================================================
 */
function leerFirmaBinaria(archivo) {
    return new Promise(function (resolver) {
        var lector = new FileReader();

        lector.onload = function (evento) {
            var buffer = new Uint8Array(evento.target.result);
            resolver(Array.prototype.slice.call(buffer, 0, 12));
        };
        lector.onerror = function () { resolver([]); };
        lector.readAsArrayBuffer(archivo.slice(0, 12));
    });
}

/**
 * ====================ENCABEZADO====================
 * FUNCIÓN: firmaCoincideConFormato() | ROL: helper JS
 * ==================================================
 * =====================DETALLES=====================
 * QUÉ HACE: compara los bytes de cabecera con la imagen que anuncia
 *     la extensión, para que un PDF renombrado a .jpg no fool al filtro.
 * VINCULADO A: la invoca revisarArchivoFoto(); comparte las firmas con
 *     validarFirmaBinaria() en Modelos/imagenesModel.php.
 * SI SE ALTERA: una firma mal puesta deja pasar un archivo disfrazado o
 *     rechaza fotos legítimas; ambos falla en el modal de usuarios.
 * FECHA: 2026-10-09 | LUGAR: Ocotal, Nueva Segovia
 * ESCRITO POR: ING. DENIS MANUEL LÓPEZ MOLINA.
 * ==================================================
 */
function firmaCoincideConFormato(bytes, extension) {
    if (bytes.length < 4) { return false; }

    var a = bytes[0], b = bytes[1], c = bytes[2], d = bytes[3];

    if (extension === 'png') {
        return a === 0x89 && b === 0x50 && c === 0x4E && d === 0x47;
    }
    if (extension === 'jpg' || extension === 'jpeg') {
        return a === 0xFF && b === 0xD8 && c === 0xFF;
    }
    if (extension === 'webp') {
        // RIFF????WEBP: hacen falta los 12 bytes para ver la etiqueta final
        if (bytes.length < 12) { return false; }
        var firma = String.fromCharCode.apply(null, bytes.slice(0, 4));
        var tipo = String.fromCharCode.apply(null, bytes.slice(8, 12));
        return firma === 'RIFF' && tipo === 'WEBP';
    }
    return false;
}

/**
 * ====================ENCABEZADO====================
 * FUNCIÓN: mostrarAvisoFoto() | ROL: helper JS
 * ==================================================
 * =====================DETALLES=====================
* QUÉ HACE: levanta la SweetAlert de rechazo con su título y su
 *     explicación, en el mismo estilo oscuro del resto del panel.
 * VINCULADO A: la invocan revisarArchivoFoto() y la validación de envío.
 * SI SE ALTERA: si pierde el customClass o el botón OK, la alerta pierde
 *     el estilo y el usuario no tiene cómo cerrarla a mano.
 * FECHA: 2026-10-09 | LUGAR: Ocotal, Nueva Segovia
 * ESCRITO POR: ING. DENIS MANUEL LÓPEZ MOLINA.
 * ==================================================
 */
function mostrarAvisoFoto(titulo, detalle) {
    if (typeof Swal === 'undefined') { return; }

    Swal.fire({
        icon: 'error',
        title: titulo,
        html: detalle,
        background: '#131517',
        color: '#fff',
        showConfirmButton: true,
        confirmButtonText: 'OK',
        buttonsStyling: false,
        customClass: { popup: 'swal-aviso-usuario', confirmButton: 'swal-boton-ok' }
    });
}

/**
 * ====================ENCABEZADO====================
 * FUNCIÓN: revisarArchivoFoto() | ROL: helper JS
 * ==================================================
 * =====================DETALLES=====================
 * QUÉ HACE: junte las razones por las que el archivo no se acepta, o
 *     devuelve vacío si pasa formato, contenido y peso.
 * VINCULADO A: la invocan el listener del input file y el submit del
 *     formulario; las mismas reglas rigen en imagenesModel::validar().
 * SI SE ALTERA: si acepta un formato o un peso que el servidor no,
 *     el usuario sube el archivo y recibe el error tarde y sin aviso previo.
 * FECHA: 2026-10-09 | LUGAR: Ocotal, Nueva Segovia
 * ESCRITO POR: ING. DENIS MANUEL LÓPEZ MOLINA.
 * ==================================================
 */
function revisarArchivoFoto(archivo, bytes) {
    var problemas = [];
    var extension = obtenerExtension(archivo.name);

    if (FOTO_EXTENSIONES_PERMITIDAS.indexOf(extension) === -1) {
        problemas.push({
            titulo: 'Formato de archivo no permitido',
            detalle: extension
                ? 'La extensión <strong>.' + extension + '</strong> no está entre las permitidas.'
                : 'Ese archivo no tiene extensión.',
            sugerencia: 'Solo se aceptan fotos en formato PNG, JPG, JPEG o WEBP.'
        });
    } else if (!firmaCoincideConFormato(bytes, extension)) {
        problemas.push({
            titulo: 'El archivo no contiene una foto',
            detalle: 'Tiene extensión <strong>.' + extension + '</strong> pero su contenido no es una imagen de ese formato.',
            sugerencia: 'Probablemente lo renombraste. Elegí el archivo original de tu foto.'
        });
    }

    if (archivo.size > FOTO_TAMANO_MAXIMO) {
        problemas.push({
            titulo: 'La imagen es demasiado pesada',
            detalle: 'Pesa <strong>' + formatearPeso(archivo.size) + '</strong> y el máximo permitido es 2 MB.',
            sugerencia: 'Reducí el tamaño de la foto o elegí una imagen más liviana.'
        });
    }

    return problemas;
}

// Vista previa de la foto elegida. En el alta reemplaza la del default;
// en la edición muestra la nueva al lado de la actual. El id del
// preview comparte prefijo: usuario-nuevo-foto_usuario-previa.
document.querySelectorAll('input.usuario-campo-foto').forEach(function (campo) {
    var esEdicion = campo.id.indexOf('editar') !== -1;
    var cajaNueva = document.getElementById(campo.id + '-nueva');

    campo.addEventListener('change', function () {
        var archivo = campo.files && campo.files[0];

        // Sin archivo elegido: en el alta se vuelve a mostrar el default
        // y en la edicion queda solo la foto actual.
        if (!archivo) {
            if (cajaNueva) {
                cajaNueva.hidden = esEdicion;
                if (esEdicion) {
                    cajaNueva.querySelector('img').removeAttribute('src');
                }
            }
            return;
        }

        if (!cajaNueva) { return; }

        leerFirmaBinaria(archivo).then(function (bytes) {
            var problemas = revisarArchivoFoto(archivo, bytes);

            if (problemas.length > 0) {
                // Limpiar el input evita que el archivo rechazado quede
                // seleccionado y se envie con el siguiente formulario.
                campo.value = '';

                if (esEdicion) {
                    cajaNueva.hidden = true;
                    cajaNueva.querySelector('img').removeAttribute('src');
                }

                if (problemas.length === 1) {
                    mostrarAvisoFoto(problemas[0].titulo, problemas[0].detalle + '<div style="margin-top:10px;font-size:13.5px;opacity:0.85;line-height:1.45">' + problemas[0].sugerencia + '</div>');
                } else {
                    var detalle = problemas.map(function (problema) {
                        return '<div style="margin-bottom:8px;font-size:13.5px;line-height:1.45"><strong>' + problema.titulo + '</strong><br>' + problema.detalle + '</div>';
                    }).join('');

                    mostrarAvisoFoto('No pudimos aceptar ese archivo', detalle);
                }
                return;
            }

            var lector = new FileReader();
            lector.onload = function (evento) {
                cajaNueva.hidden = false;
                cajaNueva.querySelector('img').src = evento.target.result;
            };
            lector.readAsDataURL(archivo);
        });
    });
});

    // Solo el superadministrador crea y edita usuarios. El botón de alta y
    // los de editar se ocultan a los demás roles: el servidor igual los
    // rechaza, esto solo evita mostrar controles que no van a funcionar.
    if (!esSuperadmin) {
        document.getElementById('botonNuevoUsuario')?.remove();

        document.querySelectorAll('button[aria-label^="Editar usuario"]').forEach(function (boton) {
            boton.remove();
        });
    }

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

    // Marca los campos que el servidor@Restó como inválidos
    var marcarCampos = function (formulario, errores) {
        formulario.querySelectorAll('.is-invalid').forEach(function (campo) {
            campo.classList.remove('is-invalid');
        });
        formulario.querySelectorAll('div.marca-error').forEach(function (nota) {
            nota.remove();
        });

        Object.keys(errores || {}).forEach(function (nombre) {
            var campo = formulario.querySelector('[name="' + nombre + '"]');
            if (!campo) { return; }

            campo.classList.add('is-invalid');

            var nota = document.createElement('div');
            nota.className = 'invalid-feedback d-block marca-error';
            nota.textContent = errores[nombre];
            campo.insertAdjacentElement('afterend', nota);
        });
    };

    // Envía un formulario de usuario por fetch y muestra el aviso.
    // Con exito recarga el listado; con fallo deja el modal abierto para
    // que el usuario no pierda lo que escribio.
    var enviarFormulario = function (formulario) {
        if (typeof Swal === 'undefined') {
            formulario.submit();   // sin librerías, el envío normal funciona
            return;
        }

        var boton = formulario.querySelector('button[type="submit"]');
        var textoOriginal = boton ? boton.innerHTML : '';
        if (boton) {
            boton.disabled = true;
            boton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span> Guardando…';
        }

        fetch(formulario.action, {
            method: 'POST',
            body: new FormData(formulario),
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(function (respuesta) {
                return respuesta.json().then(function (datos) {
                    return { ok: respuesta.ok, datos: datos };
                });
            })
            .then(function (resultado) {
                var datos = resultado.datos || {};

                if (datos.ok) {
                    mostrarAviso('success', datos.texto, datos.sugerencia);
                    // Se recarga para que la tabla muestre el usuario nuevo
                    setTimeout(function () {
                        window.location.href = datos.redirect || '<?php echo BASE_URL; ?>/usuarios';
                    }, 1400);
                    return;
                }

                marcarCampos(formulario, datos.errores);
                mostrarAviso('error', datos.texto, datos.detalle || datos.sugerencia);
            })
            .catch(function () {
                mostrarAviso(
                    'error',
                    'No pudimos conectarnos con el servidor.',
                    'Revisá tu conexión a internet e intentá de nuevo.'
                );
            })
            .finally(function () {
                if (boton) {
                    boton.disabled = false;
                    boton.innerHTML = textoOriginal;
                }
            });
    };

    document.querySelectorAll('.form-usuario').forEach(function (formulario) {
        formulario.addEventListener('submit', function (evento) {
            // Ultima barrera del lado del cliente: si el campo quedó con un
            // archivo rechazado (por ejemplo porque la validación de arriba
            // no llegó a correr), el formulario no se envía. El servidor
            // igual valida todo; esto solo evita el viaje de ida y vuelta.
            var campoFoto = formulario.querySelector('input[type="file"]');

            if (campoFoto && campoFoto.files && campoFoto.files.length > 0) {
                var pendientes = revisarArchivoFoto(
                    campoFoto.files[0],
                    leerFirmaBinaria(campoFoto.files[0])
                );

                if (pendientes.length > 0) {
                    evento.preventDefault();
                    campoFoto.value = '';

                    mostrarAvisoFoto(
                        pendientes[0].titulo,
                        pendientes[0].detalle + '<div style="margin-top:10px;font-size:13.5px;opacity:0.85;line-height:1.45">' + pendientes[0].sugerencia + '</div>'
                    );
                    return;
                }
            }

            evento.preventDefault();
            enviarFormulario(formulario);
        });
    });

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
