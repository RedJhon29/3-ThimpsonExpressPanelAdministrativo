<?php
/*====================ENCABEZADO====================
EXCEPCIÓN: errorAplicacionModel — fallo previsto con clave legible
ARCHIVO: Modelos/errorAplicacionModel.php
==================================================*/

/*=====================DETALLES=====================
QUÉ HACE: lanza los errores que la aplicación anticipa
    (validación, disco, permisos) identified by a key, para
    que la vista los muestre en lenguaje natural y no con
    códigos técnicos.
VINCULADO A: la lanzan Modelos/almacenFotosModel.php y la
    traducen usuariosController::traducirError() y loginController.
SI SE ALTERA: si cambia el nombre de una clave, hay que
    actualizarla en el mapa de traducciones del controlador o
    el usuario vería un mensaje genérico.
FECHA: 2026-10-05 | LUGAR: Ocotal, Nueva Segovia
ESCRITO POR: ING. DENIS MANUEL LÓPEZ MOLINA.
==================================================*/

/*================CUERPO DEL CÓDIGO=================*/

class errorAplicacionModel extends RuntimeException {
    /**
     * Claves que usa la aplicación. El texto técnico queda en $detalle
     * para el registro; lo que ve el usuario sale del mapa de traducciones.
     */
    public const NICK_DUPLICADO      = 'nick_duplicado';
    public const NICK_INEXISTENTE     = 'nick_inexistente';
    public const CLAVE_CORTA          = 'clave_corta';
    public const CLAVE_LARGA          = 'clave_larga';
    public const CLAVE_VACIA          = 'clave_vacia';
    public const FOTO_PESADA          = 'foto_pesada';
    public const FOTO_TIPO_INVALIDO   = 'foto_tipo_invalido';
    public const FOTO_NO_RECIBIDA     = 'foto_no_recibida';
    public const FOTO_NO_GUARDADA     = 'foto_no_guardada';
    public const FOTO_CARPETA         = 'foto_carpeta';
    public const FOTO_SUBIDA_FALLIDA  = 'foto_subida_fallida';
    public const USUARIO_NO_EXISTE    = 'usuario_no_existe';
    public const SIN_PERMISO          = 'sin_permiso';
    public const SESION_EXPIRADA      = 'sesion_expirada';
    public const SIN_USUARIOS         = 'sin_usuarios';
    public const ERROR_DE_GUARDADO    = 'error_de_guardado';

    /**
     * ====================ENCABEZADO====================
     * FUNCIÓN: __construct() | ROL: excepción
     * ==================================================
     * =====================DETALLES=====================
     * QUÉ HACE: guarda la clave del fallo y su detalle técnico.
     * VINCULADO A: la construyen los throw de almacenFotosModel.php y
     *     usuariosController; la clave busca el texto que ve el usuario.
     * SI SE ALTERA: si detalle se deja vacío, el registro pierde
     *     información del fallo real.
     * FECHA: 2026-10-05 | LUGAR: Ocotal, Nueva Segovia
     * ESCRITO POR: ING. DENIS MANUEL LÓPEZ MOLINA.
     * ==================================================
     */
    public function __construct(
        public readonly string $clave,
        string $detalle = '',
        int $codigo = 422
    ) {
        parent::__construct($detalle !== '' ? $detalle : $clave, $codigo);
    }
}

/*===========FIN DEL FRAGMENTO DE CÓDIGO============*/