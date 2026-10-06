<?php
/*====================ENCABEZADO====================
CONTROLADOR: usuariosController — gestión de usuarios
ARCHIVO: Controladores/usuariosController.php
==================================================*/

/*=====================DETALLES=====================
QUÉ HACE: gestiona el CRUD de usuarios: lista, muestra el
    formulario de alta y edición, valida el envío, y
    ejecuta borrado y cambio de estado.
VINCULADO A: lo llama index.php en las rutas /usuarios*;
    llama a Modelos/Usuario.php y a verificarTokenCsrf()
    de Configuracion/seguridad.php.
SI SE ALTERA: revisar los name del formulario y las rutas
    de index.php; toda escritura debe seguir exigiendo POST
    y token CSRF válido.
FECHA: 2026-10-05 | LUGAR: Ocotal, Nueva Segovia
ESCRITO POR: ING. DENIS MANUEL LÓPEZ MOLINA.
==================================================*/

/*================CUERPO DEL CÓDIGO=================*/

class usuariosController {
/**
 * Valores admitidos en tipo_usuario. La columna es varchar sin CHECK
 * en la base, así que esta lista es la única que evita datos basura.
 */
private const TIPOS = ['superadmin', 'admin', 'operador', 'lector'];

    /**
     * ====================ENCABEZADO====================
     * FUNCIÓN: esSuperadmin() | ROL: controlador (privado)
     * ==================================================
     * =====================DETALLES=====================
     * QUÉ HACE: indica si quien está en sesión es superadministrador.
     * VINCULADO A: lo exigen index(), eliminar() y eliminarVarios();
     *     delega en esSuperadminActual(), que lee el rol de la base.
     * SI SE ALTERA: si contesta true para cualquiera, cualquiera borra
     *     usuarios.
     * LÍMITES: un superadmin nunca puede borrarse a sí mismo ni a otro
     *     superadmin; el rol se lee de la base, no de la sesión.
     * FECHA: 2026-10-05 | LUGAR: Ocotal, Nueva Segovia
     * ESCRITO POR: ING. DENIS MANUEL LÓPEZ MOLINA.
     * ==================================================
     */
    private function esSuperadmin(): bool
    {
        return esSuperadminActual();
    }

    /**
     * ====================ENCABEZADO====================
     * FUNCIÓN: esElPropio() | ROL: controlador (privado)
     * ==================================================
     * =====================DETALLES=====================
     * QUÉ HACE: indica si un id corresponde al usuario en sesión.
     * VINCULADO A: lo usan eliminar() y eliminarVarios() para no borrar
     *     la propia cuenta; el id se toma de usuarioActual().
     * SI SE ALTERA: si falla, el admin podría borrarse y quedar sin
     *     acceso al panel.
     * FECHA: 2026-10-05 | LUGAR: Ocotal, Nueva Segovia
     * ESCRITO POR: ING. DENIS MANUEL LÓPEZ MOLINA.
     * ==================================================
     */
    private function esElPropio(int $idUsuario): bool
    {
        $usuario = usuarioActual();

        return $usuario !== null && (int)$usuario['id_usuario'] === $idUsuario;
    }

    /**
     * ====================ENCABEZADO====================
     * FUNCIÓN: index() | ROL: controlador
     * ==================================================
     * =====================DETALLES=====================
     * QUÉ HACE: muestra el listado de usuarios con el aviso de la
     *     última operación y de los errores del formulario.
     * VINCULADO A: lo llama index.php en la ruta /usuarios; consulta
     *     Modelos/Usuario.php::all(); renderiza Vistas/Usuarios/index.php.
     * SI SE ALTERA: la vista exige $pageTitle, $activeMenu, $usuarios,
     *     $tipos y $mensaje; si falta alguna, la pantalla falla.
     * FECHA: 2026-10-05 | LUGAR: Ocotal, Nueva Segovia
     * ESCRITO POR: ING. DENIS MANUEL LÓPEZ MOLINA.
     * ==================================================
     */
    public function index(): void {
        $pageTitle = 'Usuarios';
        $activeMenu = 'usuarios';
        $usuarios = Usuario::all();
        $tipos = self::TIPOS;
        $mensaje = flashMensaje();
        $errores = flashMensaje('errores_formulario') ?? [];
        $repintados = flashMensaje('valores_formulario') ?? [];
        $modalAbierto = flashMensaje('modal_abierto');
        $esSuperadmin = $this->esSuperadmin();

        include VIEW_PATH . '/Usuarios/index.php';
    }

    /**
     * ====================ENCABEZADO====================
     * FUNCIÓN: guardar() | ROL: controlador
     * ==================================================
     * =====================DETALLES=====================
     * QUÉ HACE: valida el alta, hashea la clave e inserta el usuario; después
     *     guarda la foto en la carpeta propia del id que le tocó.
     * VINCULADO A: lo llama index.php en la ruta /usuarios/guardar con
     *     POST; usa verificarTokenCsrf(), Usuario::crear(),
     *     Usuario::establecerFoto() y AlmacenFotos::validar() y guardar().
     * SI SE ALTERA: si falla la foto se deshace el alta con Usuario::eliminar();
     *     no debe quedar un usuario creado que no se pidió.
     * FECHA: 2026-10-05 | LUGAR: Ocotal, Nueva Segovia
     * ESCRITO POR: ING. DENIS MANUEL LÓPEZ MOLINA.
     * ==================================================
     */
    public function guardar(): void {
        if (!esPeticionPostOthrow(verificarTokenCsrf($_POST['csrf_token'] ?? null))) {
            return;
        }

        [$errores, $valores] = $this->validardatos(true);

        // volverAlFormulario() redirige y corta la ejecución
        if ($errores !== []) {
            $this->volverAlFormulario('nuevo', $errores, $valores);
        }

        // La foto se valida antes de tocar la base: un archivo inválido no debe
        // dejar un usuario creado a medias.
        try {
            AlmacenFotos::validar($_FILES['foto_usuario'] ?? []);
        } catch (RuntimeException $error) {
            $this->volverAlFormulario('nuevo', ['foto_usuario' => $error->getMessage()], $valores);
            return;
        }

        // Primero el usuario, porque su id es el nombre de la carpeta de la foto.
        $idUsuario = Usuario::crear(
            $valores['tipo_usuario'],
            $valores['descripcion_usuario'],
            $valores['nick_name'],
            $valores['clave']
        );

        if ($idUsuario === null) {
            flashMensaje('mensaje', ['tipo' => 'error', 'texto' => 'No se pudo crear el usuario.']);
            $this->irAUsuarios();
            return;
        }

        if (isset($_FILES['foto_usuario']['error']) && $_FILES['foto_usuario']['error'] !== UPLOAD_ERR_NO_FILE) {
            try {
                Usuario::establecerFoto($idUsuario, AlmacenFotos::guardar($_FILES['foto_usuario'], $idUsuario));
            } catch (RuntimeException $error) {
                // Sin foto el usuario no sirve: se deshace el alta completa
                Usuario::eliminar($idUsuario);
                $this->volverAlFormulario('nuevo', ['foto_usuario' => $error->getMessage()], $valores);
                return;
            }
        }

        flashMensaje('mensaje', ['tipo' => 'success', 'texto' => 'Usuario creado correctamente.']);
        $this->irAUsuarios();
    }

    /**
     * ====================ENCABEZADO====================
     * FUNCIÓN: actualizar() | ROL: controlador
     * ==================================================
     * =====================DETALLES=====================
     * QUÉ HACE: valida la edición y guarda los datos; cambia la clave solo
     *     si el formulario trajo una nueva, y la foto solo si se subió
     *     archivo, en cuyo caso borra la anterior y su carpeta vacía.
     * VINCULADO A: lo llama index.php en /usuarios/actualizar/{id} con POST;
     *     usa verificarTokenCsrf(), Usuario::actualizar(), actualizarClave(),
     *     AlmacenFotos::validar(), guardar() y eliminar().
     * SI SE ALTERA: el campo clave vacío debe seguir significando "no
     *     cambiar", porque en la edición no es obligatorio.
     * FECHA: 2026-10-05 | LUGAR: Ocotal, Nueva Segovia
     * ESCRITO POR: ING. DENIS MANUEL LÓPEZ MOLINA.
     * ==================================================
     */
    public function actualizar(string $id): void {
        if (!esPeticionPostOthrow(verificarTokenCsrf($_POST['csrf_token'] ?? null))) {
            return;
        }

        $idUsuario = (int)$id;
        $usuario = Usuario::find($idUsuario);

        if ($usuario === null) {
            flashMensaje('mensaje', ['tipo' => 'error', 'texto' => 'El usuario no existe.']);
            $this->irAUsuarios();
            return;
        }

        [$errores, $valores] = $this->validardatos(false, $idUsuario);

        if ($errores !== []) {
            $valores['id_usuario'] = $idUsuario;
            $this->volverAlFormulario('editar', $errores, $valores);
        }

        // Sin foto nueva se conserva la que ya tenía el usuario
        $fotoAnterior = (string)($usuario['foto_usuario'] ?? '');

        try {
            AlmacenFotos::validar($_FILES['foto_usuario'] ?? []);
        } catch (RuntimeException $error) {
            $valores['id_usuario'] = $idUsuario;
            $valores['foto_usuario'] = $fotoAnterior;
            $this->volverAlFormulario('editar', ['foto_usuario' => $error->getMessage()], $valores);
            return;
        }

        $hayFotoNueva = isset($_FILES['foto_usuario']['error'])
            && $_FILES['foto_usuario']['error'] !== UPLOAD_ERR_NO_FILE;

        // Primero se escribe la nueva: si fallara, la anterior sigue en su sitio.
        if ($hayFotoNueva) {
            try {
                $foto = AlmacenFotos::guardar($_FILES['foto_usuario'], $idUsuario);
            } catch (RuntimeException $error) {
                $valores['id_usuario'] = $idUsuario;
                $valores['foto_usuario'] = $fotoAnterior;
                $this->volverAlFormulario('editar', ['foto_usuario' => $error->getMessage()], $valores);
                return;
            }
        } else {
            $foto = $fotoAnterior;
        }

        Usuario::actualizar(
            $idUsuario,
            $valores['tipo_usuario'],
            $valores['descripcion_usuario'],
            $valores['nick_name'],
            $foto
        );

        // Con la nueva ya guardada y registrada, la anterior sobra
        if ($hayFotoNueva && $fotoAnterior !== '' && $fotoAnterior !== $foto) {
            AlmacenFotos::eliminar($fotoAnterior);
        }

        // Clave vacía = mantener la actual
        if ($valores['clave'] !== '') {
            Usuario::actualizarClave($idUsuario, $valores['clave']);
        }

        flashMensaje('mensaje', ['tipo' => 'success', 'texto' => 'Usuario actualizado correctamente.']);
        $this->irAUsuarios();
    }

    /**
     * ====================ENCABEZADO====================
     * FUNCIÓN: eliminar() | ROL: controlador
     * ==================================================
     * =====================DETALLES=====================
     * QUÉ HACE: borra el usuario recibido por POST, salvo que sea el mismo
     *     que está en sesión; también elimina su foto y su carpeta.
     * VINCULADO A: lo llama index.php en /usuarios/eliminar con POST; usa
     *     verificarTokenCsrf(), Usuario::find(), Usuario::eliminar() y`n     *     AlmacenFotos::eliminar().
     * SI SE ALTERA: la protección del usuario en sesión no debe quitarse;
     *     borrarse a sí mismo deja el panel sin administrador.
     * FECHA: 2026-10-05 | LUGAR: Ocotal, Nueva Segovia
     * ESCRITO POR: ING. DENIS MANUEL LÓPEZ MOLINA.
     * ==================================================
     */
    public function eliminar(): void {
        if (!esPeticionPostOthrow(verificarTokenCsrf($_POST['csrf_token'] ?? null))) {
            return;
        }

        $idUsuario = (int)($_POST['id_usuario'] ?? 0);

        if ($idUsuario <= 0) {
            flashMensaje('mensaje', ['tipo' => 'error', 'texto' => 'Usuario no válido.']);
            $this->irAUsuarios();
            return;
        }

        // Solo el superadministrador borra usuarios
        if (!$this->esSuperadmin()) {
            flashMensaje('mensaje', ['tipo' => 'error', 'texto' => 'Solo el superadministrador puede eliminar usuarios.']);
            $this->irAUsuarios();
            return;
        }

        if ($this->esElPropio($idUsuario)) {
            flashMensaje('mensaje', ['tipo' => 'error', 'texto' => 'No podés eliminar tu propio usuario.']);
            $this->irAUsuarios();
            return;
        }

        // El superadmin se conserva siempre: no se borra, solo se edita
        // o se desactiva, para no dejar el panel sin administrador.
        $objetivo = Usuario::find($idUsuario);

        if ($objetivo === null) {
            flashMensaje('mensaje', ['tipo' => 'error', 'texto' => 'El usuario no existe.']);
            $this->irAUsuarios();
            return;
        }

        if ($objetivo['tipo_usuario'] === 'superadmin') {
            flashMensaje('mensaje', ['tipo' => 'error', 'texto' => 'El superadministrador no se puede eliminar: solo editar o desactivar.']);
            $this->irAUsuarios();
            return;
        }

        // Se lee la foto antes de borrar la fila: después ya no está
        $usuario = $objetivo;
        Usuario::eliminar($idUsuario);

        // La foto y su carpeta se van con el usuario: si no, quedan ocupando
        // espacio en el servidor para siempre.
        AlmacenFotos::eliminar((string)($usuario['foto_usuario'] ?? ''));

        flashMensaje('mensaje', ['tipo' => 'success', 'texto' => 'Usuario eliminado correctamente.']);
        $this->irAUsuarios();
    }

    /**
     * ====================ENCABEZADO====================
     * FUNCIÓN: eliminarVarios() | ROL: controlador
     * ==================================================
     * =====================DETALLES=====================
     * QUÉ HACE: borra en un solo paso todos los usuarios marcados en los
     *     checkbox, con su foto y su carpeta; excluye al de la sesión.
     * VINCULADO A: lo llama index.php en /usuarios/eliminar-varios con
     *     POST; exige token CSRF y superadmin; los ids llegan en
     *     $_POST['id_usuario'] como arreglo.
     * SI SE ALTERA: no debe borrar ids que no existan: todos se
     *     resuelven con Usuario::find() antes de tocar el disco.
     * LÍMITES: revalida cada id aunque el JS ya los filtró.
     * FECHA: 2026-10-05 | LUGAR: Ocotal, Nueva Segovia
     * ESCRITO POR: ING. DENIS MANUEL LÓPEZ MOLINA.
     * ==================================================
     */
    public function eliminarVarios(): void
    {
        if (!esPeticionPostOthrow(verificarTokenCsrf($_POST['csrf_token'] ?? null))) {
            return;
        }

        if (!$this->esSuperadmin()) {
            flashMensaje('mensaje', ['tipo' => 'error', 'texto' => 'Solo el superadministrador puede eliminar usuarios.']);
            $this->irAUsuarios();
            return;
        }

        $idsSolicitados = $_POST['id_usuario'] ?? [];
        if (!is_array($idsSolicitados)) {
            $idsSolicitados = [$idsSolicitados];
        }

        $borrados = 0;
        $omitidos = 0;

        foreach ($idsSolicitados as $idCrudo) {
            $idUsuario = (int)$idCrudo;

            // El propio usuario y los ids inválidos nunca se borran
            if ($idUsuario <= 0 || $this->esElPropio($idUsuario)) {
                $omitidos++;
                continue;
            }

            $usuario = Usuario::find($idUsuario);
            if ($usuario === null) {
                $omitidos++;
                continue;
            }

            // El superadmin no se borra en lote tampoco: se omite y se avisa
            if ($usuario['tipo_usuario'] === 'superadmin') {
                $omitidos++;
                continue;
            }

            Usuario::eliminar($idUsuario);
            AlmacenFotos::eliminar((string)($usuario['foto_usuario'] ?? ''));
            $borrados++;
        }

        if ($borrados === 0) {
            flashMensaje('mensaje', [
                'tipo' => 'error',
                'texto' => 'No se eliminó ningún usuario.',
            ]);
            $this->irAUsuarios();
            return;
        }

        $texto = "Se eliminaron $borrados usuario(s) con sus fotos.";

        if ($omitidos > 0) {
            $texto .= " Se omitieron $omitidos (tu propio usuario, superadmins o inexistentes).";
        }

        flashMensaje('mensaje', ['tipo' => 'success', 'texto' => $texto]);
        $this->irAUsuarios();
    }

    /**
     * ====================ENCABEZADO====================
     * FUNCIÓN: toggleEstado() | ROL: controlador
     * ==================================================
     * =====================DETALLES=====================
     * QUÉ HACE: alterna estado_usuario entre activo e inactivo por POST,
     *     salvo que se intente desactivar al usuario en sesión.
     * VINCULADO A: lo llama index.php en /usuarios/toggle-estado con POST;
     *     usa verificarTokenCsrf(), Usuario::find() y Usuario::cambiarEstado().
     * SI SE ALTERA: si el login dejara de revisar estado_usuario, una
     *     cuenta desactivada podría seguir entrando al panel.
     * FECHA: 2026-10-05 | LUGAR: Ocotal, Nueva Segovia
     * ESCRITO POR: ING. DENIS MANUEL LÓPEZ MOLINA.
     * ==================================================
     */
    public function toggleEstado(): void {
        if (!esPeticionPostOthrow(verificarTokenCsrf($_POST['csrf_token'] ?? null))) {
            return;
        }

        $idUsuario = (int)($_POST['id_usuario'] ?? 0);
        $usuario = $idUsuario > 0 ? Usuario::find($idUsuario) : null;

        if ($usuario === null) {
            flashMensaje('mensaje', ['tipo' => 'error', 'texto' => 'Usuario no válido.']);
            $this->irAUsuarios();
            return;
        }

        if ($idUsuario === (int)($_SESSION['user_id'] ?? 0)) {
            flashMensaje('mensaje', ['tipo' => 'error', 'texto' => 'No podés desactivar tu propio usuario.']);
            $this->irAUsuarios();
            return;
        }

        $nuevoEstado = $usuario['estado_usuario'] === 'activo' ? 'inactivo' : 'activo';
        Usuario::cambiarEstado($idUsuario, $nuevoEstado);

        flashMensaje('mensaje', [
            'tipo' => 'success',
            'texto' => $nuevoEstado === 'activo' ? 'Usuario activado.' : 'Usuario desactivado.',
        ]);
        $this->irAUsuarios();
    }

    /**
     * ====================ENCABEZADO====================
     * FUNCIÓN: validardatos() | ROL: controlador (privado)
     * ==================================================
     * =====================DETALLES=====================
     * QUÉ HACE: limpia y valida los campos del formulario; devuelve los
     *     errores y los valores ya escapados para repintar el form.
     * VINCULADO A: lo llaman guardar() y actualizar(); consulta
     *     Usuario::existeNick() para el índice único de nick_name.
     * SI SE ALTERA: si agregás un campo, agregarlo también a $errores,
     *     a $valores y al input de la vista, o el dato se pierde.
     * LÍMITES: no valida foto_usuario; esa la valida AlmacenFotos::validar() con
     *     $_FILES porque es una subida de archivo, no un texto.
     * FECHA: 2026-10-05 | LUGAR: Ocotal, Nueva Segovia
     * ESCRITO POR: ING. DENIS MANUEL LÓPEZ MOLINA.
     * ==================================================
     */
    private function validardatos(bool $claveObligatoria, ?int $ignorarId = null): array {
        $errores = [];

        $tipo = trim($_POST['tipo_usuario'] ?? '');
        $descripcion = trim($_POST['descripcion_usuario'] ?? '');
        $nick = trim($_POST['nick_name'] ?? '');
        $clave = $_POST['clave'] ?? '';

        if (!in_array($tipo, self::TIPOS, true)) {
            $errores['tipo_usuario'] = 'Seleccione un tipo de usuario válido.';
        }

        if ($descripcion === '') {
            $errores['descripcion_usuario'] = 'La descripción es obligatoria.';
        } elseif (mb_strlen($descripcion) > 120) {
            $errores['descripcion_usuario'] = 'La descripción no puede superar los 120 caracteres.';
        }

        if ($nick === '') {
            $errores['nick_name'] = 'El usuario (nick) es obligatorio.';
        } elseif (!preg_match('/^[a-zA-Z0-9._-]{3,40}$/', $nick)) {
            $errores['nick_name'] = 'Use 3 a 40 caracteres: letras, números, punto, guion o guion bajo.';
        } elseif (Usuario::existeNick($nick, $ignorarId)) {
            $errores['nick_name'] = 'Ese usuario (nick) ya está registrado.';
        }

        // La foto ya no llega como texto: es un archivo que valida AlmacenFotos

        // En el alta la clave es obligatoria; en la edición, vacía = no cambiar
        if ($clave === '') {
            if ($claveObligatoria) {
                $errores['clave'] = 'La contraseña es obligatoria.';
            }
        } elseif (mb_strlen($clave) < 8) {
            $errores['clave'] = 'La contraseña debe tener al menos 8 caracteres.';
        } elseif (mb_strlen($clave) > 200) {
            $errores['clave'] = 'La contraseña no puede superar los 200 caracteres.';
        }

        return [
            $errores,
            [
                'tipo_usuario' => $tipo,
                'descripcion_usuario' => $descripcion,
                'nick_name' => $nick,
                'foto_usuario' => '', // la fija AlmacenFotos::guardar(); aquí nunca hay texto
                'clave' => $clave,
            ],
        ];
    }

    /**
     * ====================ENCABEZADO====================
     * FUNCIÓN: volverAlFormulario() | ROL: controlador (privado)
     * ==================================================
     * =====================DETALLES=====================
     * QUÉ HACE: guarda errores y valores en sesión y vuelve al formulario.
     * VINCULADO A: lo llaman guardar() y actualizar() cuando validardatos()
     *     encuentra fallos; los lee crear() y editar() con flashMensaje().
     * SI SE ALTERA: redirige y corta con exit; si dejara de cortar, la
     *     acción seguiría escribiendo en la base con datos inválidos.
     * FECHA: 2026-10-05 | LUGAR: Ocotal, Nueva Segovia
     * ESCRITO POR: ING. DENIS MANUEL LÓPEZ MOLINA.
     * ==================================================
     */
    private function volverAlFormulario(string $modal, array $errores, array $valores): void {
        unset($valores['clave']); // la contraseña nunca vuelve al formulario
        flashMensaje('errores_formulario', $errores);
        flashMensaje('valores_formulario', $valores);
        flashMensaje('modal_abierto', $modal);

header('Location: ' . BASE_URL . '/usuarios');
        exit;
    }

    /**
     * ====================ENCABEZADO====================
     * FUNCIÓN: irAUsuarios() | ROL: controlador (privado)
     * ==================================================
     * =====================DETALLES=====================
     * QUÉ HACE: deja un aviso de resultado y vuelve al listado de usuarios.
     * VINCULADO A: la usan todas las acciones del CRUD y toggleEstado(); el
     *     listado toma el aviso con flashMensaje().
     * SI SE ALTERA: el destino debe coincidir con la ruta /usuarios de
     *     index.php; si desaparece, cada escritura queda en error fatal.
     * FECHA: 2026-10-05 | LUGAR: Ocotal, Nueva Segovia
     * ESCRITO POR: ING. DENIS MANUEL LÓPEZ MOLINA.
     * ==================================================
     */
    private function irAUsuarios(): void {
        header('Location: ' . BASE_URL . '/usuarios');
        exit;
    }
}

/*===========FIN DEL FRAGMENTO DE CÓDIGO============*/



