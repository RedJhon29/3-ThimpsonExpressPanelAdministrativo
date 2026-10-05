<?php
/*====================ENCABEZADO====================
MODELO: loginModel — autenticación de administradores
ARCHIVO: Modelos/loginModel.php
==================================================*/

/*=====================DETALLES=====================
QUÉ HACE: valida nick, clave y estado en la tabla usuarios.
VINCULADO A: lo usa Controladores/loginController.php::authenticate();
    depende de obtenerConexion() en Configuracion/conexion.php.
SI SE ALTERA: cambiar columnas o el tipo de hash rompe el login;
    adaptar el controller y re-hashear las claves.
LÍMITES: sin límite de intentos fallidos ni bloqueo por IP.
FECHA: 2026-10-02 | LUGAR: Ocotal, Nueva Segovia
ESCRITO POR: ING. DENIS MANUEL LÓPEZ MOLINA.
==================================================*/

/*================CUERPO DEL CÓDIGO=================*/

class loginModel
{
    /**
     * ====================ENCABEZADO====================
     * FUNCIÓN: buscarPorNickName() | ROL: helper de acceso a datos
     * ==================================================
     * =====================DETALLES=====================
     * QUÉ HACE: devuelve la fila del usuario con ese nick o null si
     *           no existe.
     * VINCULADO A: lo llama loginController::authenticate(); consulta
     *           la tabla usuarios de PostgreSQL.
     * SI SE ALTERA: si cambian nombres de columna, la lectura falla y
     *           el login responde credenciales inválidas.
     * FECHA: 2026-10-02 | LUGAR: Ocotal, Nueva Segovia
     * ESCRITO POR: ING. DENIS MANUEL LÓPEZ MOLINA.
     * ==================================================
     */
    public static function buscarPorNickName(string $nick): ?array
    {
        $sentencia = obtenerConexion()->prepare(
            'SELECT id_usuario, tipo_usuario, descripcion_usuario, nick_name,
                    clave_usuario, foto_usuario, ultimo_login, estado_usuario
               FROM usuarios
              WHERE nick_name = ?'
        );
        $sentencia->execute([$nick]);
        $fila = $sentencia->fetch(PDO::FETCH_ASSOC);

        return $fila === false ? null : $fila;
    }

    /**
     * ====================ENCABEZADO====================
     * FUNCIÓN: verificarClave() | ROL: helper de acceso a datos
     * ==================================================
     * =====================DETALLES=====================
     * QUÉ HACE: compara la clave escrita con el hash Argon2id o bcrypt.
     * VINCULADO A: lo llama loginController::authenticate() con la fila
     *           devuelta por buscarPorNickName().
     * SI SE ALTERA: si se cambia el algoritmo de hash, re-hashear todas
     *           las claves o el login falla para todos.
     * FECHA: 2026-10-02 | LUGAR: Ocotal, Nueva Segovia
     * ESCRITO POR: ING. DENIS MANUEL LÓPEZ MOLINA.
     * ==================================================
     */
    public static function verificarClave(string $clave, string $hash): bool
    {
        // password_verify soporta el formato Argon2id de clave_usuario y también bcrypt
        return password_verify($clave, $hash);
    }

    /**
     * ====================ENCABEZADO====================
     * FUNCIÓN: registrarUltimoLogin() | ROL: helper de acceso a datos
     * ==================================================
     * =====================DETALLES=====================
     * QUÉ HACE: actualiza ultimo_login del usuario con la hora actual.
     * VINCULADO A: lo llama loginController::authenticate() después de
     *           abrir sesión; escribe en la tabla usuarios.
     * SI SE ALTERA: si se renombra ultimo_login, el UPDATE lanza
     *           PDOException y el login queda a medias.
     * FECHA: 2026-10-02 | LUGAR: Ocotal, Nueva Segovia
     * ESCRITO POR: ING. DENIS MANUEL LÓPEZ MOLINA.
     * ==================================================
     */
    public static function registrarUltimoLogin(int $idUsuario): void
    {
        // now() usa el reloj del servidor: evita mezclar la zona horaria de PHP con la de la columna
        $sentencia = obtenerConexion()->prepare(
            'UPDATE usuarios SET ultimo_login = now() WHERE id_usuario = ?'
        );
        $sentencia->execute([$idUsuario]);
    }
}

/*===========FIN DEL FRAGMENTO DE CÓDIGO============*/