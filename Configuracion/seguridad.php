<?php
/*====================ENCABEZADO====================
CONFIGURACIÓN: seguridad.php — token anti-CSRF de las sesiones
ARCHIVO: Configuracion/seguridad.php
==================================================*/

/*=====================DETALLES=====================
QUÉ HACE: resuelve el usuario de cada petición contra la base,
    emite y valida un token CSRF, guarda y lee mensajes de
    una sola vez (flash) y corta las peticiones que no son
    POST válido; además contesta JSON cuando el cliente lo pide.
VINCULADO A: lo incluye Configuracion/app.php; lo usan
    index.php (usuarioEnSesion), los Controladores
    (usuarioActual, verificarTokenCsrf, flashMensaje,
    esPeticionPostOthrow, pideRespuestaJson, responderJson)
    y las vistas (crearTokenCsrf).
SI SE ALTERA: si cambia el nombre de la clave de sesión o
    del input, los formularios dejan de validar y se cae la
    protección contra CSRF.
LÍMITES: no protege contra XSS; los value de las vistas
    siguen escapados con htmlspecialchars.
FECHA: 2026-10-05 | LUGAR: Ocotal, Nueva Segovia
ESCRITO POR: ING. DENIS MANUEL LÓPEZ MOLINA.
==================================================*/

/*================CUERPO DEL CÓDIGO=================*/

/**
 * ====================ENCABEZADO====================
 * FUNCIÓN: usuarioActual() | ROL: helper de sesión
 * ==================================================
 * =====================DETALLES=====================
 * QUÉ HACE: devuelve el usuario de la sesión leyéndolo de la
 *           base en cada petición, o null si ya no existe.
 * VINCULADO A: lo usan index.php y los Controladores; toma
 *           el id de $_SESSION y lo contrasta con Usuario::find().
 * SI SE ALTERA: si devuelve datos cacheados sin volver a
 *           consultar, un id de sesión viejo vuelve a ser válido.
 * LÍMITES: consulta una vez por petición; el resultado se
 *           guarda en una estática para no repetir el query.
 * FECHA: 2026-10-05 | LUGAR: Ocotal, Nueva Segovia
 * ESCRITO POR: ING. DENIS MANUEL LÓPEZ MOLINA.
 * ==================================================
 */
function usuarioActual(): ?array
{
    static $resuelto = false;
    static $usuario = null;

    if ($resuelto) {
        return $usuario;
    }
    $resuelto = true;

    $id = (int)($_SESSION['user_id'] ?? 0);
    if ($id <= 0) {
        return null;
    }

    $fila = Usuario::find($id);

    // El id de la sesión tiene que seguir apuntando al mismo usuario:
    // si el id se renumeró o se borró la fila, la sesión no vale.
    if ($fila === null || ($_SESSION['user_nick'] ?? '') !== $fila['nick_name']) {
        return null;
    }

    $usuario = $fila;

    return $usuario;
}

/**
 * ====================ENCABEZADO====================
 * FUNCIÓN: esSuperadminActual() | ROL: helper de sesión
 * ==================================================
 * =====================DETALLES=====================
 * QUÉ HACE: indica si el usuario en sesión es superadministrador,
 *           tomándolo del rol leído en la base y no de la sesión.
 * VINCULADO A: lo usa usuariosController para decidir si puede
 *           eliminar usuarios y para pintar los controles.
 * SI SE ALTERA: si leyera el rol de $_SESSION, un cambio de rol
 *           en la base no se aplicaría hasta el siguiente login.
 * FECHA: 2026-10-05 | LUGAR: Ocotal, Nueva Segovia
 * ESCRITO POR: ING. DENIS MANUEL LÓPEZ MOLINA.
 * ==================================================
 */
function esSuperadminActual(): bool
{
    $usuario = usuarioActual();

    return $usuario !== null && $usuario['tipo_usuario'] === 'superadmin';
}

/**
 * ====================ENCABEZADO====================
 * FUNCIÓN: cerrarSesionInvalida() | ROL: helper de sesión
 * ==================================================
 * =====================DETALLES=====================
 * QUÉ HACE: destruye la sesión y manda al login cuando el usuario
 *           guardado ya no existe o dejó de ser el mismo.
 * VINCULADO A: la llama index.php tras validar el guard; evita que
 *           una sesión con id viejo actúe sobre el panel.
 * SI SE ALTERA: si no corta la petición, index.php seguiría
 *           enrutando con una sesión sin usuario válido.
 * FECHA: 2026-10-05 | LUGAR: Ocotal, Nueva Segovia
 * ESCRITO POR: ING. DENIS MANUEL LÓPEZ MOLINA.
 * ==================================================
 */
function cerrarSesionInvalida(): void
{
    if (usuarioActual() !== null) {
        return;
    }

    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $parametros = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $parametros['path'], $parametros['domain'],
            $parametros['secure'], $parametros['httponly']
        );
    }
    session_destroy();

    header('Location: ' . BASE_URL . '/login');
    exit;
}

/**
 * ====================ENCABEZADO====================
 * FUNCIÓN: pideRespuestaJson() | ROL: helper de petición
 * ==================================================
 * =====================DETALLES=====================
 * QUÉ HACE: indica si el cliente espera JSON en vez de una
 *           redirección (fetch con Accept o cabecera XHR).
 * VINCULADO A: lo consultan las acciones del CRUD para elegir
 *           entre redirigir con flash o contestar un JSON.
 * SI SE ALTERA: si contestara false a un fetch, el navegador
 *           seguiría la redirección y se perdería el mensaje.
 * FECHA: 2026-10-05 | LUGAR: Ocotal, Nueva Segovia
 * ESCRITO POR: ING. DENIS MANUEL LÓPEZ MOLINA.
 * ==================================================
 */
function pideRespuestaJson(): bool
{
    if (($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest') {
        return true;
    }

    return str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json');
}

/**
 * ====================ENCABEZADO====================
 * FUNCIÓN: responderJson() | ROL: helper de petición
 * ==================================================
 * =====================DETALLES=====================
 * QUÉ HACE: contesta JSON y corta la ejecución, para las
 *           acciones que el JavaScript maneja sin recargar.
 * VINCULADO A: lo llaman usuariosController y loginController
 *           cuando pideRespuestaJson() es verdadero.
 * SI SE ALTERA: si no cortara con exit, la página seguiría
 *           emitiendo HTML después del JSON.
 * FECHA: 2026-10-05 | LUGAR: Ocotal, Nueva Segovia
 * ESCRITO POR: ING. DENIS MANUEL LÓPEZ MOLINA.
 * ==================================================
 */
function responderJson(array $datos, int $codigo = 200): void
{
    http_response_code($codigo);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($datos, JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * ====================ENCABEZADO====================
 * FUNCIÓN: crearTokenCsrf() | ROL: helper de seguridad
 * ==================================================
 * =====================DETALLES=====================
 * QUÉ HACE: devuelve el token CSRF de la sesión y lo crea si no existe.
 * VINCULADO A: lo invocan las vistas al pintar el input oculto; comparte
 *     clave con verificarTokenCsrf().
 * SI SE ALTERA: si la clave de sesión cambia, hay que cambiarla también
 *     en verificarTokenCsrf() o ninguna validación pasará nunca.
 * FECHA: 2026-10-05 | LUGAR: Ocotal, Nueva Segovia
 * ESCRITO POR: ING. DENIS MANUEL LÓPEZ MOLINA.
 * ==================================================
 */
function crearTokenCsrf(): string
{
    if (empty($_SESSION['csrf_token'])) {
        // random_bytes es criptográficamente seguro; bin2hex lo vuelve imprimible
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

/**
 * ====================ENCABEZADO====================
 * FUNCIÓN: verificarTokenCsrf() | ROL: helper de seguridad
 * ==================================================
 * =====================DETALLES=====================
 * QUÉ HACE: compara en tiempo constante el token recibido con el de la sesión.
 * VINCULADO A: lo llaman los Controladores antes de ejecutar un POST que
 *     escribe en la base; lee el campo csrf_token del formulario.
 * SI SE ALTERA: si devuelve true sin comparar, se pierde la protección
 *     completa contra CSRF.
 * FECHA: 2026-10-05 | LUGAR: Ocotal, Nueva Segovia
 * ESCRITO POR: ING. DENIS MANUEL LÓPEZ MOLINA.
 * ==================================================
 */
function verificarTokenCsrf(?string $tokenRecibido): bool
{
    $tokenSesion = $_SESSION['csrf_token'] ?? '';

    if ($tokenSesion === '' || $tokenRecibido === null || $tokenRecibido === '') {
        return false;
    }

    // hash_equals evita que el tiempo de comparación revele el token
    return hash_equals($tokenSesion, $tokenRecibido);
}

/**
 * ====================ENCABEZADO====================
 * FUNCIÓN: flashMensaje() | ROL: helper de sesión
 * ==================================================
 * =====================DETALLES=====================
 * QUÉ HACE: sin nombre, guarda el valor para la próxima carga; con nombre,
 *     lo devuelve y lo borra de la sesión.
 * VINCULADO A: lo usan los Controladores para pasar avisos, errores de
 *     validación y valores repintados a Vistas/Usuarios/.
 * SI SE ALTERA: si no borra la clave al leer, el mismo aviso se repite
 *     en cada recarga de la página.
 * FECHA: 2026-10-05 | LUGAR: Ocotal, Nueva Segovia
 * ESCRITO POR: ING. DENIS MANUEL LÓPEZ MOLINA.
 * ==================================================
 */
function flashMensaje(string $clave = 'mensaje', mixed $valor = null): mixed
{
    // Dos argumentos = escritura
    if (func_num_args() > 1) {
        $_SESSION['flash_' . $clave] = $valor;
        return null;
    }

    // Sin argumentos = lectura del mensaje genérico
    $claveLeida = func_num_args() === 0 ? 'mensaje' : $clave;
    $guardado = $_SESSION['flash_' . $claveLeida] ?? null;
    unset($_SESSION['flash_' . $claveLeida]);

    return $guardado;
}

/**
 * ====================ENCABEZADO====================
 * FUNCIÓN: esPeticionPostOthrow() | ROL: helper de petición
 * ==================================================
 * =====================DETALLES=====================
 * QUÉ HACE: exige método POST y token CSRF válido; si algo falla, avisa y
 *     corta la ejecución con 405 o 403.
 * VINCULADO A: lo invocan las acciones de escritura de
 *     usuariosController antes de tocar la base de datos.
 * SI SE ALTERA: si dejara pasar un GET, las acciones destructivas
 *     quedarían exposed a CSRF por link o imagen externa.
 * FECHA: 2026-10-05 | LUGAR: Ocotal, Nueva Segovia
 * ESCRITO POR: ING. DENIS MANUEL LÓPEZ MOLINA.
 * ==================================================
 */
function esPeticionPostOthrow(bool $tokenValido): bool
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        header('Allow: POST');
        exit('Método no permitido: esta acción requiere POST.');
    }

    if (!$tokenValido) {
        http_response_code(403);
        exit('Token de seguridad inválido o expirado. Recargá la página e intentá de nuevo.');
    }

    return true;
}

/*===========FIN DEL FRAGMENTO DE CÓDIGO============*/