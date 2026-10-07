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
    llama a Modelos/usuariosModel.php y a verificarTokenCsrf()
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
 * FUNCIÓN: traducirError() | ROL: controlador (privado)
 * ==================================================
 * =====================DETALLES=====================
 * QUÉ HACE: convierte una clave de errorAplicacionModel en un texto
 *     que una persona entienda, con una sugerencia de arreglo.
 * VINCULADO A: lo llaman guardar() y actualizar() cuando la
 *     petición pide JSON; el texto viaja en la respuesta.
 * SI SE ALTERA: si se agrega una clave a errorAplicacionModel y no
 *     está en el mapa, el usuario vería el mensaje genérico.
 * FECHA: 2026-10-05 | LUGAR: Ocotal, Nueva Segovia
 * ESCRITO POR: ING. DENIS MANUEL LÓPEZ MOLINA.
 * ==================================================
 */
    private function traducirError(string $clave): array {
        $mensajes = [
            errorAplicacionModel::NICK_DUPLICADO => [
                'texto' => 'Ese nombre de usuario ya está en uso.',
                'sugerencia' => 'Elegí otro nombre de usuario para este registro.',
            ],
            errorAplicacionModel::NICK_INEXISTENTE => [
                'texto' => 'El usuario que querés modificar ya no existe.',
                'sugerencia' => 'Actualizá la página para ver la lista de usuarios actual.',
            ],
            errorAplicacionModel::CLAVE_CORTA => [
                'texto' => 'La contraseña es demasiado corta.',
                'sugerencia' => 'Usá una contraseña de al menos 8 caracteres.',
            ],
            errorAplicacionModel::CLAVE_LARGA => [
                'texto' => 'La contraseña es demasiado larga.',
                'sugerencia' => 'Limitala a 200 caracteres.',
            ],
            errorAplicacionModel::CLAVE_VACIA => [
                'texto' => 'Falta la contraseña del usuario.',
                'sugerencia' => 'Escribí una contraseña de al menos 8 caracteres.',
            ],
            errorAplicacionModel::FOTO_PESADA => [
                'texto' => 'La foto que elegiste pesa demasiado.',
                'sugerencia' => 'Elegí una imagen de menos de 2 MB.',
            ],
            errorAplicacionModel::FOTO_TIPO_INVALIDO => [
                'texto' => 'Ese archivo no es una imagen válida.',
                'sugerencia' => 'Subí una foto en formato PNG, JPEG o WEBP.',
            ],
            errorAplicacionModel::FOTO_NO_RECIBIDA => [
                'texto' => 'No se recibió la foto.',
                'sugerencia' => 'Volvé a elegir el archivo e intentá otra vez.',
            ],
            errorAplicacionModel::FOTO_SUBIDA_FALLIDA => [
                'texto' => 'La carga de la foto se interrumpió.',
                'sugerencia' => 'Revisá tu conexión o elegí un archivo más chico.',
            ],
            errorAplicacionModel::FOTO_NO_GUARDADA => [
                'texto' => 'No pudimos guardar la foto en el servidor.',
                'sugerencia' => 'Intentá de nuevo en un momento; si sigue igual, avisá al administrador.',
            ],
            errorAplicacionModel::FOTO_CARPETA => [
                'texto' => 'No se pudo preparar la carpeta donde van las fotos.',
                'sugerencia' => 'Verificá los permisos de escritura del servidor y avisá al administrador.',
            ],
            errorAplicacionModel::USUARIO_NO_EXISTE => [
                'texto' => 'Ese usuario ya no existe.',
                'sugerencia' => 'Actualizá la página para ver la lista de usuarios actual.',
            ],
            errorAplicacionModel::SIN_PERMISO => [
                'texto' => 'No tenés permiso para hacer esa acción.',
                'sugerencia' => 'Solo el superadministrador puede eliminar usuarios.',
            ],
            errorAplicacionModel::SESION_EXPIRADA => [
                'texto' => 'Tu sesión expiró o la página estuvo demasiado tiempo abierta.',
                'sugerencia' => 'Recargá la página e intentá de nuevo.',
            ],
            errorAplicacionModel::SIN_USUARIOS => [
                'texto' => 'No hay ningún usuario que se pueda eliminar.',
                'sugerencia' => 'Activá la casilla de al menos un usuario antes de continuar.',
            ],
            errorAplicacionModel::ERROR_DE_GUARDADO => [
                'texto' => 'No pudimos guardar el usuario.',
                'sugerencia' => 'Revisá los datos e intentá de nuevo en un momento.',
            ],
        ];

        // Last resort: nunca se muestra una clave técnica al usuario
        return $mensajes[$clave] ?? [
            'texto' => 'Algo salió mal y no pudimos completar la operación.',
            'sugerencia' => 'Intentá de nuevo en un momento. Si el problema sigue, avisá al administrador.',
        ];
    }

/**
     * ====================ENCABEZADO====================
     * FUNCIÓN: responderErrorTraducido() | ROL: controlador (privado)
     * ==================================================
     * =====================DETALLES=====================
     * QUÉ HACE: devuelve un JSON de error con la forma que espera el
     *           JavaScript (ok, tipo, texto, sugerencia).
     * VINCULADO A: lo usan guardar() y actualizar() para los fallos
     *     que no pasan por validardatos(), como sesión expirada.
     * SI SE ALTERA: si faltara la clave ok, el JavaScript lo tomaría
     *     como un acierto y mostraría el error con el ícono correcto.
     * FECHA: 2026-10-05 | LUGAR: Ocotal, Nueva Segovia
     * ESCRITO POR: ING. DENIS MANUEL LÓPEZ MOLINA.
     * ==================================================
     */
    private function responderErrorTraducido(string $clave, int $codigo = 422): void
    {
        $m = $this->traducirError($clave);

        responderJson([
            'ok' => false,
            'tipo' => 'error',
            'texto' => $m['texto'],
            'detalle' => $m['sugerencia'],
            'sugerencia' => $m['sugerencia'],
        ], $codigo);
    }

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
     *     Modelos/usuariosModel.php::all(); renderiza Vistas/Usuarios/index.php.
     * SI SE ALTERA: la vista exige $pageTitle, $activeMenu, $usuarios,
     *     $tipos y $mensaje; si falta alguna, la pantalla falla.
     * FECHA: 2026-10-05 | LUGAR: Ocotal, Nueva Segovia
     * ESCRITO POR: ING. DENIS MANUEL LÓPEZ MOLINA.
     * ==================================================
     */
    public function index(): void {
        $pageTitle = 'Usuarios';
        $activeMenu = 'usuarios';
        $usuarios = usuariosModel::all();
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
     *     POST; usa verificarTokenCsrf(), usuariosModel::crear(),
     *     usuariosModel::establecerFoto() y almacenFotosModel::validar() y guardar().
     * SI SE ALTERA: si falla la foto se deshace el alta con usuariosModel::eliminar();
     *     no debe quedar un usuario creado que no se pidió.
     * FECHA: 2026-10-05 | LUGAR: Ocotal, Nueva Segovia
     * ESCRITO POR: ING. DENIS MANUEL LÓPEZ MOLINA.
     * ==================================================
     */
    public function guardar(): void {
        $json = pideRespuestaJson();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            if ($json) {
                $this->responderErrorTraducido(errorAplicacionModel::SESION_EXPIRADA, 405);
            }
            $this->irAUsuarios();
            return;
        }

        if (!verificarTokenCsrf($_POST['csrf_token'] ?? null)) {
            if ($json) {
                $this->responderErrorTraducido(errorAplicacionModel::SESION_EXPIRADA, 403);
            }
            $this->volverAlFormulario('nuevo', [], []);
            return;
        }

        try {
            [$errores, $valores] = $this->validardatos(true);

            if ($errores !== []) {
                $this->responderValidacion($json, 'nuevo', $errores, $valores);
                return;
            }

            // La foto se valida antes de tocar la base: un archivo inválido no
            // debe dejar un usuario creado a medias.
            almacenFotosModel::validar($_FILES['foto_usuario'] ?? []);

            // Primero el usuario, porque su id es el nombre de la carpeta de la foto.
            $idUsuario = usuariosModel::crear(
                $valores['tipo_usuario'],
                $valores['descripcion_usuario'],
                $valores['nick_name'],
                $valores['clave']
            );

            if ($idUsuario === null) {
                throw new errorAplicacionModel(errorAplicacionModel::ERROR_DE_GUARDADO, 'usuariosModel::crear devolvio null', 500);
            }

            if (almacenFotosModel::hayArchivo($_FILES['foto_usuario'] ?? [])) {
                try {
                    usuariosModel::establecerFoto($idUsuario, almacenFotosModel::guardar($_FILES['foto_usuario'], $idUsuario));
                } catch (errorAplicacionModel $error) {
                    // Sin foto el usuario no sirve: se deshace el alta completa
                    usuariosModel::eliminar($idUsuario);
                    throw $error;
                }
            }
        } catch (errorAplicacionModel $error) {
            $this->responderFallo($json, 'nuevo', $error, $valores ?? []);
            return;
        } catch (Throwable $error) {
            // PDOException u otros imprevistos: no se muestra el detalle al usuario
            error_log('[usuarios] error al guardar: ' . $error->getMessage());
            $this->responderFallo(
                $json,
                'nuevo',
                new errorAplicacionModel(errorAplicacionModel::ERROR_DE_GUARDADO, $error->getMessage(), 500),
                $valores ?? []
            );
            return;
        }

        if ($json) {
            responderJson([
                'ok' => true,
                'tipo' => 'success',
                'texto' => '¡Listo! El usuario se creó correctamente.',
                'sugerencia' => 'Ahora podés administrarlo desde la tabla.',
                'redirect' => BASE_URL . '/usuarios',
            ]);
        }

        flashMensaje('mensaje', ['tipo' => 'success', 'texto' => 'Usuario creado correctamente.']);
        $this->irAUsuarios();
    }

    /**
     * ====================ENCABEZADO====================
     * FUNCIÓN: responderValidacion() | ROL: controlador (privado)
     * ==================================================
     * =====================DETALLES=====================
     * QUÉ HACE: devuelve los errores de campo al cliente, o vuelve al
     *           formulario con los datos escritos si no pidió JSON.
     * VINCULADO A: la llaman guardar() y actualizar() cuando
     *     validardatos() encuentra algo; el texto por campo es el
     *     que ya devuelve validardatos(), en lenguaje natural.
     * SI SE ALTERA: si el JS espera otra forma de respuesta, la
     *     ventana se quedaría esperando y no marcaría los campos.
     * FECHA: 2026-10-05 | LUGAR: Ocotal, Nueva Segovia
     * ESCRITO POR: ING. DENIS MANUEL LÓPEZ MOLINA.
     * ==================================================
     */
    private function responderValidacion(bool $json, string $modal, array $errores, array $valores): void
    {
        if (!$json) {
            $this->volverAlFormulario($modal, $errores, $valores);
            return;
        }

        $primero = reset($errores);

        responderJson([
            'ok' => false,
            'tipo' => 'error',
            'texto' => 'No pudimos crear el usuario porque hay datos que revisar.',
            'detalle' => is_string($primero) ? $primero : 'Revisá los campos marcados.',
            'sugerencia' => 'Corregí los campos señalados abajo y volvé a guardar.',
            'errores' => $errores,
        ], 422);
    }

    /**
     * ====================ENCABEZADO====================
     * FUNCIÓN: responderFallo() | ROL: controlador (privado)
     * ==================================================
     * =====================DETALLES=====================
     * QUÉ HACE: traduce un errorAplicacionModel y lo devuelve como JSON,
     *           o vuelve al formulario si la petición no pidió JSON.
     * VINCULADO A: la llaman guardar() y actualizar() en sus catch.
     * SI SE ALTERA: si dejara pasar una clave técnica, el usuario
     *     vería un mensaje incomprensible en lugar de una ayuda.
     * FECHA: 2026-10-05 | LUGAR: Ocotal, Nueva Segovia
     * ESCRITO POR: ING. DENIS MANUEL LÓPEZ MOLINA.
     * ==================================================
     */
    private function responderFallo(bool $json, string $modal, errorAplicacionModel $error, array $valores): void
    {
        if (!$json) {
            $this->volverAlFormulario($modal, [$modal === 'editar' ? 'foto_usuario' : 'foto_usuario' => $error->getMessage()], $valores);
            return;
        }

        $m = $this->traducirError($error->clave);

        responderJson([
            'ok' => false,
            'tipo' => 'error',
            'texto' => $m['texto'],
            'detalle' => $m['sugerencia'],
            'sugerencia' => $m['sugerencia'],
        ], $error->getCode() >= 400 && $error->getCode() < 600 ? $error->getCode() : 422);
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
     *     usa verificarTokenCsrf(), usuariosModel::actualizar(), actualizarClave(),
     *     almacenFotosModel::validar(), guardar() y eliminar().
     * SI SE ALTERA: el campo clave vacío debe seguir significando "no
     *     cambiar", porque en la edición no es obligatorio.
     * FECHA: 2026-10-05 | LUGAR: Ocotal, Nueva Segovia
     * ESCRITO POR: ING. DENIS MANUEL LÓPEZ MOLINA.
     * ==================================================
     */
    public function actualizar(string $id): void {
        $json = pideRespuestaJson();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            if ($json) {
                $this->responderErrorTraducido(errorAplicacionModel::SESION_EXPIRADA, 405);
            }
            $this->irAUsuarios();
            return;
        }

        if (!verificarTokenCsrf($_POST['csrf_token'] ?? null)) {
            if ($json) {
                $this->responderErrorTraducido(errorAplicacionModel::SESION_EXPIRADA, 403);
            }
            $this->volverAlFormulario('editar', [], []);
            return;
        }

        $idUsuario = (int)$id;
        $usuario = usuariosModel::find($idUsuario);

        if ($usuario === null) {
            if ($json) {
                $this->responderErrorTraducido(errorAplicacionModel::USUARIO_NO_EXISTE, 404);
            }
            flashMensaje('mensaje', ['tipo' => 'error', 'texto' => 'El usuario no existe.']);
            $this->irAUsuarios();
            return;
        }

        $valores = [];

        try {
            [$errores, $valores] = $this->validardatos(false, $idUsuario);

            if ($errores !== []) {
                $valores['id_usuario'] = $idUsuario;
                $this->responderValidacion($json, 'editar', $errores, $valores);
                return;
            }

            // Sin foto nueva se conserva la que ya tenía el usuario
            $fotoAnterior = (string)($usuario['foto_usuario'] ?? '');

            almacenFotosModel::validar($_FILES['foto_usuario'] ?? []);

            $foto = $hayFotoNueva = almacenFotosModel::hayArchivo($_FILES['foto_usuario'] ?? [])
                ? almacenFotosModel::guardar($_FILES['foto_usuario'], $idUsuario)
                : $fotoAnterior;

            usuariosModel::actualizar(
                $idUsuario,
                $valores['tipo_usuario'],
                $valores['descripcion_usuario'],
                $valores['nick_name'],
                $foto
            );

            // Con la nueva ya guardada y registrada, la anterior sobra
            if ($hayFotoNueva && $fotoAnterior !== '' && $fotoAnterior !== $foto) {
                almacenFotosModel::eliminar($fotoAnterior);
            }

            // Clave vacía = mantener la actual
            if ($valores['clave'] !== '' && !usuariosModel::actualizarClave($idUsuario, $valores['clave'])) {
                throw new errorAplicacionModel(errorAplicacionModel::ERROR_DE_GUARDADO, 'actualizarClave devolvio false', 500);
            }
        } catch (errorAplicacionModel $error) {
            $valores['id_usuario'] = $idUsuario;
            $this->responderFallo($json, 'editar', $error, $valores);
            return;
        } catch (Throwable $error) {
            error_log('[usuarios] error al actualizar: ' . $error->getMessage());
            $valores['id_usuario'] = $idUsuario;
            $this->responderFallo(
                $json,
                'editar',
                new errorAplicacionModel(errorAplicacionModel::ERROR_DE_GUARDADO, $error->getMessage(), 500),
                $valores
            );
            return;
        }

        if ($json) {
            responderJson([
                'ok' => true,
                'tipo' => 'success',
                'texto' => '¡Listo! Los cambios se guardaron correctamente.',
                'sugerencia' => 'La tabla ya muestra la información actualizada.',
                'redirect' => BASE_URL . '/usuarios',
            ]);
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
     *     verificarTokenCsrf(), usuariosModel::find(), usuariosModel::eliminar() y`n     *     almacenFotosModel::eliminar().
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
        $objetivo = usuariosModel::find($idUsuario);

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
        usuariosModel::eliminar($idUsuario);

        // La foto y su carpeta se van con el usuario: si no, quedan ocupando
        // espacio en el servidor para siempre.
        almacenFotosModel::eliminar((string)($usuario['foto_usuario'] ?? ''));

        flashMensaje('mensaje', [
            'tipo' => 'success',
            'texto' => 'El usuario se eliminó correctamente.',
            'sugerencia' => 'Se eliminó a <strong>' . htmlspecialchars((string)($usuario['nick_name'] ?? ''), ENT_QUOTES, 'UTF-8') . '</strong> junto con su foto.',
        ]);
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
     *     resuelven con usuariosModel::find() antes de tocar el disco.
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

            $usuario = usuariosModel::find($idUsuario);
            if ($usuario === null) {
                $omitidos++;
                continue;
            }

            // El superadmin no se borra en lote tampoco: se omite y se avisa
            if ($usuario['tipo_usuario'] === 'superadmin') {
                $omitidos++;
                continue;
            }

            usuariosModel::eliminar($idUsuario);
            almacenFotosModel::eliminar((string)($usuario['foto_usuario'] ?? ''));
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

        // Se concorda el plural (verbo y sustantivo): "Se eliminó 1 usuario"
        // pero "Se eliminaron 3 usuarios". Evita el feo "usuario(s)".
        $palabra = $borrados === 1 ? 'usuario' : 'usuarios';
        $verbo = $borrados === 1 ? 'Se eliminó' : 'Se eliminaron';
        $texto = "$verbo $borrados $palabra con sus fotos.";

        if ($omitidos > 0) {
            $texto .= $omitidos === 1
                ? " Se omitió 1 usuario (tu propio usuario, un superadmin o inexistente)."
                : " Se omitieron $omitidos usuarios (tu propio usuario, superadmins o inexistentes).";
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
     *     usa verificarTokenCsrf(), usuariosModel::find() y usuariosModel::cambiarEstado().
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
        $usuario = $idUsuario > 0 ? usuariosModel::find($idUsuario) : null;

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
        usuariosModel::cambiarEstado($idUsuario, $nuevoEstado);

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
     *     usuariosModel::existeNick() para el índice único de nick_name.
     * SI SE ALTERA: si agregás un campo, agregarlo también a $errores,
     *     a $valores y al input de la vista, o el dato se pierde.
     * LÍMITES: no valida foto_usuario; esa la valida almacenFotosModel::validar() con
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
        } elseif (usuariosModel::existeNick($nick, $ignorarId)) {
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
                'foto_usuario' => '', // la fija almacenFotosModel::guardar(); aquí nunca hay texto
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





