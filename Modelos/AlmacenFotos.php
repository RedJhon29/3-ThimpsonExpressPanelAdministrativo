<?php
/*====================ENCABEZADO====================
MODELO: AlmacenFotos — guardián de las fotos de usuario
ARCHIVO: Modelos/AlmacenFotos.php
==================================================*/

/*=====================DETALLES=====================
QUÉ HACE: valida, guarda y borra las fotos de los usuarios
    en disco, una carpeta por usuario, y detecta las carpetas
    que quedaron sin usuario.
VINCULADO A: lo llama usuariosController (alta, edición y
    borrado) y scripts/limpiar-fotos-huerfanas.php; la base
    de datos solo guarda la ruta relativa que devuelve.
SI SE ALTERA: cambiar RUTA_BASE deja huérfanas las carpetas
    ya creadas; por eso el barrido compara contra esa misma ruta.
FECHA: 2026-10-05 | LUGAR: Ocotal, Nueva Segovia
ESCRITO POR: ING. DENIS MANUEL LÓPEZ MOLINA.
==================================================*/

/*================CUERPO DEL CÓDIGO=================*/

class AlmacenFotos {
    /**
     * Carpeta raíz de las fotos, relativa a BASE_PATH. Cada usuario tiene
     * una subcarpeta con su id: así una foto nunca se mezcla con otra.
     */
    public const RUTA_BASE = 'Publico/Recursos/uploads/usuarios';

    /** MIME real del archivo -> extensión con que se guarda. */
    private const MIMES = [
        'image/png'  => 'png',
        'image/jpeg' => 'jpg',
        'image/webp' => 'webp',
    ];

    /** 2 MB: por encima se rechaza antes de escribir en disco. */
    private const TAMANO_MAXIMO = 2097152;

    /**
     * ====================ENCABEZADO====================
     * FUNCIÓN: hayArchivo() | ROL: modelo
     * ==================================================
     * =====================DETALLES=====================
     * QUÉ HACE: indica si en el campo file venía realmente un archivo.
     * VINCULADO A: lo llaman usuariosController::guardar() y
     *     actualizar() antes de decidir si hay que tocar el disco.
     * SI SE ALTERA: si contesta mal, una subida vacía se procesa como
     *     archivo válido y guardar() lanzaría por falta de tmp_name.
     * FECHA: 2026-10-05 | LUGAR: Ocotal, Nueva Segovia
     * ESCRITO POR: ING. DENIS MANUEL LÓPEZ MOLINA.
     * ==================================================
     */
    public static function hayArchivo(array $archivo): bool
    {
        return isset($archivo['error']) && $archivo['error'] !== UPLOAD_ERR_NO_FILE;
    }

    /**
     * ====================ENCABEZADO====================
     * FUNCIÓN: validar() | ROL: modelo
     * ==================================================
     * =====================DETALLES=====================
     * QUÉ HACE: revisa el archivo subido sin escribir nada en disco.
     * VINCULADO A: la invocan los controles de alta y edición antes de
     *     tocar la base, para no dejar registros a medias.
     * SI SE ALTERA: valida el MIME por contenido con finfo, nunca por
     *     extensión; un tipo nuevo se agrega en self::MIMES.
     * FECHA: 2026-10-05 | LUGAR: Ocotal, Nueva Segovia
     * ESCRITO POR: ING. DENIS MANUEL LÓPEZ MOLINA.
     * ==================================================
     */
    public static function validar(array $archivo): void
    {
        if (!self::hayArchivo($archivo)) {
            return;
        }

        if ($archivo['error'] !== UPLOAD_ERR_OK) {
            throw new RuntimeException('No se pudo subir el archivo (código ' . $archivo['error'] . ').');
        }

        if ($archivo['size'] > self::TAMANO_MAXIMO) {
            throw new RuntimeException('La foto supera el máximo de 2 MB.');
        }

        $info = new finfo(FILEINFO_MIME_TYPE);

        if (!isset(self::MIMES[$info->file($archivo['tmp_name'])])) {
            throw new RuntimeException('La foto debe ser un archivo PNG, JPEG o WEBP.');
        }
    }

    /**
     * ====================ENCABEZADO====================
     * FUNCIÓN: guardar() | ROL: modelo
     * ==================================================
     * =====================DETALLES=====================
     * QUÉ HACE: escribe la foto en la carpeta del usuario y devuelve
     *     la ruta relativa que se guarda en la base de datos.
     * VINCULADO A: lo llama usuariosController tras validar(); el name
     *     del campo es foto_usuario. Exige validar() antes.
     * SI SE ALTERA: el nombre lo genera el servidor con random_bytes;
     *     usar el nombre que envía el cliente permitiría escribir rutas.
     * FECHA: 2026-10-05 | LUGAR: Ocotal, Nueva Segovia
     * ESCRITO POR: ING. DENIS MANUEL LÓPEZ MOLINA.
     * ==================================================
     */
    public static function guardar(array $archivo, int $idUsuario): string
    {
        if (!self::hayArchivo($archivo) || $archivo['error'] !== UPLOAD_ERR_OK) {
            throw new RuntimeException('No se recibió ningún archivo.');
        }

        $info = new finfo(FILEINFO_MIME_TYPE);
        $extension = self::MIMES[$info->file($archivo['tmp_name'])];
        $carpetaRelativa = self::carpetaDe($idUsuario);
        $carpetaAbsoluta = BASE_PATH . '/' . $carpetaRelativa;

        if (!is_dir($carpetaAbsoluta) && !mkdir($carpetaAbsoluta, 0755, true) && !is_dir($carpetaAbsoluta)) {
            throw new RuntimeException('No se pudo crear la carpeta de la imagen.');
        }

        $nombreArchivo = 'usuario_' . bin2hex(random_bytes(8)) . '.' . $extension;

        if (!move_uploaded_file($archivo['tmp_name'], $carpetaAbsoluta . '/' . $nombreArchivo)) {
            throw new RuntimeException('No se pudo guardar la imagen en el servidor.');
        }

        return $carpetaRelativa . '/' . $nombreArchivo;
    }

    /**
     * ====================ENCABEZADO====================
     * FUNCIÓN: eliminar() | ROL: modelo
     * ==================================================
     * =====================DETALLES=====================
     * QUÉ HACE: borra la foto del disco y, si su carpeta queda vacía,
     *     también la elimina.
     * VINCULADO A: la llaman usuariosController al reemplazar la foto,
     *     al borrar un usuario y al borrar varios; y el script de barredora.
     * SI SE ALTERA: solo toca rutas dentro de self::RUTA_BASE; con otro
     *     prefijo, un valor manipulado podría borrar archivos ajenos.
     * FECHA: 2026-10-05 | LUGAR: Ocotal, Nueva Segovia
     * ESCRITO POR: ING. DENIS MANUEL LÓPEZ MOLINA.
     * ==================================================
     */
    public static function eliminar(string $rutaFoto): void
    {
        $prefijo = self::RUTA_BASE . '/';

        if ($rutaFoto === '' || !str_starts_with($rutaFoto, $prefijo)) {
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
     * FUNCIÓN: carpetaDe() | ROL: modelo
     * ==================================================
     * =====================DETALLES=====================
     * QUÉ HACE: devuelve la ruta relativa de la carpeta de un usuario.
     * VINCULADO A: la usa guardar() y la barredora de huérfanos; cada
     *     usuario tiene una carpeta y así sus fotos quedan contenidas.
     * SI SE ALTERA: cambiar el patrón deja huérfanas las carpetas ya
     *     creadas; hay que correr antes la barredora.
     * FECHA: 2026-10-05 | LUGAR: Ocotal, Nueva Segovia
     * ESCRITO POR: ING. DENIS MANUEL LÓPEZ MOLINA.
     * ==================================================
     */
    public static function carpetaDe(int $idUsuario): string
    {
        return self::RUTA_BASE . '/' . $idUsuario;
    }

    /**
     * ====================ENCABEZADO====================
     * FUNCIÓN: limpiarHuerfanos() | ROL: modelo
     * ==================================================
     * =====================DETALLES=====================
     * QUÉ HACE: borra las carpetas de fotos cuyo usuario ya no existe
     *     en la base; devuelve los ids que se eliminaron.
     * VINCULADO A: lo invocan scripts/limpiar-fotos-huerfanas.php y el
     *     controlador; cubre bajas hechas por SQL fuera de la aplicación.
     * SI SE ALTERA: solo considera carpetas con nombre numérico dentro
     *     de RUTA_BASE; cualquier otra carpeta se deja intacta.
     * FECHA: 2026-10-05 | LUGAR: Ocotal, Nueva Segovia
     * ESCRITO POR: ING. DENIS MANUEL LÓPEZ MOLINA.
     * ==================================================
     */
    public static function limpiarHuerfanos(): array
    {
        $baseAbsoluta = BASE_PATH . '/' . self::RUTA_BASE;

        if (!is_dir($baseAbsoluta)) {
            return [];
        }

        // Ids que siguen vivos en la base: sus carpetas se respetan
        $vivos = array_map('intval', obtenerConexion()
            ->query('SELECT id_usuario FROM usuarios')
            ->fetchAll(PDO::FETCH_COLUMN));

        $eliminados = [];

        foreach (array_diff(scandir($baseAbsoluta), ['.', '..']) as $entrada) {
            if (!ctype_digit($entrada)) {
                continue; // .htaccess u otras carpetas: no se tocan
            }

            $idUsuario = (int)$entrada;
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