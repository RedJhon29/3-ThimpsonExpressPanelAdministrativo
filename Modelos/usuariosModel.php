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
}

/*===========FIN DEL FRAGMENTO DE CÓDIGO============*/