<!--====================ENCABEZADO====================
VISTA: Usuarios/modales_usuarios — campos de alta y edición
ARCHIVO: Vistas/Usuarios/modales_usuarios.php
==================================================-->

<!--=====================DETALLES=====================
QUÉ HACE: pinta los campos del usuario y el token CSRF. Cada
    input lleva id y clase con prefijo del modal (usuario-nuevo-*
    o usuario-editar-*) para que no se repitan al usar dos veces.
VINCULADO A: lo incluye Vistas/Usuarios/index.php en los
    modales #modalNuevo y #modalEditar; el post va a
    verificarTokenCsrf() y los name a validardatos().
    Espera $valores, $errores, $tipos, $prefijoId,
    $claveOpcional y $textoBoton, que define cada modal.
SI SE ALTERA: los name de los inputs deben coincidir con
    validardatos() en usuariosController; los id los usa
    conectarAccionesUsuarios() para rellenar el modal editar.
FECHA: 2026-10-05 | LUGAR: Ocotal, Nueva Segovia
ESCRITO POR: ING. DENIS MANUEL LÓPEZ MOLINA.
==================================================-->

<!--================CUERPO DEL CÓDIGO=================-->

<?php
// Variables que define el archivo que incluye este partial.
// Se declaran con ?? para que Intelephense no marque "undefined variable"
// y para que el partial no falle si se incluye sin prepararlas.
$valores = $valores ?? [];
$errores = $errores ?? [];
$tipos = $tipos ?? [];
$prefijoId = $prefijoId ?? 'nuevo';
$claveOpcional = $claveOpcional ?? false;
$textoBoton = $textoBoton ?? 'Guardar';

// El modal de edicion se renderiza antes de saber a quien se edita: la
// foto actual la inyecta el JS desde data-foto del boton que lo abrio.
// En el alta no hay foto previa y se muestra la del default.
$previaMostrarDefault = ($prefijoId !== 'editar');

// El partial se incluye dos veces (modales nuevo y editar): sin prefijo,
// los id se repetirían en el DOM y el HTML sería inválido.
$idDe = static function (string $campo) use ($prefijoId): string {
    return 'usuario-' . $prefijoId . '-' . $campo;
};
?>

<input type="hidden"
       name="csrf_token"
       id="<?php echo $idDe('csrf_token'); ?>"
       class="usuario-campo usuario-campo-csrf"
       value="<?php echo htmlspecialchars(crearTokenCsrf()); ?>">

<div class="row g-3">
    <div class="col-md-6">
        <label for="<?php echo $idDe('tipo_usuario'); ?>" class="form-label">Tipo de usuario</label>
        <select name="tipo_usuario"
                id="<?php echo $idDe('tipo_usuario'); ?>"
                class="form-select usuario-campo usuario-campo-tipo"
                aria-describedby="<?php echo $idDe('tipo_usuario-error'); ?>"
                required>
            <?php foreach ($tipos as $tipo): ?>
                <option value="<?php echo htmlspecialchars($tipo); ?>"
                    <?php echo ($valores['tipo_usuario'] ?? '') === $tipo ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars(ucfirst($tipo)); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php if (isset($errores['tipo_usuario'])): ?>
            <div class="invalid-feedback d-block"
                 id="<?php echo $idDe('tipo_usuario-error'); ?>">
                <?php echo htmlspecialchars($errores['tipo_usuario']); ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="col-md-6">
        <label for="<?php echo $idDe('nick_name'); ?>" class="form-label">Usuario (nick)</label>
        <input type="text"
               class="form-control usuario-campo usuario-campo-nick"
               id="<?php echo $idDe('nick_name'); ?>"
               name="nick_name"
               aria-describedby="<?php echo $idDe('nick_name-error'); ?>"
               maxlength="40"
               required
               autocomplete="off"
               placeholder="Ej: jperez"
               value="<?php echo htmlspecialchars($valores['nick_name'] ?? ''); ?>">
        <?php if (isset($errores['nick_name'])): ?>
            <div class="invalid-feedback d-block"
                 id="<?php echo $idDe('nick_name-error'); ?>">
                <?php echo htmlspecialchars($errores['nick_name']); ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="col-12">
        <label for="<?php echo $idDe('descripcion_usuario'); ?>" class="form-label">Descripción / nombre visible</label>
        <input type="text"
               class="form-control usuario-campo usuario-campo-descripcion"
               id="<?php echo $idDe('descripcion_usuario'); ?>"
               name="descripcion_usuario"
               aria-describedby="<?php echo $idDe('descripcion_usuario-error'); ?>"
               maxlength="120"
               required
               placeholder="Ej: Juan Pérez — Supervisor de entregas"
               value="<?php echo htmlspecialchars($valores['descripcion_usuario'] ?? ''); ?>">
        <?php if (isset($errores['descripcion_usuario'])): ?>
            <div class="invalid-feedback d-block"
                 id="<?php echo $idDe('descripcion_usuario-error'); ?>">
                <?php echo htmlspecialchars($errores['descripcion_usuario']); ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="col-md-6">
        <label for="<?php echo $idDe('clave'); ?>" class="form-label">
            Contraseña
            <?php if ($claveOpcional): ?>
                <span class="usuario-campo-ayuda">— dejar vacío para no cambiarla</span>
            <?php endif; ?>
        </label>
        <input type="password"
               class="form-control usuario-campo usuario-campo-clave"
               id="<?php echo $idDe('clave'); ?>"
               name="clave"
               aria-describedby="<?php echo $idDe('clave-error'); ?>"
               <?php echo $claveOpcional ? '' : 'required'; ?>
               minlength="8"
               maxlength="200"
               autocomplete="new-password"
               placeholder="<?php echo $claveOpcional ? 'Sin cambios' : 'Mínimo 8 caracteres'; ?>">
        <?php if (isset($errores['clave'])): ?>
            <div class="invalid-feedback d-block"
                 id="<?php echo $idDe('clave-error'); ?>">
                <?php echo htmlspecialchars($errores['clave']); ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="col-md-6">
        <label for="<?php echo $idDe('foto_usuario'); ?>" class="form-label">Foto de perfil</label>
        <input type="file"
               class="form-control usuario-campo usuario-campo-foto"
               id="<?php echo $idDe('foto_usuario'); ?>"
               name="foto_usuario"
               aria-describedby="<?php echo $idDe('foto_usuario-error'); ?>"
               accept="image/png,image/jpeg,image/webp">
        <!-- Vista previa. En el alta arranca con la foto por defecto; al elegir
             un archivo la reemplaza. En la edición muestra la actual a la
             izquierda y, si se elige otra, la nueva a la derecha. -->
        <div class="usuario-foto-previa mt-2" id="<?php echo $idDe('foto_usuario-previa'); ?>">
            <?php if ($previaMostrarDefault): ?>
                <div class="usuario-foto-caja" id="<?php echo $idDe('foto_usuario-nueva'); ?>">
                    <img src="<?php echo htmlspecialchars(BASE_URL . '/' . usuariosModel::FOTO_POR_DEFECTO); ?>"
                         alt="Foto por defecto que tendrá el usuario"
                         class="usuario-foto-imagen">
                    <span class="usuario-foto-etiqueta">Foto por defecto</span>
                </div>
            <?php else: ?>
                <div class="usuario-foto-caja"
                     id="<?php echo $idDe('foto_usuario-actual'); ?>"
                     <?php /* se muestra solo si el usuario tiene foto */ ?>hidden>
                    <img alt="Foto que tiene el usuario"
                         class="usuario-foto-imagen">
                    <span class="usuario-foto-etiqueta">Foto actual</span>
                </div>
                <div class="usuario-foto-caja" id="<?php echo $idDe('foto_usuario-nueva'); ?>" hidden>
                    <img alt="Foto que se va a subir"
                         class="usuario-foto-imagen">
                    <span class="usuario-foto-etiqueta nueva">Foto nueva</span>
                </div>
            <?php endif; ?>
        </div>
        <?php if (isset($errores['foto_usuario'])): ?>
            <div class="invalid-feedback d-block"
                 id="<?php echo $idDe('foto_usuario-error'); ?>">
                <?php echo htmlspecialchars($errores['foto_usuario']); ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="d-flex gap-2 justify-content-end mt-4">
    <button type="button"
            class="btn btn-secondary-custom usuario-accion-cancelar"
            id="<?php echo $idDe('cancelar'); ?>"
            data-bs-dismiss="modal">Cancelar</button>
    <button type="submit"
            class="btn btn-primary-custom usuario-accion-guardar"
            id="<?php echo $idDe('guardar'); ?>">
        <i class="bi bi-check-lg"></i> <?php echo htmlspecialchars($textoBoton); ?>
    </button>
</div>