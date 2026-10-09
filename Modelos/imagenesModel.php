<?php
/*====================ENCABEZADO====================
MODELO: imagenesModel — gestión de archivos de imagen del panel
ARCHIVO: Modelos/imagenesModel.php
==================================================*/

/*=====================DETALLES=====================
QUÉ HACE: valida, guarda, reemplaza y borra las imágenes que
    suben los distintos módulos del panel. Cada módulo tiene su
    propia carpeta dentro de uploads/ y sus propias imágenes por
    defecto.
VINCULADO A: lo llaman los controladores de cada módulo al crear
    o editar un registro; depende de BASE_PATH y del .htaccess de
    Publico/Recursos/uploads/, que impide ejecutar scripts ahí.
SI SE ALTERA: la raíz no puede cambiar de sitio porque el
    .htaccess raíz solo sirve lo que está bajo Publico/; un módulo
    nuevo se agrega pasando su nombre, no creando otro modelo.
LÍMITES: valida el MIME real con finfo, nunca la extensión que
    envía el cliente. El nombre del archivo lo genera el servidor.
FECHA: 2026-10-07 | LUGAR: Ocotal, Nueva Segovia
ESCRITO POR: ING. DENIS MANUEL LÓPEZ MOLINA.
==================================================*/

/*================CUERPO DEL CÓDIGO=================*/

class imagenesModel {
    /**
     * Raíz de todo lo que se sube. Debe quedar bajo Publico/ porque el
     * .htaccess raíz solo deja servir esa carpeta, y las imágenes
     * necesitan ser accesibles por URL para las datatables y reportes.
     */
    public const RUTA_BASE = 'Publico/Recursos/uploads';

    /** Imagen genérica cuando el usuario no sube ninguna. */
    public const DEFAULT_GENERICA = 'Publico/Recursos/uploads/default/default.jpg';

    /** MIME real del archivo -> extensión con que se guarda. */
    private const MIMES = [
        'image/png'  => 'png',
        'image/jpeg' => 'jpg',
        'image/webp' => 'webp',
    ];

    /** 2 MB: por encima se rechaza antes de escribir en disco. */
    private const TAMANO_MAXIMO = 2097152;

    /** Formatos que el usuario puede elegir en el campo file. */
    public const EXTENSIONES_PERMITIDAS = ['png', 'jpg', 'jpeg', 'webp'];

    /**
     * Tope de peso expuesto a las vistas para que el filtro del navegador
     * use el mismo número que valida() en el servidor.
     */
    public const TAMANO_MAXIMO_PUBLICO = 2097152;

    /**
     * Módulos que pueden usar este modelo. Evita que un controlador
     * escriba en una carpeta cualquiera con un nombre inventado.
     */
    private const MODULOS = [
        'usuarios',
        'conductores',
        'socio-conductores',
        'negocios',
    ];

    /**
     * ====================ENCABEZADO====================
     * FUNCIÓN: esModuloValido() | ROL: modelo (privado)
     * ==================================================
     * =====================DETALLES=====================
     * QUÉ HACE: confirma que el módulo pertenece a la lista permitida.
     * VINCULADO A: la llaman las funciones que arman rutas de disco.
     * SI SE ALTERA: si se acepta cualquier nombre, un valor tomado
     *     de la petición escribiría archivos fuera de uploads/.
     * FECHA: 2026-10-07 | LUGAR: Ocotal, Nueva Segovia
     * ESCRITO POR: ING. DENIS MANUEL LÓPEZ MOLINA.
     * ==================================================
     */
    private static function esModuloValido(string $modulo): bool
    {
        return in_array($modulo, self::MODULOS, true);
    }

    /**
     * ====================ENCABEZADO====================
     * FUNCIÓN: carpetaModulo() | ROL: modelo (privado)
     * ==================================================
     * =====================DETALLES=====================
     * QUÉ HACE: devuelve la ruta de la carpeta del módulo, creándola
     *     si hace falta.
     * VINCULADO A: la usan guardar(), usarDefault() y crearCarpeta().
     * SI SE ALTERA: el módulo debe existir en self::MODULOS.
     * FECHA: 2026-10-07 | LUGAR: Ocotal, Nueva Segovia
     * ESCRITO POR: ING. DENIS MANUEL LÓPEZ MOLINA.
     * ==================================================
     */
    private static function carpetaModulo(string $modulo): string
    {
        if (!self::esModuloValido($modulo)) {
            throw new RuntimeException('carpeta_invalida|modulo ' . $modulo);
        }

        $carpetaRelativa = self::RUTA_BASE . '/' . $modulo;
        $carpetaAbsoluta = BASE_PATH . '/' . $carpetaRelativa;

        if (!is_dir($carpetaAbsoluta) && !mkdir($carpetaAbsoluta, 0755, true) && !is_dir($carpetaAbsoluta)) {
            throw new RuntimeException('carpeta_error|mkdir ' . $carpetaAbsoluta, 500);
        }

        return $carpetaRelativa;
    }

    /**
     * ====================ENCABEZADO====================
     * FUNCIÓN: existeArchivo() | ROL: modelo
     * ==================================================
     * =====================DETALLES=====================
     * QUÉ HACE: indica si en el campo file venía realmente un archivo.
     * VINCULADO A: la llaman los controladores antes de decidir si hay
     *     que tocar el disco o conviene dejar la imagen anterior.
     * SI SE ALTERA: si contesta mal, una subida vacía se procesa como
     *     archivo válido y fallaría por falta de tmp_name.
     * FECHA: 2026-10-07 | LUGAR: Ocotal, Nueva Segovia
     * ESCRITO POR: ING. DENIS MANUEL LÓPEZ MOLINA.
     * ==================================================
     */
    public static function existeArchivo(array $archivo): bool
    {
        return isset($archivo['error']) && $archivo['error'] !== UPLOAD_ERR_NO_FILE;
    }

    /**
     * ====================ENCABEZADO====================
     * FUNCIÓN: extensionPermitida() | ROL: modelo
     * ==================================================
     * =====================DETALLES=====================
     * QUÉ HACE: confirma que la extensión que envía el cliente sea una
     *     de las cuatro autorizadas.
     * VINCULADO A: la usan validar() y el filtro del input file en las
     *     vistas; el attribute accept de modales_usuarios.php la replica.
     * SI SE ALTERA: si se acepta una extensión nueva hay queReflectarla
     *     también en self::EXTENSIONES_PERMITIDAS y en el accept del input.
     * FECHA: 2026-10-09 | LUGAR: Ocotal, Nueva Segovia
     * ESCRITO POR: ING. DENIS MANUEL LÓPEZ MOLINA.
     * ==================================================
     */
    public static function extensionPermitida(string $extension): bool
    {
        return in_array(strtolower($extension), self::EXTENSIONES_PERMITIDAS, true);
    }

    /**
     * ====================ENCABEZADO====================
     * FUNCIÓN: leerBytesIniciales() | ROL: modelo
     * ==================================================
     * =====================DETALLES=====================
     * QUÉ HACE: lee los primeros bytes del archivo para comparar la
     *     firma binaria real contra la extensión que dice el cliente.
     * VINCULADO A: la invoca validar(); el equivalente del navegador vive
     *     en leerFirmaBinaria() dentro de Vistas/Usuarios/index.php.
     * SI SE ALTERA: una firma mal puesta deja pasar un archivo que no es
     *     la imagen que su extensión anuncia.
     * FECHA: 2026-10-09 | LUGAR: Ocotal, Nueva Segovia
     * ESCRITO POR: ING. DENIS MANUEL LÓPEZ MOLINA.
     * ==================================================
     */
    private static function leerBytesIniciales(string $ruta, int $cantidad): string
    {
        $manejador = fopen($ruta, 'rb');

        if ($manejador === false) {
            return '';
        }

        $bytes = fread($manejador, $cantidad);
        fclose($manejador);

        return is_string($bytes) ? $bytes : '';
    }

    /**
     * ====================ENCABEZADO====================
     * FUNCIÓN: validarFirmaBinaria() | ROL: modelo
     * ==================================================
     * =====================DETALLES=====================
     * QUÉ HACE: exige que los bytes de cabecera coincidan con la imagen
     *     que la extensión promete, para que un PDF renombrado a .jpg
     *     no fool a finfo.
     * VINCULADO A: la invocan validar() y guardar(); comparte las firmas
     *     con leerFirmaBinaria() del lado del navegador.
     * SI SE ALTERA: una firma incorrecta rechazaría imágenes legítimas o,
     *     al revés, dejaría pasar un archivo disfrazado.
     * FECHA: 2026-10-09 | LUGAR: Ocotal, Nueva Segovia
     * ESCRITO POR: ING. DENIS MANUEL LÓPEZ MOLINA.
     * ==================================================
     */
    private static function validarFirmaBinaria(string $ruta, string $extension): bool
    {
        $firma = self::leerBytesIniciales($ruta, 12);

        return match ($extension) {
            'png'  => str_starts_with($firma, "\x89PNG\r\n\x1a\n"),
            'jpg', 'jpeg' => str_starts_with($firma, "\xFF\xD8\xFF"),
            'webp' => str_starts_with($firma, 'RIFF') && substr($firma, 8, 4) === 'WEBP',
            default => false,
        };
    }

    /**
     * ====================ENCABEZADO====================
     * FUNCIÓN: validar() | ROL: modelo
     * ==================================================
     * =====================DETALLES=====================
     * QUÉ HACE: revisa el archivo subido sin escribir nada en disco.
     * VINCULADO A: la invocan los controladores antes de tocar la
     *     base, para no dejar registros a medias.
     * SI SE ALTERA: valida el MIME por contenido con finfo, nunca por
     *     extensión; un tipo nuevo se agrega en self::MIMES.
     * LÍMITES: lanza RuntimeException con "clave|detalle"; el texto que
     *     ve el usuario lo arma cada controlador con su traducción.
     * FECHA: 2026-10-07 | LUGAR: Ocotal, Nueva Segovia
     * ESCRITO POR: ING. DENIS MANUEL LÓPEZ MOLINA.
     * ==================================================
     */
    public static function validar(array $archivo): void
    {
        if (!self::existeArchivo($archivo)) {
            return;
        }

        if ($archivo['error'] !== UPLOAD_ERR_OK) {
            throw new RuntimeException('foto_subida_fallida|PHP upload error ' . $archivo['error'], 400);
        }

        if ($archivo['size'] > self::TAMANO_MAXIMO) {
            throw new RuntimeException('foto_pesada|tamano ' . $archivo['size'] . ' bytes');
        }

        $extension = strtolower(pathinfo($archivo['name'] ?? '', PATHINFO_EXTENSION));

        if (!self::extensionPermitida($extension)) {
            throw new RuntimeException('foto_extension_invalida|extension ' . $extension);
        }

        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($archivo['tmp_name']);

        if (!isset(self::MIMES[$mime])) {
            throw new RuntimeException('foto_tipo_invalido|mime ' . $mime);
        }

        if (!self::validarFirmaBinaria($archivo['tmp_name'], $extension)) {
            throw new RuntimeException('foto_contenido_invalido|extension ' . $extension . ' no coincide con su contenido');
        }
    }

    /**
     * ====================ENCABEZADO====================
     * FUNCIÓN: nombreCarpeta() | ROL: modelo
     * ==================================================
     * =====================DETALLES=====================
     * QUÉ HACE: arma el nombre id_nick_AAAAMMDD de la carpeta de un
     *     registro. El prefijo numérico permite recuperar el dueño.
     * VINCULADO A: la usan guardar() y usarDefault().
     * SI SE ALTERA: limpiarHuerfanos() toma el id de lo que va antes
     *     del primer "_", así que debe seguir empezando por el id.
     * FECHA: 2026-10-07 | LUGAR: Ocotal, Nueva Segovia
     * ESCRITO POR: ING. DENIS MANUEL LÓPEZ MOLINA.
     * ==================================================
     */
    public static function nombreCarpeta(int $id, string $nombre, ?string $fecha = null): string
    {
        $limpio = preg_replace('/[^A-Za-z0-9_-]/', '', $nombre);
        $limpio = $limpio === '' ? 'registro' : $limpio;

        return $id . '_' . $limpio . '_' . ($fecha ?? date('Ymd'));
    }

    /**
     * ====================ENCABEZADO====================
     * FUNCIÓN: guardar() | ROL: modelo
     * ==================================================
     * =====================DETALLES=====================
     * QUÉ HACE: escribe la imagen en la carpeta del registro y
     *     devuelve la ruta relativa que se guarda en la base.
     * VINCULADO A: la llaman los controladores tras validar(); el name
     *     del campo es el que cada módulo use en su formulario.
     * SI SE ALTERA: el nombre del archivo lo genera el servidor con
     *     random_bytes; usar el que envía el cliente permitiría escribir rutas.
     * FECHA: 2026-10-07 | LUGAR: Ocotal, Nueva Segovia
     * ESCRITO POR: ING. DENIS MANUEL LÓPEZ MOLINA.
     * ==================================================
     */
    public static function guardar(array $archivo, string $modulo, int $id, string $nombre, ?string $fecha = null): string
    {
        if (!self::existeArchivo($archivo) || $archivo['error'] !== UPLOAD_ERR_OK) {
            throw new RuntimeException('foto_no_recibida');
        }

        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($archivo['tmp_name']);
        $extension = self::MIMES[$mime] ?? null;

        if ($extension === null) {
            throw new RuntimeException('foto_tipo_invalido|mime ' . $mime);
        }

        $carpetaRelativa = self::carpetaModulo($modulo) . '/' . self::nombreCarpeta($id, $nombre, $fecha);
        $carpetaAbsoluta = BASE_PATH . '/' . $carpetaRelativa;

        if (!is_dir($carpetaAbsoluta) && !mkdir($carpetaAbsoluta, 0755, true) && !is_dir($carpetaAbsoluta)) {
            throw new RuntimeException('carpeta_error|mkdir ' . $carpetaAbsoluta, 500);
        }

        $nombreArchivo = 'imagen_' . bin2hex(random_bytes(8)) . '.' . $extension;
        $rutaDestino = $carpetaAbsoluta . '/' . $nombreArchivo;

        if (!move_uploaded_file($archivo['tmp_name'], $rutaDestino)) {
            throw new RuntimeException('foto_no_guardada|move_uploaded_file ' . $nombreArchivo, 500);
        }

        // El archivo queda re-codificado por GD: al reconstruir la imagen
        // se descartan la metadata y cualquier dato agregado despues del
        // cierre de la imagen, que es donde se esconde codigo en un PNG
        // renombrado. Si GD no logra, se borra y el alta falla.
        self::recodificarImagen($rutaDestino, $extension);

        return $carpetaRelativa . '/' . $nombreArchivo;
    }

    /**
     * ====================ENCABEZADO====================
     * FUNCIÓN: recodificarImagen() | ROL: modelo
     * ==================================================
     * =====================DETALLES=====================
     * QUÉ HACE: reconstruye la imagen guardada con GD para eliminar la
     *     metadata y los datos agregados tras el cierre del archivo.
     * VINCULADO A: la invoca guardar() justo después del
     *     move_uploaded_file(); es la barrera que impide que un PNG
     *     válido con código PHP pegado al final quede en el disco.
     * SI SE ALTERA: sin esto vuelve a pasar un archivo con contenido
     *     extra; el .htaccess de uploads/ sigue impidiendo ejecutarlo.
     * LÍMITES: si GD no está disponible se deja el archivo como está
     *     (finfo ya validó el tipo) y el alta continúa.
     * FECHA: 2026-10-09 | LUGAR: Ocotal, Nueva Segovia
     * ESCRITO POR: ING. DENIS MANUEL LÓPEZ MOLINA.
     * ==================================================
     */
    private static function recodificarImagen(string $ruta, string $extension): void
    {
        if (!extension_loaded('gd')) {
            return;
        }

        $contenido = file_get_contents($ruta);

        if ($contenido === false) {
            throw new RuntimeException('foto_no_guardada|lectura ' . basename($ruta), 500);
        }

        $origen = @imagecreatefromstring($contenido);

        if ($origen === false) {
            // GD no pudo reconstruirla: se descarta en vez de dejarla.
            @unlink($ruta);
            throw new RuntimeException('foto_contenido_invalido|GD no pudo reconstruir ' . $extension);
        }

        $ancho = imagesx($origen);
        $alto = imagesy($origen);
        $limpia = imagecreatetruecolor($ancho, $alto);

        if ($extension === 'png') {
            imagealphablending($limpia, false);
            imagesavealpha($limpia, true);
            imagecopy($limpia, $origen, 0, 0, 0, 0, $ancho, $alto);
            $resultado = imagepng($limpia, $ruta);
        } else {
            $resultado = imagejpeg($limpia, $ruta, 90);
        }

        imagedestroy($limpia);
        imagedestroy($origen);

        if ($resultado === false) {
            throw new RuntimeException('foto_no_guardada|re-codificacion ' . basename($ruta), 500);
        }
    }

    /**
     * ====================ENCABEZADO====================
     * FUNCIÓN: usarDefault() | ROL: modelo
     * ==================================================
     * =====================DETALLES=====================
     * QUÉ HACE: copia la imagen por defecto del módulo a la carpeta del
     *     registro y devuelve su ruta, para que sin subida las tablas
     *     muestren una imagen y no un cuadro vacío.
     * VINCULADO A: la llaman los controladores cuando no se subió
     *     archivo, o cuando el registro venía con la imagen por defecto.
     * SI SE ALTERA: $imagenOrigen permite que cada módulo use su propio
     *     default; si el archivo no está, falla y el alta se deshace.
     * FECHA: 2026-10-07 | LUGAR: Ocotal, Nueva Segovia
     * ESCRITO POR: ING. DENIS MANUEL LÓPEZ MOLINA.
     * ==================================================
     */
    public static function usarDefault(string $modulo, int $id, string $nombre, ?string $fecha = null, ?string $imagenOrigen = null): string
    {
        $origen = $imagenOrigen ?? self::DEFAULT_GENERICA;

        if (!str_starts_with($origen, self::RUTA_BASE . '/')) {
            throw new RuntimeException('foto_no_guardada|default fuera de uploads: ' . $origen, 500);
        }

        $origenAbsoluto = BASE_PATH . '/' . $origen;

        if (!is_file($origenAbsoluto)) {
            throw new RuntimeException('foto_no_guardada|falta ' . $origen, 500);
        }

        $carpetaRelativa = self::carpetaModulo($modulo) . '/' . self::nombreCarpeta($id, $nombre, $fecha);
        $carpetaAbsoluta = BASE_PATH . '/' . $carpetaRelativa;

        if (!is_dir($carpetaAbsoluta) && !mkdir($carpetaAbsoluta, 0755, true) && !is_dir($carpetaAbsoluta)) {
            throw new RuntimeException('carpeta_error|mkdir ' . $carpetaAbsoluta, 500);
        }

        $nombreArchivo = basename($origen);

        if (!copy($origenAbsoluto, $carpetaAbsoluta . '/' . $nombreArchivo)) {
            throw new RuntimeException('foto_no_guardada|copy ' . $nombreArchivo, 500);
        }

        return $carpetaRelativa . '/' . $nombreArchivo;
    }

    /**
     * ====================ENCABEZADO====================
     * FUNCIÓN: esDefault() | ROL: modelo
     * ==================================================
     * =====================DETALLES=====================
     * QUÉ HACE: indica si la imagen guardada es una copia del default.
     * VINCULADO A: la llaman los controladores al editar, para saber si
     *     hay que crear carpeta o solo reemplazar el archivo.
     * SI SE ALTERA: si devuelve mal, reemplazar una copia del default
     *     intentaría escribir en una carpeta que puede no existir.
     * FECHA: 2026-10-07 | LUGAR: Ocotal, Nueva Segovia
     * ESCRITO POR: ING. DENIS MANUEL LÓPEZ MOLINA.
     * ==================================================
     */
    public static function esDefault(string $rutaImagen): bool
    {
        return $rutaImagen !== '' && str_ends_with($rutaImagen, '/' . basename(self::DEFAULT_GENERICA));
    }

    /**
     * ====================ENCABEZADO====================
     * FUNCIÓN: eliminar() | ROL: modelo
     * ==================================================
     * =====================DETALLES=====================
     * QUÉ HACE: borra la imagen del disco y, si su carpeta queda vacía,
     *     también la elimina.
     * VINCULADO A: la llaman los controladores al reemplazar la
     *     imagen, al borrar un registro y al borrar varios.
     * SI SE ALTERA: solo toca rutas dentro de self::RUTA_BASE; con otro
     *     prefijo, un valor manipulado podría borrar archivos ajenos.
     * FECHA: 2026-10-07 | LUGAR: Ocotal, Nueva Segovia
     * ESCRITO POR: ING. DENIS MANUEL LÓPEZ MOLINA.
     * ==================================================
     */
    public static function eliminar(string $rutaImagen): void
    {
        $prefijo = self::RUTA_BASE . '/';

        if ($rutaImagen === '' || !str_starts_with($rutaImagen, $prefijo)) {
            return;
        }

        // default/ es el origen de las copias por defecto: nunca se borra
        if (str_starts_with($rutaImagen, self::DEFAULT_GENERICA)) {
            return;
        }

        $rutaAbsoluta = BASE_PATH . '/' . $rutaImagen;
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
     * FUNCIÓN: limpiarHuerfanos() | ROL: modelo
     * ==================================================
     * =====================DETALLES=====================
     * QUÉ HACE: borra las carpetas de imágenes cuyo registro ya no
     *     existe en su tabla; devuelve los ids que eliminó.
     * VINCULADO A: la invocan scripts/limpiar-fotos-huerfanas.php y los
     *     controladores; cubre bajas hechas por SQL fuera de la aplicación.
     * SI SE ALTERA: recorre los módulos de self::MODULOS. Las carpetas
     *     se nombran id_nick_AAAAMMDD, así que el id se toma del prefijo
     *     antes del primer "_". default/ y .htaccess se dejan intactos.
     * LÍMITES: $tabla debe existir y traer una columna id de entero.
     *     Cada módulo se limpia con su propia tabla.
     * FECHA: 2026-10-07 | LUGAR: Ocotal, Nueva Segovia
     * ESCRITO POR: ING. DENIS MANUEL LÓPEZ MOLINA.
     * ==================================================
     */
    public static function limpiarHuerfanos(string $modulo, string $tabla, string $columnaId): array
    {
        if (!self::esModuloValido($modulo) || !preg_match('/^[a-z_]+$/', $tabla)) {
            throw new RuntimeException('carpeta_invalida|modulo o tabla no permitido');
        }

        $baseAbsoluta = BASE_PATH . '/' . self::RUTA_BASE . '/' . $modulo;

        if (!is_dir($baseAbsoluta)) {
            return [];
        }

        // Ids que siguen vivos: sus carpetas se respetan
        $vivos = array_map('intval', obtenerConexion()
            ->query("SELECT $columnaId FROM $tabla")
            ->fetchAll(PDO::FETCH_COLUMN));

        $eliminados = [];

        foreach (array_diff(scandir($baseAbsoluta), ['.', '..']) as $entrada) {
            if (!preg_match('/^(\d+)_/', $entrada)) {
                continue;
            }

            $id = (int)explode('_', $entrada, 2)[0];
            if (in_array($id, $vivos, true)) {
                continue;
            }

            $carpeta = $baseAbsoluta . '/' . $entrada;
            $eliminados[$id] = 0;

            foreach (array_diff(scandir($carpeta), ['.', '..']) as $archivo) {
                if (is_file($carpeta . '/' . $archivo)) {
                    @unlink($carpeta . '/' . $archivo);
                    $eliminados[$id]++;
                }
            }

            @rmdir($carpeta);
        }

        return $eliminados;
    }

    /**
     * ====================ENCABEZADO====================
     * FUNCIÓN: url() | ROL: modelo
     * ==================================================
     * =====================DETALLES=====================
     * QUÉ HACE: arma la URL pública de una imagen guardada. Es el
     *     método que las datatables y los reportes deben usar para
     *     mostrar fotos sin repetir el BASE_URL a mano.
     * VINCULADO A: lo usan las vistas y, más adelante, los PDF y
     *     exportaciones que necesiten la imagen.
     * SI SE ALTERA: si devuelve una ruta relativa, las imágenes no
     *     cargarán en ninguna vista.
     * FECHA: 2026-10-07 | LUGAR: Ocotal, Nueva Segovia
     * ESCRITO POR: ING. DENIS MANUEL LÓPEZ MOLINA.
     * ==================================================
     */
    public static function url(string $rutaImagen): string
    {
        return $rutaImagen === '' ? BASE_URL . '/' . self::DEFAULT_GENERICA : BASE_URL . '/' . $rutaImagen;
    }
}

/*===========FIN DEL FRAGMENTO DE CÓDIGO============*/