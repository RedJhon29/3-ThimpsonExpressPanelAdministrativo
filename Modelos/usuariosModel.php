<?php
/*====================ENCABEZADO====================
MODELO: Usuario — modelo de datos de usuarios del panel
ARCHIVO: Modelos/usuariosModel.php
==================================================*/

/*=====================DETALLES=====================
QUÉ HACE: expone la lectura y la escritura de usuarios
    (listado, búsqueda, alta, edición, borrado y cambio de
    estado) sobre la tabla usuarios de PostgreSQL.
VINCULADO A: lo llama Controladores/usuariosController.php
    y lo consume Vistas/Usuarios/; depende de
    obtenerConexion() en Configuracion/conexion.php.
SI SE ALTERA: cambia el CRUD de usuarios del panel; cada
    clave debe existir en la vista que la pinta y los nombres
    de columna deben coincidir con la tabla usuarios.
FECHA: 2026-10-05 | LUGAR: Ocotal, Nueva Segovia
ESCRITO POR: ING. DENIS MANUEL LÓPEZ MOLINA.
==================================================*/

/*================CUERPO DEL CÓDIGO=================*/

class usuariosModel {
    /**
     * Columnas de la tabla usuarios en el orden del CREATE TABLE.
     * Se mantienen aparte porque loginModel::buscarPorNickName() necesita
     * además clave_usuario (el hash) y esta vista nunca debe leerla.
     */
    private const COLUMNAS = 'id_usuario, tipo_usuario, descripcion_usuario,
                             nick_name, foto_usuario, ultimo_login, estado_usuario';

    /**
     * Raíz de las fotos. Cada usuario sin foto sube recibe una subcarpeta
     * propia nombrada id_nick_AAAAMMDD, así una foto nunca se mezcla con
     * otra y la fecha del alta queda a la vista.
     */
    public const RUTA_FOTOS = 'Publico/Recursos/uploads/usuarios';

    /**
     * Foto por defecto: se copia a la carpeta del usuario cuando no se
     * sube ninguna, para que la tabla nunca muestre un cuadro vacío.
     */
    public const FOTO_POR_DEFECTO = 'Publico/Recursos/uploads/usuarios/default/default.png';

    /** MIME real del archivo -> extensión con que se guarda. */
    private const MIMES_FOTO = [
        'image/png'  => 'png',
        'image/jpeg' => 'jpg',
        'image/webp' => 'webp',
    ];

    /** 2 MB: por encima se rechaza antes de escribir en disco. */
    private const TAMANO_MAXIMO_FOTO = 2097152;

    /**
     * ====================ENCABEZADO====================
     * FUNCIÓN: all() | ROL: modelo
     * ==================================================
     * =====================DETALLES=====================
     * QUÉ HACE: devuelve todos los usuarios de la tabla usuarios.
     * VINCULADO A: lo llama Controladores/usuariosController.php::index()
     *     y lo recorre Vistas/Usuarios/index.php para la tabla.
     * SI SE ALTERA: cambia la tabla de usuarios visible en el panel.
     * FECHA: 2026-10-05 | LUGAR: Ocotal, Nueva Segovia
     * ESCRITO POR: ING. DENIS MANUEL LÓPEZ MOLINA.
     * ==================================================
     */
    public static function all(): array {
        $sentencia = obtenerConexion()->query(
            'SELECT ' . self::COLUMNAS . ' FROM usuarios ORDER BY id_usuario'
        );
        return $sentencia->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * ====================ENCABEZADO====================
     * FUNCIÓN: find() | ROL: modelo
     * ==================================================
     * =====================DETALLES=====================
     * QUÉ HACE: localiza un usuario por su id y devuelve null si no existe.
     * VINCULADO A: lo usa Controladores/usuariosController.php::editar()
     *     para precargar el formulario; el null marca "no encontrado".
     * SI SE ALTERA: quien lo llame debe validar el null antes de leer campos.
     * FECHA: 2026-10-05 | LUGAR: Ocotal, Nueva Segovia
     * ESCRITO POR: ING. DENIS MANUEL LÓPEZ MOLINA.
     * ==================================================
     */
    public static function find(int $id): ?array {
        $sentencia = obtenerConexion()->prepare(
            'SELECT ' . self::COLUMNAS . ' FROM usuarios WHERE id_usuario = ?'
        );
        $sentencia->execute([$id]);
        $fila = $sentencia->fetch(PDO::FETCH_ASSOC);
        return $fila === false ? null : $fila;
    }

    /**
     * ====================ENCABEZADO====================
     * FUNCIÓN: existeNick() | ROL: modelo
     * ==================================================
     * =====================DETALLES=====================
     * QUÉ HACE: indica si un nick ya está tomado, opcionalmente ignorando un id.
     * VINCULADO A: lo llama usuariosController al validar alta y edición;
     *     respalda el índice único usuarios_nick_name_key.
     * SI SE ALTERA: si no excluye $ignorarId, editar un usuario siempre
     *     daría "nick duplicado" contra su propio registro.
     * FECHA: 2026-10-05 | LUGAR: Ocotal, Nueva Segovia
     * ESCRITO POR: ING. DENIS MANUEL LÓPEZ MOLINA.
     * ==================================================
     */
    public static function existeNick(string $nick, ?int $ignorarId = null): bool {
        $sql = 'SELECT 1 FROM usuarios WHERE nick_name = ?';
        $parametros = [$nick];

        if ($ignorarId !== null) {
            $sql .= ' AND id_usuario <> ?';
            $parametros[] = $ignorarId;
        }

        $sentencia = obtenerConexion()->prepare($sql);
        $sentencia->execute($parametros);

        return $sentencia->fetchColumn() !== false;
    }

    /**
     * ====================ENCABEZADO====================
     * FUNCIÓN: crear() | ROL: modelo
     * ==================================================
     * =====================DETALLES=====================
     * QUÉ HACE: inserta un usuario con su clave hasheada y devuelve el id nuevo.
     * VINCULADO A: lo llama usuariosController::guardar(); escribe en la
     *     tabla usuarios y usa password_hash con Argon2id.
     * SI SE ALTERA: id_usuario es IDENTITY ALWAYS, por eso el INSERT no
     *     puede llevar esa columna y el id se recupera con RETURNING.
     * FECHA: 2026-10-05 | LUGAR: Ocotal, Nueva Segovia
     * ESCRITO POR: ING. DENIS MANUEL LÓPEZ MOLINA.
     * ==================================================
     */
    public static function crear(
        string $tipo,
        string $descripcion,
        string $nick,
        string $clave,
        string $foto = ''
    ): ?int {
        // Argon2id: mismo algoritmo que loginModel::verificarClave() espera
        $hash = password_hash($clave, PASSWORD_ARGON2ID);

        if ($hash === false) {
            return null;
        }

        $sentencia = obtenerConexion()->prepare(
            'INSERT INTO usuarios
                (tipo_usuario, descripcion_usuario, nick_name, clave_usuario, foto_usuario, estado_usuario)
             VALUES (?, ?, ?, ?, ?, ?)
             RETURNING id_usuario'
        );
        $sentencia->execute([$tipo, $descripcion, $nick, $hash, $foto, 'activo']);

        $id = $sentencia->fetchColumn();

        return $id === false ? null : (int)$id;
    }

    /**
     * ====================ENCABEZADO====================
     * FUNCIÓN: actualizar() | ROL: modelo
     * ==================================================
     * =====================DETALLES=====================
     * QUÉ HACE: actualiza tipo, descripción, nick y foto de un usuario.
     * VINCULADO A: lo llama usuariosController::actualizar(); no toca
     *     clave_usuario, estado_usuario ni ultimo_login.
     * SI SE ALTERA: si se le agrega la clave, hay que decidir si se
     *     re-hashea; hoy el cambio de clave va en actualizarClave().
     * FECHA: 2026-10-05 | LUGAR: Ocotal, Nueva Segovia
     * ESCRITO POR: ING. DENIS MANUEL LÓPEZ MOLINA.
     * ==================================================
     */
    public static function actualizar(
        int $id,
        string $tipo,
        string $descripcion,
        string $nick,
        string $foto = ''
    ): bool {
        $sentencia = obtenerConexion()->prepare(
            'UPDATE usuarios
                SET tipo_usuario = ?, descripcion_usuario = ?, nick_name = ?, foto_usuario = ?
              WHERE id_usuario = ?'
        );

        return $sentencia->execute([$tipo, $descripcion, $nick, $foto, $id]);
    }

    /**
     * ====================ENCABEZADO====================
     * FUNCIÓN: actualizarClave() | ROL: modelo
     * ==================================================
     * =====================DETALLES=====================
     * QUÉ HACE: reemplaza la clave del usuario por un hash nuevo.
     * VINCULADO A: lo llama usuariosController::actualizar() solo si el
     *     formulario trajo clave; exige clave vacía como "no cambiar".
     * SI SE ALTERA: si deja de usar Argon2id, loginModel::verificarClave()
     *     podría rechazar las claves nuevas.
     * FECHA: 2026-10-05 | LUGAR: Ocotal, Nueva Segovia
     * ESCRITO POR: ING. DENIS MANUEL LÓPEZ MOLINA.
     * ==================================================
     */
    public static function actualizarClave(int $id, string $clave): bool {
        $hash = password_hash($clave, PASSWORD_ARGON2ID);

        if ($hash === false) {
            return false;
        }

        $sentencia = obtenerConexion()->prepare(
            'UPDATE usuarios SET clave_usuario = ? WHERE id_usuario = ?'
        );

        return $sentencia->execute([$hash, $id]);
    }

    /**
     * ====================ENCABEZADO====================
     * FUNCIÓN: cambiarEstado() | ROL: modelo
     * ==================================================
     * =====================DETALLES=====================
     * QUÉ HACE: pasa estado_usuario entre 'activo' e 'inactivo'.
     * VINCULADO A: lo llama usuariosController::toggleEstado(); el login
     *     de loginController::authenticate() rechaza los inactivos.
     * SI SE ALTERA: si escribe otro valor distinto de 'activo', el login
     *     trataría al usuario como desactivado.
     * FECHA: 2026-10-05 | LUGAR: Ocotal, Nueva Segovia
     * ESCRITO POR: ING. DENIS MANUEL LÓPEZ MOLINA.
     * ==================================================
     */
    public static function cambiarEstado(int $id, string $estado): bool {
        $sentencia = obtenerConexion()->prepare(
            'UPDATE usuarios SET estado_usuario = ? WHERE id_usuario = ?'
        );

        return $sentencia->execute([$estado, $id]);
    }

    /**
     * ====================ENCABEZADO====================
     * FUNCIÓN: establecerFoto() | ROL: modelo
     * ==================================================
     * =====================DETALLES=====================
     * QUÉ HACE: guarda en la fila la ruta de la foto ya subida a disco.
     * VINCULADO A: lo llama usuariosController::guardar() después de crear
     *     el usuario, porque la carpeta de la foto lleva su id.
     * SI SE ALTERA: si se borra la columna, la tabla no muestra imágenes.
     * FECHA: 2026-10-05 | LUGAR: Ocotal, Nueva Segovia
     * ESCRITO POR: ING. DENIS MANUEL LÓPEZ MOLINA.
     * ==================================================
     */
    public static function establecerFoto(int $id, string $ruta): bool {
        $sentencia = obtenerConexion()->prepare(
            'UPDATE usuarios SET foto_usuario = ? WHERE id_usuario = ?'
        );

        return $sentencia->execute([$ruta, $id]);
    }

    /**
     * ====================ENCABEZADO====================
     * FUNCIÓN: eliminar() | ROL: modelo
     * ==================================================
     * =====================DETALLES=====================
     * QUÉ HACE: borra físicamente el usuario con ese id.
     * VINCULADO A: lo llama usuariosController::eliminar() después de
     *     validar el token CSRF y que no sea el usuario en sesión.
     * SI SE ALTERA: es un DELETE duro; si mañana hay auditoría o borrado
     *     lógico, hay que cambiarlo por un UPDATE de estado.
     * FECHA: 2026-10-05 | LUGAR: Ocotal, Nueva Segovia
     * ESCRITO POR: ING. DENIS MANUEL LÓPEZ MOLINA.
     * ==================================================
     */
    public static function eliminar(int $id): bool {
        $sentencia = obtenerConexion()->prepare(
            'DELETE FROM usuarios WHERE id_usuario = ?'
        );

        return $sentencia->execute([$id]);
    }

    /**
     * ====================ENCABEZADO====================
     * FUNCIÓN: contarPorEstado() | ROL: modelo
     * ==================================================
     * =====================DETALLES=====================
     * QUÉ HACE: cuenta los usuarios por estado en una sola consulta y
     *     devuelve total, activos e inactivos.
     * VINCULADO A: lo llama Modelos/panelModel.php::getStats() para las
     *     tarjetas de usuarios del dashboard.
     * SI SE ALTERA: los inactivos se derivan restando los activos al
     *     total, no contando un literal, para que las tres tarjetas
     *     siempre sumen y nunca dejen un usuario fuera.
     * LÍMITES: estado_usuario es varchar sin CHECK en la base; solo se
     *     reconoce 'activo' y cualquier otro valor cuenta como inactivo.
     * FECHA: 2026-10-07 | LUGAR: Ocotal, Nueva Segovia
     * ESCRITO POR: ING. DENIS MANUEL LÓPEZ MOLINA.
     * ==================================================
     */
    public static function contarPorEstado(): array {
        $sentencia = obtenerConexion()->query(
            "SELECT COUNT(*) AS total,
                    COUNT(*) FILTER (WHERE estado_usuario = 'activo') AS activos
             FROM usuarios"
        );

        $fila = $sentencia->fetch(PDO::FETCH_ASSOC) ?: ['total' => 0, 'activos' => 0];
        $total = (int) $fila['total'];
        $activos = (int) $fila['activos'];

        return [
            'total' => $total,
            'activos' => $activos,
            'inactivos' => $total - $activos,
        ];
    }

    /**
     * ====================ENCABEZADO====================
     * FUNCIÓN: hayFotoSubida() | ROL: modelo
     * ==================================================
     * =====================DETALLES=====================
     * QUÉ HACE: indica si en el campo file venía realmente un archivo.
     * VINCULADO A: la llaman usuariosController::guardar() y
     *     actualizar() antes de decidir si hay que tocar el disco.
     * SI SE ALTERA: si contesta mal, una subida vacía se procesa como
     *     archivo válido y guardar() fallaría por falta de tmp_name.
     * FECHA: 2026-10-07 | LUGAR: Ocotal, Nueva Segovia
     * ESCRITO POR: ING. DENIS MANUEL LÓPEZ MOLINA.
     * ==================================================
     */
    public static function hayFotoSubida(array $archivo): bool
    {
        return isset($archivo['error']) && $archivo['error'] !== UPLOAD_ERR_NO_FILE;
    }

    /**
     * ====================ENCABEZADO====================
     * FUNCIÓN: validarFoto() | ROL: modelo
     * ==================================================
     * =====================DETALLES=====================
     * QUÉ HACE: revisa el archivo subido sin escribir nada en disco.
     * VINCULADO A: la invocan usuariosController::guardar() y
     *     actualizar() antes de tocar la base, para no dejar registros a medias.
     * SI SE ALTERA: valida el MIME por contenido con finfo, nunca por
     *     extensión; un tipo nuevo se agrega en self::MIMES_FOTO.
     * LÍMITES: lanza RuntimeException con una clave de error; el texto
     *     que ve el usuario lo arma el controlador con su traducción.
     * FECHA: 2026-10-07 | LUGAR: Ocotal, Nueva Segovia
     * ESCRITO POR: ING. DENIS MANUEL LÓPEZ MOLINA.
     * ==================================================
     */
    public static function validarFoto(array $archivo): void
    {
        if (!self::hayFotoSubida($archivo)) {
            return;
        }

        if ($archivo['error'] !== UPLOAD_ERR_OK) {
            throw new RuntimeException('foto_subida_fallida|PHP upload error ' . $archivo['error'], 400);
        }

        if ($archivo['size'] > self::TAMANO_MAXIMO_FOTO) {
            throw new RuntimeException('foto_pesada|tamano ' . $archivo['size'] . ' bytes');
        }

        $info = new finfo(FILEINFO_MIME_TYPE);

        if (!isset(self::MIMES_FOTO[$info->file($archivo['tmp_name'])])) {
            throw new RuntimeException('foto_tipo_invalido|mime ' . $info->file($archivo['tmp_name']));
        }
    }

    /**
     * ====================ENCABEZADO====================
     * FUNCIÓN: nombreCarpetaFoto() | ROL: modelo
     * ==================================================
     * =====================DETALLES=====================
     * QUÉ HACE: arma el nombre id_nick_AAAAMMDD de la carpeta del usuario.
     * VINCULADO A: la usan guardarFoto() y usarFotoPorDefecto().
     * SI SE ALTERA: el prefijo numérico es lo que usa
     *     limpiarCarpetasHuerfanas() para recuperar el id del dueño, así
     *     que debe seguir empezando con el id.
     * FECHA: 2026-10-07 | LUGAR: Ocotal, Nueva Segovia
     * ESCRITO POR: ING. DENIS MANUEL LÓPEZ MOLINA.
     * ==================================================
     */
    public static function nombreCarpetaFoto(int $idUsuario, string $nick, ?string $fecha = null): string
    {
        $limpio = preg_replace('/[^A-Za-z0-9_-]/', '', $nick);
        $limpio = $limpio === '' ? 'usuario' : $limpio;
        $dia = $fecha ?? date('Ymd');

        return $idUsuario . '_' . $limpio . '_' . $dia;
    }

    /**
     * ====================ENCABEZADO====================
     * FUNCIÓN: guardarFoto() | ROL: modelo
     * ==================================================
     * =====================DETALLES=====================
     * QUÉ HACE: escribe la foto en la carpeta propia del usuario y
     *     devuelve la ruta relativa que se guarda en la base.
     * VINCULADO A: la llama usuariosController tras validarFoto(); el
     *     name del campo es foto_usuario.
     * SI SE ALTERA: el nombre del archivo lo genera el servidor con
     *     random_bytes; usar el que envía el cliente permitiría escribir rutas.
     * FECHA: 2026-10-07 | LUGAR: Ocotal, Nueva Segovia
     * ESCRITO POR: ING. DENIS MANUEL LÓPEZ MOLINA.
     * ==================================================
     */
    public static function guardarFoto(array $archivo, int $idUsuario, string $nick, ?string $fecha = null): string
    {
        if (!self::hayFotoSubida($archivo) || $archivo['error'] !== UPLOAD_ERR_OK) {
            throw new RuntimeException('foto_no_recibida');
        }

        $info = new finfo(FILEINFO_MIME_TYPE);
        $extension = self::MIMES_FOTO[$info->file($archivo['tmp_name'])] ?? null;

        if ($extension === null) {
            throw new RuntimeException('foto_tipo_invalido|mime ' . $info->file($archivo['tmp_name']));
        }

        $carpetaRelativa = self::RUTA_FOTOS . '/' . self::nombreCarpetaFoto($idUsuario, $nick, $fecha);
        $carpetaAbsoluta = BASE_PATH . '/' . $carpetaRelativa;

        if (!is_dir($carpetaAbsoluta) && !mkdir($carpetaAbsoluta, 0755, true) && !is_dir($carpetaAbsoluta)) {
            throw new RuntimeException('foto_carpeta|mkdir ' . $carpetaAbsoluta, 500);
        }

        $nombreArchivo = 'usuario_' . bin2hex(random_bytes(8)) . '.' . $extension;

        if (!move_uploaded_file($archivo['tmp_name'], $carpetaAbsoluta . '/' . $nombreArchivo)) {
            throw new RuntimeException('foto_no_guardada|move_uploaded_file ' . $nombreArchivo, 500);
        }

        return $carpetaRelativa . '/' . $nombreArchivo;
    }

    /**
     * ====================ENCABEZADO====================
     * FUNCIÓN: usarFotoPorDefecto() | ROL: modelo
     * ==================================================
     * =====================DETALLES=====================
     * QUÉ HACE: copia default.png a la carpeta del usuario y devuelve su
     *     ruta, para que sin subida la tabla muestre una imagen y no un hueco.
     * VINCULADO A: la llaman usuariosController::guardar() cuando no se
     *     subió archivo, y actualizar() cuando el usuario venía con default.
     * SI SE ALTERA: si el archivo por defecto no está en disco, la copia
     *     falla y el alta se deshace: conviene no borrar esa imagen.
     * FECHA: 2026-10-07 | LUGAR: Ocotal, Nueva Segovia
     * ESCRITO POR: ING. DENIS MANUEL LÓPEZ MOLINA.
     * ==================================================
     */
    public static function usarFotoPorDefecto(int $idUsuario, string $nick, ?string $fecha = null): string
    {
        $origen = BASE_PATH . '/' . self::FOTO_POR_DEFECTO;

        if (!is_file($origen)) {
            throw new RuntimeException('foto_no_guardada|falta ' . self::FOTO_POR_DEFECTO, 500);
        }

        $carpetaRelativa = self::RUTA_FOTOS . '/' . self::nombreCarpetaFoto($idUsuario, $nick, $fecha);
        $carpetaAbsoluta = BASE_PATH . '/' . $carpetaRelativa;

        if (!is_dir($carpetaAbsoluta) && !mkdir($carpetaAbsoluta, 0755, true) && !is_dir($carpetaAbsoluta)) {
            throw new RuntimeException('foto_carpeta|mkdir ' . $carpetaAbsoluta, 500);
        }

        $nombreArchivo = 'default.png';

        if (!copy($origen, $carpetaAbsoluta . '/' . $nombreArchivo)) {
            throw new RuntimeException('foto_no_guardada|copy default.png', 500);
        }

        return $carpetaRelativa . '/' . $nombreArchivo;
    }

    /**
     * ====================ENCABEZADO====================
     * FUNCIÓN: esFotoPorDefecto() | ROL: modelo
     * ==================================================
     * =====================DETALLES=====================
     * QUÉ HACE: indica si la foto guardada es la copia del default.
     * VINCULADO A: la llama usuariosController::actualizar() para saber
     *     si al subir foto nueva debe crear carpeta en vez de reemplazar.
     * SI SE ALTERA: si devuelve mal, reemplazar una copia del default
     *     intentaría escribir en una carpeta que puede no existir.
     * FECHA: 2026-10-07 | LUGAR: Ocotal, Nueva Segovia
     * ESCRITO POR: ING. DENIS MANUEL LÓPEZ MOLINA.
     * ==================================================
     */
    public static function esFotoPorDefecto(string $rutaFoto): bool
    {
        return $rutaFoto !== '' && str_ends_with($rutaFoto, '/' . basename(self::FOTO_POR_DEFECTO));
    }

    /**
     * ====================ENCABEZADO====================
     * FUNCIÓN: eliminarFoto() | ROL: modelo
     * ==================================================
     * =====================DETALLES=====================
     * QUÉ HACE: borra la foto del disco y, si su carpeta queda vacía,
     *     también la elimina.
     * VINCULADO A: la llaman usuariosController al reemplazar la foto,
     *     al borrar un usuario y al borrar varios; y el script de barredora.
     * SI SE ALTERA: solo toca rutas dentro de self::RUTA_FOTOS; con otro
     *     prefijo, un valor manipulado podría borrar archivos ajenos.
     * FECHA: 2026-10-07 | LUGAR: Ocotal, Nueva Segovia
     * ESCRITO POR: ING. DENIS MANUEL LÓPEZ MOLINA.
     * ==================================================
     */
    public static function eliminarFoto(string $rutaFoto): void
    {
        $prefijo = self::RUTA_FOTOS . '/';

        if ($rutaFoto === '' || !str_starts_with($rutaFoto, $prefijo)) {
            return;
        }

        // La carpeta default/ es la fuente del archivo por defecto: nunca
        // se borra aunque quede vacía, porque se usa para las altas nuevas.
        if (str_starts_with($rutaFoto, self::FOTO_POR_DEFECTO)) {
            return;
        }

        $rutaAbsoluta = BASE_PATH . '/' . $rutaFoto;
        if (is_file($rutaAbsoluta)) {
            unlink($rutaAbsoluta);
        }

        $carpeta = dirname($rutaAbsoluta);
        if (is_dir($carpeta) && count(array_diff(scandir($carpeta), ['.', '..'])) === 0) {
            rmdir($carpeta);
        }
    }

    /**
     * ====================ENCABEZADO====================
     * FUNCIÓN: limpiarCarpetasHuerfanas() | ROL: modelo
     * ==================================================
     * =====================DETALLES=====================
     * QUÉ HACE: borra las carpetas de fotos cuyo usuario ya no existe en
     *     la base; devuelve los ids que se eliminaron.
     * VINCULADO A: la invocan scripts/limpiar-fotos-huerfanas.php y el
     *     controlador; cubre bajas hechas por SQL fuera de la aplicación.
     * SI SE ALTERA: las carpetas se nombran id_nick_AAAAMMDD, así que el id
     *     se toma del prefijo antes del primer "_" y no del nombre entero.
     *     default/ y .htaccess se dejan intactos.
     * FECHA: 2026-10-07 | LUGAR: Ocotal, Nueva Segovia
     * ESCRITO POR: ING. DENIS MANUEL LÓPEZ MOLINA.
     * ==================================================
     */
    public static function limpiarCarpetasHuerfanas(): array
    {
        $baseAbsoluta = BASE_PATH . '/' . self::RUTA_FOTOS;

        if (!is_dir($baseAbsoluta)) {
            return [];
        }

        // Ids que siguen vivos en la base: sus carpetas se respetan
        $vivos = array_map('intval', obtenerConexion()
            ->query('SELECT id_usuario FROM usuarios')
            ->fetchAll(PDO::FETCH_COLUMN));

        $eliminados = [];

        foreach (array_diff(scandir($baseAbsoluta), ['.', '..']) as $entrada) {
            // Solo carpetas de usuarios: default/ y archivos sueltos se ignoran
            if (!preg_match('/^(\d+)_/', $entrada)) {
                continue;
            }

            $idUsuario = (int)explode('_', $entrada, 2)[0];
            if (in_array($idUsuario, $vivos, true)) {
                continue;
            }

            $carpeta = $baseAbsoluta . '/' . $entrada;
            $eliminados[$idUsuario] = 0;

            foreach (array_diff(scandir($carpeta), ['.', '..']) as $archivo) {
                if (is_file($carpeta . '/' . $archivo)) {
                    @unlink($carpeta . '/' . $archivo);
                    $eliminados[$idUsuario]++;
                }
            }

            @rmdir($carpeta);
        }

        return $eliminados;
    }
}

/*===========FIN DEL FRAGMENTO DE CÓDIGO============*/